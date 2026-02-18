# Image Upload Reliability and Testing

## Current validation and error handling

### Admin Image Manager (`admin/controller/common/filemanager.php`)

| Check | Behavior |
|-------|----------|
| **Permission** | Requires `modify` on `common/filemanager`. |
| **Directory** | Target must exist and be under `DIR_IMAGE . 'catalog'` (no path traversal). |
| **Filename length** | 3–255 characters. |
| **Extension** | Allowed: `jpg`, `jpeg`, `gif`, `png`. |
| **MIME type** | Allowed: `image/jpeg`, `image/pjpeg`, `image/png`, `image/x-png`, `image/gif`. |
| **File size** | Max 10MB (10485760 bytes). |
| **PHP upload errors** | Uses `error_upload_1` … `error_upload_8` from language. |
| **Move** | On `move_uploaded_file` failure, returns generic `error_upload`. |
| **Post-save** | `chmod($destination, 0644)`; cache delete for `image`. |

**Gaps:** No server-side check that the file is a real image (e.g. `getimagesize()`). MIME is client-derived and can be spoofed; extension + MIME are the only validations.

### Catalog (seller) filemanager (`catalog/controller/extension/common/filemanager.php`)

| Check | Behavior |
|-------|----------|
| **Directory** | Same as admin (under `DIR_IMAGE . 'catalog'`). |
| **Filename length** | 3–255 characters. |
| **Extension** | Same allowed list: jpg, jpeg, gif, png. |
| **MIME type** | Same allowed list. |
| **PHP upload errors** | Uses `error_upload_<code>` (from main language if not in filemanager). |
| **File size** | Now enforced (e.g. 10MB) after hardening. |
| **Move** | Now checked; error set on failure after hardening. |

**Gaps (before hardening):** No size limit; `move_uploaded_file` return value ignored. Single-file upload must be supported (same pattern as admin).

### General tool upload (`catalog/controller/tool/upload.php`, `admin/controller/tool/upload.php`)

- Used for arbitrary files (e.g. order uploads), not only images.
- Validates extension and MIME from store config (`config_file_ext_allowed`, `config_file_mime_allowed`).
- Rejects content containing `<?php`.
- Stores file under `DIR_UPLOAD` with a tokenized name; returns a `code` for reference.

---

## Stability considerations

1. **PHP limits** – `upload_max_filesize` and `post_max_size` in `php.ini` must be at least 10MB (or your chosen limit) or uploads will fail before reaching application code.
2. **Disk space** – Failed or full disk can make `move_uploaded_file` fail; the app now surfaces this as an error where the move is checked.
3. **Concurrent uploads** – Sellers get unique filenames (timestamp suffix in catalog filemanager); admin does not (same name overwrites).
4. **Directory creation** – Admin/filemanager does not create missing directories; the target directory must exist and be writable.

---

## How to test image upload

### 1. Admin Image Manager (Design > Image Manager or product image picker)

**Environment:** Log in as admin; open Image Manager or add/edit product and open the image browser.

| # | Test | Expected |
|---|------|----------|
| 1 | Upload valid JPEG (e.g. &lt; 10MB, .jpg) | Success message; image appears in list and can be selected. |
| 2 | Upload valid PNG, GIF | Same as above. |
| 3 | Upload file &gt; 10MB | Error: file size exceeds maximum (10MB). |
| 4 | Upload .php renamed to .jpg (and send as image MIME) | Accepted (no getimagesize check). For security, consider adding server-side image validation. |
| 5 | Upload with extension .exe or .pdf | Error: incorrect file type. |
| 6 | Upload with MIME text/plain or application/pdf | Error: incorrect file type. |
| 7 | Filename length 1–2 chars | Error: filename must be between 3 and 255. |
| 8 | Omit file (empty upload) | Error (e.g. file could not be uploaded). |
| 9 | Try directory outside catalog (e.g. `directory=../../../etc`) | Error: directory does not exist (path traversal blocked). |
| 10 | No permission (user without filemanager modify) | Permission denied. |

### 2. Catalog (seller) filemanager

**Environment:** Log in as seller; go to product add/edit and use the image upload / file manager.

| # | Test | Expected |
|---|------|----------|
| 1 | Upload valid image (JPEG/PNG/GIF, &lt; 10MB) | Success; image appears. |
| 2 | Upload file &gt; 10MB | Error: file size exceeds maximum. |
| 3 | Wrong extension or MIME | Error: incorrect file type. |
| 4 | Single file upload | Handled and saved (single-file path implemented). |
| 5 | Multiple files | All valid files uploaded; errors shown for invalid ones where implemented. |
| 6 | move_uploaded_file fails (e.g. read-only dir) | Error message instead of silent failure. |

### 3. Manual test steps (quick checklist)

1. **Admin**
   - Open admin > Design > Image Manager (or Catalog > Products > Edit product > Image).
   - Upload a small .jpg: expect success.
   - Upload a 12MB image: expect “file size exceeds maximum” (or PHP limit error if PHP limits are lower).
   - Upload a .txt file renamed to .jpg: expect “incorrect file type” if MIME is not image/*.

2. **Seller (catalog)**
   - Log in as seller; add or edit a product.
   - Open the image/file manager and upload one image: expect success.
   - Upload an oversized file: expect size error.

3. **PHP limits**
   - In `php.ini`: set `upload_max_filesize = 10M` and `post_max_size = 12M` (or higher) so application limits can be exercised.

### 4. Optional: automated script (simulate upload endpoint)

You can simulate the upload endpoint with `curl` to test validation and error responses (no browser):

```bash
# From project root; requires a real image file and a valid admin session (cookie or token)
# Replace ADMIN_SESSION_COOKIE with your session cookie if testing admin filemanager.

# Success (small valid image)
curl -s -X POST -b "OCSESSID=YOUR_SESSION" \
  -F "file=@/path/to/valid.jpg" \
  "http://localhost:8000/admin/index.php?route=common/filemanager/upload" \
  | head -c 500

# Oversized (create 11MB file and upload)
dd if=/dev/zero of=/tmp/big.jpg bs=1M count=11
curl -s -X POST -b "OCSESSID=YOUR_SESSION" \
  -F "file=@/tmp/big.jpg" \
  "http://localhost:8000/admin/index.php?route=common/filemanager/upload" \
  | head -c 500
```

Expect JSON with either `"success"` or `"error"` and verify the message matches the test case.

---

## Summary

- **Validation:** Extension, MIME, filename length, file size (admin and catalog), directory scope, and PHP upload error codes.
- **Error handling:** Language keys for upload errors; move failure and size limit return clear errors after catalog hardening.
- **Stability:** Depends on PHP limits, disk space, and writable target directory; behavior is consistent when validations and move result are checked.
- **Testing:** Use the tables above for manual tests; optionally use `curl` to hit the upload route and assert success/error JSON for each scenario.
