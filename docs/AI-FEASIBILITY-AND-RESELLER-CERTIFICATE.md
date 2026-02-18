# AI Feasibility and Reseller Certificate (Contract 2.3(E) and AI Module)

This document maps the contract requirements for the AI-assisted module and reseller certificate verification to the current codebase and outlines what is done, what is missing, and how to implement the rest.

---

## Contract summary

1. **AI-assisted module or API** for:
   - **(i) Image enhancement and product description improvement** (onboarding and site uniformity).
   - **(ii) Reseller certificate verification** (AI scans certificate for authenticity and accuracy; name on certificate must match buyer; once verified, sales tax removed).

2. **Reseller certificate at signup (2.3(E))**: Buyers may upload a reseller certificate during account sign up. An AI-enabled module verifies the certificate; once verified, sales tax is removed from the order.

---

## Current implementation

### Reseller certificate upload at signup (2.3(E) – non-AI part)

| Requirement | Status | Location |
|-------------|--------|----------|
| Method for buyers to upload reseller certificate during sign up | **Done** | `catalog/controller/account/register.php` (file upload, optional); `catalog/view/theme/default/template/account/register.twig` (form: file, cert number, issuing state). |
| Store certificate and link to customer | **Done** | File saved under `image/catalog/reseller_certs/`; record in `customer_tax_exemption` via `system/library/tax_exemption_helper.php` (`submitExemption()`). |
| Remove sales tax when certificate is verified | **Done** | `catalog/model/extension/total/tax.php`: if customer has **approved** exemption (`hasValidExemption()`), tax totals are cleared. `TaxExemptionHelper::hasValidExemption()` checks `status = 'approved'` and expiry. |

**Database:** `customer_tax_exemption` (and `customer_tax_exemption_log`) created by `install-tax-exemption-tables.php`. Fields include: customer_id, exemption_type, certificate_number, issuing_state, certificate_file, status (pending/approved/rejected), approved_by, approved_date, etc.

**Verification today:** Manual. Admin approves or rejects the certificate; there is no AI scan, no automatic name match, and no automatic “authenticity” check.

### AI-related items

| Requirement | Status | Notes |
|-------------|--------|--------|
| (i) Image enhancement and product description improvement | **Not implemented** | No AI module or API in codebase for product images or descriptions. |
| (ii) AI scan of reseller certificate (authenticity + name match) | **Not implemented** | No OCR, no AI verification, no automatic name comparison. |

---

## Gaps to meet the contract

1. **Reseller certificate – AI verification**
   - After a buyer uploads a certificate (at signup or later), an **AI-enabled** step should:
     - **Scan** the certificate (image/PDF) to extract text (OCR) and optionally assess authenticity (e.g. format, consistency, or use of a third-party verification service).
     - **Compare** the name on the certificate to the **buyer’s name** (account firstname + lastname). If they match (within defined rules), treat as “name verified”.
   - **Outcome:** Either auto-approve when AI passes, or set an “AI verified” flag and allow fast-track admin approval; only then is tax removed (already implemented once status = approved).

2. **Image enhancement and product description (onboarding/site uniformity)**
   - Separate AI module or API to:
     - Improve product images (e.g. resize, normalize, enhance quality).
     - Improve or standardize product descriptions (e.g. grammar, structure, keywords).
   - Typically used during seller onboarding or product add/edit.

---

## Feasibility and implementation outline

### (ii) AI reseller certificate verification

**Feasibility:** Yes, with an external AI/OCR service or self-hosted model.

**Options:**

- **A. Third-party API**
  - Use a document/ID verification or OCR API (e.g. AWS Textract, Google Document AI, Azure Form Recognizer, or a dedicated reseller-certificate vendor) to:
    - Extract text from the uploaded image/PDF.
    - Return structured fields (e.g. certificate holder name, number, state, expiry).
  - In your code: after upload, send the file to the API; get back the name (and optionally number/state). Compare certificate name to `customer.firstname` + `customer.lastname` (normalize spaces/case; optionally fuzzy match). If match and optional authenticity checks pass, call `TaxExemptionHelper::approveExemption()` (or set status to a new “ai_verified” and have admin or a cron auto-approve).

- **B. Self-hosted OCR + rules**
  - Run OCR (e.g. Tesseract) on the image/PDF, parse text for a “name” field, compare to buyer name. Authenticity can be limited to format/pattern checks unless you add a separate model or service.

**Where to plug in:**

- **Option 1 – Right after upload (register or a dedicated “upload certificate” flow):**  
  In `catalog/controller/account/register.php` after saving the file and calling `submitExemption()`, call a new helper (e.g. `ResellerCertAIVerifier::verify($cert_file_path, $customer_id)`). That helper calls the AI/OCR service, gets the name, loads customer name from DB, compares; if pass, call `$helper->approveExemption($exemption_id, 0)` (or use a system user id for “AI approved”).

- **Option 2 – Async (queue/cron):**  
  Store the exemption as “pending”. A cron or queue worker picks up “pending” rows with a file, runs AI verification, and approves or flags for admin review. Better for heavy files or slow APIs.

**Data to store:** Consider adding columns to `customer_tax_exemption` such as `ai_verified` (tinyint), `ai_verified_at` (datetime), `ai_extracted_name` (varchar), so you can audit and support manual override.

### (i) Image enhancement and product description

**Feasibility:** Yes, via external API or self-hosted model.

**Options:**

- **Image:** Use an image API (e.g. cloud vision, resize/quality services) or a small pipeline (resize, normalize format, optional upscale/denoise) during product save or a bulk “onboarding” job.
- **Description:** Use an LLM or writing API (e.g. OpenAI, Claude, or local model) with a prompt that takes the current description and returns an improved/standardized version; store as suggestion or overwrite per store policy.

**Where to plug in:** Seller product add/edit (e.g. `catalog/controller/extension/account/purpletree_multivendor/sellerproduct.php`) or a dedicated “onboarding” / “improve listing” action that receives product_id and runs enhancement, then updates product image/description.

---

## Checklist (contract alignment)

- [x] Buyers can upload a reseller certificate during account sign up (optional section on register form).
- [x] Certificate is stored and linked to customer; tax is removed from the order when exemption status is **approved**.
- [ ] **AI verification** of reseller certificate (authenticity + name match); then auto-approve or fast-track so tax is removed without manual review when AI passes.
- [ ] **(i) AI-assisted image enhancement and product description improvement** for onboarding and site uniformity (separate module or API).

---

## Suggested next steps

1. **Reseller cert AI:** Choose an OCR/document API or self-hosted stack; add a small “verifier” class that takes certificate file + customer_id, runs OCR/API, compares name to customer, and approves or flags. Integrate that into the flow immediately after upload (or via cron). Add DB fields for ai_verified and ai_extracted_name if desired.
2. **Image/description AI:** Define scope (which fields, which products, seller vs admin); then add an enhancement step (API or internal tool) and hook it into product add/edit or onboarding.
3. **Admin UI:** Ensure there is an admin screen to list “pending” exemptions, see AI result (e.g. “name match: yes”), and approve/reject. Existing helper already supports approve/reject; the list view may need to be added or extended to show certificate list and AI status.

Once AI verification is in place and wired to approval, the flow “upload at signup → AI verifies → tax removed” satisfies the contract; the current code already removes tax when status is approved.
