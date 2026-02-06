# E-commerce site (OpenCart)

OpenCart-based e-commerce platform.

## Branches

- **main** – Base codebase (original GitHub state).
- **enhancements** – Base plus customizations and tooling (see below).

## Changes on `enhancements` branch

This branch adds the following on top of `main`.

### New helper libraries (`system/library/`)

- **analytics_helper.php** – Google Analytics 4 and Facebook Pixel integration.
- **tax_exemption_helper.php** – Tax exemption logic.
- **discount_helper.php** – Discount handling.
- **multi_vendor_shipping_helper.php** – Multi-vendor shipping.
- **email_notification_manager.php** – Email and notification handling.
- **seo_helper.php** – SEO helpers.
- **product_visibility_helper.php** – Product visibility.
- **session_helper.php** – Session helpers.
- **error_logger.php** – Error logging.

### Admin and catalog

- Startup enhancements (admin and catalog).
- Admin “Cache clear” tool (controller, language, template).
- Small updates to download controller, file manager, product model, and account forgotten flow.

### Database

- **install-enhancements.sql** – Defines extra tables: email queue, product visibility log, SEO URL log, cache stats.

### Scripts and tooling

- **audit-platform.php** – Platform audit (PHP, DB, OpenCart config).
- **create-admin.php** – Create admin user.
- **reset-admin-password.php** – Reset admin password.
- **check-modules.php** – Module checks.
- **check-products.php** – Product checks.

### Setup and config

- **SETUP-LOCAL.md** – Local setup instructions.
- **admin/config.local.example.php** – Example admin config.
- **.gitignore** – Excludes storage cache and logs.

For detailed local setup, see **SETUP-LOCAL.md**.
