# SEO Setup and Best Practices

This document explains how the site supports search engine optimization (Google, Bing, etc.) and how to use meta descriptions, keywords, and other settings effectively.

---

## What the site does for SEO

- **Meta tags:** Every page can set a unique `<title>`, `<meta name="description">`, and `<meta name="keywords">`. These are filled from store settings (home page) or from product/category/information/blog meta fields.
- **Robots:** A `<meta name="robots">` tag is output on every page. Public pages use `index, follow`. Cart, checkout, login, register, and account dashboard use `noindex, nofollow` so they are not indexed.
- **Canonical URL:** Home, product, category, manufacturer, blog article, and other main pages set a canonical link so search engines know the preferred URL.
- **Open Graph and Twitter Card:** Each page outputs `og:title`, `og:description`, `og:url`, `og:type`, `og:image`, `og:site_name` and equivalent `twitter:*` tags. Product pages use the product image and type `product`; other pages use the store logo and type `website`. This improves how links look when shared and can help search engines.
- **Structured data (JSON-LD):**
  - **Home page:** `WebSite` schema (with search action) and `Organization` schema.
  - **Product page:** `Product` schema (name, description, image, SKU, offers with price and availability) and `BreadcrumbList` schema.

---

## Where to set meta title, description, and keywords

### Store (home page)

- **Admin > System > Settings** (or **Stores** and edit the default store).
- **General** tab: **Meta Title**, **Meta Tag Description**, **Meta Tag Keywords**.
- Use a clear, concise title (e.g. store name and main offer). Keep meta description to about 150–160 characters so it is not cut off in search results. Use relevant keywords in description and keywords field without stuffing.

### Products

- **Admin > Catalog > Products** (or seller product form): edit a product.
- **SEO** or **Data** tab (theme-dependent): **Meta Title**, **Meta Description**, **Meta Keywords**.
- If left empty, the store may fall back to the product name/description. Prefer unique meta title and description per product for better rankings.

### Categories

- **Admin > Catalog > Categories**: edit a category.
- **Data** tab: **Meta Title**, **Meta Description**, **Meta Keywords**.
- Use category-specific wording so category pages rank for relevant searches.

### Information pages

- **Admin > Catalog > Information**: edit a page.
- **Meta Title**, **Meta Description**, **Meta Keywords**.
- Use for About, Contact, Shipping, etc., with clear titles and descriptions.

### Blog (if used)

- Blog module settings often have global meta; individual articles may have their own meta title, description, and keywords. Fill these for important posts.

---

## Best practices for ranking

1. **Unique titles and descriptions:** Avoid duplicate or generic text across products and categories. Each page should have a distinct meta title and a short, unique description that matches the content.
2. **Keyword use:** Include the main search terms people use in the meta title and description, and in the visible page content. Avoid repeating the same phrase too often (keyword stuffing).
3. **Length:** Meta title about 50–60 characters; meta description about 150–160 characters so they display well in Google and Bing.
4. **URLs:** Use **SEO URLs** (Admin > System > Settings > Server: “Use SEO URLs”) and ensure product/category/information SEO keywords are set so URLs are short and readable (e.g. `/product-name`, `/category-name`).
5. **Sitemap:** Submit an XML sitemap to Google Search Console and Bing Webmaster Tools if the site or an extension generates one. This helps discovery and indexing.
6. **Mobile and speed:** Use a responsive theme and keep the site fast; both affect rankings.
7. **Content:** Add useful, original content (product descriptions, category text, blog posts) so pages are relevant to search queries.

---

## Verifying SEO

- **Google Search Console:** Add the site and check indexing, coverage, and any meta or structured data issues.
- **Bing Webmaster Tools:** Add the site for Bing/Yahoo indexing.
- **Rich results:** Use [Google Rich Results Test](https://search.google.com/test/rich-results) to confirm Product and BreadcrumbList (and other) structured data are valid.
- **View source:** On the storefront, open page source and confirm `<title>`, `<meta name="description">`, `<meta name="robots">`, `og:*` and `twitter:*` meta tags, and `<script type="application/ld+json">` blocks are present and correct.

---

## Technical notes

- Meta and OG data are set in catalog controllers (e.g. `common/home`, `product/product`, `product/category`, `information/information`) via `$this->document->setTitle()`, `setDescription()`, `setKeywords()`, and (for product) `setOgImage()`, `setOgUrl()`, `setOgType()`. The common header then outputs them in all main storefront themes (default and tt_makali).
- Robots are set in the Document class; checkout, cart, login, register, and account dashboard controllers call `$this->document->setRobots('noindex, nofollow')`.
- JSON-LD is built in the home and product controllers and output in the corresponding Twig templates (default and tt_makali home and product views).
