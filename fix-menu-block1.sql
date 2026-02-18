-- Add OC Mega Menu (Horizontal Menu 01) to block1 so the main nav shows.
-- Run this against your local OpenCart database if the menu is missing.
--
-- Usage:
--   mysql -u YOUR_USER -p YOUR_DATABASE < fix-menu-block1.sql
--
-- If your DB uses a different table prefix than "oc_", replace "oc_layout_module"
-- in this file with your prefix (e.g. "mystore_layout_module").
-- The ocmegamenu.215 module must exist in oc_module (from a full DB import).

-- Ensure ocmegamenu.215 is in block1 for layout 7 (default), 10 (category), 11 (product), 12 (home)
-- Inserts only when not already present to avoid duplicates.

INSERT INTO oc_layout_module (layout_module_id, layout_id, code, position, sort_order)
SELECT (SELECT COALESCE(MAX(layout_module_id), 0) + 1 FROM oc_layout_module AS m2), 7, 'ocmegamenu.215', 'block1', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM oc_layout_module WHERE layout_id = 7 AND code = 'ocmegamenu.215' AND position = 'block1');

INSERT INTO oc_layout_module (layout_module_id, layout_id, code, position, sort_order)
SELECT (SELECT COALESCE(MAX(layout_module_id), 0) + 1 FROM oc_layout_module AS m2), 10, 'ocmegamenu.215', 'block1', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM oc_layout_module WHERE layout_id = 10 AND code = 'ocmegamenu.215' AND position = 'block1');

INSERT INTO oc_layout_module (layout_module_id, layout_id, code, position, sort_order)
SELECT (SELECT COALESCE(MAX(layout_module_id), 0) + 1 FROM oc_layout_module AS m2), 11, 'ocmegamenu.215', 'block1', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM oc_layout_module WHERE layout_id = 11 AND code = 'ocmegamenu.215' AND position = 'block1');

INSERT INTO oc_layout_module (layout_module_id, layout_id, code, position, sort_order)
SELECT (SELECT COALESCE(MAX(layout_module_id), 0) + 1 FROM oc_layout_module AS m2), 12, 'ocmegamenu.215', 'block1', 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM oc_layout_module WHERE layout_id = 12 AND code = 'ocmegamenu.215' AND position = 'block1');
