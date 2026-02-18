# Analytics Setup and Verification

This document describes how to configure Google Analytics 4 and Facebook Pixel on the site and how to verify they are working.

---

## Where to set IDs

- **Default store:** Admin > **System > Settings** (or **Stores > Edit** for the default store) > **Analytics** tab.
- **Additional stores:** Admin > **Stores** > **Edit** the store > **Analytics** tab.

Enter:

- **Google Analytics 4 Measurement ID** – e.g. `G-XXXXXXXXXX`. Find it in [Google Analytics](https://analytics.google.com/) > Admin > Data Streams > select your Web stream > Measurement ID.
- **Facebook Pixel ID** – numeric ID. Find it in [Meta Events Manager](https://business.facebook.com/events_manager) > Data Sources > your Pixel > Settings > Pixel ID.

Leave a field empty to disable that tracker. Save the form.

---

## What is installed

- **Base tracking:** On every page load, the site injects:
  - GA4: gtag.js with your Measurement ID and a default `config`; plus a **PageView** (implicit with config).
  - Facebook: fbevents.js with your Pixel ID and a **PageView** event.
- **Events (when the helper is used):**
  - **Product view** – GA4 `view_item`, Facebook `ViewContent` (product page).
  - **Add to cart** – GA4 `add_to_cart`, Facebook `AddToCart`.
  - **Purchase** – GA4 `purchase`, Facebook `Purchase` (e.g. checkout success).
  - **Search** – GA4 `search` (search term).

Event scripts are output by `system/library/analytics_helper.php`. Controllers that need these events (product page, cart, checkout success, search) must call the helper and output the returned HTML in the template.

---

## Verifying Google Analytics 4

1. Enter your **GA4 Measurement ID** in the Analytics tab and save.
2. Open the **storefront** in a browser (ideally in a clean/incognito session).
3. In GA4: **Reports > Realtime**. You should see at least one user and the current page URL.
4. For events: complete a product view, add to cart, or purchase; in **Realtime > Event count by Event name** you should see `view_item`, `add_to_cart`, or `purchase` when the corresponding actions are done.
5. Optional: use [Google Tag Assistant](https://tagassistant.google.com/) or browser DevTools (Network tab, filter by `google-analytics.com` or `googletagmanager.com`) to confirm gtag requests.

---

## Verifying Facebook Pixel

1. Enter your **Facebook Pixel ID** in the Analytics tab and save.
2. In [Events Manager](https://business.facebook.com/events_manager) > your Pixel > **Test Events**: turn on **Test events** and (if offered) add your site URL.
3. Open the **storefront** and browse (e.g. homepage, product page, add to cart). In Test Events you should see **PageView**, and **ViewContent** / **AddToCart** / **Purchase** when those actions occur.
4. Optional: install the [Meta Pixel Helper](https://www.facebook.com/business/help/952192354843755) Chrome extension and check that the Pixel fires on your pages.

---

## Other recommended analytics (optional)

- **Microsoft Clarity** – session recordings and heatmaps. Add Clarity’s script snippet in the same way as GA4/Facebook (e.g. a new config key and output in header) if you want it.
- **TikTok Pixel** – if you run TikTok ads, add the TikTok Pixel snippet and map events (PageView, ViewContent, AddToCart, Purchase) similarly to the Facebook Pixel.

The current codebase only includes GA4 and Facebook Pixel in the Analytics tab and in `AnalyticsHelper`. To add Clarity or TikTok, extend the admin settings and the header/helper output accordingly.

---

## Troubleshooting

- **No data in GA4 / Facebook:** Confirm the correct ID is saved (no spaces), the storefront uses the theme that outputs `analytics_helper_html` (default and tt_makali themes do), and you are not blocking the scripts (ad-blockers, strict privacy extensions).
- **Events missing:** Ensure the controllers that render the product page, cart, and checkout success pass the helper’s event HTML into the template and that the template outputs it (e.g. `{{ analytics_events|raw }}`). Check `system/library/analytics_helper.php` for method names: `trackProductView`, `trackAddToCart`, `trackPurchase`, `trackSearch`.
