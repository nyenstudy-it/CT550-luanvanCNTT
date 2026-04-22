-- restore_and_delete_april_orders.sql
-- Purpose: Restore import_items.remaining_quantity from all April orders (2026-04-01 -> 2026-04-18)
-- then delete all April orders and dependent records (returns, cancellations, payments, items).
-- IMPORTANT: Run a full DB backup before executing. Tested for MySQL 8+ (JSON_TABLE).

SET @START_DATE = '2026-04-01 00:00:00';
SET @END_DATE = '2026-04-18 23:59:59';

-- Begin transactional operation
START TRANSACTION;

-- 1) Build temporary table of quantities to restore per import_item (batch)
DROP TEMPORARY TABLE IF EXISTS temp_batch_restore;
CREATE TEMPORARY TABLE temp_batch_restore AS
SELECT
  jt.batch_id AS import_item_id,
  SUM(jt.quantity) AS qty_sum
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
-- Parse JSON batch_details; if NULL rows will be ignored
JOIN JSON_TABLE(oi.batch_details, '$[*]'
  COLUMNS (
    batch_id BIGINT PATH '$.batch_id',
    quantity INT PATH '$.quantity'
  )
) AS jt
WHERE o.created_at BETWEEN @START_DATE AND @END_DATE
GROUP BY jt.batch_id;

-- 2) Apply restore to import_items.remaining_quantity
UPDATE import_items ii
JOIN temp_batch_restore t ON ii.id = t.import_item_id
SET ii.remaining_quantity = ii.remaining_quantity + t.qty_sum;

-- 3) Recompute inventories.available_quantity and total_quantity from import_items
DROP TEMPORARY TABLE IF EXISTS temp_inventory;
CREATE TEMPORARY TABLE temp_inventory AS
SELECT
  product_variant_id,
  SUM(remaining_quantity) AS available_qty,
  SUM(quantity) AS total_qty
FROM import_items
GROUP BY product_variant_id;

UPDATE inventories i
JOIN temp_inventory t ON i.product_variant_id = t.product_variant_id
SET i.available_quantity = t.available_qty,
    i.total_quantity = t.total_qty,
    i.updated_at = NOW();

-- 4) Delete dependent records for April orders in safe order
-- 4.1 order_return_images -> order_returns -> order_returns
DELETE ri FROM order_return_images ri
JOIN order_returns r ON ri.order_return_id = r.id
JOIN orders o ON r.order_id = o.id
WHERE o.created_at BETWEEN @START_DATE AND @END_DATE;

DELETE r FROM order_returns r
JOIN orders o ON r.order_id = o.id
WHERE o.created_at BETWEEN @START_DATE AND @END_DATE;

-- 4.2 order_cancellations
DELETE c FROM order_cancellations c
JOIN orders o ON c.order_id = o.id
WHERE o.created_at BETWEEN @START_DATE AND @END_DATE;

-- 4.3 payments
DELETE p FROM payments p
JOIN orders o ON p.order_id = o.id
WHERE o.created_at BETWEEN @START_DATE AND @END_DATE;

-- 4.4 order_items
DELETE oi FROM order_items oi
JOIN orders o ON oi.order_id = o.id
WHERE o.created_at BETWEEN @START_DATE AND @END_DATE;

-- 4.5 finally orders
DELETE FROM orders
WHERE created_at BETWEEN @START_DATE AND @END_DATE;

COMMIT;

-- Helpful checks (run after commit manually if desired):
-- 1) Count leftover April orders (should be 0)
-- SELECT COUNT(*) FROM orders WHERE created_at BETWEEN @START_DATE AND @END_DATE;

-- 2) Show import_items changes applied
-- SELECT * FROM temp_batch_restore LIMIT 50;

-- NOTES:
-- - This script relies on order_items.batch_details being stored as JSON arrays with objects {batch_id, quantity}.
-- - If your MySQL version is older and does not support JSON_TABLE, restore must be done via a PHP/Laravel script that parses JSON and increments import_items.
-- - BACKUP your DB before executing. For phpMyAdmin paste and run in SQL tab, or use `mysql` CLI.
