# How to Test 2b: Buyer/Seller/Transaction Notification Reliability

Scope 2.1(2b): *Buyer/seller/transaction notification reliability (no duplicates, consistent).*  
Status: **Partial** – queue infrastructure in place; order/seller emails still sent via events. This test verifies behaviour and that no duplicate emails are sent for the same event.

---

## 1. Infrastructure check (optional)

Confirm the queue and manager exist:

- **Table:** `oc_email_queue` (from `install-enhancements.sql`).  
  Run: `mysql -u root YOUR_DB -e "SHOW TABLES LIKE 'oc_email_queue';"`  
  Expect: one row.

- **Manager:** `system/library/email_notification_manager.php` exists.  
  It provides `queue()`, `send()`, `processQueue()`. Order/seller emails are not yet routed through it; they use the event-based `mail/order` and `mail/order/alert` (and Purpletree seller emails).

---

## 2. Functional test: one order, expected emails, no duplicates

Use **two email addresses you can access** (e.g. your email as buyer, another as seller store email, or a test inbox).

### 2.1 Prepare

- Note **buyer email** (e.g. the address you use at checkout).
- Note **seller email** (store email for a seller whose product you will buy), or use **config_mail_alert_email** (Admin > System > Settings > Mail: “Mail Alert”) as the admin alert inbox.
- Ensure mail is working (e.g. SMTP with App Password) so emails are actually delivered.

### 2.2 Place one order

- As **buyer**: add one product to cart, go through checkout, place **one** order.
- Do **not** change order status again in admin (so only one `addOrderHistory` run for the new order).

### 2.3 Check inboxes

- **Buyer inbox:** Expect **exactly one** order confirmation (or “order received”) for that order.  
  **Pass:** 1 email. **Fail:** 0 or 2+ (duplicate).

- **Seller inbox** (if product is from a seller and Purpletree sends seller mail): Expect **exactly one** seller order notification for that order.  
  **Pass:** 1 email. **Fail:** 2+ (duplicate).

- **Admin alert inbox** (Mail Alert in store settings): Expect **exactly one** new order alert for that order.  
  **Pass:** 1 email. **Fail:** 2+ (duplicate).

### 2.4 Duplicate check (same event)

- Place **one** more order (different order).
- Again check buyer, seller, and admin inboxes.  
  **Pass:** Each party receives exactly one new email for this second order (no duplicate for the same order/event).

---

## 3. Optional: test the email queue

If you want to confirm the queue and manager work:

- Add a small script or admin action that:
  - Instantiates `EmailNotificationManager` with your config/db/log.
  - Calls `queue('your@email.com', 'Test', 'Body')`.
  - Then calls `processQueue(10)` (or run a cron that does the same).
- Check your inbox for the test email and check `oc_email_queue`: one row with `status = 'sent'`.

---

## 4. Pass criteria for 2b (current “partial” scope)

- **Infrastructure:** `oc_email_queue` exists; `EmailNotificationManager` is present and can queue/send/process (optional run above).
- **Behaviour:** For a single order, buyer gets one confirmation, seller gets one notification (if applicable), admin gets one alert; no duplicate emails for the same order/event.
- **Note:** Full “reliability” (all notifications via queue + cron + retry) would require wiring all mail triggers to the manager and running a queue processor; that is not required for this test.

Use this together with **MANUAL-TEST-SCRIPT-ALL.md** section 1.3 (Notifications).
