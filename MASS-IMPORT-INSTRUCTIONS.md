# Mass Product Import (Scope 2.3(B))

The platform uses **Purpletree Bulk Product Upload** for CSV/Excel-based mass import. This document covers how to use it and how validation and error reporting work.

## Where to find it

- **Seller:** Seller Dashboard > **Bulk product upload** (or Bulk Product Upload in the store section).
- **Admin:** Extensions > Purpletree Multivendor (or the multivendor menu) > Bulk product upload / product management.

## How to use

1. **Export a template** from the bulk upload page (Excel format). Use it as the schema for your data.
2. **Fill the Data sheet** with product fields (product_id, model, sku, quantity, price, status, etc.). Use the General sheet for product names/descriptions per language.
3. **Upload** the Excel file via the upload form on the same page.
4. **Review** any on-screen success/error messages after import.

## Validation and reducing errors

- **Required columns:** Ensure product_id (for updates) or required fields for new products (model, name, price, etc.) are present and non-empty.
- **Numeric fields:** Quantity, price, weight, minimum, sort_order must be numeric.
- **Dates:** Use the format expected by the template (e.g. YYYY-MM-DD for date_available).
- **Stock status / status:** Use the exact values from the export (e.g. Enabled/Disabled, or the IDs used in the system).
- **Seller scope:** Sellers only import/update their own products; the module restricts by seller_id.

## Error reporting

- After import, the page shows success messages or error messages. Check the session messages at the top of the bulk upload page.
- For detailed debugging: enable **System > Settings > Server** error logging (or the platform’s log), then retry the import and check `storage/logs/` (or the path set in config) for PHP/database errors.
- **Data corruption prevention:** Always keep a backup of the database before large imports. Use the exported template as the single source of column names and formats so column shifts or renames don’t corrupt data.

## Technical location

- **Controller (seller):** `catalog/controller/extension/account/purpletree_multivendor/bulkproductupload.php` (and admin equivalent if present).
- **Model:** `admin/model/extension/purpletree_multivendor/bulkproductupload.php` (product insert/update and export logic).

To add stricter validation or a downloadable error report, extend the controller’s import method to validate each row before calling the model, collect per-row errors, and return or display them (e.g. in a table or CSV).
