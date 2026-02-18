# Scalability and Performance

This document describes how the platform is prepared to scale to many concurrent visitors, sellers, and buyers without crashing, slowing down, or malfunctioning, and what you should configure for production.

---

## What the codebase does for scale

### 1. Caching (catalog)

- **Category data:** `getCategory()` and `getCategories()` (catalog) are cached per store/language. Keys: `category.{id}.{store}.{lang}` and `category.list.{parent_id}.{store}.{lang}`. Invalidated when categories are updated in admin (or via product visibility helper).
- **Product detail:** `getProduct()` (catalog) is cached per product/store/language/customer group. Key: `product.{id}.{store}.{lang}.{customer_group}`. Invalidated when a product is updated (admin or seller).
- **Other cached data:** Store list, manufacturer list, product latest/popular/bestseller, localisation (zones, order status, language, currency, country), cmsblock, blog, etc. already use the cache layer.

Reduces database load on category menus, product pages, and listing widgets under high traffic.

### 2. File cache under load

- The **file** cache adaptor no longer runs expired-file cleanup on every request. Cleanup runs on about 1% of requests to avoid heavy I/O when many requests hit the same server. Expired entries are still ignored on read.

### 3. Session storage

- **Database sessions** are the default for the catalog (`config/session_engine` = `db`). Sessions are stored in the `session` table so multiple PHP app servers can share the same session store. Required for horizontal scaling behind a load balancer.

### 4. Cache invalidation

- When products or categories are added/edited/deleted (admin or seller), the relevant cache keys are cleared so the next request gets fresh data. With **Redis** or **Memcached**, the built-in adaptors use single-key `delete()`. The **file** adaptor’s `delete('category')` and `delete('product')` use a key prefix, so all category/product cache files are removed. For Redis, if you use keys like `category.1.1.1`, consider a custom adaptor that supports delete-by-prefix (e.g. `SCAN` + `DEL`) so a single `delete('category')` clears all category-related keys; otherwise rely on cache TTL.

---

## What you should configure for high traffic

### 1. Use Redis or Memcached for cache

- **Default** is `file` cache. For multiple app servers and better performance, switch to **Redis** or **Memcached**.
- In your **config** (e.g. `config.php` or wherever `cache_engine` is set), set:
  - `cache_engine` = `redis` or `mem` / `memcached`
  - Define `CACHE_HOSTNAME`, `CACHE_PORT`, and `CACHE_PREFIX` (and for Redis, ensure the Redis PHP extension is installed).
- This gives you a shared cache across all front-end servers and avoids filesystem I/O for cache.

### 2. Keep sessions in the database

- Catalog config should use **`session_engine` = `db`** (default in this project). Do not use file sessions when you run more than one app server.

### 3. Session table index

- The `session` table is used for session GC: `DELETE FROM session WHERE expire < ?`. Add an index on `expire` if it is missing so this query does not do a full table scan on large session tables:

```sql
-- Run once (replace oc_ with your DB_PREFIX if different)
CREATE INDEX idx_expire ON oc_session(expire);
```

### 4. PHP and OPcache

- Enable **OPcache** for PHP and set sensible values (e.g. `opcache.enable=1`, `opcache.memory_consumption` 128–256, `opcache.max_accelerated_files` 10000+). This reduces CPU and improves response time under load.

### 5. MySQL/MariaDB

- **Connections:** Set `max_connections` high enough for (number of app servers × max PHP workers per server) plus admin/cron.
- **InnoDB buffer pool:** Set `innodb_buffer_pool_size` to a large fraction of available RAM (e.g. 50–70%) so hot data stays in memory.
- **Query cache:** On MariaDB 10.x, consider query cache only if recommended for your version; on MySQL 8, query cache is removed so rely on application and Redis/Memcached caching instead.
- **Indexes:** Ensure indexes exist on frequently filtered/joined columns (e.g. `product_to_store`, `category_to_store`, `product_id`, `category_id`, `order_id`, `customer_id`). The session `expire` index above is important for GC.

### 6. CDN and static assets

- Serve images, CSS, and JS from a **CDN** or separate static host. Use `config_url` / `config_ssl` (and any theme/base URL settings) so asset URLs point to the CDN. This reduces load on app servers and improves latency for visitors worldwide.

### 7. Horizontal scaling (multiple app servers)

- Put **multiple PHP application servers** behind a **load balancer** (e.g. nginx, HAProxy, or cloud LB).
- Use **shared session store** (DB sessions, as above).
- Use **shared cache** (Redis or Memcached, as above).
- Ensure **file uploads** (product images, etc.) are on shared storage (e.g. NFS, object storage with a shared URL base) so every app server can serve the same files.
- If you use **cron** or queue workers, run them on a single node or use a distributed lock so jobs are not duplicated.

### 8. Rate limiting and abuse

- Protect **checkout** and **API** endpoints with rate limiting (e.g. at the load balancer, nginx, or in application middleware) to avoid abuse and sudden spikes that could cause timeouts or crashes.
- Optionally add **CAPTCHA** or similar on login/register if you see bot traffic.

### 9. Background jobs

- Offload heavy work (e.g. bulk emails, reports, export) to **queues** or **cron** so HTTP requests stay fast. The codebase may already have an email queue or similar; use it instead of sending mail synchronously during a request where possible.

### 10. Monitoring and limits

- **Monitor** CPU, memory, disk I/O, database connections, and cache hit rates. Set **alerts** on high error rates or slow response times.
- Configure **PHP** `max_execution_time` and `memory_limit` appropriately. Use **time limits** for long-running CLI jobs rather than the web limit.

---

## Checklist for production scale

- [ ] `cache_engine` = Redis or Memcached; CACHE_* configured.
- [ ] `session_engine` = `db` for catalog.
- [ ] Index on `session(expire)`.
- [ ] OPcache enabled and tuned.
- [ ] MySQL/MariaDB: `max_connections`, `innodb_buffer_pool_size`, and key indexes in place.
- [ ] Static assets (images, CSS, JS) served via CDN or separate host.
- [ ] Multiple app servers: load balancer, shared session (DB), shared cache (Redis/Memcached), shared file storage.
- [ ] Rate limiting on checkout/API; optional CAPTCHA on auth.
- [ ] Heavy work moved to queues/cron; monitoring and alerts in place.

These steps, together with the built-in catalog caching and file-cache throttling, help the platform handle a large number of concurrent visitors and users without crashing, slowing down, or malfunctioning.
