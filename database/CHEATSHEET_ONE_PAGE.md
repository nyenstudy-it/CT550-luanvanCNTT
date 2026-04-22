# 📋 POWERDESIGNER CDM↔LDM↔PDM - One-Page Cheatsheet

## 🎯 3 Model Definitions (In This Order)

```
┌─────────────────────────────────────────────────────────┐
│ CDM (Conceptual) = BUSINESS VIEW                        │
│ • Entities, Relationships, Business Rules              │
│ • DB-Independent • Easy to understand                  │
│ • WHO: Business Analyst, Product Manager               │
│ • TOOL: Reverse from PDM or create from requirements   │
└─────────────────────────────────────────────────────────┘
                         ↓ [Normalize]
┌─────────────────────────────────────────────────────────┐
│ LDM (Logical) = NORMALIZED LOGIC                        │
│ • Tables, PK/FK, Attributes • 3NF Format              │
│ • DB-Independent • Technical but clear                │
│ • WHO: Architect, Analyst, Senior Developer           │
│ • TOOL: Generate from CDM via Tools→Generate→LDM      │
└─────────────────────────────────────────────────────────┘
                         ↓ [Transform to MySQL]
┌─────────────────────────────────────────────────────────┐
│ PDM (Physical) = MYSQL-SPECIFIC REALITY                │
│ • CREATE TABLE syntax, real data types, indexes       │
│ • MySQL-specific: BIGINT, DECIMAL, ENUM, etc.         │
│ • WHO: DBA, Backend Developer                          │
│ • TOOL: Generate from LDM via Tools→Generate→PDM      │
│ • DBMS MUST = MySQL 5.1+ or 8.0                       │
└─────────────────────────────────────────────────────────┘
```

---

## ⚡ 3-Step Quick Workflow

### **STEP 1: PDM → CDM** (5 min)

```bash
# Khi: Bạn có PDM MySQL → cần business view
Tools → Generate → Conceptual Data Model
  Name: CDM_senhong_ocop
  Options: ✓ all defaults
  [OK] → Auto-generates CDM
File → Save As → CDM_senhong_ocop.cdm
```

**Result:** Entities + Attributes (no FK, indexes, data types)

---

### **STEP 2: CDM → LDM** (5 min)

```bash
# Khi: Bạn có CDM → cần normalize 3NF
Tools → Generate → Logical Data Model
  Name: LDM_senhong_ocop
  Options:
    ☑ 3rd Normal Form (3NF)
    ☑ Create junction Tables for M:M
    ☑ Create Primary Keys
    ☑ Create Foreign Keys
  [OK] → Auto-generates LDM normalized
File → Save As → LDM_senhong_ocop.ldm
```

**Result:** Tables + PK/FK + Normalized (no MySQL-specific types)

---

### **STEP 3: LDM → PDM MySQL** (5 min)

```bash
# Khi: Bạn có LDM → cần MySQL-specific for deployment
Tools → Generate → Physical Data Model
  Name: PDM_senhong_ocop_MySQL_v1
  DBMS: MySQL 5.1+ ⭐⭐⭐ [CRITICAL!]
  Options:
    ☑ Create Indexes
    ☑ Create Foreign Keys
    ☑ Create Unique Constraints
    ☑ Create Check Constraints
  [OK] → Auto-generates PDM
File → Save As → PDM_senhong_ocop_MySQL_v1.pdm
```

**Result:** MySQL CREATE TABLE + data types + indexes + constraints

---

## 🔄 Reverse Workflow (PDM ← LDM ← CDM)

```bash
Bạn có database hiện tại → Tạo PDM bằng Reverse Engineer:

1. Database → Configure Connections
   Server: localhost | Port: 3306 | User: root | DB: senhong_ocop

2. Database → Reverse Engineer → From Database Connection
   Select: [all tables]

3. Result: PDM created automatically

4. Then: PDM → LDM → CDM (lặp lại steps above)
```

---

## ⚠️ Top 5 Mistakes to AVOID

### ❌ #1: Quên chọn DBMS = MySQL

```bash
# WRONG:
Tools → Generate → PDM
[Dialog opens with DBMS = Generic]
[Click OK] ← WRONG! Result: invalid MySQL SQL

# RIGHT:
Tools → Generate → PDM
DBMS dropdown: [MySQL 5.1+] ← SELECT THIS!
[Click OK] ✓
```

### ❌ #2: M:N quan hệ vẫn còn trong LDM

```bash
# WRONG LDM:
discounts M···N products (tức không có junction table)

# RIGHT LDM:
discounts (1)→ discount_product (N)
products (1)→ discount_product (N)
```

**Fix:** Regenerate LDM → ☑ Create junction tables

---

### ❌ #3: Missing Foreign Keys in PDM

```bash
# WRONG SQL:
CREATE TABLE orders (
  id BIGINT PK,
  customer_id BIGINT,  ← No FK constraint!
  ...
);

# RIGHT SQL:
CREATE TABLE orders (
  id BIGINT PK,
  customer_id BIGINT,
  CONSTRAINT fk_orders_customers
    FOREIGN KEY (customer_id)
    REFERENCES customers(id) ON DELETE SET NULL
);
```

**Fix:** Generate PDM → ☑ Create Foreign Keys

---

### ❌ #4: Data types bị nhầm ở PDM

```bash
# WRONG:
id VARCHAR(32767) ← way too long!
email VARCHAR(1)  ← too short!
amount INTEGER    ← no decimal!

# RIGHT:
id BIGINT(20) UNSIGNED
email VARCHAR(255)
amount DECIMAL(12,2)
```

**Fix:** Review PDM types before SQL generation

---

### ❌ #5: Normalization không 3NF ở LDM

```bash
# WRONG LDM:
orders (order_id, customer_id, customer_name, customer_email)
  ↑ customer_name & customer_email là dự phòng!

# RIGHT LDM:
orders (order_id, customer_id FK)
customers (customer_id, name, email)
```

**Fix:** Regenerate LDM with ☑ 3NF option

---

## 🎮 PowerDesigner Menu Map

```
MAIN GENERATION (Tools Menu):

Tools
 ├─ Generate
 │  ├─ Conceptual Data Model ← CDM (from PDM)
 │  ├─ Logical Data Model ← LDM (from CDM)
 │  ├─ Physical Data Model ← PDM (from LDM)
 │  ├─ SQL Create Script ← SQL (from PDM)
 │  └─ DDL Scripts
 │
 ├─ General Options
 │  ├─ Naming Conventions ← Set before generate
 │  └─ DBMS Selection ← ⭐ CRITICAL!
 │
 └─ Reverse Engineer → From Database Connection

DEPLOYMENT (File Menu):

File
 ├─ Generate → SQL Create Script
 ├─ Export → PDF / PNG / SVG (for documentation)
 └─ Print
```

---

## ✅ Pre-Generation Checklist

### Before generating CDM:

- ☑ PDM file open (have all 33 tables?)
- ☑ All tables visible
- ☑ All relationships correct
- ☑ PDM file saved

### Before generating LDM:

- ☑ CDM file open
- ☑ Each entity has identifier
- ☑ All relationships defined
- ☑ CDM file saved

### Before generating PDM:

- ☑ LDM file open
- ☑ Normalized to 3NF (check!)
- ☑ All PK/FK visible
- ☑ **DBMS selected: MySQL 5.1+**
- ☑ LDM file saved

### Before generating SQL:

- ☑ PDM file open (MySQL-specific)
- ☑ All data types correct
- ☑ All constraints defined
- ☑ All indexes present
- ☑ Naming convention consistent
- ☑ PDM file saved

---

## 📊 What Changes in Each Step?

| Feature            | CDM          | LDM            | PDM                  |
| ------------------ | ------------ | -------------- | -------------------- |
| Entities/Tables    | Entity       | Table          | Table                |
| Attributes/Columns | Attribute    | Column         | Column               |
| Identifiers        | Identifier   | PK             | PK                   |
| Relationships      | Relationship | FK             | FK                   |
| Data Types         | Generic      | Generic        | MySQL-specific       |
| Indexes            | ❌           | ❌             | ✅                   |
| Constraints        | ❌           | Basic          | Full (CHECK, UNIQUE) |
| M:N Relations      | M···N        | Junction Table | Junction Table       |
| DBMS-specific      | No           | No             | Yes (MySQL)          |

---

## 🚀 Execute SQL After Generation

### Generate SQL Script from PDM:

```bash
File → Generate → SQL Create Script
  Name: senhong_ocop_create.sql
  Options:
    ☑ DROP TABLE IF EXISTS
    ☑ CREATE TABLE
    ☑ Indexes
    ☑ Constraints
  Save → senhong_ocop_create.sql
```

### Execute in MySQL:

```bash
# Terminal:
mysql -h 127.0.0.1 -u root senhong_ocop < senhong_ocop_create.sql

# Or MySQL Workbench:
File → Open SQL Scrip → Select senhong_ocop_create.sql
Edit → Execute (Ctrl+Enter)
```

---

## 🎯 Naming Convention Standard

```
CDM Layer:
  Entity: PascalCase (Order, Customer, Product)
  Attribute: camelCase (totalAmount, createdDate)

LDM & PDM Layer:
  Table: snake_case (orders, customers, products)
  Column: snake_case (total_amount, created_date)
  PK: id
  FK: {table}_id (customer_id, order_id)
  INDEX: idx_{table}_{column} (idx_orders_status)
  UNIQUE: uk_{table}_{column} (uk_users_email)
```

---

## 📋 File Naming Convention

```
GOOD:
  CDM_senhong_ocop_v1.0.cdm
  LDM_senhong_ocop_v1.0.ldm
  PDM_senhong_ocop_MySQL_v1.0.pdm
  senhong_ocop_v1.0.sql

BAD:
  Model.cdm
  final.pdm
  script.sql
  updated_model.ldm
```

---

## 🐛 If Generation Fails

```
Error Message?
   ↓
1. Check PowerDesigner Log:
   View → Message Window (F9)

2. Verify Source Model:
   File → Close (don't save)
   File → Open (reopen)

3. Fix Common Issues:
   • Wrong DBMS selected?
   • Missing relationships?
   • Duplicate entity names?
   • Circular references?

4. Try Again:
   Tools → Generate → [Model Type]
```

---

## 📌 Quick Reference: Key Shortcuts

```
Ctrl+S      Save
Ctrl+Shift+S Save As
Ctrl+Z      Undo
Ctrl+A      Select All
F5          Refresh
F9          Message Window
F12         Properties
Ctrl+N      New Model
Ctrl+O      Open

Tools → Generate ← Most important!
```

---

## 🎓 Common Workflows

### **Workflow 1: Initial Setup (from scratch)**

```
1. Gather requirements from stakeholder
2. Create CDM (or reverse engineer from existing DB)
3. Stakeholder review CDM
4. Generate LDM (normalize)
5. Tech team review LDM
6. Generate PDM for MySQL
7. DBA/Dev review PDM
8. Generate SQL
9. Deploy to database
```

---

### **Workflow 2: Schema Changes**

```
1. Update CDM (add/remove/modify entities)
2. Regenerate LDM from new CDM
3. Regenerate PDM from new LDM
4. Compare old vs new SQL scripts
5. Create migration script for changes
6. Deploy migration
```

---

### **Workflow 3: Performance Optimization**

```
1. Open existing PDM (MySQL)
2. Review execution plans
3. Add composite indexes
4. Consider denormalization if needed
5. Regenerate SQL with optimized DDL
6. Benchmark before/after
```

---

## 📞 Where to Find Help

| Problem             | Location                          |
| ------------------- | --------------------------------- |
| Understand 3 models | CDM_LDM_PDM_TRANSFORMATION.md     |
| Menu paths          | POWERDESIGNER_QUICK_COMMANDS.md   |
| Fix errors          | COMMON_MISTAKES_BEST_PRACTICES.md |
| Schema reference    | DATABASE_SCHEMA_ANALYSIS.md       |
| Quick reference     | QUICK_REFERENCE.md                |

---

## ✨ Golden Rules

```
1. ⭐ ALWAYS select DBMS = MySQL before generating PDM
2. ⭐ ALWAYS verify 3NF before generating SQL
3. ⭐ ALWAYS check naming conventions are consistent
4. ⭐ ALWAYS verify Foreign Keys are present in PDM
5. ⭐ ALWAYS backup .pdm files with version numbers
6. ⭐ ALWAYS export models to PDF before deploying
7. ⭐ ALWAYS review generated SQL before executing
```

---

**Last Print Date**: 2026-04-16 | **Print This Page** → Keep as desk reference!

---

## 🎯 Next Steps

1. ✅ Understand this page (5 min)
2. ✅ Open your PDM file
3. ✅ Follow 3-Step Workflow above
4. ✅ Generate CDM, LDM, PDM
5. ✅ Generate SQL script
6. ✅ Deploy to MySQL
7. ✅ Success! 🎉
