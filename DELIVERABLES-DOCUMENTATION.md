# Deliverables Documentation (Scope 2.5)

This document describes what was changed, where the changes are, and how to manage them going forward. It covers work delivered under the Platform Audit, Stabilization, and Feature Implementation scope.

---

## 1. What was changed (high-level)

- **Platform audit:** Audit script added; run to capture PHP, DB, OpenCart config, extensions.
- **Stabilization:** Forgotten password flow (catalog); email queue and notification manager; product visibility (cache clear on product add/edit); discount helper; analytics and SEO helpers; cache-clear tool; scaling-related tables and session/error logging.
- **Supplier onboarding:** Onboarding checkpoints added to seller dashboard (store info, Stripe, first product).
- **Tax / reseller:** Tax exemption backend; buyer can upload certificate at registration; checkout skips tax when exemption is approved (see 3.8).
- **Shipping:** Purpletree shipping extension (`purpletree_shipping`) provides per-seller shipping at checkout; `multi_vendor_shipping_helper.php` is available for custom per-seller logic.
- **Minimum order:** Checkout blocks orders below the configured low-order total (same config as low order fee).
- **Mass import:** Purpletree bulk upload has row validation (model, price, quantity) and clear error reporting; see `MASS-IMPORT-INSTRUCTIONS.md`.
- **Variants/options:** Product options with option-value pricing provide variant-style pricing; see `VARIANTS-OPTIONS-PRICING.md`.
- **Stripe:** Current flow uses Express OAuth; migration to Account Link API and commission/split verification are documented in `STRIPE-INTEGRATION-NOTES.md`.

---

## 2. Where the changes were made

| Deliverable | Location (files / area) |
|-------------|-------------------------|
| Platform audit | `audit-platform.php` (root) |
| Forgotten password | `catalog/controller/account/forgotten.php`, `catalog/language/en-gb/mail/forgotten.php` |
| Email queue / notifications | `system/library/email_notification_manager.php`, `install-enhancements.sql` (oc_email_queue); cron: `catalog/controller/tool/email_queue.php` (route `tool/email_queue/process`), **docs/EMAIL-QUEUE-CRON.md** |
| Product visibility (show when enabled) | `system/library/product_visibility_helper.php`; `admin/controller/catalog/product.php` (calls helper after add/edit) |
| Discounts | `system/library/discount_helper.php`; instructions: `DISCOUNT-INSTRUCTIONS.md` |
| Analytics (GA4, Facebook) | `system/library/analytics_helper.php` |
| SEO | `system/library/seo_helper.php`; `install-enhancements.sql` (oc_seo_url_log) |
| Cache clear (admin) | `admin/controller/tool/cache_clear.php`, `admin/view/template/tool/cache_clear.twig` |
| Scaling / logging | `install-enhancements.sql` (oc_cache_stats, oc_error_log, oc_session_activity); `system/library/error_logger.php`, `session_helper.php` |
| Tax exemption backend | `system/library/tax_exemption_helper.php`; `install-enhancements.sql` (oc_customer_tax_exemption, oc_customer_tax_exemption_log) |
| Multi-seller shipping | `catalog/model/extension/shipping/purpletree_shipping.php` (checkout); `system/library/multi_vendor_shipping_helper.php` (helper) |
| Minimum order | `catalog/controller/checkout/checkout.php`; config: Extensions > Totals > Low Order Fee |
| Reseller exemption (signup + checkout) | `catalog/controller/account/register.php`, `catalog/view/.../register.twig`; `catalog/model/extension/total/tax.php` (TaxExemptionHelper) |
| Tax exemption admin (approve/reject) | `admin/controller/customer/tax_exemption.php`, `admin/view/template/customer/tax_exemption.twig`; menu: Customers > Tax Exemptions |
| Commission (product-only; Stripe fee split) | `catalog/controller/extension/payment/pp_adaptive.php` (shipping commission = 0); `catalog/controller/extension/payment/pts_stripe.php` (fee split); `catalog/model/.../sellerorder.php` (commission block); `docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md` |
| Mass import validation / errors | `admin/controller/.../bulkproductupload.php` and `catalog/controller/.../bulkproductupload.php` (pre-import validation, status_msgg) |
| Onboarding checkpoints | `catalog/controller/extension/account/purpletree_multivendor/dashboardicons.php` (method `getOnboardingCheckpoints`); `catalog/view/theme/default/template/account/purpletree_multivendor/dashboardicons.twig` (Onboarding progress panel) |
| Startup / config | `admin/controller/startup/enhancements.php`, `catalog/controller/startup/enhancements.php`; `config.php` / `admin/config.php` (paths, DB) |
| Scripts / tooling | `create-admin.php`, `reset-admin-password.php`, `check-modules.php`, `check-products.php` (root) |
| Setup / docs | `SETUP-LOCAL.md`, `README.md`, `admin/config.local.example.php`, `.gitignore` |

---

## 3. How to manage changes going forward

### 3.1 Platform audit

- **Run:** Execute `audit-platform.php` via browser or CLI (ensure DB credentials in the script match your environment).
- **Update:** Edit the script to add/remove checks or output format.

### 3.2 Forgotten password

- **Config:** Ensure mail settings in Admin > System > Settings > Edit Store (Mail tab) are correct so reset emails send.
- **Text:** Edit `catalog/language/en-gb/mail/forgotten.php` and `catalog/language/en-gb/account/forgotten.php` for wording.

### 3.3 Notifications (email queue)

- **Tables:** `oc_email_queue` created by `install-enhancements.sql`. To use the queue, code that sends notifications must call `EmailNotificationManager::queue()` or use the manager for sending; existing event-based mail may still send directly unless refactored.
- **Processing:** Run a cron or scheduled task that calls the logic that processes the queue (e.g. a controller or script that uses `EmailNotificationManager::processQueue()`).

### 3.4 Product visibility (new product shows when enabled)

- **Behaviour:** When a product is added or edited in Admin > Catalog > Products, the product and category caches are cleared so the product can show on the site without long delay.
- **Change:** To alter what gets cleared, edit `admin/controller/catalog/product.php` method `triggerProductVisibility()` and/or `system/library/product_visibility_helper.php`.

### 3.5 Discounts

- **Usage:** Follow `DISCOUNT-INSTRUCTIONS.md` (coupons, product discount/special tabs, store-level via product settings).
- **Logic:** Core discount logic uses `system/library/discount_helper.php`; admin and catalog use standard OpenCart coupon and product discount/special screens.

### 3.6 Analytics and SEO

- **Analytics:** Configure GA4 and Facebook IDs in store settings (or wherever the theme reads `config_ga4_measurement_id` etc.); ensure layout/template includes the output from `AnalyticsHelper` (e.g. in header).
- **SEO:** Use `SeoHelper` where needed; `oc_seo_url_log` is for audit. Meta and keywords are managed per product/category in admin.

### 3.7 Cache clear

- **Use:** Admin > Tools (or the route you installed) > Cache clear.
- **Config:** Route and permissions are defined in the controller and menu; adjust in admin permissions if needed.

### 3.8 Tax exemption (reseller)

- **Admin screen:** Customers > **Tax Exemptions** lists all certificate requests (filter by status). For pending rows, use **Approve** or **Reject** (optional reason). Certificate file link opens the uploaded PDF/image. Grant Access and Modify for `customer/tax_exemption` in System > Users > User Groups if the menu does not appear.
- **Backend:** `admin/controller/customer/tax_exemption.php`; `TaxExemptionHelper` in `system/library/tax_exemption_helper.php`; data in `oc_customer_tax_exemption`.
- **Signup:** Registration includes optional reseller certificate upload (file, certificate number, state); stored in `image/catalog/reseller_certs/` and `oc_customer_tax_exemption` (pending).
- **Checkout:** `catalog/model/extension/total/tax.php` uses `TaxExemptionHelper::hasValidExemption()` so tax is not applied when the customer has an approved exemption.

### 3.8a Commission and split payment (admin)

- **Shipping commission = 0:** Extensions > Multivendor > Settings > Vendor settings > set Shipping commission to 0 (product-only commission).
- **Store commission (e.g. 12% / 14% / 15%):** Extensions > Multivendor > Stores > Edit store > Seller commission tab; set Commission (%) per store.
- **Stripe fee split:** Implemented in `catalog/controller/extension/payment/pts_stripe.php` (2.9% + $0.30, 50/50). Change in code if needed.
- **Full checklist and code refs:** **docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md**.

### 3.9 Multi-seller shipping

- **Checkout:** The Purpletree shipping extension (`shipping_purpletree_shipping`) is used at checkout; it calls `$this->cart->getSellerShippingCharge($address)` to get per-seller shipping. Enable it in Extensions > Extensions > Shipping.
- **Custom logic:** `MultiVendorShippingHelper` can be used for custom per-seller calculations (weight/distance, breakdown).

### 3.10 Onboarding checkpoints

- **Where:** Seller dashboard (dashboardicons) shows “Onboarding progress” with steps: Store information completed, Payment (Stripe) connected, At least one product added.
- **Change steps:** Edit `getOnboardingCheckpoints()` in `catalog/controller/extension/account/purpletree_multivendor/dashboardicons.php`. Edit the panel in `catalog/view/theme/default/template/account/purpletree_multivendor/dashboardicons.twig` (or the active theme’s equivalent).

### 3.11 Database

- **New tables:** All from `install-enhancements.sql`. Apply once per environment. For new installs, run after the main OpenCart (and any multivendor) schema.
- **Config:** `config.php` and `admin/config.php`: update `DIR_*` and `HTTP_*` for each environment; set DB credentials.

### 3.12 Stripe, minimum order, mass import, variants

- **Stripe:** See `STRIPE-INTEGRATION-NOTES.md` for current OAuth flow and steps to migrate to Account Link API; commission/split (product-only %, 2.9%+$0.30 split) to be verified in payment/order code.
- **Minimum order:** Configure in Admin > Extensions > Order Totals > Low Order Fee (set "Total" and enable). Checkout redirects to cart with an error when subtotal is below that value.
- **Mass import:** Use Purpletree bulk product upload; see `MASS-IMPORT-INSTRUCTIONS.md`. Row validation (model required; price/quantity numeric) and per-row error messages are in the bulk upload controller.
- **Variants/options:** See `VARIANTS-OPTIONS-PRICING.md`. Use product options and option value price in Admin > Catalog > Products > Option tab.
- **Regression testing:** Use `REGRESSION-TEST-CHECKLIST.md` after changes.
- **AI feasibility:** Assessment only in `AI-FEASIBILITY.md` (image/description, reseller cert verification).

---

## 4. Completion status (what is fully done vs documented only)

| Item | Status |
|------|--------|
| Platform audit, forgotten password (single path), image upload fix, discounts + instructions, product visibility, analytics in header, onboarding checkpoints, minimum order, reseller exemption (signup + checkout), mass import validation in both admin and seller bulk upload, regression checklist, all documentation above | **Implemented in code and/or docs** |
| Multi-seller shipping at checkout | **Implemented** via existing Purpletree shipping extension (`getSellerShippingCharge`). `MultiVendorShippingHelper` is available for custom logic; it is not wired into the shipping quote in this deliverable. |
| Commission/split (product-only %, Stripe fee split, sellerorder) | **Implemented** in pp_adaptive, pts_stripe, sellerorder; admin steps in docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md. |
| Stripe: Account Link API | **Implemented**: Connect uses Account Link by default (create account + AccountLink, return_url with token). Set `payment_pts_stripe_use_account_link` = 0 in admin/oc_setting to use legacy Express OAuth. |
| AI (image/description, reseller cert verification) | **Documented only** in AI-FEASIBILITY.md (assessment); no AI code added. |
| Notifications (email queue) | **Implemented**: queue and manager exist; cron processor at route `tool/email_queue/process` (token-secured); see **docs/EMAIL-QUEUE-CRON.md**. Not all mail triggers use the queue; only code that calls the manager does. |
| SEO content, scaling load tests | **Partial**: helpers/tooling in place; content and load testing are separate. |

## 5. Support (4 weeks post-delivery) – 2.5 B

Per contract (Section 2.5 B): For four (4) weeks after Final Delivery, Contractor will provide reasonable Q&A and bug/error fixes **only** for issues **directly related** to the Deliverables. This support does **not** include new features, scope expansion, or third-party vendor negotiations. If Client so requests, the 4-week period may commence at a **later date** rather than immediately upon delivery; Contractor will use best efforts to accommodate. To the extent feasible, Contractor will **demo** the new features and fixes with Client in set **video-sessions** to expedite review after delivery.

Full 2.5 A and B wording and document index: **docs/SECTION-2.5-DOCUMENTATION-AND-HANDOFF.md**.
