# ⚠️ PowerDesigner: Common Mistakes & Best Practices

## 🐛 Lỗi Thường Gặp & Cách Khắc Phục

### **❌ Lỗi 1: Quên Chọn Target DBMS Trước Khi Generate PDM**

**Triệu chứng:**

```
• PDM generation output có SQL từ DBMS sai
• Data types lạ anormal (VARCHAR(32767) thay vì VARCHAR(255))
• Syntax SQL không hợp lệ cho MySQL
```

**Nguyên nhân:**

```
Tools → Generate → Physical Data Model
[Dialog mở nhưng DBMS = "Generic" hoặc sai]
[Click OK mà không chọn MySQL]
```

**Khắc Phục:**

```bash
# Cách 1: Tạo lại (recommended)
File → Close current PDM without save
Tools → Generate → PDM
   Target DBMS: [Click dropdown] → MySQL 5.1+ (⭐ PHẢI CHỌN)
   Click OK

# Cách 2: Edit PDM properties (nếu đã tạo)
Right-click PDM name → Properties
   DBMS: Change to MySQL 8.0
   Save
   File → Regenerate DDL
```

---

### **❌ Lỗi 2: M:N Relationships Không Có Junction Table Sau Generate LDM**

**Triệu chứng:**

```
LDM sau generation:
  ┌────────────────┐           ┌──────────────┐
  │    discount    │ M···N···M │   product    │
  └────────────────┘           └──────────────┘
  ↑ SAI! Vẫn còn M:N
```

**Nguyên nhân:**

```
Tools → Generate → Logical Data Model
[Dialog mở nhưng option không check]
   ☐ Create junction Tables for M:M  ← CHƯA CHECK
```

**Khắc Phục:**

```
Tools → Generate → Logical Data Model (lại)
Options:
   ☑ Create junction Tables for M:M [MUST CHECK]
   ☑ Create Primary Keys
   ☑ Create Foreign Keys
Click OK
[Sẽ tạo discount_product junction table]
```

---

### **❌ Lỗi 3: Foreign Keys Bị Mất Sau Convert CDM → LDM → PDM**

**Triệu chứng:**

```
PDM final không có CONSTRAINT FOREIGN KEY
MySQL creation script không có:
   FOREIGN KEY (customer_id) REFERENCES customers(id)
```

**Nguyên nhân:**

```
1. CDM generation không giữ lại FK metadata
2. LDM generation không tạo lại FK
3. PDM generation với option:
   ☐ Create Foreign Keys  ← CHƯA CHECK
```

**Khắc Phục:**

```bash
# Option A: Generate PDM lại
Tools → Generate → Physical Data Model
   Options:
   ☑ Create Foreign Keys [MUST CHECK]
   ☑ Create Indexes
   ☑ Create Unique Constraints
   Click OK

# Option B: Thêm FK thủ công
Mở PDM file
Right-click Table → Properties
References tab → Add reference
Chọn Referenced table → OK
```

---

### **❌ Lỗi 4: Normalization Không Đúng 3NF**

**Triệu chứng:**

```
LDM có:
• Table users: user_id, name, phone, department_name, department_created_date
  ↑ SAI! department_name & department_created_date là transitive dependency

• Table orders: order_id, customer_id, customer_name, customer_email
  ↑ SAI! customer_name & customer_email là partial dependency
```

**Nguyên nhân:**

```
Attributes không được separate thành entities riêng

Đúng 3NF:
• Table users: user_id, name, phone, department_id (FK)
• Table departments: dept_id, name, created_date

• Table orders: order_id, customer_id (FK)
• Table customers: customer_id, name, email
```

**Khắc Phục:**

```
1. Reopen CDM
2. Add new Entity: Department ngay trong CDM
3. Create Relationship: Users ←(1:N)→ Department
4. Regenerate LDM
   ☑ 3rd Normal Form (3NF)
   ☑ Resolve transitive dependencies
   Click OK
```

---

### **❌ Lỗi 5: Primary Key Không Rõ Ràng Trong CDM**

**Triệu chứng:**

```
CDM:
  Entity: Product
  ├─ Attributes:
  │  ├─ ProductID (not marked as Identifier)
  │  ├─ SKU (marked as Identifier)
  │  └─ Name
  ↑ SAI! Có 2 candidates cho PK
```

**Nguyên nhân:**

```
CDM không định nghĩa rõ Primary Identifier
```

**Khắc Phục:**

```
CDM → RIGHT-CLICK Entity Product → Properties
Identifier tab:
   Add Identifier: ProductID
   [This becomes PK in LDM/PDM]

✓ Hoặc mark SKU nếu cái đó là unique business key
```

---

### **❌ Lỗi 6: Relationships Có Sai Cardinality (1:N vs 1:1 vs M:N)**

**Triệu chứng:**

```
LDM:
  staffs ──(1:1)──→ users  ✓ ĐÚNG

  staffs ──(1:N)──→ attendances  ✓ ĐÚNG

  discounts ──(1:1)──→ products  ✗ SAI (phải M:N)
```

**Nguyên nhân:**

```
Cardinality không match với business requirement
```

**Khắc Phục:**

```
LDM → SELECT relationship line between discount-product
   RIGHT-CLICK → Properties
   Cardinality tab:
      Parent Cardinality: 1
      Child Cardinality: N (change if wrong)

   → Create junction table manually
      Create Table discount_product (
         discount_id BIGINT FK,
         product_id BIGINT FK,
         UNIQUE(discount_id, product_id)
      )
```

---

### **❌ Lỗi 7: Naming Convention Inconsistent Across Models**

**Triệu chứng:**

```
CDM:   Entity: CustomerOrder
LDM:   Table: customer_orders          ← snake_case
PDM:   Table: CustomerOrders           ← PascalCase (sai!)
SQL:   CREATE TABLE `customer-orders`  ← kebab-case (sai!)
```

**Nguyên nhân:**

```
Không set Naming Convention trước generate
```

**Khắc Phục:**

```
Trước generate bất kì model nào:

Tools → General Options
   ↓ Naming Conventions

      MySQL - Set Rules:
      ├─ Table Name: [uppercase] [%NAME$]
      ├─ Column Name: [lowercase] [%NAME$]
      ├─ Primary Key: [ID]
      ├─ Foreign Key: [%CNAME$_ID]
      ├─ Index Name: [idx_%TNAME$_%CNAME$]
      └─ Unique Constraint: [uk_%TNAME$_%CNAME$]

      Apply to: [All models to be generated]
```

---

### **❌ Lỗi 8: SQL Script Có Syntax Error Khi Generate**

**Triệu chứng:**

```
Generated SQL:
   CREATE TABLE orders (
      id BIGINT(20) UNSIGNED PRIMARY KEY,
      status ENUM('pending', 'processing'), ← SAI MySQL syntax
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE
   );
   ↑ Lỗi syntax
```

**Nguyên nhân:**

```
1. Target DBMS không chính xác
2. Generate option incomplete
3. PowerDesigner version cũ
```

**Khắc Phục:**

```
Cách 1: Regenerate with correct DBMS
   File → Generate → SQL Create Script
   DBMS: Make sure = MySQL 8.0 ⭐
   Options:
      ☑ Include DROP IF EXISTS
      ☑ Include CREATE TABLE
      ☑ Include Indexes
      ☑ Include Constraints

Cách 2: Manual cleanup
   Open SQL file (.sql)
   Find syntax error (line: ???)
   Fix manually or regenerate PDM
```

---

### **❌ Lỗi 9: Attributes Bị Mất Trong Conversion**

**Triệu chứng:**

```
CDM: Entity Order
  ├─ OrderID
  ├─ CustomerRef
  ├─ TotalAmount
  ├─ Status
  └─ CreatedDate

LDM: Table Order
  ├─ id (OrderID)
  ├─ customer_id (CustomerRef) ← FK added
  ├─ total_amount
  ├─ status
  └─ [CreatedDate MISSING!]  ← ⚠️ mất!
```

**Nguyên nhân:**

```
CDM attributes không được định nghĩa rõ
Hoặc chọn sai option khi generate
```

**Khắc Phục:**

```
1. Review CDM:
   Kiểm tra mỗi attribute trong:
   Entity → Properties → Domain tab
   Verify: Không bỏ sót

2. Regenerate LDM:
   Tools → Generate → Logical Data Model
   Options:
      ☑ Create All Attributes ← MAKE SURE
      ☑ Preserve Attribute Names
      ☑ Add Domain References
```

---

### **❌ Lỗi 10: UNIQUE Constraints & INDEXES Không Generate**

**Triệu chứung:**

```
Generated SQL không có:
   CREATE UNIQUE INDEX idx_email ON users(email);
   UNIQUE KEY uk_product_sku (sku);
```

**Nguyên nhân:**

```
PDM generation options:
   ☐ Create Indexes  ← CHƯA CHECK
   ☐ Create Unique Constraints  ← CHƯA CHECK
```

**Khắc Phục:**

```
File → Generate → DDL Scripts
Options:
   ☑ Create Indexes  [MUST CHECK]
   ☑ Create Unique Constraints  [MUST CHECK]
   ☑ Create Check Constraints
   ☑ Create Foreign Key Constraints

Generate
```

---

## ✅ Best Practices

### **1️⃣ File Management - Đặt Tên Rõ Ràng**

```
❌ SAI:
   Model.cdm
   Model.ldm
   Model.pdm
   final.pdm
   final2.pdm

✅ ĐÚNG:
   CDM_senhong_ocop_v1.0.cdm
   LDM_senhong_ocop_v1.0.ldm
   PDM_senhong_ocop_MySQL_v1.0.pdm
   PDM_senhong_ocop_MySQL_v1.1_optimized.pdm

   Naming convention:
   [ModelType]_[ProjectName]_[DBMS]_v[Version].[ext]
```

---

### **2️⃣ Version Control - Backup Each Phase**

```
Directory structure:
models/
├─ CDM/
│  ├─ CDM_v1.0.cdm (signed off)
│  ├─ CDM_v1.1.cdm (after review)
│  └─ CDM_v1.2_final.cdm
├─ LDM/
│  ├─ LDM_v1.0.ldm (from CDM v1.2)
│  ├─ LDM_v1.1_normalized.ldm
│  └─ LDM_v1.2_final.ldm
├─ PDM/
│  ├─ PDM_MySQL_v1.0.pdm (from LDM v1.2)
│  ├─ PDM_MySQL_v1.1_optimized.pdm (with indexes)
│  └─ PDM_MySQL_v1.2_production.pdm
└─ SQL_Scripts/
   ├─ senhong_ocop_v1.0.sql
   ├─ senhong_ocop_v1.1.sql
   └─ senhong_ocop_v1.2.sql
```

---

### **3️⃣ Documentation - Export Each Model**

```
Để mỗi team member hiểu rõ:

After generate CDM:
   File → Export → CDM_senhong_ocop.pdf
   ↓ (chia cho business/product team, stakeholders)

After generate LDM:
   File → Export → LDM_senhong_ocop.png
   File → Export → LDM_senhong_ocop_report.html
   ↓ (chia cho BA/Analyst để review normalization)

After generate PDM:
   File → Export → PDM_senhong_ocop_MySQL.pdf
   File → Generate → SQL Create Script
   ↓ (chia cho DBA/Developer để implementation)
```

---

### **4️⃣ Verification Checklist - Trước Mỗi Deploy**

#### **Pre-CDM Generation:**

```
☑ PDM loaded đúng file
☑ Tất cả tables visible (33 tables)
☑ Tất cả relationships vẽ
☑ Không có orphan entities
☑ File saved: PDM_source.pdm
```

#### **Pre-LDM Generation:**

```
☑ CDM loaded đúng file
☑ Kiểm tra: mỗi entity có identifier?
☑ Kiểm tra: cardinality (1:1, 1:N, M:N)?
☑ Kiểm tra: không có duplicate attributes?
☑ File saved: CDM_source.cdm
☑ Options chuẩn:
   ☑ 3NF
   ☑ Junction tables for M:N
   ☑ Create PK/FK
```

#### **Pre-PDM Generation:**

```
☑ LDM loaded đúng file
☑ Normalized theo 3NF
☑ Tất cả table có PK
☑ Tất cả FK visible
☑ Target DBMS = MySQL 8.0 ⭐⭐⭐
☑ Options:
   ☑ Create Indexes
   ☑ Create Constraints (UNIQUE, CHECK)
   ☑ Create FK Constraints
☑ File saved: LDM_source.ldm
```

#### **Pre-SQL Generation:**

```
☑ PDM loaded đúng file (MySQL)
☑ Data types check (no VARCHAR(32767))
☑ Constraints check (FK có ON DELETE?)
☑ Indexes check (tất cả FK có index?)
☑ Naming convention consistent:
   ☑ Tables: snake_case
   ☑ Columns: snake_case
   ☑ Foreign keys: {table}_id
   ☑ Indexes: idx_{table}_{column}
   ☑ Unique: uk_{table}_{column}
☑ File generate location set
☑ Output format: SQL script (.sql)
```

---

### **5️⃣ Performance Optimization Tips - Trong PDM**

```
Trước generate SQL, thêm:

✓ Composite Indexes (cho joins thường)
  CREATE INDEX idx_orders_customer_date
    ON orders(customer_id, created_at);

✓ Covering Indexes (cho queries phổ biến)
  CREATE INDEX idx_import_items_lookup
    ON import_items(product_variant_id, expiry_date, remaining_qty);

✓ Partial Indexes (nếu DBMS support)
  CREATE INDEX idx_active_orders
    ON orders(created_at)
    WHERE status IN ('pending', 'processing');

✓ Denormalization (nếu cần performance)
  Table: order_cache_summary
    (order_id, total_amount, item_count, customer_name)
  ← Cached from denormalization

Làm trong PDM GUI:
   Right-click Table → Properties
   Indexes tab → Add → Configure columns
```

---

### **6️⃣ Repository & Workspace Management**

```
PowerDesigner Workspace tốt:

File → New → Project
   Name: senhong_ocop_project_v1

   Generated models saved in:
   Models/
   ├─ CDM_senhong_ocop.cdm
   ├─ LDM_senhong_ocop.ldm
   ├─ PDM_senhong_ocop_MySQL.pdm

   Scripts/
   ├─ senhong_ocop_v1.sql
   └─ migration_v1_v2.sql

   Documentation/
   ├─ CDM_diagram.pdf
   ├─ LDM_report.html
   └─ Entity_Glossary.xlsx
```

---

### **7️⃣ Collaboration Best Practices**

```
Team Workflow:

1. Analyst/BA:
   ├─ Create CDM
   ├─ Export → CDM.pdf
   └─ Share with stakeholders

2. Architect/DBA:
   ├─ Review CDM
   ├─ Generate LDM
   ├─ Normalize & validate
   └─ Export → LDM_report.html

3. Lead Developer:
   ├─ Review LDM
   ├─ Generate PDM
   ├─ Add indexes, optimization
   ├─ Generate SQL
   └─ Share SQL script with team

4. Team Repository:
   ├─ All models → Git/SVN
   ├─ Version with commit message
   └─ Track changes per model
```

---

### **8️⃣ Training & Consistency**

```
Before team starts using models:

✓ Educate about 3 levels:
  • CDM = Business view (non-technical)
  • LDM = Logical view (database-independent)
  • PDM = Physical view (MySQL-specific)

✓ Establish documentation standards:
  • When to use each model type
  • Naming conventions (must be consistent)
  • Adding comments/descriptions
  • Export/sharing procedures

✓ Regular audits:
  • Check for naming convention violations
  • Verify normalization level
  • Review performance considerations
  • Update documentation
```

---

### **9️⃣ Backup Strategy**

```
Backup frequency:

Daily:
   ├─ Auto-backup after major generation
   ├─ Save with versioned filename
   └─ Store in shared repository

Weekly:
   ├─ Export all models to PDF
   ├─ Archive SQL scripts
   └─ Create snapshot

Monthly:
   ├─ Full backup of workspace
   ├─ Tag version milestone
   └─ Document changes log
```

---

## 📋 Troubleshooting Decision Tree

```
"Generation failed. Làm gì?"

1. Check log:
   View → Message Window (F9)
   Error: ???

   ├─ "File not found"
   │  └─ Reopen file, save again
   │
   ├─ "DBMS not supported"
   │  └─ Select correct DBMS (MySQL, not Generic)
   │
   ├─ "Cardinality error"
   │  └─ Review relationships, fix M:N mapping
   │
   ├─ "Attribute missing"
   │  └─ Regenerate with "Create All Attributes"
   │
   └─ "Unknown error"
      └─ Close PowerDesigner → restart → retry
```

---

## 🎓 Recommended Reading Order

```
1. Read: CDM_LDM_PDM_TRANSFORMATION.md
   (understand the 3 models)

2. Read: POWERDESIGNER_QUICK_COMMANDS.md
   (learn the exact menu steps)

3. Read: THIS FILE (common mistakes)
   (avoid pitfalls)

4. Practice: Generate all 3 models for senhong_ocop
   (hands-on experience)

5. Review: Generated SQL
   (validate output)

6. Deploy: Execute in MySQL
   (verify real database)
```

---

**Last Updated**: 2026-04-16 | **Version**: 1.0 | **Database**: senhong_ocop
