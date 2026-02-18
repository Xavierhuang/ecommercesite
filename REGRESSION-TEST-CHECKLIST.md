# Regression Test Checklist (Scope 2.1(4))

Use this checklist after any change to confirm that fixes and new features do not reintroduce known issues and that core flows still work.

## Pre-test

- Use a test store (e.g. dev) with test seller and buyer accounts.
- Ensure test data (products, categories, one seller with Stripe connected) exists.

## 2.1 Stabilization

- [ ] **Forgotten password:** Request reset from storefront; receive email; use link; set new password; log in with new password.
- [ ] **Notifications:** Place a test order; confirm buyer and seller receive expected emails; confirm no duplicate emails for the same event.
- [ ] **Image upload (admin):** In product edit, open image manager; upload an image; confirm it appears in the list and can be selected/attached on first attempt; save product and confirm image shows on front.
- [ ] **Discounts:** Add a coupon in admin; at checkout enter coupon and confirm discount applies. Add product discount/special; confirm correct price at checkout.
- [ ] **New product visibility:** As seller or admin, add a new product and enable it; confirm it appears on the storefront without long delay or cache refresh.
- [ ] **Analytics:** If GA4/Facebook IDs are set, load storefront and confirm tracking scripts are present in page source (no script errors in console).
- [ ] **Cache clear:** Admin > Tools > Cache clear (if installed); confirm cache clears without error.

## 2.2 / 2.1(5) Onboarding

- [ ] **Seller dashboard:** Log in as seller; confirm “Onboarding progress” shows and checkpoints (store info, Stripe, first product) reflect actual state.

## 2.3 Features

- [ ] **Minimum order:** Set “Low order fee” total in Extensions > Order totals (e.g. 50). Add cart subtotal below 50; try to open checkout; confirm redirect to cart with minimum order message. Add items to meet minimum; confirm checkout loads.
- [ ] **Bulk import:** Export template; fill one row; upload; confirm product appears or errors are shown.
- [ ] **Variants/options:** Product with options and option prices; add to cart with different options; confirm cart and checkout show correct prices.
- [ ] **Multi-seller shipping:** Cart with products from more than one seller; go to checkout; confirm shipping step shows and a shipping method (e.g. Purpletree shipping) is available and selectable.
- [ ] **Reseller tax exemption:** Register with reseller certificate upload; in admin approve the exemption; as that customer, add to cart and go to checkout; confirm tax is not applied (or is zero).

## Core flows (no regressions)

- [ ] **Registration:** New buyer and new seller registration complete without error.
- [ ] **Login / logout:** Buyer and seller login and logout work.
- [ ] **Cart:** Add/remove/update quantity; cart total and shipping eligibility correct.
- [ ] **Checkout:** Full checkout as guest and as logged-in customer; order is created and appears in admin and (if applicable) seller panel.
- [ ] **Admin:** Login; edit product; save; clear cache; confirm front reflects change.

## Performance

- [ ] After changes, key pages (home, category, product, checkout) load in a reasonable time; no sustained high CPU or errors in logs during normal browsing.

If any item fails, fix the regression before considering the deliverable complete. Document the failure and the fix in your handoff notes.
