# Stripe Integration Notes (Scope 2.1(3))

## Current implementation

- **Seller onboarding:** The platform uses the **Stripe Account Link API** by default (create account + Account Link, return with token). Legacy Express OAuth is used when `payment_pts_stripe_use_account_link` = 0.
- **Files:** `catalog/controller/extension/account/purpletree_multivendor/stripeconnect.php` (Account Link start/return + OAuth); `dashboardicons.php` and `common/column_left.php` (Connect link); model `stripeconnect.php` (pending table). Table `oc_stripe_account_link_pending` for return flow.

## Account Link API (implemented)

Connect uses Account Link by default. Flow:

1. **Create a Connected Account** (or use existing): `\Stripe\Account::create([...])` with type `express` (or `standard`).
2. **Create an Account Link:** `\Stripe\AccountLink::create(['account' => $account_id, 'refresh_url' => ..., 'return_url' => ..., 'type' => 'account_onboarding'])`.
3. **Redirect the seller** to `$accountLink->url` instead of the Express OAuth URL.
4. On return at `return_url`, the account is already linked; verify the account id and store it for the seller.

Set `payment_pts_stripe_use_account_link` = 0 to use legacy Express OAuth. This removes dependency on the legacy OAuth flow and aligns with Stripe’s current Connect onboarding.

## Commission and split-payment rules (contract)

- **Platform commission:** Product subtotal only (exclude shipping and taxes). Platinum 12%, Gold 14%, Silver 15%.
- **Stripe fee split:** 2.9% + $0.30 per transaction split equally between seller and platform.

A full review of split-payment and commission logic is in **`docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md`**. Summary: commission is calculated in sellerorder (commented block), pp_adaptive, and via purpletree_vendor_commissions; payout uses purpletree_order_total and commission invoice. Shipping has been removed from commission in pp_adaptive (product-only). Admin should set shipping commission to 0, map Platinum/Gold/Silver to 12/14/15% (e.g. store_commission), and Stripe fee split (50/50) is not yet implemented in transfer flow.

## First-attempt success and common failure states

**Goal:** Sellers can create Stripe Connected accounts on the first attempt where eligible; common failure states are identified and mitigated within the platform’s control.

**Eligibility (within our control):**
- The **Connect** link is shown only when the seller has completed store information and has an approved store (`store_status` set). Sellers who are not yet approved see a clear message (“Complete store information and get approved first to connect Stripe”) and no link, so they do not complete OAuth only to be rejected on return.

**Failure states and mitigations:**

| Failure state | Mitigation |
|---------------|------------|
| User denies or closes Stripe OAuth | Callback reads `error` / `error_description` in the redirect. For `access_denied` we show: “You cancelled or did not approve the Stripe connection. You can try Connect again when ready.” Other OAuth errors show Stripe’s message. |
| No `code` in callback (e.g. stale bookmark, broken redirect) | Message: “Stripe did not return an authorization code. Please try Connect again.” |
| Token exchange fails (network, Stripe down) | cURL errors set: “Connection to Stripe failed. Please try again.” Invalid or non-JSON response: “Stripe returned an invalid response. Please try Connect again.” |
| Stripe returns `invalid_grant` (code expired or already used) | User message: “The connection link expired or was already used. Please click Connect again to get a new link.” |
| Stripe returns other API error (e.g. wrong client_secret) | We surface Stripe’s `error_description` (or `error`) so support can diagnose. |
| Store not active when callback runs | Message: “Your seller store is not active yet. Complete and get your store approved first, then try Connect again.” (Eligibility check reduces how often this happens.) |
| DB save fails after successful token exchange | Message: “Stripe account could not be saved. Please try again or contact support.” Logged when debug enabled. |
| Any exception in callback | Catch block sets: “An error occurred while connecting to Stripe. Please try again or contact support.” (Previously no user-visible error was set.) |

**Outside platform control:** Redirect URI must match exactly in the Stripe Dashboard (Connect settings). Wrong URI causes Stripe to show an error before redirecting back. Admin must configure client ID/secret and redirect URI correctly.

**When moving to Account Link API:** Use `refresh_url` so if the user drops off during onboarding they can be sent back to the same flow; handle `return_url` and optional `refresh_url` in the UI for a smoother first attempt.

## Checklist

- [x] Replace Express OAuth link in dashboard with Account Link flow (create account + create AccountLink + redirect to url). *(Implemented; default is Account Link; set payment_pts_stripe_use_account_link = 0 to use OAuth.)*
- [x] Confirm sellers can complete onboarding on first attempt; handle common failures (e.g. refresh_url) in the UI. *(Eligibility gating, OAuth error handling, invalid_grant message, and catch-block message implemented; refresh_url used in Account Link.)*
- [x] Verify commission % and fee split in payment/order code and admin commission reports. *(See docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md. Commission-on-subtotal-only enforced in pp_adaptive; Stripe fee split in pts_stripe; sellerorder commission block enabled.)*
