# Finish scalability setup

Use this checklist to get the site ready for thousands of users. Do these in order.

**Quick option:** From the project root, run:

```bash
php run-scalability-setup.php
```

That adds the session table index. To also enable Redis cache in config:

```bash
php run-scalability-setup.php --redis
```

(Redis server must be running and the PHP Redis extension installed for cache to work.)

**Run on the dev server (sshpass):** From project root, set your SSH password then run the script:

```bash
export SSHPASS='your_ssh_password'
./run-scalability-on-server.sh
```

Requires `sshpass` (e.g. `brew install sshpass`). The script runs `php run-scalability-setup.php --redis` in `~/diversiply.co/tkurydyfjkv/` on the server.

---

## 1. Session table index (do this first)

Run the SQL once so session cleanup does not full-scan the table:

- Open `docs/scalability-session-index.sql`.
- If your table prefix is not `oc_`, change `oc_session` to `{your_prefix}session`.
- Run the script in your MySQL/MariaDB client (e.g. phpMyAdmin, command line, or your DB tool).

---

## 2. Switch cache to Redis (recommended for high traffic)

**2a. Install Redis**

- On the server: install Redis and the PHP Redis extension (e.g. `php-redis` or `pecl install redis`).
- Start Redis and ensure it listens on the host/port you will use (e.g. `127.0.0.1:6379`).

**2b. Define cache constants**

- In the project root, open `config.php`.
- Uncomment and, if needed, edit the cache lines at the bottom:
  - `CACHE_HOSTNAME` – Redis server (e.g. `127.0.0.1`).
  - `CACHE_PORT` – e.g. `6379` for Redis.
  - `CACHE_PREFIX` – optional; e.g. `oc_` to avoid key clashes.

**2c. Use Redis as cache engine**

- Open `system/config/default.php`.
- Find: `$_['cache_engine'] = 'file';`
- Change to: `$_['cache_engine'] = 'redis';`
- Save.

**2d. Test**

- Load the storefront and a few category/product pages. If you see no PHP errors and pages load, Redis cache is in use.

---

## 3. Keep using database sessions

- Catalog already uses DB sessions by default (`system/config/catalog.php` and default config).
- Do not switch catalog back to file sessions if you run more than one app server.

---

## 4. PHP OPcache

- In `php.ini`, enable OPcache and set reasonable limits, for example:
  - `opcache.enable=1`
  - `opcache.memory_consumption=128`
  - `opcache.max_accelerated_files=10000`
- Restart PHP (or the web server) after changing `php.ini`.

---

## 5. MySQL/MariaDB

- Set `max_connections` high enough for your app servers and cron (e.g. 200+).
- Set `innodb_buffer_pool_size` to a large share of RAM (e.g. 50–70% of available memory).
- Restart MySQL/MariaDB after changing the config.

---

## 6. Optional: CDN for static files

- Put images, CSS, and JS on a CDN or separate static host.
- Point the store’s base URL (and any asset URLs) to that CDN in store settings and/or theme config so all static requests go there.

---

When 1–5 are done, the platform is in good shape for thousands of users. For more detail and multi-server scaling, see `docs/SCALABILITY-AND-PERFORMANCE.md`.
