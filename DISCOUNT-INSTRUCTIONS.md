# How to Add and Manage Discounts (Scope 2.1)

This platform supports discounts at **store** (seller), **product**, and **site-wide** levels. Below is how to verify the feature and how to use each type.

---

## How to verify this is done

1. **File check (optional)**  
   From project root run: `php verify-deliverables.php`  
   Confirm **Discount helper** is listed as `[OK]` (file `system/library/discount_helper.php` exists).

2. **Site-wide coupons**  
   - Log in to **Admin**.  
   - Go to **Extensions > Extensions**, choose type **Promotions**. Find **Coupon** and ensure it is enabled (green).  
   - Go to **Marketing > Coupons**. You should see the coupon list (may be empty). Click **Add New** and confirm you can set Code, Type (Percent/Fixed), Discount, dates, etc.  
   - On the **storefront**, add a product to cart, go to **Checkout**. Confirm there is a **Coupon** / "Have a coupon?" field where the customer can enter a code.

3. **Product-level (admin)**  
   - **Admin > Catalog > Products** > open any product for **Edit**.  
   - Confirm these tabs exist: **Discount** and **Special**.  
   - In **Discount**: add a row (Customer Group, Quantity, Priority, Price, Date Start/End) and Save.  
   - In **Special**: add a row (Customer Group, Priority, Price, Date Start/End) and Save.  
   - On the storefront, view that product: the special price or quantity-based discount should apply when conditions match.

4. **Store-level (seller)**  
   - Log in as a **seller**. Open **Seller Dashboard** (e.g. **Account > Seller Dashboard** or your theme's seller area).  
   - Confirm **Coupons** (or "Seller Coupons") appears in the menu; open it and add a coupon (code, type, discount, products/categories if available).  
   - Go to **Products > Edit** for one of the seller's products. Confirm **Discount** and **Special** tabs exist and you can add rows.  
   - On the storefront, add that product to cart and use the seller's coupon at checkout (or confirm the product shows the special/discount when applicable).

If all of the above work, the deliverable "easily add discounts to stores, items, or site-wide" is in place.

---

## 1. Site-wide / cart: Coupons

**Where:** Admin creates site-wide coupons; sellers can create their own coupons (store-level) from the Seller Dashboard.

**Admin – enable and create a coupon**

1. **Admin > Extensions > Extensions** → select type **Promotions** → find **Coupon** → enable it if needed.  
2. **Admin > Marketing > Coupons** → click **Add New**.  
3. Fill in:  
   - **Coupon name** (internal label).  
   - **Code** (e.g. `SAVE10`) – this is what the customer types at checkout.  
   - **Type**: **Percentage** or **Fixed Amount**.  
   - **Discount**: e.g. `10` for 10% or $10 fixed.  
   - **Total**: minimum order total required (optional; leave 0 for no minimum).  
   - **Date start / Date end** (optional).  
   - **Uses total** and **Uses per customer** (optional limits).  
4. Optionally restrict to specific **Products** or **Categories**.  
5. Save.

**Use:** Customer enters the coupon code in the checkout "Have a coupon?" / "Coupon" field and the discount is applied to the order.

---

## 2. Product-level: Product Discounts

**Where:** Admin or seller edits a product and uses the **Discount** tab. Applies when the customer is in the chosen group and buys at least the given quantity.

**Steps**

1. **Admin:** **Catalog > Products** → click **Edit** on a product.  
   **Seller:** **Seller Dashboard > Products** → **Edit** on a product.  
2. Open the **Discount** tab.  
3. Click **Add** (or equivalent) and set:  
   - **Customer Group** (e.g. Default).  
   - **Quantity**: minimum quantity to get this price (e.g. 2 = "buy 2 or more").  
   - **Priority**: if multiple discount rows exist, lower number = higher priority.  
   - **Price**: discounted unit price for this quantity tier.  
   - **Date Start** / **Date End** (optional).  
4. Save the product.

The discount applies at cart/checkout when the customer's group and quantity match.

---

## 3. Product-level: Specials

**Where:** Same product edit screen as above; **Special** tab. Replaces the regular price when conditions match (no minimum quantity required unless you combine with other rules).

**Steps**

1. **Admin > Catalog > Products** (or **Seller Dashboard > Products**) → **Edit** a product.  
2. Open the **Special** tab.  
3. Add a row: **Customer Group**, **Priority**, **Price** (special price), **Date Start**, **Date End**.  
4. Save.

The product will show the special price on the storefront when the customer is in that group and the dates are valid.

---

## 4. Store-level (seller store) discounts

**Where:** Seller dashboard. There is no single "store-wide percentage" field; store-level effect is achieved by (a) seller coupons and (b) product discounts/specials on that seller's products.

**Seller coupons (store-level)**

1. Log in as **seller** → open **Seller Dashboard**.  
2. In the menu, open **Coupons** (or **Seller Coupons**).  
3. **Add** a coupon: set **Code**, **Type** (Percent/Fixed), **Discount**, **Total** (min order), dates, and optionally restrict to **Products** or **Categories** for that seller.  
4. Customers enter this code at checkout; it applies to the seller's products (or the whole cart, depending on how the extension is configured).

**Product-level for a store**

- In **Seller Dashboard > Products > Edit** each product, use the **Discount** and **Special** tabs as in sections 2 and 3.  
- Applying discounts/specials to multiple products in the same store gives a "store-level" effect without a separate store-wide field.

---

## 5. Summary

| Type        | Who sets it   | Where to set it                                      | How customer gets it                    |
|-------------|---------------|------------------------------------------------------|-----------------------------------------|
| Site-wide   | Admin         | Marketing > Coupons                                  | Enter code at checkout                   |
| Store       | Seller        | Seller Dashboard > Coupons; or product Discount/Special | Enter seller coupon or buy qualifying products |
| Per item    | Admin or seller | Catalog/Seller Products > Edit > Discount / Special | Automatic when group/quantity/dates match |

The **DiscountHelper** (`system/library/discount_helper.php`) is used for coupon validation. Product discounts and specials are stored in the database (`product_discount`, `product_special`, `coupon`). No code changes are required to add or remove discounts; use the admin and seller screens above.
