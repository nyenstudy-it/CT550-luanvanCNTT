# QUICK FIX SQL COMMANDS

## ✋ BACKUP FIRST!
```sql
-- Create backup tables before making changes
CREATE TABLE orders_backup AS SELECT * FROM orders;
CREATE TABLE reviews_backup AS SELECT * FROM reviews;
```

---

## 🔧 FIX #1: DELETE INVALID ORDERS (customer_id = 1 which is admin)

```sql
-- Check how many orders are affected
SELECT COUNT(*) as total_invalid_orders FROM orders WHERE customer_id = 1;

-- List affected orders
SELECT id, customer_id, total_amount, status, created_at 
FROM orders 
WHERE customer_id = 1
ORDER BY id;

-- DELETE invalid orders (if confirmed test data)
DELETE FROM orders WHERE customer_id = 1;

-- Verify deletion
SELECT COUNT(*) FROM orders WHERE customer_id = 1;  -- Should be 0
```

---

## 🔧 FIX #2: DELETE INVALID REVIEWS

```sql
-- Check how many reviews are invalid
SELECT COUNT(*) as total_invalid_reviews 
FROM reviews r
WHERE NOT EXISTS (
    SELECT 1 FROM users u 
    WHERE u.id = r.customer_id AND u.role = 'customer'
);

-- List invalid reviews with details
SELECT 
    r.id, 
    r.customer_id, 
    COALESCE(u.name, 'DELETED USER') as customer_name,
    COALESCE(u.role, 'N/A') as user_role,
    r.product_id,
    r.rating,
    r.created_at
FROM reviews r
LEFT JOIN users u ON r.customer_id = u.id
WHERE NOT EXISTS (
    SELECT 1 FROM users u2 
    WHERE u2.id = r.customer_id AND u2.role = 'customer'
)
ORDER BY r.id;

-- DELETE invalid reviews
DELETE FROM reviews r
WHERE NOT EXISTS (
    SELECT 1 FROM users u 
    WHERE u.id = r.customer_id AND u.role = 'customer'
);

-- Verify deletion
SELECT COUNT(*) FROM reviews r
WHERE NOT EXISTS (
    SELECT 1 FROM users u 
    WHERE u.id = r.customer_id AND u.role = 'customer'
);  -- Should be 0
```

---

## 🔧 FIX #3: ADD FOREIGN KEY CONSTRAINTS

```sql
-- Add FK constraint to prevent future invalid reviews
ALTER TABLE reviews 
ADD CONSTRAINT reviews_customer_id_fk 
FOREIGN KEY (customer_id) REFERENCES users(id) 
ON DELETE CASCADE;

-- Add FK constraint to ensure orders only reference customers
ALTER TABLE orders 
ADD CONSTRAINT orders_customer_fk 
FOREIGN KEY (customer_id) REFERENCES users(id) 
ON DELETE RESTRICT;

-- Add index for performance
ALTER TABLE orders ADD INDEX idx_orders_customer_id (customer_id);
ALTER TABLE reviews ADD INDEX idx_reviews_customer_id (customer_id);
```

**Note**: If constraints fail, it means there are still invalid references. Run FIX #1 and #2 first.

---

## 🔧 FIX #4: VERIFY DATA INTEGRITY

```sql
-- Check all user references from various tables
SELECT 
    'orders' as table_name,
    COUNT(*) as total_records,
    SUM(CASE WHEN customer_id NOT IN (SELECT id FROM users) THEN 1 ELSE 0 END) as invalid_references
FROM orders
UNION ALL
SELECT 
    'reviews',
    COUNT(*),
    SUM(CASE WHEN customer_id NOT IN (SELECT id FROM users) THEN 1 ELSE 0 END)
FROM reviews
UNION ALL
SELECT 
    'attendances',
    COUNT(*),
    SUM(CASE WHEN staff_id NOT IN (SELECT user_id FROM staffs) THEN 1 ELSE 0 END)
FROM attendances
UNION ALL
SELECT 
    'payments',
    COUNT(*),
    SUM(CASE WHEN order_id NOT IN (SELECT id FROM orders) THEN 1 ELSE 0 END)
FROM payments;
```

---

## 🔍 VERIFICATION QUERIES

### Check 1: Verify admin doesn't have orders
```sql
SELECT COUNT(*) as admin_orders
FROM orders o
WHERE EXISTS (SELECT 1 FROM users u WHERE u.id = o.customer_id AND u.role = 'admin');
-- Should return: 0
```

### Check 2: Verify all reviews are from customers only
```sql
SELECT COUNT(*) as reviews_from_non_customers
FROM reviews r
WHERE NOT EXISTS (
    SELECT 1 FROM users u 
    WHERE u.id = r.customer_id AND u.role = 'customer'
);
-- Should return: 0
```

### Check 3: Verify all orders reference valid customers
```sql
SELECT COUNT(*) as orders_with_invalid_customer
FROM orders o
WHERE NOT EXISTS (
    SELECT 1 FROM users u 
    WHERE u.id = o.customer_id AND u.role = 'customer'
);
-- Should return: 0
```

### Check 4: Verify all attendances reference valid staff
```sql
SELECT COUNT(*) as attendances_with_invalid_staff
FROM attendances a
WHERE NOT EXISTS (
    SELECT 1 FROM staffs s 
    WHERE s.user_id = a.staff_id
);
-- Should return: 0
```

### Check 5: Verify no locked users currently logged in
```sql
SELECT COUNT(*) as locked_users
FROM users
WHERE status = 'locked';
-- Check if this matches expected locked accounts
```

---

## 📊 AFTER FIX VERIFICATION

```sql
-- Re-run system check
SELECT 'Total Users' as metric, COUNT(*) as count FROM users
UNION ALL SELECT 'Admins', COUNT(*) FROM users WHERE role = 'admin'
UNION ALL SELECT 'Staff', COUNT(*) FROM users WHERE role = 'staff'
UNION ALL SELECT 'Customers', COUNT(*) FROM users WHERE role = 'customer'
UNION ALL SELECT 'Total Orders', COUNT(*) FROM orders
UNION ALL SELECT 'Valid Orders', COUNT(*) FROM orders o WHERE EXISTS (SELECT 1 FROM users u WHERE u.id = o.customer_id AND u.role = 'customer')
UNION ALL SELECT 'Total Reviews', COUNT(*) FROM reviews
UNION ALL SELECT 'Valid Reviews', COUNT(*) FROM reviews r WHERE EXISTS (SELECT 1 FROM users u WHERE u.id = r.customer_id AND u.role = 'customer');
```

---

## ⚠️ IF BACKUPS NEEDED LATER

```sql
-- Restore from backup (if needed)
-- TRUNCATE orders;
-- INSERT INTO orders SELECT * FROM orders_backup;

-- TRUNCATE reviews;
-- INSERT INTO reviews SELECT * FROM reviews_backup;
```

