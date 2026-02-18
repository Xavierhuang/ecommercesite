# Where to Deploy and How

Your code lives in this repo. The contract defines:

- **Production (prod):** diversiply.co (live, public site)
- **Development (dev):** tkurydyfjkv.diversiply.co (subdomain; deploy here first, then push to prod)

---

## 1. Server folder locations (DreamHost) – confirmed

From your `ls` on the server:

| Environment | Path | URL |
|-------------|------|-----|
| **Prod** | `/home/dh_rasa5k/diversiply.co/` | https://diversiply.co/ |
| **Dev** | `/home/dh_rasa5k/diversiply.co/tkurydyfjkv/` | https://tkurydyfjkv.diversiply.co/ (or http:// if SSL not yet enabled) |

Dev is a **subdirectory** of the prod folder. The dev folder currently has: `admin/`, `catalog/`, `config.php`, `image/`, `index.php`, `system/`, `stripe-lib`, `twitter/`, `vendor/`, `.htaccess`, etc. It does **not** have a `storage/` directory; OpenCart needs `storage/` (cache, logs, modification, session, upload). When you deploy, include `storage/` from your repo (or create it on the server and set permissions) so the dev site works.

---

## 2. Which folder to deploy your code to

- **To update the dev site (tkurydyfjkv.diversiply.co):**  
  Deploy into **`/home/dh_rasa5k/diversiply.co/tkurydyfjkv/`**.  
  Do **not** deploy into the prod root if you only want to change dev. Include the **`storage/`** directory (your repo has it; the current server dev copy does not).

- **To update production (diversiply.co):**  
  Deploy into **`/home/dh_rasa5k/diversiply.co/`**.  
  Contract: implement on dev first, then push the same changes to prod.

---

## 3. Config files that define “where” the site runs

These files control URLs and paths. **Do not overwrite server config with your local copies**; adjust them for the server (or use env-specific copies).

| File | What to set on server |
|------|------------------------|
| **config.php** (catalog) | `HTTP_SERVER` / `HTTPS_SERVER`: **Dev:** `https://tkurydyfjkv.diversiply.co/` **Prod:** `https://diversiply.co/` |
| **admin/config.php** | `HTTP_SERVER` / `HTTP_CATALOG` / `HTTPS_SERVER` / `HTTPS_CATALOG`: same base URL as above (admin = base + `admin/`, catalog = base). |
| Both | `DIR_*`: use `$base = dirname(__FILE__) . '/';` (or `dirname(dirname(__FILE__)) . '/'` in admin) so paths stay correct in the deployed folder. |
| Both | `DB_*`: DreamHost DB host, database name, user, password (from panel or client). |

Example for **dev** (tkurydyfjkv.diversiply.co subdomain), if the app is in `~/diversiply.co/tkurydyfjkv/`:

```php
// config.php (catalog) on DEV
define('HTTP_SERVER', 'https://tkurydyfjkv.diversiply.co/');
define('HTTPS_SERVER', 'https://tkurydyfjkv.diversiply.co/');
// DIR_* via $base is fine as-is if you deploy the full tree
// DB_* = server credentials
```

Example for **prod** (diversiply.co), if the app is in `~/diversiply.co/`:

```php
// config.php (catalog) on PROD
define('HTTP_SERVER', 'https://diversiply.co/');
define('HTTPS_SERVER', 'https://diversiply.co/');
```

So: the **folder** you deploy to (and the **config.php / admin/config.php** in that folder) is what “points” the site at dev vs prod.

---

## 4. Check before you deploy (don't overwrite blindly)

Before running rsync or uploading code:

1. **Confirm the target**  
   - Dev: `tkurydyfjkv.diversiply.co` = path `~/diversiply.co/tkurydyfjkv/`  
   - Prod: `diversiply.co` = path `~/diversiply.co/`  
   Full rsync overwrites all files in that path (except excludes). Wrong target = wrong site updated.

2. **See what would change (dry run)**  
   Add `--dry-run -n` to rsync to list what would be copied without changing any files:
   ```bash
   rsync -avzn --exclude '.git' --exclude 'node_modules' --exclude 'storage/logs/*' \
     /Users/weijiahuang/Desktop/dev/ \
     dh_rasa5k@iad1-shared-e1-18.dreamhost.com:diversiply.co/
   ```
   Use `diversiply.co/tkurydyfjkv/` for dev. Review the list before running without `-n`.

3. **Check server files before overwriting**  
   SSH in and inspect (or download) files you care about so you don't lose server-only changes:
   ```bash
   ssh dh_rasa5k@iad1-shared-e1-18.dreamhost.com \
     "cat diversiply.co/config.php" > /tmp/prod_config.php
   ```
   Compare with your local copy. Do the same for `admin/config.php` if needed. **Do not overwrite server config with local** unless you then edit the deployed config for that environment (URLs, DB_*).

4. **Optional: backup on server before deploy**  
   On the server, copy config aside before deploy:
   ```bash
   ssh dh_rasa5k@iad1-shared-e1-18.dreamhost.com
   cd ~/diversiply.co
   cp config.php config.php.bak.$(date +%Y%m%d) ; cp admin/config.php admin/config.php.bak.$(date +%Y%m%d)
   ```
   Use `~/diversiply.co/tkurydyfjkv/` for dev. Restore from `.bak.*` if deploy overwrote and broke config.

5. **Single-file or small updates**  
   To update only one file (e.g. after testing locally), rsync just that file so you don't touch the rest:
   ```bash
   rsync -avz -e ssh /Users/weijiahuang/Desktop/dev/catalog/controller/mail/forgotten.php \
     dh_rasa5k@iad1-shared-e1-18.dreamhost.com:diversiply.co/catalog/controller/mail/
   ```
   Always double-check the remote path (prod vs tkurydyfjkv).

---

## 5. How to deploy

1. **Deploy to dev first** (diversiply.co/tkurydyfjkv):
   ```bash
   rsync -avz --exclude '.git' --exclude 'node_modules' --exclude 'storage/logs/*' \
     /Users/weijiahuang/Desktop/dev/ \
     dh_rasa5k@iad1-shared-e1-18.dreamhost.com:diversiply.co/tkurydyfjkv/
   ```
   Run from a directory where the remote path is relative to `~/` (e.g. `cd ~` then the rsync above), or use full path: `dh_rasa5k@iad1-shared-e1-18.dreamhost.com:/home/dh_rasa5k/diversiply.co/tkurydyfjkv/`
2. **On the server:** Ensure `diversiply.co/tkurydyfjkv/storage/` (and subdirs) are writable (e.g. `chmod -R 755 storage` or 775 if needed). Edit `config.php` and `admin/config.php` in **tkurydyfjkv** so `HTTP_SERVER` / `HTTPS_SERVER` / `HTTP_CATALOG` / `HTTPS_CATALOG` are `https://diversiply.co/tkurydyfjkv/` (and admin = that + `admin/`). Set `DB_*` to the dev/server database credentials.
3. **Test** at https://diversiply.co/tkurydyfjkv/
4. When approved, **deploy to prod** (same repo into the prod folder):
   ```bash
   rsync -avz --exclude '.git' --exclude 'node_modules' --exclude 'storage/logs/*' \
     /Users/weijiahuang/Desktop/dev/ \
     dh_rasa5k@iad1-shared-e1-18.dreamhost.com:diversiply.co/
   ```
   Then set **prod** URLs (`https://diversiply.co/`) and prod DB in `config.php` and `admin/config.php` in the prod folder.

---

## 6. Quick reference

| Question | Answer |
|----------|--------|
| Where is the folder for the site? | **Prod:** `/home/dh_rasa5k/diversiply.co/`. **Dev:** `/home/dh_rasa5k/diversiply.co/tkurydyfjkv/`. |
| Where is the config that points the site? | `config.php` and `admin/config.php` in that folder (HTTP_SERVER / HTTPS_SERVER / HTTP_CATALOG / HTTPS_CATALOG). |
| Where should I deploy my code? | **Dev first:** `~/diversiply.co/tkurydyfjkv/`. **Then prod:** `~/diversiply.co/`. Dev URL: **https://tkurydyfjkv.diversiply.co/** (or http:// if SSL not yet enabled). |

---

## 6b. Dev site (tkurydyfjkv.diversiply.co subdomain)

Dev uses the subdomain **tkurydyfjkv.diversiply.co**. URL: https://tkurydyfjkv.diversiply.co/ (or http:// if SSL not yet enabled). Admin: https://tkurydyfjkv.diversiply.co/admin/.

### Add SSL (Let's Encrypt) for tkurydyfjkv.diversiply.co

If HTTPS shows "Site Not Found" or a certificate error, add a free Let's Encrypt certificate in the DreamHost panel.

**Prerequisites:**
- Subdomain must be **Fully Hosted** in DreamHost (Manage Websites > Add Website > Create subdomain, or ensure tkurydyfjkv.diversiply.co exists with document root at `/home/dh_rasa5k/diversiply.co/tkurydyfjkv/`).
- DNS must point to DreamHost (A record for `tkurydyfjkv` to DreamHost IP, or nameservers at DreamHost).

**Steps:**
1. Log into DreamHost panel.
2. Go to **Domains** > **Secure Certificates**.
3. Find **tkurydyfjkv.diversiply.co** in the list (or add the subdomain under Manage Websites first if it is missing).
4. Click **Add** to the right of the subdomain.
5. Choose **Let's Encrypt** and click **Select this Certificate**.
6. Wait 10–30 minutes for issuance.

**If certificate installation fails:**
- Temporarily disable `.htaccess` in `tkurydyfjkv`: rename it to `.htaccess_OFF`, then run the SSL install again. Rename back to `.htaccess` afterward.
- Or add this rule to `.htaccess` so Let's Encrypt can complete the challenge: `RewriteRule ^.well-known/(.*)$ - [L]`

**Legacy path-based URL** (if subdomain not used): If **https://diversiply.co/tkurydyfjkv/** and **https://diversiply.co/tkurydyfjkv/admin** both show that message (while https://diversiply.co/ works), OpenCart in the dev folder is running but **routing is wrong**. Fix the config **on the server** in the **tkurydyfjkv** folder:

1. **SSH in and edit dev config files** (do not overwrite with local; edit in place):
   ```bash
   ssh dh_rasa5k@iad1-shared-e1-18.dreamhost.com
   cd ~/diversiply.co/tkurydyfjkv
   nano config.php
   ```
2. In **config.php** (catalog), set exactly:
   - `HTTP_SERVER` = `'https://diversiply.co/tkurydyfjkv/'`
   - `HTTPS_SERVER` = `'https://diversiply.co/tkurydyfjkv/'`
   - Ensure `DIR_APPLICATION`, `DIR_SYSTEM`, `DIR_IMAGE`, `DIR_STORAGE`, etc. point to the **tkurydyfjkv** path (e.g. `dirname(__FILE__) . '/...'` so they resolve to `/home/dh_rasa5k/diversiply.co/tkurydyfjkv/...`).
3. In **admin/config.php**, set:
   - `HTTP_SERVER` = `'https://diversiply.co/tkurydyfjkv/admin/'`
   - `HTTPS_SERVER` = `'https://diversiply.co/tkurydyfjkv/admin/'`
   - `HTTP_CATALOG` = `'https://diversiply.co/tkurydyfjkv/'`
   - `HTTPS_CATALOG` = `'https://diversiply.co/tkurydyfjkv/'`
   - And all `DIR_*` so they point into the tkurydyfjkv folder (often `dirname(__FILE__) . '/../'` for catalog root).
4. **Ensure dev has `storage/`** and it’s writable:  
   `mkdir -p storage/cache storage/logs storage/download storage/upload storage/session storage/modification` then `chmod -R 755 storage`
5. Clear storage cache (optional): delete or empty files in `storage/cache/*` and `storage/modification/*` (or leave modification if you use OCMod).
6. Try again: https://diversiply.co/tkurydyfjkv/ and https://diversiply.co/tkurydyfjkv/admin

If it still fails, check the server error log (DreamHost panel or `~/logs/`); a PHP notice or wrong path can break the router.

---

## 7. Why "confirmation link" email is not received (forgotten password / mail)

The message "An email with a confirmation link has been sent to your email address" can appear even when the email **did not send**. If you never receive the email, do the following.

### 7.1 Fix SMTP so emails actually send

Password reset (and other transactional emails) use the mail settings from **Admin → System → Settings → Mail**.

- **If you use Gmail (smtp.gmail.com):**
  - You must use an **App Password**, not your normal Gmail password (Google blocks "less secure apps").
  - In Admin → System → Settings → Mail set:
    - **Mail Protocol:** Mail
    - **Mail Engine:** smtp
    - **SMTP Hostname:** `tls://smtp.gmail.com` (the `tls://` prefix enables STARTTLS)
    - **SMTP Username:** your full Gmail address
    - **SMTP Password:** the 16-character App Password from your Google Account → Security → 2-Step Verification → App passwords
    - **SMTP Port:** 587
    - **SMTP Timeout:** 5
  - Create an App Password at: https://myaccount.google.com/apppasswords (requires 2-Step Verification).

- **If you use DreamHost mail:**
  - Use the SMTP host and credentials from your DreamHost panel (e.g. mail.yourdomain.com, or the host they give for SMTP). Use port 587 with TLS if they support it, or the port they specify.

- **If Mail Engine is "mail" (PHP mail()):**
  - On shared hosting, PHP mail() often does not deliver or goes to spam. Prefer SMTP with real credentials.

### 7.2 When mail fails: error message and reset link in log

After deploying the updated `catalog/controller/account/forgotten.php` and `catalog/controller/mail/forgotten.php`:

- If sending fails (e.g. "Password not accepted from server!"), the user sees the **error message** from the language file instead of "email has been sent".
- The reset link is always written to **`storage/logs/forgotten_password_reset.log`** (one line per request: date, email, URL). You can send that URL to the customer manually if needed.

### 7.3 Check and fix email on the dev site (step-by-step)

Target: **dev** only = `https://tkurydyfjkv.diversiply.co` (path `~/diversiply.co/tkurydyfjkv/`).

1. **Check what’s on dev (optional)**  
   Backup dev config so you can restore if needed, and optionally see what would change:
   ```bash
   ssh dh_rasa5k@iad1-shared-e1-18.dreamhost.com "cd ~/diversiply.co/tkurydyfjkv && cp config.php config.php.bak.$(date +%Y%m%d) 2>/dev/null; cp admin/config.php admin/config.php.bak.$(date +%Y%m%d) 2>/dev/null; ls -la catalog/controller/account/forgotten.php catalog/controller/mail/forgotten.php 2>/dev/null"
   ```
   Dry run (dev only):
   ```bash
   rsync -avzn --exclude '.git' --exclude 'node_modules' --exclude 'storage/logs/*' \
     /Users/weijiahuang/Desktop/dev/ \
     dh_rasa5k@iad1-shared-e1-18.dreamhost.com:diversiply.co/tkurydyfjkv/
   ```

2. **Deploy only the forgotten-password code to dev** (no config overwrite):
   ```bash
   rsync -avz -e ssh /Users/weijiahuang/Desktop/dev/catalog/controller/account/forgotten.php \
     dh_rasa5k@iad1-shared-e1-18.dreamhost.com:diversiply.co/tkurydyfjkv/catalog/controller/account/
   rsync -avz -e ssh /Users/weijiahuang/Desktop/dev/catalog/controller/mail/forgotten.php \
     dh_rasa5k@iad1-shared-e1-18.dreamhost.com:diversiply.co/tkurydyfjkv/catalog/controller/mail/
   ```
   If dev has no `storage/` yet, ensure it exists and is writable (for logs):  
   `ssh dh_rasa5k@iad1-shared-e1-18.dreamhost.com "mkdir -p ~/diversiply.co/tkurydyfjkv/storage/logs; chmod -R 755 ~/diversiply.co/tkurydyfjkv/storage"`

3. **Fix mail settings for dev**  
   Log in to **dev** Admin: `https://diversiply.co/tkurydyfjkv/admin`  
   Go to **System → Settings → Edit (your store) → Mail** tab.  
   - Set **Mail Engine** to **smtp**.  
   - If Gmail: **SMTP Hostname** `tls://smtp.gmail.com`, **Port** 587, **Username** = full Gmail address, **Password** = Gmail App Password (not your normal password).  
   - If DreamHost: use the SMTP host and credentials from the DreamHost panel.  
   Save.

4. **Test on dev**  
   Open `https://diversiply.co/tkurydyfjkv` → Login → “Forgotten Password”, enter an email you can check.  
   - If mail works: you receive the email with the reset link.  
   - If mail fails: you should see the **error message** (not “email has been sent”); the reset link is in `~/diversiply.co/tkurydyfjkv/storage/logs/forgotten_password_reset.log` on the server (you can send that URL to the user manually).
