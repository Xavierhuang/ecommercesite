# Contract Requirements Test

Use this document to verify that all contract deliverables (Scope 2.1–2.5) are met. Each row maps to the contract, lists how to test, and has a checkbox for sign-off.

**Reference:** `SCOPE-VS-ACHIEVEMENT.md`, `DELIVERABLES-DOCUMENTATION.md`, `REGRESSION-TEST-CHECKLIST.md`.  
**Full step-by-step script:** `MANUAL-TEST-SCRIPT-ALL.md` (all tests in one runnable order).

---

## Automated checks (run from project root)

Run these to confirm deliverable code and docs exist and the platform audit works:

| Check | Command | Expected |
|-------|---------|----------|
| File presence | `php verify-deliverables.php` | All items show [OK]; "0 missing" |
| Platform audit | `php audit-platform.php` | Report prints; JSON in `devdocs/work/audit/audit-data.json` |

**Audit script DB credentials:** Uses `root` / no password by default. Override with env: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.

---

## Section 2.1 Platform Audit & Stabilization

| # | Contract requirement | How to test | Pass? |
|---|------------------------|-------------|-------|
| 1 | **Platform audit** – script to capture PHP, DB, OpenCart, extensions | Run `php audit-platform.php` from project root (set DB credentials in script if needed). Report prints to console; JSON saved to `devdocs/work/audit/audit-data.json`. | [ ] |
| 2a | **Forgotten password** – single code path, no duplicate email | Request password reset from storefront; receive one email; use link; set new password; log in. Check no duplicate emails. | [ ] |
| 2b | **Notifications** – reliable, no duplicates | Place test order; confirm buyer and seller get expected emails; confirm no duplicate emails for same event. | [ ] |
| 2c | **Image upload** – first upload shows in list, attach works | Admin > Product > Edit > Image manager: upload image; confirm it appears in list and can be selected on first attempt; save; confirm on front. | [ ] |
| 2d | **Discounts** – easy to add + instructions | Add coupon in admin; at checkout enter coupon, confirm discount. Add product discount/special; confirm price at checkout. See `DISCOUNT-INSTRUCTIONS.md`. | [ ] |
| 2e | **New product shows when enabled** – no long wait | Add new product (seller or admin), enable it; confirm it appears on storefront without long delay. | [ ] |
| 2f | **Analytics** – GA4/Facebook installed and functioning | If GA4/Facebook IDs are set in store settings, load storefront; view page source and confirm tracking scripts present; no script errors in console. | [ ] |
| 2g | **SEO** – descriptions, keywords for search engines | Helper and audit log in place. SEO content updates are separate (per product/category in admin). | [ ] |
| 2h | **Platform scalable** – visitors, sellers, buyers | Cache-clear tool, stats tables, startup enhancements exist. Load testing is separate. | [ ] |
| 3 | **Stripe review** – Account Link API, split payments, commission | Documented in `STRIPE-INTEGRATION-NOTES.md`. Code still uses OAuth; migration and commission verification are doc-only unless implemented. | [ ] |
| 4 | **Regression testing** | Run through `REGRESSION-TEST-CHECKLIST.md` after changes. | [ ] |
| 5 | **Onboarding checkpoints** – clear indicators for supplier onboarding | Log in as seller; confirm "Onboarding progress" panel shows; checkpoints (store info, Stripe, first product) reflect actual state. | [ ] |
| AI | **AI feasibility** – image/description/cert verification | Assessment in `AI-FEASIBILITY.md`; no AI code required per scope. | [ ] |

---

## Section 2.2 Supplier Onboarding

| # | Contract requirement | How to test | Pass? |
|---|------------------------|-------------|-------|
| 1 | Streamline seller onboarding; reduce bugs, friction | Checkpoints delivered; full wizard not in scope. Confirm dashboard onboarding panel is clear and accurate. | [ ] |
| 2 | Validation and UX to reduce setup errors | Manual: go through seller signup and first steps; note any blockers or confusion. | [ ] |
| 3 | Clear checkpoints for completed onboarding steps | Same as 2.1(5): onboarding progress panel on seller dashboard. | [ ] |

---

## Section 2.3 Feature Implementation

| # | Contract requirement | How to test | Pass? |
|---|------------------------|-------------|-------|
| A | **Minimum order amount** | Set Low Order Fee total in Admin (e.g. 50). Cart below 50: try checkout, confirm redirect to cart with message. Meet minimum: checkout loads. | [ ] |
| B | **Mass product import** – validation, error reporting | Use Purpletree bulk upload: export template, fill row(s), upload. Confirm product appears or clear errors shown. See `MASS-IMPORT-INSTRUCTIONS.md`. | [ ] |
| C | **Variants/options pricing** | Create product with options and option-value prices. Add to cart with different options; confirm cart and checkout show correct prices. See `VARIANTS-OPTIONS-PRICING.md`. | [ ] |
| D | **Multi-seller shipping** | Cart with products from 2+ sellers; checkout; confirm shipping step shows and Purpletree (or configured) shipping is available. | [ ] |
| E | **Reseller tax exemption** | Register with reseller certificate upload; in admin approve exemption; as that customer, checkout; confirm tax not applied (or zero). | [ ] |

---

## Section 2.4 Testing

**Process:** See `docs/SECTION-2.4-TESTING.md` for how 2.4 (testing of all 2.1–2.3 deliverables, no regressions, no unsustainable slowdown) is fulfilled and which assets to use.

| # | Contract requirement | How to test | Pass? |
|---|------------------------|-------------|-------|
| 1 | All deliverables tested; no breaks; no unsustainable slowdown | Complete `MANUAL-TEST-SCRIPT-ALL.md` (Parts 0–6) or equivalent: `REGRESSION-TEST-CHECKLIST.md` plus 2.1/2.2/2.3 rows in this doc. Key pages (home, category, product, checkout) load in reasonable time; no sustained errors in logs. Any failure corrected and re-tested. | [ ] |

---

## Section 2.5 Documentation & Handoff

| # | Contract requirement | How to test | Pass? |
|---|------------------------|-------------|-------|
| A | Documentation – what changed, where, how to manage | Confirm these exist and are handed off. **Primary:** `DELIVERABLES-DOCUMENTATION.md`. **Supporting:** `docs/SECTION-2.2-AND-2.3-DELIVERABLES.md`, `docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md`, `docs/SECTION-2.4-TESTING.md`, `docs/SECTION-2.5-DOCUMENTATION-AND-HANDOFF.md`, `docs/AI-FEASIBILITY-AND-RESELLER-CERTIFICATE.md`, `MASS-IMPORT-INSTRUCTIONS.md`, `VARIANTS-OPTIONS-PRICING.md`, `STRIPE-INTEGRATION-NOTES.md`, `REGRESSION-TEST-CHECKLIST.md`, `DISCOUNT-INSTRUCTIONS.md`, `SETUP-LOCAL.md`, `README.md`. Full index: **docs/SECTION-2.5-DOCUMENTATION-AND-HANDOFF.md**. | [ ] |
| B | 4 weeks support | Post-delivery; not testable from code. Terms in `docs/SECTION-2.5-DOCUMENTATION-AND-HANDOFF.md` (2.5 B). | N/A |

---

## Summary

- **Fully testable in code/site:** 2.1 (audit, forgotten password, image upload, discounts, product visibility, analytics, onboarding, regression checklist), 2.2 (checkpoints), 2.3 (A–E), 2.4 (regression), 2.5A (docs).
- **Documented only (no code test):** Stripe Account Link migration, commission/split; AI feasibility.
- **Partial / environment-dependent:** Notifications (queue + cron); SEO content; scaling/load tests.

Mark each row Pass when verified. Use this with the client to confirm contract compliance.
