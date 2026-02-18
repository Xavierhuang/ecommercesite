# Platform Audit – Report and How to Use It

The platform audit deliverable consists of an **audit script** that collects system information and optionally a **list of findings** (this document and/or the script output).

---

## What Was Delivered

1. **Audit script:** `audit-platform.php` (in the project root)  
   - Run via command line: `php audit-platform.php`  
   - Or via browser if the server is configured to execute PHP in the project root.

2. **What the script captures (the “list of findings”)**
   - **PHP:** Version, memory limit, max execution time, upload/post limits, key extensions (mysqli, curl, gd, zip, mbstring, xml, json, openssl).
   - **Database:** MySQL version, database name, table count.
   - **OpenCart:** Version, store name, config email, theme.
   - **Extensions:** All installed extensions by type (payment, shipping, total, etc.).
   - **Modules:** Configured modules (first 10 listed; count of rest).
   - **Users:** Count of active admin users and customers.
   - **Products:** Count of enabled vs disabled products.
   - **Orders:** Total order count and breakdown by status.
   - **Events:** Active event triggers and their actions.

3. **Output**
   - Printed to the console (or browser).
   - Detailed data is also saved to `devdocs/work/audit/audit-data.json` when the script runs successfully.

---

## How to Get a Report or List of Findings

- **Option A – Run the script:** From the project root, run `php audit-platform.php`. The console output is the human-readable report; share that (or paste it into a doc) as the “list of findings” for that environment.
- **Option B – Use the JSON file:** After running the script, open `devdocs/work/audit/audit-data.json` for the full structured data (e.g. for parsing or dashboards).
- **Option C – Share this document:** You can share this `AUDIT-REPORT.md` with the client so they know what the audit covers and how to get a fresh report.

**Note:** The script connects to the database. Edit the connection details at the top of `audit-platform.php` if your DB host, user, or database name differ (e.g. production vs local). Ensure `devdocs/work/audit/` exists if you want the JSON file to be written.

---

## Summary for the Client

The platform audit has been delivered as:

- An **audit script** (`audit-platform.php`) that you can run to produce a **report** and a **list of findings** (PHP, database, OpenCart, extensions, modules, users, products, orders, events).  
- There is no separate static “audit report” file unless someone runs the script and saves the output (e.g. into a document or this repo). To get a current list of findings, run the script in the target environment and use the printed report or the generated JSON.

---

## Message You Can Send to the Client

You can send something like this:

"Yes. The platform audit deliverable is an audit script plus a short report doc. You can read: (1) **AUDIT-REPORT.md** in the project – it describes what the audit covers and how to run it. (2) The **audit script** itself is **audit-platform.php** in the project root. When we run it, it prints a report (PHP version, database, OpenCart version, extensions, modules, users, products, orders, events) and optionally saves detailed data to a JSON file. So the 'list of findings' is the output you get when you run that script. If you'd like a one-off written report for a specific environment (e.g. production), we can run the script there and send you the output as a document."
