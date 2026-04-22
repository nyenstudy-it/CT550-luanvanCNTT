# 📥 Import CDM/DBML vào PowerDesigner - Chi Tiết Đầy Đủ

## 📋 Mục Lục

1. [File Nào Để Import](#file-nào-để-import)
2. [Prepare File](#prepare-file)
3. [Cách Import Đúng Chuẩn](#cách-import-đúng-chuẩn)
4. [Xác Minh Sau Import](#xác-minh-sau-import)
5. [Troubleshooting](#troubleshooting)
6. [Sử Dụng CDM Bình Thường](#sử-dụng-cdm-bình-thường)

---

## 📁 File Nào Để Import?

### **Option 1: Import từ SQL DDL** (Khuyến Nghị) ⭐

```
File: senhong_ocop_CDM.sql
Location: c:\xampp\htdocs\luanvan\database\senhong_ocop_CDM.sql

✅ Ưu Điểm:
   • 100% compatible với PowerDesigner
   • Có tất cả constraints & relationships
   • Tự động generate PDM trước
   • Từ PDM → Generate CDM

Kết quả: PDM trước, sau đó generate CDM từ PDM
```

### **Option 2: Import từ DBML** (Nhanh hơn)

```
File: senhong_ocop_CDM.dbml
Location: c:\xampp\htdocs\luanvan\database\senhong_ocop_CDM.dbml

✅ Ưu Điểm:
   • Format chuẩn DBML
   • Dùng DBML CLI convert sang SQL
   • Sau đó follow Option 1

⚠️ Lưu ý:
   • Cần cài DBML CLI (npm install -g @dbml/cli)
   • Hoặc dùng dbdiagram.io online
```

### **Option 3: Reverse Engineer từ Database** (Tàu Tốc Hành) ⭐⭐⭐

```
File: Database hiện tại (senhong_ocop)
Cách: PowerDesigner → Database → Reverse Engineer

✅ Ưu Điểm:
   • 100% chính xác với database thực
   • Tự động tạo PDM
   • Sau đó generate CDM từ PDM
   • Không cần import file

👉 RECOMMENDATION: Dùng cách này nếu có thể!
```

---

## 🔧 Prepare File

### **Before Import - Kiểm Tra File**

```bash
# 1. Verify file exists & readable
File: c:\xampp\htdocs\luanvan\database\senhong_ocop_CDM.sql
Size: 1000+ KB (should be 1-2 MB)

# 2. Check file format (open with text editor)
Head of file should be:
┌─────────────────────────────────────┐
│ -- ========================================
│ -- SENHONG OCOP SYSTEM - CDM
│ -- Database: senhong_ocop
│ -- MySQL Version: 8.0+
│ -- ========================================
│
│ CREATE TABLE IF NOT EXISTS users (
│     id BIGINT UNSIGNED PRIMARY KEY...
└─────────────────────────────────────┘

# 3. Check MySQL Server running
Services: MySQL80 (hoặc MySQL version khác)
Status: Running ✓

# 4. Verify Database exists
Database: senhong_ocop (already exists → backup first!)
User: root
Password: (empty)
```

---

## 🚀 Cách Import Đúng Chuẩn

### **PHƯƠNG PHÁP 1: Reverse Engineer từ Database** (BEST) ⭐⭐⭐

**Tại sao dùng cách này?**

- ✓ 100% chính xác từ database hiện tại
- ✓ Không cần import file
- ✓ Tự động detect tất cả tables, FK, indexes
- ✓ PowerDesigner tự tạo PDM
- ✓ Từ PDM → generate CDM (bình thường)

#### **Bước 1: Mở PowerDesigner**

```
Start → PowerDesigner 16+ (hoặc phiên bản mới hơn)
File → New → Data Model
Type: Physical Data Model (PDM)
DBMS: MySQL 5.1+ (hoặc 8.0)
[Create]
```

#### **Bước 2: Tạo Database Connection**

```
Database → Configure Connections
[Right-click] → New Connection
┌──────────────────────────────────────┐
│ Connection Properties:               │
│ ├─ Name: senhong_ocop_live          │
│ ├─ DBMS: MySQL 5.1+ (or 8.0+)       │
│ ├─ Server: localhost (hoặc 127.0.0.1)
│ ├─ Port: 3306                        │
│ ├─ User: root                        │
│ ├─ Password: (leave blank)           │
│ ├─ Database: senhong_ocop            │
│ └─ [Test Connection] ← Verify OK    │
└──────────────────────────────────────┘

Kết quả: ✓ Connected
```

#### **Bước 3: Reverse Engineer từ Database**

```
Database → Reverse Engineer
┌──────────────────────────────────────┐
│ Reverse Engineer from Database        │
│ ├─ Connection: senhong_ocop_live     │
│ ├─ [Next →]                          │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│ Select objects to reverse:           │
│ ├─ ☑ Tables                          │
│ ├─ ☑ Foreign Keys                    │
│ ├─ ☑ Indexes                         │
│ ├─ ☑ Triggers (nếu có)              │
│ ├─ [Select All] ← Click này          │
│ └─ [Next →]                          │
└──────────────────────────────────────┘

[Finish]
PowerDesigner auto-generates PDM với 33 tables
```

**Kết quả:**

```
✓ PDM được tạo với:
  • 33 tables (users, orders, products, etc.)
  • 45+ Foreign Keys
  • 60+ Indexes
  • All constraints visible
```

#### **Bước 4: Generate CDM từ PDM**

```
Sau khi PDM được tạo:

Tools → Generate → Conceptual Data Model
┌──────────────────────────────────────┐
│ Generate CDM Dialog:                 │
│ ├─ Name: CDM_senhong_ocop            │
│ ├─ Options:                          │
│ │  ☑ Replace PK by Identifier       │
│ │  ☑ Remove SQL-specific Types      │
│ │  ☑ Generalize Relationships       │
│ │  ☑ Group Attributes by Domain     │
│ └─ [OK]                              │
└──────────────────────────────────────┘

[PowerDesigner auto-generates CDM]
```

**Kết quả:**

```
✓ CDM được tạo với:
  • Entities (thay cho Tables)
  • Relationships (1:1, 1:N, M:N)
  • Attributes (generic types)
  • No FK/PK details (business view)
  • Easy to understand
```

---

### **PHƯƠNG PHÁP 2: Import SQL Script** (Alternative)

**Khi nào?** Database chưa setup, hoặc muốn tạo từ script

#### **Bước 1: Mở PowerDesigner**

```
File → New → Physical Data Model (PDM)
DBMS: MySQL 5.1+
```

#### **Bước 2: Import SQL Script**

```
File → Import
┌──────────────────────────────────────┐
│ Import Dialog:                       │
│ ├─ File type: SQL Scripts (*.sql)   │
│ ├─ Browse: senhong_ocop_CDM.sql     │
│ ├─ DBMS: MySQL 5.1+                 │
│ ├─ Options:                          │
│ │  ☑ Create Object from Import      │
│ │  ☑ Create Tables                  │
│ │  ☑ Create FK Constraints          │
│ │  ☑ Create Indexes                 │
│ └─ [OK]                              │
└──────────────────────────────────────┘

⚠️ Note: File path phải full path hoặc relative từ project folder
```

**Kết quả:**

```
✓ PDM created từ SQL script
✓ Tables, columns, types imported
✓ FK constraints visible
✓ Indexes visible

❌ Có thể: Một số properties không import 100%
→ Sửa manual nếu cần
```

#### **Bước 3: Validate PDM**

```
File → Save As → PDM_from_SQL.pdm

Verify:
☑ 33 tables present? (Tools → Model → Show Object List)
☑ All FK visible? (Check relationships)
☑ All indexes visible? (Right-click table → Indexes)
☑ No errors in Message Window (F9)

Nếu OK → Proceed
Nếu có issue → Manual fix
```

#### **Bước 4: Generate CDM**

```
Sau khi PDM OK:
Tools → Generate → Conceptual Data Model
[Làm giống Phương pháp 1 - Bước 4]
```

---

### **PHƯƠNG PHÁP 3: Convert DBML → SQL → Import**

**Khi nào?** Có file DBML, muốn convert trước

#### **Bước 1: Cài DBML CLI** (Windows Terminal - Admin)

```bash
npm install -g @dbml/cli

Verify:
dbml2sql --version
```

#### **Bước 2: Convert DBML → SQL**

```bash
# Navigate to folder
cd c:\xampp\htdocs\luanvan\database

# Convert
dbml2sql senhong_ocop_CDM.dbml --mysql -o senhong_ocop_from_dbml.sql

# Verify created
dir senhong_ocop_from_dbml.sql
```

#### **Bước 3: Import SQL** (Follow Phương pháp 2)

```
PowerDesigner → File → Import → senhong_ocop_from_dbml.sql
[Rest theo Phương pháp 2]
```

---

## ✅ Xác Minh Sau Import

### **Checklist Kiểm Tra Ngay Sau Import**

```
1️⃣ Count Objects
   ────────────────
   ☑ 33 tables visible?
   ☑ No orphan entities?

   How to check:
   Tools → Model → Show Object List
   Count tables: should be 33+3 (users, cache, jobs, etc.)
```

```
2️⃣ Verify Foreign Keys
   ────────────────────
   ☑ 45+ FK visible?
   ☑ Relationships drawn correctly?

   How to check:
   View → Diagram → Relationships
   Select 1 FK → Properties → verify target table

   Example FKs to check:
   ├─ staffs.user_id → users.id
   ├─ orders.customer_id → customers.id
   ├─ attendances.staff_id → staffs.user_id
   ├─ import_items.import_id → imports.id
```

```
3️⃣ Verify Data Types
   ──────────────────
   ☑ BIGINT for IDs?
   ☑ VARCHAR for strings?
   ☑ DECIMAL for amounts?
   ☑ ENUM for status fields?

   How to check:
   Right-click table → Properties → Columns tab
   Check: id (BIGINT), email (VARCHAR), total (DECIMAL), status (ENUM)
```

```
4️⃣ Verify Constraints
   ───────────────────
   ☑ PRIMARY KEYs present on all tables?
   ☑ UNIQUE constraints on key fields?
   ☑ CHECK constraints on amounts?

   How to check:
   Right-click table → Properties → Constraints tab
   Verify: PK on id, UNIQUE on email/sku/order_number
```

```
5️⃣ Verify Indexes
   ───────────────
   ☑ 60+ indexes present?
   ☑ Index on FK columns?
   ☑ Index on frequently queried columns?

   How to check:
   Right-click table → Properties → Indexes tab
   Examples: idx_orders_status, idx_customer_id, idx_email
```

```
6️⃣ Check Message Window
   ────────────────────
   ☑ No errors?
   ☑ No warnings about missing objects?

   How to check:
   View → Message Window (F9)
   Scroll through: should be clean
   (Ignore warnings about comments)
```

### **Detailed Verification Script**

```sql
-- Run these queries to verify database matches PDM

-- 1. Count tables
SELECT COUNT(*) as table_count
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'senhong_ocop';
-- Expected: 37+ (33 core + 3 system + views)

-- 2. List all tables
SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'senhong_ocop'
ORDER BY TABLE_NAME;
-- Expected: 33+ tables (attendances, blogs, orders, etc.)

-- 3. Count foreign keys
SELECT COUNT(*) as fk_count
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_NAME IS NOT NULL;
-- Expected: 45+

-- 4. Count indexes
SELECT COUNT(*) as index_count
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND INDEX_NAME != 'PRIMARY';
-- Expected: 60+

-- 5. List all FK relationships
SELECT
  TABLE_NAME,
  COLUMN_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;

-- 6. Verify critical tables
SELECT
  CASE WHEN COUNT(*) > 0 THEN 'EXIST' ELSE 'MISSING' END as users,
  (SELECT CASE WHEN COUNT(*) > 0 THEN 'EXIST' ELSE 'MISSING' END FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='senhong_ocop' AND TABLE_NAME='orders') as orders,
  (SELECT CASE WHEN COUNT(*) > 0 THEN 'EXIST' ELSE 'MISSING' END FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='senhong_ocop' AND TABLE_NAME='products') as products
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'senhong_ocop' AND TABLE_NAME = 'users';
```

---

## 🐛 Troubleshooting

### **❌ Problem 1: Connection Refused Khi Reverse Engineer**

**Triệu chứng:**

```
Error: "Access denied for user 'root'@'localhost'"
hoặc
"Connection refused at 127.0.0.1:3306"
```

**Nguyên nhân:**

- MySQL Server không chạy
- Username/password sai
- Database không tồn tại

**Khắc Phục:**

```bash
# 1. Check MySQL Server running
services.msc
Search "MySQL80" (or MySQL version)
Status: Running ✓

Nếu không → Start service

# 2. Verify database exists
mysql -h 127.0.0.1 -u root
SHOW DATABASES;
[Look for senhong_ocop]

# 3. If database missing:
mysql -h 127.0.0.1 -u root < senhong_ocop_CDM.sql

# 4. Try connection again in PowerDesigner
Database → Configure Connections
[Right-click connection] → Test Connection
[Should show: ✓ Connected]
```

---

### **❌ Problem 2: Import Không Tạo Tất Cả Tables**

**Triệu chứng:**

```
SQL import successful nhưng chỉ có 10 tables thay vì 33
Message: "Partial import successful"
```

**Nguyên nhân:**

- SQL file bị cắt hoặc incomplete
- Có syntax error trong SQL file
- Character encoding issue

**Khắc Phục:**

```bash
# 1. Verify SQL file size
dir c:\xampp\htdocs\luanvan\database\senhong_ocop_CDM.sql
[Should be 1-2 MB, not just 100 KB]

# 2. Check SQL file integrity
# Open file with text editor → scroll to bottom
# Should end with: -- END OF CDM SCHEMA

# 3. Check for syntax errors
# PowerDesigner Message Window (F9)
# Look for: "Syntax error near line XXX"

# If error found:
#   → Open senhong_ocop_CDM.sql in editor
#   → Find line with error
#   → Fix syntax (or regenerate from database)

# 4. Try reimport
# Close PDM without saving
# File → Import → select SQL file again
# Message Window → watch for errors
```

---

### **❌ Problem 3: Foreign Keys Not Rendering**

**Triệu chứng:**

```
PDM opens, tables visible, nhưng relationships (FK) không thấy
View → Diagram → Không có connection lines
```

**Nguyên nhân:**

- FK constraints not imported
- Relationship display disabled
- Table layout scattered apart

**Khắc Phục:**

```
1. View → Diagram → Show Relationships
   ☑ Make sure checked

2. View → Arrange → Auto Layout
   [This will redraw diagram with relationships]

3. Verify FK imported:
   Right-click table → Properties
   References tab → Should show FK references

4. If still no FK:
   Database → Reverse Engineer again
   ☑ Make sure "Foreign Keys" is selected in options
```

---

### **❌ Problem 4: Indexes Not Visible**

**Triệu chứng:**

```
PDM created but no indexes visible
Right-click table → Properties → Indexes tab = empty
```

**Nguyên nhân:**

- Indexes were not selected during import
- Database has no indexes (unlikely)

**Khắc Phục:**

```
Manual add indexes:
1. Right-click table → Properties
2. Indexes tab → [New]
3. Add columns for index
4. Set property: Unique (if needed)
5. [OK]

OR regenerate with index export:

Database → Reverse Engineer → [in options:]
☑ Indexes [MUST BE CHECKED]
[Finish]
```

---

### **❌ Problem 5: Wrong Data Types After Import**

**Triệu chứng:**

```
Import complete nhưng data types sai:
• id = VARCHAR(20) instead of BIGINT(20) UNSIGNED
• email = TEXT instead of VARCHAR(255)
• amount = INT instead of DECIMAL(12,2)
```

**Nguyên nhân:**

- SQL file generated từ wrong DBMS
- PowerDesigner parser issue

**Khắc Phục:**

```
Quick fix (1 table):
1. Right-click table → Properties
2. Columns tab
3. Select wrong column (e.g., id)
4. Edit → Domain: change to BIGINT
5. [OK]

Better fix (all tables):
1. Close PDM without save
2. Regenerate SQL from database:
   Database → Reverse Engineer → From DB Connection
   [Select correct DBMS: MySQL]
   [Finish]
3. This ensures 100% correct types
```

---

### **❌ Problem 6: Can't Generate CDM After Import**

**Triệu Chứng:**

```
Tools → Generate → Conceptual Data Model
[Dialog opens but] → [OK button disabled]
hoặc
Error: "Cannot generate CDM from this model type"
```

**Nguyên Nhân:**

```
PDM không complete hoặc corrupt
```

**Khắc Phục:**

```bash
# Option 1: Save & reopen
File → Save (PDM file)
File → Close
File → Open (reopen same PDM)
Tools → Generate → Conceptual Data Model
[Should work now]

# Option 2: Regenerate from scratch
File → Close (discard)
Database → Reverse Engineer → From DB Connection (fresh start)
[Create new clean PDM]
Tools → Generate → CDM
```

---

## 🎯 Sử Dụng CDM Bình Thường

### **Sau Khi Import & Verify Thành Công**

#### **1. Export CDM Để Documentation**

```
File → Export
┌──────────────────────────────────────┐
│ Export Format: Select one             │
│ ├─ PNG (Best for presentations)      │
│ ├─ PDF (Best for printing)           │
│ ├─ SVG (Best for editing later)      │
│ └─ HTML (Best for web sharing)       │
└──────────────────────────────────────┘

Output: CDM_senhong_ocop.png
Use cases:
├─ PowerPoint presentations
├─ Confluence/Wiki documentation
├─ Design review meetings
└─ Team onboarding
```

#### **2. View CDM in Different Ways**

```
File → Recent Models
├─ View current CDM
├─ View PDM (for comparison)
└─ View LDM (if generated)

Window → Model List
├─ Switch between open models
├─ Arrange side-by-side
└─ Compare entities
```

#### **3. Edit / Annotate CDM**

```
Add notes/comments:
Right-click Entity → Properties
Comments tab → Add description
Example:
Entity: Order
Comments: "Main transaction entity. Status: pending→completed. Links to Customer 1:N and OrderItem 1:N"

This helps team understand business logic
```

#### **4. Generate LDM from CDM**

```
После успешного import CDM:
Tools → Generate → Logical Data Model
[Dialog opens with CDM as source]
└─ This creates normalized LDM version
```

#### **5. Generate SQL Script**

```
From PDM (after import):
File → Generate → SQL Create Script
Output: senhong_ocop_MySQL.sql
Use case:
├─ Verify current database matches PDM
├─ Create migration scripts
├─ Deploy to staging/production
└─ Backup/restore procedures
```

---

## 📊 Summary: Import Workflow

```
┌─────────────────────────────┐
│ START: Database or File?    │
└─────────────────────────────┘
         ↙              ↖
    From DB         From File
      ↓                ↓
┌──────────────┐  ┌──────────────────┐
│ Rev Engineer │  │ Import SQL File  │
│ from DB      │  │ or DBML→SQL      │
└──────────────┘  └──────────────────┘
      ↓                        ↓
┌────────────────────────────────────┐
│ PDM Created (33 tables visible)    │
│ ✓ Verify: count, FK, types, idx   │
└────────────────────────────────────┘
         ↓
┌────────────────────────────────────┐
│ Save PDM File                      │
│ (PDM_senhong_ocop_MySQL_v1.pdm)   │
└────────────────────────────────────┘
         ↓
┌────────────────────────────────────┐
│ Generate CDM from PDM              │
│ Tools → Generate → CDM             │
└────────────────────────────────────┘
         ↓
┌────────────────────────────────────┐
│ CDM Created (Entities visible)     │
│ ✓ Business-friendly view ready    │
│ ✓ Export to PDF/PNG               │
│ ✓ For stakeholder review           │
└────────────────────────────────────┘
```

---

## ✅ Final Checklist

- [ ] MySQL Server running (check services)
- [ ] Database senhong_ocop exists
- [ ] Choose import method (Reverse Engineer = best)
- [ ] Connection tested ✓ [in PowerDesigner]
- [ ] PDM imported/generated successfully
- [ ] Verify: 33 tables visible
- [ ] Verify: 45+ FK relationships
- [ ] Verify: 60+ indexes
- [ ] Message Window clean (F9)
- [ ] PDM saved with version name
- [ ] CDM generated from PDM
- [ ] CDM exported to PDF (for documentation)
- [ ] Ready for use! ✅

---

**Last Updated**: 2026-04-16 | **Version**: 1.0 | **Database**: senhong_ocop
