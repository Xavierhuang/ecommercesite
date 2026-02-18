# Variants / Options Pricing (Scope 2.3(C))

OpenCart supports **product options** with **option-specific pricing**, which provides variant-style behaviour (e.g. size/color with different prices) without duplicate product listings.

## How it works

- One **product** can have multiple **options** (e.g. Size, Color).
- Each **option value** can have a **price** (and price prefix + or -), **weight**, and **points**.
- The same product description, SEO, and main image are shared; only the selected option changes price (and optionally weight).

## Where to configure

- **Admin:** Catalog > Products > Edit product > **Option** tab.
- Add option (e.g. "Size"), then add option values (Small, Medium, Large).
- For each value set **Price** (e.g. +5.00 or 10.00) and **Price Prefix** (+ or - or =).
- **Variant-specific price:** Use the option value price to add or override (e.g. base price 10, Large +3 gives 13 for Large).

## Bulk import (variants)

- In **Bulk Product Upload**, use the **ProductOption** and **ProductOptionValue** sheets (see export template). Include option_id, option_value_id, quantity, subtract, **price**, price_prefix, points, weight as needed.
- Validation: ensure option_id and option_value_id exist in the system; price should be numeric.

## Testing

- Create one product with options and variant prices; add to cart with different options and confirm cart and checkout show the correct price per variant.
- Run through seller onboarding: add a product with options and confirm no errors and correct display on the storefront.

## Technical note

- Tables: `oc_product_option`, `oc_product_option_value`. The catalog uses these when adding to cart and calculating totals. No additional variant module is required for standard option-based variant pricing.
