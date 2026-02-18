# Section 2.4 Testing (Contract)

Contract language:

> All Deliverables under each of Sections 2.1–2.3 shall include testing performed by Contractor to ensure that the feature or adjustment, respectively, is functioning properly and fully, that such features have not caused other items to break or malfunction, and that such features do not slow the website down unsustainably. If any feature or adjustment is not working as anticipated, or if another feature of the website has malfunctioned as a result of Contractor's work, Contractor will correct the malfunction, feature or adjustment implementation to ensure that such feature, adjustment or Deliverable is working perfectly, without any additional cost to Client.

---

## How 2.4 is fulfilled

Testing is performed by the Contractor using the following assets. Each deliverable under 2.1–2.3 is covered so that:

1. **Features work properly and fully** – Each deliverable has explicit test steps and a pass criterion.
2. **No other items broken or malfunctioning** – Core flows (registration, login, cart, checkout, admin edit, cache) are re-tested after changes (regression).
3. **No unsustainable slowdown** – Key pages (home, category, product, checkout) are checked for reasonable load time; logs are checked for sustained errors.

If any test fails or a regression is found, the Contractor corrects the implementation until the feature works and regressions are resolved, at no additional cost to the Client.

---

## Test assets (use these to perform 2.4 testing)

| Asset | Purpose |
|-------|---------|
| **CONTRACT-REQUIREMENTS-TEST.md** | Master checklist: maps every contract item (2.1–2.5) to “How to test” and a Pass? checkbox. Use for sign-off. |
| **MANUAL-TEST-SCRIPT-ALL.md** | Step-by-step script in runnable order: automated checks, 2.1, 2.2, 2.3, **2.4 (Part 4)**, core flows (Part 5), performance (Part 6), docs (Part 7). Complete this to satisfy “all deliverables tested; no breaks; no unsustainable slowdown”. |
| **REGRESSION-TEST-CHECKLIST.md** | Short regression checklist to run after any change. Covers 2.1 stabilization, 2.2/2.3 features, core flows, and performance. Use to confirm no breaks and no slowdown. |
| **docs/SECTION-2.2-AND-2.3-DELIVERABLES.md** | Quick reference for 2.2 and 2.3: what is implemented, where in code, and how to verify each item. |

---

## 2.4 checklist (Contractor)

- [ ] **2.1** – All 2.1 deliverables tested per CONTRACT-REQUIREMENTS-TEST.md (audit, forgotten password, notifications, image upload, discounts, product visibility, analytics, onboarding, regression, Stripe notes, AI feasibility).
- [ ] **2.2** – Onboarding checkpoints and validation tested; no dead ends or broken links.
- [ ] **2.3 A–E** – Minimum order, mass import, variants/options, multi-seller shipping, reseller tax exemption tested per SECTION-2.2-AND-2.3-DELIVERABLES.md and REGRESSION-TEST-CHECKLIST.md.
- [ ] **Regression** – Core flows (registration, login, cart, checkout, admin product edit, cache clear) executed with no breaks.
- [ ] **Performance** – Home, category, product, checkout pages load in reasonable time; no sustained errors in logs after normal browsing.
- [ ] **Fixes** – Any failure or regression corrected and re-tested until all pass.

Once all items above are checked and any failures corrected, 2.4 Testing is complete. Document results and any fixes in handoff notes.
