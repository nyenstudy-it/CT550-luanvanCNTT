# ✅ Post-Import Validation & Testing Guide

## 📊 Verification After Import (Complete Checklist)

### **LEVEL 1: Quick Visual Check (2 minutes)**

#### ✓ Check Objects Are Present

```
PowerDesigner menu:
Tools → Model → Show Object List

┌─────────────────────────────────────────┐
│ Object Browse Pane:                     │
│                                         │
│ Expand each section:                    │
│ ├─ [+] Tables               (should be 33-36)
│ ├─ [+] Relationships        (should be 45+)
│ ├─ [+] Indexes              (should be 60+)
│ ├─ [+] Domains              (should be 25+)
│ ├─ [+] Triggers             (0-5)
│ └─ [+] Views                (3+)
└─────────────────────────────────────────┘

Expected values:
✓ Tables: 33 core + 3 system = 36 tables
✓ Relationships: 45+
✓ Indexes: 60+
✓ Domains: 25+ (data type definitions)

If counts differ → ⚠️ Issue detected!
```

#### ✓ Check Message Window

```
View → Message Window (F9)

┌─────────────────────────────────────────┐
│ Messages:                               │
│                                         │
│ Types expected:                         │
│ ☑ Blue [INFO]: Model loaded, objects  │
│ ☑ Green [OK]: Operations successful    │
│ ✗ Red [ERROR]: ❌ IF PRESENT = PROBLEM  │
│ ⚠️ Yellow [WARN]: Informational         │
│                                         │
│ Sample OK messages:                     │
│ • [INFO] Loaded 36 tables              │
│ • [INFO] Loaded 45 relationships       │
│ • [OK] Model generation complete      │
└─────────────────────────────────────────┘

ACTION: If you see RED messages:
  1. Read the error message
  2. Note the object name mentioned
  3. Check that specific table/relationship
  4. Compare with database
```

---

### **LEVEL 2: Detailed Entity Verification (5 minutes)**

#### 🔍 Spot Check Key Tables

```
Right-click table in diagram → Properties
Verify these critical tables:
```

**Table 1: users**

```
✓ Name: users
✓ Columns visible: 8+ (id, email, password, etc.)
✓ Primary Key: id (BIGINT UNSIGNED)
✓ Unique Key: email
✓ Indexes: idx_email, idx_created_at

Result: ☐ OK  ☐ MISSING COLUMNS  ☐ WRONG TYPES
```

**Table 2: orders**

```
✓ Name: orders
✓ Columns visible: 12+ (id, customer_id, total, status, etc.)
✓ Primary Key: id (BIGINT UNSIGNED)
✓ Foreign Keys showing:
    - customer_id → customers.id (1:N)
    - status → check constraint
✓ Indexes: idx_customer_id, idx_status, idx_created_at

Result: ☐ OK  ☐ MISSING FK  ☐ WRONG RELATIONSHIPS
```

**Table 3: products**

```
✓ Name: products
✓ Columns visible: 12+ (id, sku, name, price, etc.)
✓ Primary Key: id (BIGINT UNSIGNED)
✓ Unique Key: sku
✓ Foreign Keys:
    - category_id → categories.id (1:N)
    - supplier_id → suppliers.id (1:N)
✓ Indexes: idx_sku, idx_category_id, idx_supplier_id

Result: ☐ OK  ☐ MISSING RELATIONSHIPS  ☐ ORPHAN FK
```

**Table 4: attendances**

```
✓ Name: attendances
✓ Columns visible: 8+ (id, staff_id, date, status, etc.)
✓ Primary Key: id (BIGINT UNSIGNED)
✓ Foreign Keys:
    - staff_id → staffs.user_id (M:N junction)
    - UNIQUE constraint: staff_id + date per day
✓ Indexes: idx_staff_id, idx_date, idx_status

Result: ☐ OK  ☐ MISSING UNIQUE CONSTRAINT  ☐ FK ISSUE
```

---

### **LEVEL 3: Relationship & FK Detailed Check (5 minutes)**

#### 🔗 Verify Critical Foreign Keys

```
Method: Right-click Relationship line → Properties

Expected FKs (sample):
```

**FK #1: orders → customers**

```
┌──────────────────────────────────────┐
│ Foreign Key Properties:              │
│                                      │
│ Name: fk_orders_customers           │
│ Parent Table: customers              │
│ Parent Key: id                       │
│ Child Table: orders                  │
│ Child Column: customer_id            │
│                                      │
│ Cardinality: 1:N (one-to-many)      │
│ ON DELETE: SET NULL or CASCADE       │
│ ON UPDATE: CASCADE or RESTRICT       │
│                                      │
│ ✓ All properties match? → YES ✅    │
└──────────────────────────────────────┘
```

**FK #2: order_items → products**

```
✓ Name: fk_order_items_products
✓ Cardinality: M:N ← (important!)
✓ Parent: products
✓ Child: order_items
✓ Uses junction table: order_items
```

**FK #3: staffs → users**

```
✓ Name: fk_staffs_users
✓ Cardinality: M:1 (many staffs per user)
✓ Unique constraint on user_id+staffs? (No - users can have many staffs)
```

Test Command:

```sql
-- Run in MySQL to verify FKs

SELECT
  CONSTRAINT_NAME,
  TABLE_NAME,
  COLUMN_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;

Expected output: 45+ rows (one per FK)
```

---

### **LEVEL 4: Data Type & Constraint Verification (5 minutes)**

#### 📊 Verify Critical Data Types

```
For each table, check column data types:

Table: orders
Expected types:
├─ id: BIGINT UNSIGNED (✓ or ✗)
├─ customer_id: BIGINT UNSIGNED (✓ or ✗)
├─ total: DECIMAL(12,2) (✓ or ✗)
├─ discount: DECIMAL(12,2) (✓ or ✗)
├─ status: ENUM('pending','confirmed','shipped','delivered','cancelled') (✓ or ✗)
├─ created_at: DATETIME (✓ or ✗)
├─ updated_at: DATETIME (✓ or ✗)
└─ deleted_at: DATETIME NULL (✓ or ✗)

PowerDesigner Display (Right-click table → Properties → Columns):
┌────────────────────────────────────┐
│ Column    │ Type    │ Size │ Null  │
│───────────│─────────│──────│───────│
│ id        │ BIGINT  │ 20   │ NO    │
│ total     │ DECIMAL │ 12,2 │ NO    │
│ status    │ ENUM    │      │ YES   │
│ created_at│ DATETIME│      │ NO    │
└────────────────────────────────────┘

Key Points:
✓ All IDs = BIGINT UNSIGNED
✓ All money fields = DECIMAL(12,2)
✓ All status fields = ENUM
✓ All timestamps = DATETIME
✓ Not-nullable fields = NOT NULL marked
```

Test Command:

```sql
-- Verify data types
DESC orders;

Expected output:
Field           | Type              | Null | Key | Default | Extra
id              | bigint unsigned   | NO   | PRI | NULL    | auto_increment
customer_id     | bigint unsigned   | NO   | MUL | NULL    |
total           | decimal(12,2)     | NO   |     | 0.00    |
status          | enum(...)         | NO   |     | pending |
created_at      | datetime          | NO   |     | CURRENT |
deleted_at      | datetime          | YES  |     | NULL    |
```

---

### **LEVEL 5: Index Verification (3 minutes)**

#### 🔍 Check Indexes Present

```
Run in MySQL:

SELECT
  TABLE_NAME,
  INDEX_NAME,
  COLUMN_NAME,
  SEQ_IN_INDEX
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND TABLE_NAME IN ('orders', 'customers', 'products')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

Expected sample output:
┌─────────────┬──────────────────┬─────────────┬────────────┐
│ Table       │ Index Name       │ Column      │ Sequence   │
├─────────────┼──────────────────┼─────────────┼────────────┤
│ customers   │ PRIMARY          │ id          │ 1          │
│ customers   │ UQ_email         │ email       │ 1          │
│ customers   │ idx_created_at   │ created_at  │ 1          │
│ orders      │ PRIMARY          │ id          │ 1          │
│ orders      │ idx_customer_id  │ customer_id │ 1          │
│ orders      │ idx_status       │ status      │ 1          │
│ orders      │ idx_created_at   │ created_at  │ 1          │
│ orders      │ idx_total        │ total       │ 1          │
└─────────────┴──────────────────┴─────────────┴────────────┘

Count: Should have 60+ index entries total
```

---

### **LEVEL 6: Normalization Verification** (5 minutes)

#### 📐 Check 3NF (Third Normal Form)

For each table, verify:

**✓ 1NF: No repeating groups**

```
Bad example (NOT normalized):
orders table with column: items (TEXT) = "item1, item2, item3"

Good example (normalized):
orders table with 1:N to order_items table

PowerDesigner check:
├─ Should NOT see: TEXT/JSON fields containing lists
├─ Should see: Separate tables for M:N relationships
└─ Order Items table should be separate from Orders
```

**✓ 2NF: All non-key attributes depend on entire PK**

```
Check composite keys:
Example: order_items (order_id, product_id)
├─ quantity → depends on both (order_id, product_id) ✓
├─ unit_price → depends on both? (might depend only on product_id)

If unit_price depends only on product_id:
→ Move to separate prices table (↓ denormalize for performance, keep info)
```

**✓ 3NF: No transitive dependencies**

```
Bad: orders table with (customer_id, customer_email, customer_name)
     customer_email depends on customer_id (transitive)

Good:
├─ orders (order_id, customer_id)
└─ customers (customer_id, email, name)

PowerDesigner check:
Tools → Model → Analyze → Normalization Report
Should show: "All tables 3NF compliant" ✅
```

---

### **LEVEL 7: Compare with Actual Database** (10 minutes)

#### 🔄 Consistency Check

```sql
-- Compare object counts

-- PDM should match live database:

-- 1. Table count
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND TABLE_TYPE = 'BASE TABLE';
-- Expected: 33-36 (should match PDM count)

-- 2. FK count
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_NAME IS NOT NULL;
-- Expected: 45+ (should match PDM FK count)

-- 3. Index count (excluding PRIMARY)
SELECT COUNT(*)
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND INDEX_NAME != 'PRIMARY';
-- Expected: 60+ (should match PDM index count)

-- 4. Column count by table
SELECT
  TABLE_NAME,
  COUNT(*) as column_count
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'senhong_ocop'
GROUP BY TABLE_NAME
ORDER BY TABLE_NAME;

Expected sample:
orders          | 12
customers       | 10
products        | 14
...
```

**PowerDesigner Comparison:**

```
In PowerDesigner:
Tools → Model → Show Object List

Manually count:
• Tables: compare with SQL COUNT
• Relationships: compare with SQL FK count
• Indexes: compare with SQL index count

Record results:

┌──────────────────┬──────────────┬──────────────┐
│ Object Type      │ SQL Count    │ PDM Count    │
├──────────────────┼──────────────┼──────────────┤
│ Tables           │ 36           │ 36 ✓         │
│ Foreign Keys     │ 45           │ 45 ✓         │
│ Indexes          │ 62           │ 62 ✓         │
└──────────────────┴──────────────┴──────────────┘

If all match → ✅ Import 100% successful
If mismatch → ⚠️ Investigate the difference
```

---

## 🧪 Automated Test Script

### **Build Validation SQL Report**

```sql
-- Run this complete script to generate validation report

-- ==========================================
-- SENHONG_OCOP DATABASE VALIDATION REPORT
-- ==========================================
-- Execution Date: 2026-04-16
-- Database: senhong_ocop
-- Purpose: Verify model import accuracy
-- ==========================================

SELECT '=== TABLES SUMMARY ===' as section;

SELECT COUNT(*) as total_tables
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'senhong_ocop';

SELECT TABLE_NAME, COLUMN_COUNT FROM (
  SELECT
    TABLE_NAME,
    COUNT(*) as COLUMN_COUNT
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = 'senhong_ocop'
  GROUP BY TABLE_NAME
  ORDER BY TABLE_NAME
) t LIMIT 40;

SELECT '=== FOREIGN KEYS SUMMARY ===' as section;

SELECT COUNT(*) as total_foreign_keys
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_NAME IS NOT NULL;

SELECT
  TABLE_NAME,
  CONSTRAINT_NAME,
  COLUMN_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;

SELECT '=== INDEXES SUMMARY ===' as section;

SELECT COUNT(*) as total_indexes
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND INDEX_NAME != 'PRIMARY';

SELECT
  TABLE_NAME,
  INDEX_NAME,
  COLUMN_NAME
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND INDEX_NAME != 'PRIMARY'
ORDER BY TABLE_NAME, INDEX_NAME;

SELECT '=== UNIQUE CONSTRAINTS ===' as section;

SELECT
  TABLE_NAME,
  COLUMN_NAME,
  NON_UNIQUE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND NON_UNIQUE = 0
AND INDEX_NAME != 'PRIMARY'
ORDER BY TABLE_NAME;

SELECT '=== ENUM COLUMNS ===' as section;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND COLUMN_TYPE LIKE 'enum%'
ORDER BY TABLE_NAME;

SELECT '=== NULL CONFIGURATIONS ===' as section;

SELECT TABLE_NAME, COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'senhong_ocop'
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT '=== VALIDATION COMPLETE ===' as section;
```

Save this as: `validation_report.sql`

Run:

```bash
mysql -u root senhong_ocop < validation_report.sql > validation_report.txt
cat validation_report.txt
```

---

## ✅ Final Verification Checklist

After running all checks above:

```
LEVEL 1 - Visual (2 min)
☑ Object counts correct (33 tables, 45+ FK, 60+ idx)
☑ Message window clean (no red errors)

LEVEL 2 - Entities (5 min)
☑ users table: 8+ columns, correct PK
☑ orders table: 12+ columns, FK to customers
☑ products table: 12+ columns, multiple FK
☑ attendances table: 8+ columns, unique constraint

LEVEL 3 - Relationships (5 min)
☑ orders → customers: 1:N ✓
☑ order_items → products: M:N via junction ✓
☑ staffs → users: M:1 ✓
☑ attendances → staffs: M:N (or 1:N) ✓

LEVEL 4 - Data Types (5 min)
☑ All IDs: BIGINT UNSIGNED
☑ All money: DECIMAL(12,2)
☑ All status: ENUM
☑ All timestamps: DATETIME

LEVEL 5 - Indexes (3 min)
☑ Primary keys present on all tables
☑ 60+ indexes total
☑ Index on all foreign keys
☑ Index on common query columns

LEVEL 6 - Normalization (5 min)
☑ No repeating groups (1NF)
☑ No partial dependencies (2NF)
☑ No transitive dependencies (3NF)

LEVEL 7 - Database Consistency (10 min)
☑ Table count: SQL = PDM
☑ FK count: SQL = PDM
☑ Index count: SQL = PDM
☑ Column types: SQL = PDM

FINAL STATUS:
☑ All checks passed → ✅ IMPORT SUCCESSFUL
⚠️ Any issues → 🔧 NEEDS FIX
```

---

## 🚀 Next Steps

After validation passes:

```
1. ✅ Save PDM file
   File → Save

2. ✅ Export CDM
   Tools → Generate → CDM
   File → Export as PNG/PDF

3. ✅ Generate LDM (optional)
   Tools → Generate → Logical Data Model

4. ✅ Generate SQL for backup
   File → Generate → SQL Create Script

5. ✅ Document findings
   Create validation report PDF
   Share with team

6. ✅ Use for migrations
   Use generated SQL for:
   • Production deployment
   • Staging setup
   • Developer environments
```

---

**Validation Guide Version**: 1.0 | **Last Updated**: 2026-04-16 | **Expected Duration**: 30-40 minutes total
