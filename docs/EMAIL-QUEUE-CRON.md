# Email Queue Processor (Cron)

The email queue (`oc_email_queue`) is processed by calling the processor URL or by sending mail immediately where the manager is used. To send queued emails on a schedule, run the processor via cron.

## 1. Set a secret token

Add a config value so only your cron can call the processor:

```sql
INSERT INTO oc_setting (`store_id`, `code`, `key`, `value`, `serialized`) 
VALUES (0, 'config', 'tool_email_queue_token', 'YOUR_SECRET_TOKEN', 0)
ON DUPLICATE KEY UPDATE `value` = 'YOUR_SECRET_TOKEN';
```

Replace `YOUR_SECRET_TOKEN` with a long random string (e.g. 32+ characters). Use the same token in the cron URL below.

## 2. Schedule the cron

Call the storefront URL with the token (and optional limit):

```
https://your-domain.com/index.php?route=tool/email_queue/process&token=YOUR_SECRET_TOKEN
```

Optional: `&limit=100` to process up to 100 emails per run (default 50).

Example crontab (every 5 minutes):

```
*/5 * * * * curl -s "https://your-domain.com/index.php?route=tool/email_queue/process&token=YOUR_SECRET_TOKEN" > /dev/null 2>&1
```

## 3. Adding emails to the queue

Code that should queue instead of sending immediately can use `EmailNotificationManager::queue()` (in `system/library/email_notification_manager.php`). The manager is not wired into all OpenCart mail triggers; only callers that explicitly use the manager will use the queue. The tax exemption approval email (in `TaxExemptionHelper`) has a placeholder for queue/send; wire it there if you want those emails to go through the queue.
