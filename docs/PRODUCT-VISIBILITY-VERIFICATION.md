# Verify: New Seller Product Shows on Site When Enabled

This checks that a product added (or enabled) by a seller appears on the storefront quickly, without constant refresh or long delays.

---

## What the code does

- **Seller add/edit product:** The seller product model clears the **product** cache (`$this->cache->delete('product')`) when a product is added or updated, so listing pages can show the new/updated product immediately.
- **Admin add/edit product:** The admin product controller also calls **ProductVisibilityHelper**, which clears product, category, manufacturer, and SEO caches so the product is visible right away.

---

## How to verify

### 1. Seller adds a new product (enabled)

1. Log in as a **seller**.
2. Go to **Seller Dashboard > Products > Add Product** (or your theme’s equivalent).
3. Fill required fields (name, price, category, etc.) and set **Status** to **Enabled**.
4. **Save** the product.
5. Open the **storefront** in a new tab or window (or same browser, different tab).
6. Go to the **homepage** or the **category** where you assigned the product.
7. **Check:** The new product should appear in the list within a **single page load**. One refresh is enough; you should not need to refresh repeatedly or wait a long time.

### 2. Seller enables an existing disabled product

1. As seller, open **Products**, edit a product that is currently **Disabled**.
2. Set **Status** to **Enabled** and save.
3. On the **storefront**, go to the relevant category or search for that product.
4. **Check:** The product appears after one refresh (or immediately if you navigate to the category after saving).

### 3. Optional: Admin adds a product

1. **Admin > Catalog > Products > Add**.
2. Create a product with **Status** Enabled and save.
3. On the storefront, open the category (or homepage) where it should show.
4. **Check:** Product appears without long delay or multiple refreshes.

---

## If the product does not appear

- **One refresh:** Doing a single refresh (F5 or reload) after saving is normal; cache is cleared on save, so the next request should see the new data.
- **Still missing after one refresh:** Confirm the product is **Enabled**, has a **category**, is **in stock** (if the theme filters out out-of-stock), and that the seller’s products are approved (e.g. `purpletree_vendor_products.is_approved = 1` if your flow uses it).
- **Cache:** If you use file or Redis cache, ensure the cache directory is writable so `cache->delete()` can run. You can also try **Admin > System > Settings > Edit store > Server tab** and clear cache, or use any **Clear cache** tool you have.

---

## Summary

| Step | Action | Expected |
|------|--------|----------|
| 1 | Seller adds product, Status = Enabled, Save | Product saved. |
| 2 | Open storefront, go to category or home | Product visible within one page load / one refresh. |
| 3 | (Optional) Seller enables a disabled product, Save | Same: visible after one refresh. |

If step 2 passes, the deliverable *“new product added to a seller’s store shows up on the site when enabled without constant refresh or long wait”* is verified.
