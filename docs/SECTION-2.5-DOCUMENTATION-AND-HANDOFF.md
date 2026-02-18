# Section 2.5 Documentation & Handoff; Post-Delivery Support

Contract language:

**A) Documentation (Included).** Contractor will provide documentation covering:
- What was changed;
- Where the changes were made (high-level); and
- Detailed instructions on where to find the changes and how to manage the changes going forward such that Client will not have to rehire a developer to make basic changes and updates.

**B) Included Support (4 weeks).** For four (4) weeks after Final Delivery, Contractor will provide reasonable Q&A and bug/error fixes only for issues directly related to the Deliverables. This support does not include new features, scope expansion, or third-party vendor negotiations. If Client so requests, the 4-week period may commence at a later date rather than immediately upon delivery. Contractor will use best efforts to accommodate such request. To the extent feasible, Contractor will demo the new features and fixes with Client in set video-sessions to expedite review after delivery.

---

## 2.5 A – Documentation (fulfilled by these assets)

### Primary handoff document

| Document | Purpose |
|----------|---------|
| **DELIVERABLES-DOCUMENTATION.md** | **Main 2.5 A doc.** Section 1: what was changed (high-level). Section 2: where changes were made (file/area table). Section 3: detailed instructions on where to find each change and how to manage it going forward (so Client can make basic changes without rehiring). Section 4: completion status. Section 5: support terms. |

### Supporting documents (what changed / where / how to manage)

| Document | Covers |
|----------|--------|
| **docs/SECTION-2.2-AND-2.3-DELIVERABLES.md** | 2.2 onboarding and 2.3 A–E: implementation summary, code references, how to verify each item. |
| **docs/SPLIT-PAYMENT-AND-COMMISSION-REVIEW.md** | Commission rules (product-only, 12/14/15%), shipping commission = 0, Stripe fee split; where in code; admin steps. |
| **MASS-IMPORT-INSTRUCTIONS.md** | Bulk product import: how to use, validation, error reporting, template. |
| **VARIANTS-OPTIONS-PRICING.md** | Product options/variants: how to configure, variant pricing, bulk import of options. |
| **STRIPE-INTEGRATION-NOTES.md** | Stripe OAuth, first-attempt success, failure states, commission/split notes. |
| **DISCOUNT-INSTRUCTIONS.md** | Coupons, product discount/special, how to add and manage. |
| **REGRESSION-TEST-CHECKLIST.md** | What to run after any change to avoid regressions. |
| **docs/EMAIL-QUEUE-CRON.md** | Email queue: token setup and cron URL for `tool/email_queue/process`. |
| **docs/SECTION-2.4-TESTING.md** | How 2.4 testing is performed; links to test scripts and checklist. |
| **docs/AI-FEASIBILITY-AND-RESELLER-CERTIFICATE.md** | Reseller cert workflow, AI feasibility (image/description, cert verification). |
| **CONTRACT-REQUIREMENTS-TEST.md** | Master test checklist for 2.1–2.5; sign-off. |
| **MANUAL-TEST-SCRIPT-ALL.md** | Step-by-step manual test script for all deliverables. |
| **SETUP-LOCAL.md** | Local setup and run. |
| **README.md** | Project overview and entry points. |

Handoff: deliver the repository (or agreed artifact) containing the above files. Client can use **DELIVERABLES-DOCUMENTATION.md** as the single entry point for “what / where / how to manage”; other docs give detail per topic.

---

## 2.5 B – Included Support (4 weeks)

- **Scope:** Reasonable Q&A and bug/error fixes **only** for issues **directly related** to the Deliverables (Sections 2.1–2.3). Does **not** include: new features, scope expansion, or third-party vendor negotiations.
- **Start date:** Four (4) weeks run from Final Delivery. If Client requests a later start (e.g. due to schedule), Contractor will use best efforts to accommodate so the 4-week period begins at that later date.
- **Demos:** To the extent feasible, Contractor will demo new features and fixes with Client in set video-sessions to expedite review after delivery.

Support is a contractual commitment; no code change implements it. Document the agreed Final Delivery date and (if applicable) the agreed support start date in handoff notes.
