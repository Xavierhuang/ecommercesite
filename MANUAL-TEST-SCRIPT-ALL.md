# Manual Test Script – All Contract Requirements

Step-by-step script to test every contract deliverable. Use a test store with test buyer, seller, and products. Mark [ ] as [x] when done and passed.

---

## Part 0: Automated (terminal)

| Step | Action | Pass criteria |
|------|--------|----------------|
| 0.1 | In project root run: `php verify-deliverables.php` | Output shows all [OK], "0 missing" |
| 0.2 | Run: `php audit-platform.php` | Report prints (PHP, DB, OpenCart, extensions, modules, users, products, orders, events); JSON saved to `devdocs/work/audit/audit-data.json` |

---

## Part 1: Section 2.1 – Platform Audit & Stabilization

### 1.1 Platform audit (2.1 #1)
- [x] Run `php audit-platform.php` (if not in 0.2). Confirm report and JSON file exist. *(Done: report prints; `devdocs/work/audit/audit-data.json` exists.)*

### 1.2 Forgotten password (2.1 #2a)
- [ ] Storefront: open Login (or Account) and click "Forgotten Password". *(Forgotten password page opened at `index.php?route=account/forgotten`.)*
- [ ] Enter a valid customer email; submit.
- [ ] Check inbox: exactly one reset email; use the link.
- [ ] Set new password; submit.
- [ ] Log in with the new password. **Pass:** One email only; reset works; login works.

### 1.3 Notifications – no duplicates (2.1 #2b)
- [ ] Place a test order (any method) so order confirmation emails are sent.
- [ ] Check buyer inbox: expected order confirmation (one per event).
- [ ] Check seller inbox (if applicable): expected seller notification (one per event).
- [ ] **Pass:** No duplicate emails for the same event.
- [ ] *Full steps and pass criteria:* see **TEST-NOTIFICATION-RELIABILITY-2b.md** (infrastructure check, one order = one email each, optional queue test).

**Best way to check (minimal):**

1. **Use two inboxes you can open:** e.g. your main email as buyer, and either (a) a second address you own as the seller store email, or (b) the admin “Mail Alert” address (Admin > System > Settings > Mail).
2. **Ensure mail works:** If using SMTP, ensure credentials/App Password are set and correct so emails are actually sent (not just queued).
3. **Place one order:** As buyer, add one product (ideally from a seller whose store email you control), complete checkout, place **one** order. Do not change order status in admin afterward (to avoid extra history emails).
4. **Count emails:**
   - **Buyer inbox:** Exactly **1** order confirmation for that order. Pass = 1; fail = 0 or 2+.
   - **Seller inbox** (if product was from a seller): Exactly **1** seller order notification. Pass = 1; fail = 2+.
   - **Admin Mail Alert inbox** (if set): Exactly **1** new order alert. Pass = 1; fail = 2+.
5. **Repeat once:** Place a **second** order (different order). Again expect exactly one new email per party for that order. Pass = no duplicates for the same event.

**How to run the two-order notification test (step-by-step):**

| Step | What to do | Pass / Fail |
|------|------------|-------------|
| **Setup** | |
| 1 | Pick **buyer email**: an inbox you control (e.g. your main email). | |
| 2 | Pick **seller email**: either (a) a second inbox you control, or (b) the store **Mail Alert** address (Admin > System > Settings > [Store] > Mail tab). | |
| 3 | If using a seller product: ensure one product is from a **seller whose store email** is the seller inbox above (Seller dashboard or Admin: set that store email). | |
| 4 | Confirm **Mail** sends: Admin > System > Settings > Mail. Use SMTP with valid credentials (e.g. Gmail App Password). Test with Forgotten Password if needed. | Emails deliver. |
| **Order 1** | |
| 5 | **Place order 1:** Storefront – add one product to cart, checkout, use **buyer email**, place order. Note the order ID or time. | Order placed. |
| 6 | Do **not** change order status in Admin (that can trigger extra emails). | |
| 7 | **Buyer inbox:** Count emails for order 1 (e.g. Order confirmation / order #). | **Pass:** exactly **1**. Fail: 0 or 2+. |
| 8 | **Seller inbox:** Count seller order notification for order 1. | **Pass:** exactly **1**. Fail: 2+. |
| 9 | **Admin Mail Alert inbox** (if set): Count new-order alert for order 1. | **Pass:** exactly **1**. Fail: 2+. |
| **Order 2** | |
| 10 | **Place order 2:** New order (different product or same product, different order). Checkout with same or different buyer email you control. | Order placed. |
| 11 | Again do not change order status in Admin. | |
| 12 | **Buyer inbox:** One new email for order 2 only. | **Pass:** 1 new for order 2; no duplicate for order 1. |
| 13 | **Seller inbox:** One new seller notification for order 2 only. | **Pass:** 1 new for order 2; no duplicate for order 1. |
| 14 | **Admin Mail Alert:** One new alert for order 2 only. | **Pass:** 1 new for order 2; no duplicate for order 1. |
| **Done** | Each party got exactly one email per order; no duplicates. | **Pass:** All counts above correct. |

**Quick infra check (optional):** `oc_email_queue` table exists (from `install-enhancements.sql`). Order/seller emails are sent via OpenCart events and Purpletree, not necessarily through the queue; the test above still validates “one email per event, no duplicates.”

### 1.4 Image upload (2.1 #2c)
- [ ] Admin: Catalog > Products > Edit any product (or add new).
- [ ] Click the image field; open Image Manager.
- [ ] Upload an image (first time). Confirm it appears in the list immediately.
- [ ] Select/attach it to the product; save.
- [ ] Storefront: open that product. **Pass:** Image shows in list on first attempt; image shows on front.

### 1.5 Discounts (2.1 #2d)
- [ ] Admin: Extensions > Order Totals: ensure Coupon is enabled. Marketing > Coupons: add a coupon (e.g. 10% or fixed amount).
- [ ] Storefront: add product to cart; go to checkout (or cart). Enter coupon code; apply.
- [ ] **Pass:** Discount applies (total reduced).
- [ ] Admin: Catalog > Products > Edit a product: add Product Discount or Special; save.
- [ ] Storefront: view that product and add to cart. **Pass:** Correct discounted/special price at checkout.

### 1.6 New product visibility (2.1 #2e)
- [ ] As seller or admin: add a new product; set Status = Enabled; save.
- [ ] Within a short time (no long wait), open storefront and navigate to the category or search for the product.
- [ ] **Pass:** New product appears without cache refresh or long delay.

### 1.7 Analytics (2.1 #2f)
- [ ] If GA4 and/or Facebook Pixel IDs are set in store settings: load storefront homepage.
- [ ] View Page Source (or DevTools Network): search for gtag, ga4, fbq, or similar tracking script.
- [ ] Open browser console: no script errors. **Pass:** Tracking scripts present (if configured); no errors.

### 1.8 SEO (2.1 #2g)
- [ ] Confirm `system/library/seo_helper.php` and `oc_seo_url_log` (from install-enhancements.sql) exist. SEO content is per product/category in admin. **Pass:** Helper and table present.

### 1.9 Platform scalable (2.1 #2h)
- [ ] Confirm cache-clear tool exists: Admin > Tools > Cache clear (or equivalent). Run it once.
- [ ] **Pass:** Cache clears without error. (Load testing is separate.)

### 1.10 Stripe review (2.1 #3)
- [ ] Read `STRIPE-INTEGRATION-NOTES.md`. Confirm Account Link migration and commission/split are documented. Code may still use OAuth. **Pass:** Doc exists and describes current state.

### 1.11 Regression testing (2.1 #4)
- [ ] Complete this full script (Parts 1–5 and Core flows). **Pass:** All steps run without regression.

### 1.12 Onboarding checkpoints (2.1 #5)
- [ ] Log in as a seller (seller dashboard).
- [ ] Locate "Onboarding progress" (or similar) panel.
- [ ] Confirm checkpoints shown: e.g. Store information completed, Payment (Stripe) connected, At least one product added.
- [ ] **Pass:** Panel visible; checkpoints reflect actual state (e.g. completed vs not).

### 1.13 AI feasibility (2.1 AI)
- [ ] Confirm `AI-FEASIBILITY.md` exists; assessment only, no AI code required. **Pass:** Doc present.

---

## Part 2: Section 2.2 – Supplier Onboarding

### 2.1 Onboarding panel clarity (2.2 #1)
- [ ] Same as 1.12: seller dashboard onboarding panel is clear and accurate. **Pass:** Panel clear; no confusion.

### 2.2 Seller signup and first steps (2.2 #2)
- [ ] (Optional) Register a new seller; go through store info, Stripe, first product steps. Note any blockers or confusing UX. **Pass:** No critical blockers.

### 2.3 Checkpoints for completed steps (2.2 #3)
- [ ] Same as 1.12: checkpoints show completed vs incomplete. **Pass:** Checkpoints reflect state.

---

## Part 3: Section 2.3 – Feature Implementation

### 3.1 Minimum order (2.3 A)
- [ ] Admin: Extensions > Extensions > Order Totals. Edit "Low Order Fee". Set "Total" (e.g. 50); set "Order Status" and "Fee" if needed; enable; save.
- [ ] Storefront: add to cart so subtotal is below 50. Go to checkout.
- [ ] **Pass:** Redirected to cart with minimum order message (e.g. "Minimum order amount is $50.00...").
- [ ] Add items so subtotal is 50 or more. Go to checkout again.
- [ ] **Pass:** Checkout page loads (no redirect to cart).

### 3.2 Mass product import (2.3 B)
- [ ] Admin or seller: open Purpletree Bulk Product Upload. Export template (if available).
- [ ] Fill at least one row (model required; price and quantity numeric). Upload file.
- [ ] **Pass:** Product is created and visible, or clear validation/error messages are shown (e.g. "Model is required", "Price must be numeric").
- [ ] (Optional) Upload a row with invalid data (e.g. missing model). **Pass:** Row error reported, not "success".

### 3.3 Variants/options pricing (2.3 C)
- [ ] Admin: Catalog > Products. Edit (or add) a product. Option tab: add option (e.g. Size) with values (S, M, L); set option value prices (e.g. +$2 for L). Save.
- [ ] Storefront: open product; select different options; add to cart. Open cart and proceed to checkout.
- [ ] **Pass:** Cart and checkout show correct option and price (base + option price).

### 3.4 Multi-seller shipping (2.3 D)
- [ ] Ensure Purpletree shipping is enabled: Admin > Extensions > Shipping.
- [ ] Storefront: add to cart products from at least two different sellers. Go to checkout.
- [ ] **Pass:** Shipping step appears; at least one shipping method (e.g. Purpletree shipping) is available and selectable.

### 3.5 Reseller tax exemption (2.3 E)
- [ ] Storefront: register a new customer. On registration form: upload reseller certificate (file); enter certificate number and state if fields exist. Submit.
- [ ] Admin: find the customer's tax exemption record (or Tax Exemption / Reseller area). Approve the exemption.
- [ ] As that customer: add product to cart; go to checkout. Check order summary/totals.
- [ ] **Pass:** Tax is not applied (or is zero) for that customer after approval.

---

## Part 4: Section 2.4 – Testing

### 4.1 Full regression and performance (2.4 #1)
- [ ] Complete Part 5 (Core flows) and Part 6 (Performance) below.
- [ ] **Pass:** No breaks; key pages load in reasonable time; no sustained errors in logs.

---

## Part 5: Core flows (no regressions)

### 5.1 Registration
- [ ] New buyer: storefront > Register; complete form; submit. **Pass:** Registration completes; can log in.
- [ ] New seller: use seller registration flow; complete required steps. **Pass:** Seller account created; can access seller dashboard.

### 5.2 Login / logout
- [ ] Buyer: log in; then log out. **Pass:** Login and logout work.
- [ ] Seller: log in to seller area; then log out. **Pass:** Login and logout work.

### 5.3 Cart
- [ ] Add product to cart; change quantity; remove one item. **Pass:** Cart total and shipping eligibility update correctly.

### 5.4 Checkout
- [ ] Full checkout as guest: address, shipping, payment; place order. **Pass:** Order created; appears in Admin > Sales > Orders.
- [ ] Full checkout as logged-in customer: place order. **Pass:** Order created; appears in admin and (if applicable) seller panel.

### 5.5 Admin – edit product and cache
- [ ] Admin: log in; Catalog > Products > Edit a product; change name or price; save.
- [ ] Run cache clear (Admin > Tools > Cache clear or equivalent).
- [ ] Storefront: view that product. **Pass:** Change is visible on front.

---

## Part 6: Performance

### 6.1 Page load and logs
- [ ] Open home, a category, a product, and checkout in turn. **Pass:** Pages load in reasonable time (no long hangs).
- [ ] (Optional) Check PHP/OpenCart error logs after a short browse. **Pass:** No sustained or critical errors.

---

## Part 7: Section 2.5 – Documentation

### 7.1 Documentation handoff (2.5 A)
- [ ] Confirm these files exist in the project and are handed off:
  - [ ] **Primary:** DELIVERABLES-DOCUMENTATION.md
  - [ ] **Section docs:** docs/SECTION-2.2-AND-2.3-DELIVERABLES.md, docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md, docs/SECTION-2.4-TESTING.md, docs/SECTION-2.5-DOCUMENTATION-AND-HANDOFF.md, docs/AI-FEASIBILITY-AND-RESELLER-CERTIFICATE.md
  - [ ] MASS-IMPORT-INSTRUCTIONS.md, VARIANTS-OPTIONS-PRICING.md, STRIPE-INTEGRATION-NOTES.md, REGRESSION-TEST-CHECKLIST.md, DISCOUNT-INSTRUCTIONS.md, SETUP-LOCAL.md, README.md
- [ ] **Pass:** All listed docs present. Full index: docs/SECTION-2.5-DOCUMENTATION-AND-HANDOFF.md.

### 7.2 Support (2.5 B)
- [ ] N/A for code testing (4 weeks support is post-delivery).

---

## Sign-off summary

| Section | Items | Passed | Notes |
|---------|-------|--------|-------|
| 0 – Automated | 2 | | |
| 1 – 2.1 Stabilization | 13 | | |
| 2 – 2.2 Onboarding | 3 | | |
| 3 – 2.3 Features | 5 | | |
| 4 – 2.4 Testing | 1 | | |
| 5 – Core flows | 5 | | |
| 6 – Performance | 1 | | |
| 7 – 2.5 Documentation | 1 | | |

**Tester:** _________________ **Date:** _________________

**Contract compliance:** All items above passed and documented, except those marked N/A or documented-only (Stripe migration, AI feasibility).
