# AI Feasibility (Scope 2.1)

The contract asks for an assessment and, where feasible, implementation of:

1. **Image enhancement and product description improvement** (onboarding and site uniformity).
2. **Reseller certificate verification:** Scan uploaded certificate for authenticity and match of name on certificate to buyer name; on verification, remove sales tax.

## Reseller certificate verification

- **Current state:** Buyers can upload a reseller certificate at registration (scope 2.3(E)). The file is stored and a record is created in `oc_customer_tax_exemption` with status `pending`. An admin can approve or reject manually. Tax is removed at checkout when the customer has an approved exemption.
- **AI feasibility:** Automating verification typically requires:
  - **Document parsing:** An API or service (e.g. OCR + entity extraction, or a dedicated document-verification API) to read the certificate and extract name, number, state, expiry.
  - **Name matching:** Compare certificate name to the buyer’s registered first/last name (or business name); allow for minor variations (e.g. trim, case, “Inc”).
  - **Integration point:** A post-upload step (cron or queue) that (1) sends the uploaded file to the AI/API, (2) receives result, (3) updates `oc_customer_tax_exemption` (e.g. set status to `approved` if match and valid, or `rejected` with a note).
- **Recommendation:** Implement as an optional module that calls an external API (e.g. OpenAI Vision, or a document-verification provider). Keep manual approval as fallback. Store “verified_by” (e.g. `manual` vs `ai`) in the exemption record if needed for auditing.

## Image enhancement and product description improvement

- **Use case:** Improve seller onboarding and site uniformity by enhancing product images and descriptions.
- **Feasibility:** 
  - **Image enhancement:** Use an image API (e.g. resize, background removal, or quality enhancement) when a seller uploads a product image; optional “enhance” button in the product image manager.
  - **Description improvement:** Use a language/API (e.g. completion or rewrite) to suggest or expand product descriptions from a short input; show suggestion in product edit for the seller to accept or edit.
- **Recommendation:** Implement as optional features behind config flags. Use a single provider (e.g. OpenAI API) for both image and text to limit keys and complexity. Ensure seller consent and that automated changes are clearly indicated (e.g. “AI-suggested description”).

## Summary

- **Reseller certificate:** Automatable with an external document/OCR + matching API; integrate as an optional step after upload, with manual approval still available.
- **Image and description:** Feasible with standard image and language APIs; implement as optional tools in seller product flow with config and consent.

No AI code is included in the current deliverable; this document serves as the feasibility assessment. Implementation would require API keys, provider choice, and (if applicable) compliance review for data sent to third parties.
