# Section 2.2 Supplier Onboarding and 2.3 Feature Implementation

This document maps contract deliverables for **2.2 Supplier Onboarding Improvements** and **2.3 Feature Implementation** to the codebase and provides verification steps.

---

## 2.2 Supplier Onboarding Improvements

### Deliverables

| Deliverable | Implementation | How to verify |
|-------------|----------------|---------------|
| Streamline seller onboarding; reduce bugs, friction, confusion, delays | Onboarding checkpoints panel on seller dashboard; Stripe Connect eligibility gating (store must be approved before Connect link); clear error messages on Stripe callback (denied, expired code, store not active). No multi-step wizard. | Log in as seller; complete store info, then Stripe, then add a product. Confirm dashboard shows progress and links; no dead ends. |
| Validation and UX improvements to reduce setup errors | Server-side validation on store form and Stripe callback; eligibility check so Connect is only shown when store is approved; per-row validation in bulk upload (see 2.3 B). | Try connecting Stripe before store is approved: message and no link. Try invalid bulk row: see row-level error. |
| Clear checkpoints/indicators for completed onboarding steps | **Onboarding progress** panel on seller dashboard (dashboardicons) with three steps: (1) Store information completed, (2) Payment (Stripe) connected, (3) At least one product added. Each step shows done (check) or incomplete (box) and a "Complete" link when applicable. | Log in as seller; open Dashboard. Confirm "Onboarding progress" panel and that each step reflects actual state (e.g. Stripe connected vs not). |

### Code references

- **Checkpoints:** `catalog/controller/extension/account/purpletree_multivendor/dashboardicons.php` – `getOnboardingCheckpoints()`; template `catalog/view/theme/default/template/account/purpletree_multivendor/dashboardicons.twig` (onboarding_steps).
- **Stripe eligibility / errors:** `catalog/controller/extension/account/purpletree_multivendor/stripeconnect.php` (OAuth error/error_description, invalid_grant, catch block); `dashboardicons.php` (stripe_connect_eligible, text_stripe_connect_required).

---

## 2.3 Feature Implementation

### A) Minimum Order Amount

| Requirement | Implementation | How to verify |
|-------------|----------------|---------------|
| Extension installed and functioning | Standard OpenCart **Low Order Fee** total extension. Checkout **blocks** access when cart subtotal is below the configured "Total" and redirects to cart with a clear message. | **Admin:** Extensions > Extensions > Order Totals > Low Order Fee. Set "Total" (e.g. 50) as minimum; enable; save. **Storefront:** Add to cart below minimum, go to checkout. **Pass:** Redirect to cart with "Minimum order amount is $50.00. Please add more items to your cart." Add items so subtotal >= 50; checkout loads. |

**Code:** `catalog/controller/checkout/checkout.php` (lines 9–14): reads `total_low_order_fee_status` and `total_low_order_fee_total`; redirects to cart and sets `error_minimum_order` when subtotal is below. Language: `catalog/language/en-gb/checkout/checkout.php` (`error_minimum_order`).

---

### B) Mass Product Import

| Requirement | Implementation | How to verify |
|-------------|----------------|---------------|
| Reliable bulk import (CSV/Excel) | **Purpletree Bulk Product Upload** (seller and admin). Excel-based; Data sheet + General (names/descriptions); optional ProductOption / ProductOptionValue sheets for variants. | Seller or Admin: open Bulk product upload; export template; fill Data (model, price, quantity required); upload. **Pass:** Products created or clear errors. |
| Validation and error reporting | Per-row validation: model required; price and quantity must be numeric. Invalid rows are skipped; errors collected in `$status_msgg` (row number/product_id + message). Session `status_msg` shown on redirect; errors also written to log. | Upload a file with one row missing model and one row with non-numeric price. **Pass:** Row-level error messages (e.g. "Row 3: Model is required"; "Row 4: Price must be numeric") and no corrupt insert for those rows. |

**Code:** `catalog/controller/extension/account/purpletree_multivendor/bulkproductupload.php` (Data sheet loop: row_errors, model/price/quantity checks, status_msgg, failed_array). **Docs:** `MASS-IMPORT-INSTRUCTIONS.md`.

---

### C) Variants / Options Pricing (Format, Color, Layout)

| Requirement | Implementation | How to verify |
|-------------|----------------|---------------|
| Single product listing with multiple sizes/colors/scents/flavors | OpenCart **product options** and **option values** with **option value price** (and price prefix +/−). One product, one description/SEO/images; options (e.g. Size, Color) with variant-specific price. | **Admin:** Catalog > Products > Edit > Option tab. Add option (e.g. Size) and values (S, M, L). Set price per value (e.g. L +$2). Save. **Storefront:** Open product; select options; add to cart. **Pass:** Cart and checkout show correct option and price (base + option price). |
| Shared description, SEO, GEO, images | Same product record; options only change selected value and price/weight. | Confirm one product URL; change option; description and main image unchanged; price updates. |
| No duplicate product listings | Options live on the single product (oc_product_option, oc_product_option_value). | No need for separate "SKU-S", "SKU-M" products; one listing, multiple choices. |

**Docs:** `VARIANTS-OPTIONS-PRICING.md`. Bulk import of options: use ProductOption and ProductOptionValue sheets in the bulk upload template.

---

### D) Shipping (Multi-Seller Cart)

| Requirement | Implementation | How to verify |
|-------------|----------------|---------------|
| Dynamic shipping based on distance and weight | **Purpletree shipping**: per-seller **matrix shipping** (weight range + zipcode range), **flexible shipping** (weight/zip or flat fallback), **flat rate**; **geozone**-based pricing by zone. Weight comes from cart products (including option weight). | Configure a seller with Matrix or Flexible shipping (weight + zip/zone). Add products to cart from that seller; set shipping address; confirm quote reflects weight/zone. |
| Separate shipping charges per seller; one transaction with multiple sellers | **Purpletree Shipping** extension aggregates **per-seller** charges: each seller's shipping is calculated (matrix/weight/zip or flat), then summed. Single checkout shows one combined shipping total; order totals and vendor orders split by seller. | Cart with products from 2+ sellers; each seller has different shipping (e.g. flat $5 vs matrix). Checkout: confirm shipping line is sum of per-seller charges; order details show per-seller shipping. |

**Code:** `catalog/model/extension/purpletree_multivendor/quick_order.php` – `getQuoteShipping()`, `getMatrixShippingCharge()` (weight + country + zipcode range or geozone), `getsellershipping()`; `catalog/model/extension/shipping/purpletree_shipping.php` – `getQuote()` calls quick_order. Sellers configure: Store edit > Shipping tab (type: flat / matrix / flexible; charge; order-wise or product-wise). Admin: Extensions > Shipping > Purpletree Shipping; seller-level matrix/geozone in Purpletree Multivendor > Shipping (bulk upload or per seller).

---

### E) Taxes (Reseller Exemption Workflow)

| Requirement | Implementation | How to verify |
|-------------|----------------|---------------|
| State-based taxes unless buyer is verified reseller | OpenCart **tax rates** are geo-zone based (country + zone/state). Standard flow applies tax by shipping/payment address. **Tax exemption:** When buyer has an **approved** reseller exemption, tax is not applied (see below). | Admin: set up tax rates per geo zone (Localisation > Tax Rates; link to zones). Storefront: change shipping state and confirm tax changes; as reseller with approved cert, confirm no tax. |
| Workflow: buyers upload reseller certificate and request tax exemption | **Upload at signup:** Register form has optional "Reseller Tax Exemption" section (file upload PDF/image, cert number, issuing state). File saved; record inserted into `customer_tax_exemption` with status **pending**. | Register new account; upload a reseller cert (PDF/image); submit. Confirm record in `customer_tax_exemption` (status pending) and file in `image/catalog/reseller_certs/`. |
| Operational mechanism to apply tax waiver upon verification | **Approved exemption** = tax waiver. `catalog/model/extension/total/tax.php`: if customer is logged in and `TaxExemptionHelper::hasValidExemption(customer_id)` (status = approved, not expired), tax totals are cleared so no sales tax is added. Admin approves/rejects pending certificates (approve sets status = approved). | Admin approves a pending exemption. Buyer places order; confirm checkout shows no tax line. Reject exemption; buyer sees tax again (or clear cache/session). |

**Code:** `catalog/controller/account/register.php` (reseller file upload, `TaxExemptionHelper::submitExemption()`); `system/library/tax_exemption_helper.php` (submit, approve, reject, `hasValidExemption()`); `catalog/model/extension/total/tax.php` (skip tax when valid exemption); `install-tax-exemption-tables.php` (creates `customer_tax_exemption`, `customer_tax_exemption_log`). **Docs:** `docs/AI-FEASIBILITY-AND-RESELLER-CERTIFICATE.md` (workflow; AI verification is optional enhancement).

**Admin screen:** Customers > **Tax Exemptions** lists all exemption requests (filter by status: Pending, Approved, Rejected). For pending rows, admin can **Approve** (sales tax then waived for that customer) or **Reject** (optional reason). Certificate file link opens the uploaded PDF/image. Controller: `admin/controller/customer/tax_exemption.php`; view: `admin/view/template/customer/tax_exemption.twig`. Grant **Access** and **Modify** for `customer/tax_exemption` in System > Users > User Groups if the menu item does not appear or access is denied.

---

## Quick test checklist

- [ ] **2.2** Seller dashboard shows Onboarding progress; steps match reality; "Complete" links work.
- [ ] **2.2** Stripe Connect only offered when store is approved; after deny/expire, clear message and re-try possible.
- [ ] **2.3 A** Low Order Fee Total set in admin; checkout blocked below minimum with message; allowed at or above.
- [ ] **2.3 B** Bulk upload: valid rows import; invalid row (e.g. no model, non-numeric price) returns row error, no silent corrupt data.
- [ ] **2.3 C** Product with options and option prices: storefront shows options and correct price per variant in cart/checkout.
- [ ] **2.3 D** Multi-seller cart: products from 2+ sellers; shipping method shows combined per-seller shipping; checkout completes with correct per-seller shipping in order.
- [ ] **2.3 E** State-based tax applies when address changes; reseller uploads cert at signup (pending); admin approves; buyer's next order has no sales tax.

For full regression and contract coverage, see `CONTRACT-REQUIREMENTS-TEST.md` and `MANUAL-TEST-SCRIPT-ALL.md`.
