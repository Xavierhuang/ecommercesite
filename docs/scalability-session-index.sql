-- Add index on session expire for faster session GC (recommended for scale).
-- Replace oc_ with your DB_PREFIX if different. Run once.

CREATE INDEX idx_expire ON oc_session(expire);
