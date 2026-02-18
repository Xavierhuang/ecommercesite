# Split-Payment and Commission Review (Business Rules)

## Intended business rules

1. **Platform commission on product amounts only (exclude shipping and taxes)**
   - Platinum subscriptions: **12%** of product subtotal retained by platform
   - Gold subscriptions: **14%** of product subtotal retained by platform
   - Silver subscriptions: **15%** of product subtotal retained by platform

2. **Stripe processing fee split**
   - Stripe fee **2.9% + $0.30** per transaction is split **equally** between seller and platform (50% each).

---

## Current implementation (findings)

### Where commission is calculated

| Location | Base used | Shipping in commission? | Notes |
|----------|-----------|--------------------------|--------|
| `catalog/model/extension/purpletree_multivendor/sellerorder.php` | `order_product['total']` | N/A (block commented out) | Commission written when order status changes to processing/complete; currently **commented out** so no commission rows created on status change. |
| `catalog/model/extension/purpletree_multivendor/quick_order.php` | N/A | N/A | Inserts commission = 0; vendor orders created with `total_price` = product total. |
| `catalog/controller/extension/payment/pp_adaptive.php` | `sellerorder['total_price']` | **Yes** | Adds `shippingcommision` (from `module_purpletree_multivendor_shipping_commission` on seller_shipping total) on top of product commission. **Contradicts “commission on product only”.** |
| `catalog/controller/extension/payment/wk_stripe.php` | Product totals (with tax recalc in one path) | Unclear | Complex seller split; includes tax in seller amount in one branch. |
| `catalog/controller/extension/payment/pts_stripe.php` | Uses `purpletree_vendor_commissions` and `purpletree_order_total` (code='total') | Depends on how commission rows and “total” are built | Transfer = invoice `total_pay_amount` = total_price - commission. Commission comes from DB. |

### Commission rate source (Platinum / Gold / Silver)

- **Global:** `module_purpletree_multivendor_commission` (admin Multivendor settings).
- **Per store:** `purpletree_vendor_stores.store_commission` (admin edit store).
- **Per category:** `purpletree_vendor_categories_commission` (category commission).
- **Subscription tiers:** There are no named “Platinum”, “Gold”, or “Silver” in code. To align with 12% / 14% / 15%, admin must set **store_commission** (or category/group) to 12, 14, or 15 for the corresponding subscription level.

### Seller “total” and payout

- **pts_stripe:** Seller total = `purpletree_order_total.value` where `code='total'`. That “total” is built per seller (e.g. sub_total + seller_shipping + …). So it can include shipping. **Payout** = that total minus sum of `purpletree_vendor_commissions.commission` for that seller/order.
- **Commission base:** In sellerorder (when uncommented) and pp_adaptive, base is product-related (`order_product['total']` or `total_price`). In OpenCart, `order_product.total` is usually the line total; whether it includes tax depends on store config. For strict “exclude taxes”, base should be explicit product subtotal (pre-tax).

### Stripe fee split (2.9% + $0.30)

- **Not implemented.** Transfers to connected accounts are for the full `total_pay_amount`. No deduction for Stripe’s fee or 50/50 split. To implement: compute fee = `amount * 0.029 + 0.30`, deduct `fee / 2` from the transfer to the seller, and retain the other half from the platform side (or reduce platform commission by that amount).

---

## Recommendations

### 1. Commission on product subtotal only (exclude shipping and taxes)

- **Config / admin:** Ensure commission is never applied to shipping:
  - Set **Shipping commission** (e.g. `module_purpletree_multivendor_shipping_commission`) to **0** in Multivendor settings so no commission is taken on shipping.
- **Code (pp_adaptive):** Stop adding `shippingcommision` to the commission amount so commission is based only on product total (see change below).
- **Code (sellerorder):** If the commented commission block is re-enabled, keep base as `order_product['total']` (product line total). If the store uses tax-inclusive order totals, consider basing commission on a subtotal that excludes tax (e.g. derive from price and quantity without tax) so commission is strictly on product amounts only.
- **purpletree_order_total “total”:** For payout logic that uses `code='total'`, ensure “total” is used only for seller payout amount, not as the commission base. Commission base should remain product-only (e.g. from `purpletree_vendor_orders.total_price` or order_product totals).

### 2. Platinum 12% / Gold 14% / Silver 15%

- Map subscription plans to commission in admin:
  - Either set **store_commission** per seller to 12, 14, or 15 according to their plan, or
  - Use **category commission** or **seller group** so that the correct percentage is applied (12 / 14 / 15) with no commission on shipping.

### 3. Stripe fee split (2.9% + $0.30, 50/50)

- In the Stripe transfer flow (e.g. pts_stripe when creating the transfer):
  - Compute Stripe fee: `fee = transfer_amount * 0.029 + 0.30` (in same currency unit as transfer, e.g. dollars).
  - Seller share of fee: `seller_fee = fee / 2`.
  - Transfer to seller: `total_pay_amount - seller_fee` (and platform effectively keeps the other half of the fee from its commission or margin).
- Apply the same idea if using application_fee or other Stripe flows so the net effect is 50/50 fee split.

---

## Checklist

- [ ] Set Multivendor “Shipping commission” to 0 (admin).
- [x] Remove shipping from commission in pp_adaptive (code: do not add shippingcommission to commission).
- [x] Ensure sellerorder commission block (if re-enabled) uses product-only base; commission_shipping = 0.
- [x] Map Platinum / Gold / Silver to 12% / 14% / 15% via store_commission — see Commission admin steps below.
- [x] Implement Stripe fee split in pts_stripe: deduct half of (2.9% + $0.30) from seller transfer.

---

## Commission admin steps

**1. Shipping commission = 0**

- Go to Admin > Extensions > Extensions > Modules > Purpletree Multivendor (or Extensions > Purpletree Multivendor).
- Find **Shipping Commission (in percent)** and set it to **0**. Save.
- This ensures no commission is taken on shipping where the module uses shipping_commission (pp_adaptive already forces 0 in code).

**2. Platinum 12% / Gold 14% / Silver 15%**

- Set **Store commission** per store: Admin > Purpletree Multivendor > Sellers/Stores > Edit store.
- Set **Store Commission** to **12** (Platinum), **14** (Gold), or **15** (Silver) per subscription.
- Or use Category commission or Seller group so the correct percentage applies (category > store_commission > global commission).
