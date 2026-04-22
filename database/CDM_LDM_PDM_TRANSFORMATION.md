# 📐 PowerDesigner: CDM ↔ LDM ↔ PDM - HƯỚNG DẪN CHUYỂN ĐỔI ĐÚNG CHUẨN

## 📋 Mục Lục

1. [Định nghĩa 3 Model](#định-nghĩa-3-model)
2. [Khi nào dùng loại model nào](#khi-nào-dùng-loại-model-nào)
3. [Quy trình chuyển đổi chính thức](#quy-trình-chuyển-đổi-chính-thức)
4. [Chi tiết các bước PowerDesigner](#chi-tiết-các-bước-powerdesigner)
5. [Best Practices](#best-practices)
6. [Kiểm tra từng bước](#kiểm-tra-từng-bước)

---

## 📊 Định Nghĩa 3 Model

```
                    CHUYÊN MỤC THIẾT KẾ DATABASE
                           (3 TẦNG)

┌─────────────────────────────────────────────────────────┐
│  CDM - Conceptual Data Model (Mô hình Khái niệm)       │
│  • Độc lập DBMS                                         │
│  • Tập trung vào business logic                          │
│  • Entities, relationships, định nghĩa business rules  │
│  • Không có bảng, không có column cụ thể               │
│  • Dễ hiểu cho non-technical stakeholders              │
└─────────────────────────────────────────────────────────┘
                            ↓ (Normalize)
                            ↑ (Generalize)
┌─────────────────────────────────────────────────────────┐
│  LDM - Logical Data Model (Mô hình Logic)              │
│  • Độc lập DBMS                                         │
│  • Bình thường hóa 3NF (3rd Normal Form)               │
│  • Foreign Keys, Primary Keys rõ ràng                   │
│  • Tất cả attributes được định nghĩa                   │
│  • Không còn thông tin DBMS-specific                    │
│  • Dễ hiểu cho BA/Analyst                               │
└─────────────────────────────────────────────────────────┘
                            ↓ (Transform)
                            ↑ (Map)
┌─────────────────────────────────────────────────────────┐
│  PDM - Physical Data Model (Mô hình Vật Lý)           │
│  • Phụ thuộc vào DBMS cụ thể (MySQL, Oracle, etc.)    │
│  • Chi tiết kỹ thuật: data types, constraints, indexes │
│  • CREATE TABLE, triggers, stored procedures           │
│  • Performance optimization                             │
│  • Phát triển: constraints, indexes, partitions        │
│  • Phục vụ DBA/Developer                                │
└─────────────────────────────────────────────────────────┘
```

---

## 🎯 Khi Nào Dùng Loại Model Nào

### **CDM - Khi Nào Dùng?**

```
✅ Dùng khi:
   • Gặp nhau lần đầu tiên với stakeholder để hiểu requirement
   • Trình bày business logic không kỹ thuật
   • Review architecture cao cấp
   • Documentation cho management
   • Design thay đổi lớn trong hệ thống

❌ Không dùng khi:
   • Implement code
   • Tối ưu performance
   • Chọn database server
   • Viết SQL queries
```

### **LDM - Khi Nào Dùng?**

```
✅ Dùng khi:
   • Normalize schema (3NF, BCNF)
   • Validate relationships trước khi implement
   • Phân tích dependency giữa entities
   • Document business requirements dưới dạng data
   • Team planning trước khi dev
   • Database review với architect

✓ Standard dùng nhất: LDM là chuẩn trong hầu hết dự án
```

### **PDM - Khi Nào Dùng?**

```
✅ Dùng khi:
   • Implement trong database cụ thể (MySQL, Oracle, SQL Server)
   • Tối ưu performance (indexes, partitions, denormalization)
   • Viết migration scripts
   • Development & deployment
   • DBA cần làm maintenance, backup, recovery

✓ Standard thực tế: PDM là cái bạn thực tế deploy
```

---

## 🔄 Quy Trình Chuyển Đổi Chính Thức

### **Tiêu Chuẩn ISO/IEC 11582**

```
REQUIREMENT ANALYSIS
        ↓
    ┌───────────────────────────────────────┐
    │  PHASE 1: CONCEPTUAL DESIGN (CDM)    │
    │  ✓ Entity-Relationship (ER) Modeling │
    │  ✓ Business rules definition          │
    │  ✓ Constraints & attributes           │
    │  ✓ Không DB-specific                  │
    └───────────────────────────────────────┘
        ↓ [Normalization Process]
    ┌───────────────────────────────────────┐
    │  PHASE 2: LOGICAL DESIGN (LDM)       │
    │  ✓ Apply 3NF (Third Normal Form)     │
    │  ✓ Remove redundancy                  │
    │  ✓ Define keys (PK, FK)               │
    │  ✓ Resolve many-to-many               │
    │  ✓ Không DB-specific                  │
    └───────────────────────────────────────┘
        ↓ [Transformation to Target DBMS]
    ┌───────────────────────────────────────┐
    │  PHASE 3: PHYSICAL DESIGN (PDM)      │
    │  ✓ Select data types per DBMS         │
    │  ✓ Define storage parameters          │
    │  ✓ Create indexes                     │
    │  ✓ Optimize for performance           │
    │  ✓ Specific to MySQL/Oracle/SQL Server│
    └───────────────────────────────────────┘
        ↓
IMPLEMENTATION & DEPLOYMENT
```

---

## 🛠️ Chi Tiết Các Bước PowerDesigner

### **BƯỚC 1: Đã Có PDM → Tạo CDM**

#### **Phương Pháp A: Automatic Generation** (PowerDesigner 16+)

```
1️⃣ Mở file PDM trong PowerDesigner
   File → Open → chọn file .pdm

2️⃣ Chuyển ngôn ngữ (Language)
   Tools → General Options → Scripting Language: VB Script

3️⃣ Generate CDM từ PDM
   Tools → Generate → Conceptual Data Model

   ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
   ┃ Generate CDM Options:              ┃
   ┃ □ Name: [CDM_senhong_ocop]         ┃
   ┃ □ Rename tables to entities        ┃
   ┃ □ Generalize relationships         ┃
   ┃ □ Remove technical attributes      ┃
   ┃ □ Simplify data types              ┃
   ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

4️⃣ PowerDesigner sẽ:
   ✓ Chuyển tables → entities
   ✓ Chuyển columns → attributes
   ✓ Giữ relationships (1:1, 1:N, M:N)
   ✓ Bỏ data types (VARCHAR → String)
   ✓ Bỏ technical constraints
```

#### **Phương Pháp B: Manual Conversion**

```
1️⃣ File → New → Data Model
   Chọn CDM (Conceptual Data Model)

2️⃣ Tạo Entities + Attributes từ PDM
   View → Entity/Relationship
   Insert → Entity → Thêm từng entity

3️⃣ Tạo Relationships
   Insert → Relationship → Nối entities

4️⃣ Định nghĩa Identifiers (thay cho PK)
   Entity Properties → Primary Identifier

5️⃣ Save as: CDM_senhong_ocop.cdm
```

#### **PowerDesigner GUI Step-by-Step (A1)**

```
┌─ Tools Menu ─────────────────────────────────┐
│ File                                          │
│ Edit                                          │
│ View                                          │
│ Insert                                        │
│ Format                                        │
│ Diagram                                       │
│ Database                                      │
│ Tools ⟶ Generate ⟶ Select Generation Mode    │
│        ⟶ Conceptual Data Model               │
│ ⟶ Options ⟶ Configure transformation rules   │
│ ⟶ Generate                                    │
└───────────────────────────────────────────────┘
```

#### **Kết Quả Cần Đạt:**

```
PDM Before:
┌────────────────────────────┐
│ Table: orders              │
│ ├─ id: BIGINT UNSIGNED PK  │
│ ├─ customer_id: BIGINT FK  │
│ ├─ total: DECIMAL(12,2)    │
│ ├─ status: VARCHAR(50)     │
│ └─ created_at: TIMESTAMP   │
└────────────────────────────┘

CDM After:
┌────────────────────────────┐
│ Entity: Order              │
│ ├─ id (Identifier)         │
│ ├─ customer (Reference)    │
│ ├─ total (Number)          │
│ ├─ status (Text)           │
│ └─ created_date (Date)     │
└────────────────────────────┘
```

---

### **BƯỚC 2: Tạo LDM từ CDM**

#### **Tự động (Recommended)**

```
1️⃣ Mở CDM file
   File → Open → CDM_senhong_ocop.cdm

2️⃣ Generate LDM (Normalize)
   Tools → Generate → Logical Data Model

   ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
   ┃ Generate LDM Options:              ┃
   ┃ ☑ Normalize to 3NF                 ┃
   ┃ ☑ Create junction tables (M:N)     ┃
   ┃ ☑ Define all attributes            ┃
   ┃ ☑ Add primary/foreign keys         ┃
   ┃ ☑ Remove redundancy                ┃
   ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

3️⃣ PowerDesigner sẽ:
   ✓ Áp dụng 3NF normalization
   ✓ Tạo junction tables cho M:N
   ✓ Thêm Primary Keys
   ✓ Thêm Foreign Keys
   ✓ Giữ logic business rules
   ✓ Vẫn DB-independent
```

#### **Kết Quả Cần Đạt:**

```
CDM (Many-to-Many):
┌─────────────┐           ┌──────────────┐
│  discount   │ M···N···M │  product     │
└─────────────┘           └──────────────┘

LDM (Normalized):
┌─────────────┐     ┌──────────────────┐     ┌──────────────┐
│  discount   │───→ │ discount_product │ ←─── │  product    │
│ (PK: id)    │ 1:N │ (PK: id)         │ 1:N  │ (PK: id)    │
└─────────────┘     │ (FK: discount_id)│     └──────────────┘
                    │ (FK: product_id) │
                    └──────────────────┘
```

---

### **BƯỚC 3: Tạo PDM từ LDM (cho Database cụ thể)**

#### **Tự động - Cách Tốt Nhất**

```
1️⃣ Mở LDM file
   File → Open → LDM_senhong_ocop.ldm

2️⃣ Generate PDM cho MySQL 8.0
   Tools → Generate → Physical Data Model

   ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
   ┃ Generate PDM Options:              ┃
   ┃ Database: MySQL 5.1+               ┃
   ┃ ☑ Add constraints (FK, unique)     ┃
   ┃ ☑ Add indexes                      ┃
   ┃ ☑ Convert to SQL data types        ┃
   ┃ ☑ Add storage parameters           ┃
   ┃ Naming Convention: ___custom       ┃
   ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛

3️⃣ PowerDesigner sẽ:
   ✓ Chuyển tables với CREATE TABLE
   ✓ Thêm data types MySQL-specific
   ✓ Thêm PRIMARY KEY constraints
   ✓ Thêm FOREIGN KEY constraints
   ✓ Tạo INDEXES tự động
   ✓ Thêm UNIQUE constraints
   ✓ Tối ưu cho MySQL
```

#### **Kết Quả Cần Đạt:**

```
LDM (Generic):
┌────────────────────────────┐
│ Table: orders              │
│ ├─ id: Number (Identifier) │
│ ├─ customer_id: Number (FK)│
│ ├─ total: Numeric          │
│ └─ status: Text            │
└────────────────────────────┘

PDM MySQL (Specific):
┌────────────────────────────────────┐
│ Table: orders                      │
│ ├─ id: BIGINT(20) UNSIGNED PK     │
│ ├─ customer_id: BIGINT(20) FK     │
│ ├─ total: DECIMAL(12,2)            │
│ ├─ status: ENUM(...)               │
│ ├─ created_at: TIMESTAMP DEFAULT   │
│ ├─ INDEX idx_customer_id           │
│ └─ INDEX idx_status                │
└────────────────────────────────────┘
```

---

## ✅ Best Practices

### **1. Naming Convention qua các tầng**

```
CDM Layer:
  Entity:   PascalCase (Order, Customer, Product)
  Attribute: camelCase (totalAmount, createdDate)

LDM Layer:
  Table:     snake_case (orders, customers)
  Column:    snake_case (total_amount, created_date)
  PK:        id
  FK:        {table}_id (customer_id)

PDM Layer (MySQL):
  Table:     snake_case (orders, customers)
  Column:    snake_case (total_amount, created_date)
  PK:        id (BIGINT UNSIGNED)
  FK:        {table}_id (BIGINT UNSIGNED)
  Index:     idx_{table}_{column}
```

### **2. Cardinality Notation**

```
CDM (Chen notation):
  1 ─── 1     (one-to-one)
  1 ─── N     (one-to-many)
  M ─── N     (many-to-many)

LDM (Crow's Foot):
  ─┤●        (zero or one)
  ─┤|        (exactly one)
  ─○│        (zero or many)
  ━┤|        (one or many)

PDM SQL:
  Foreign Key constraints
  Unique constraints
  Check constraints
```

### **3. Attribute Determination**

```
CDM → LDM transformation:
✓ Tất cả attributes phải có type (Number, Text, Date, etc.)
✓ Định nghĩa domain (value range)
✓ Add constraints (required, optional, unique)
✓ Remove duplicate attributes

LDM → PDM transformation:
✓ Map to SQL data types (INT, VARCHAR, DATE, etc.)
✓ Add length/precision (VARCHAR(255), DECIMAL(12,2))
✓ Add default values
✓ Add check constraints
```

### **4. Relationship Resolution**

```
CDM Relationships:
  ┌─────────────────────────────────────┐
  │  Entities connected by relationships │
  └─────────────────────────────────────┘

LDM Resolution:
  1:1  → Add FK to one table
  1:N  → Add FK to N table  ← MOST COMMON
  M:N  → Create junction table with 2 FKs

PDM Implementation:
  ┌──────────────────────────┐
  │ CREATE TABLE junction (  │
  │   id BIGINT PK,          │
  │   parent_id BIGINT FK,   │
  │   child_id BIGINT FK,    │
  │   UNIQUE(parent, child)  │
  │ );                       │
  └──────────────────────────┘
```

---

## 🔍 Kiểm Tra Từng Bước

### **After CDM Generation - Kiểm Tra:**

```sql
☑ Tất cả entities hết tables không?
☑ Mỗi entity có ít nhất 1 identifier không?
☑ Relationships được vẽ chính xác không?
☑ Data không có technical details (BIGINT, VARCHAR)?
☑ Business rules được ghi chú rõ không?
```

### **After LDM Generation - Kiểm Tra:**

```sql
☑ Chuẩn hóa 3NF?
  • 1NF: Không có repeating groups
  • 2NF: Không có partial dependency
  • 3NF: Không có transitive dependency

☑ M:N relationships có junction table?
☑ Tất cả FK được công khai?
☑ Không có dữ liệu dự phòng?
☑ Tất cả attributes có định nghĩa rõ?
```

### **After PDM Generation - Kiểm Tra:**

```sql
☑ Tất cả tables cùng MySQL dialect?
☑ Data types thích hợp (không VARCHAR(1000) cho ID)?
☑ Indexes trên FK + thường query?
☑ Constraints: PK, FK, UNIQUE, CHECK?
☑ Default values hợp lý?
☑ Triggers & stored procedures nếu cần?
```

### **SQL Validation**

```sql
-- Kiểm tra table structure
SHOW TABLES FROM senhong_ocop;

-- Kiểm tra column details
DESCRIBE orders;
SHOW COLUMNS FROM orders;

-- Kiểm tra Foreign Keys
SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_SCHEMA IS NOT NULL;

-- Kiểm tra Indexes
SHOW INDEXES FROM orders;

-- Kiểm tra Constraints
SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = 'senhong_ocop';
```

---

## 📋 Checklist Chuyển Đổi Toàn Bộ

### **Phase 1: PDM → CDM**

- [ ] Mở PDM file trong PowerDesigner
- [ ] Tools → Generate → Conceptual Data Model
- [ ] Kiểm tra entities không có technical details
- [ ] Kiểm tra relationships (1:1, 1:N, M:N)
- [ ] Save as: CDM_senhong_ocop.cdm
- [ ] Export CDM → PDF/PNG (documentation)

### **Phase 2: CDM → LDM**

- [ ] Mở CDM file
- [ ] Tools → Generate → Logical Data Model
- [ ] Chọn Target DBMS: Generic (DB-independent)
- [ ] Kiểm tra 3NF normalization
- [ ] Xác nhận M:N junction tables
- [ ] Save as: LDM_senhong_ocop.ldm
- [ ] Export LDM → PDF (for analysts)

### **Phase 3: LDM → PDM (MySQL 8.0)**

- [ ] Mở LDM file
- [ ] Tools → Generate → Physical Data Model
- [ ] Chọn DBMS: MySQL 5.1+
- [ ] Chọn: Add Indexes, Constraints, Domains
- [ ] Kiểm tra data types MySQL-specific
- [ ] Thêm UUID/Auto-increment strategies
- [ ] Save as: PDM_senhong_ocop_MySQL.pdm

### **Phase 4: Generate & Deploy**

- [ ] File → Generate → SQL Create Script
- [ ] Chọn: Include Indexes, Foreign Keys, Unique Constraints
- [ ] Export → senhong_ocop_FINAL.sql
- [ ] Review SQL script
- [ ] Execute trong MySQL database
- [ ] Verify tất cả tables/indexes

---

## 📊 Bảng So Sánh 3 Model

| Tiêu Chí             | CDM           | LDM     | PDM              |
| -------------------- | ------------- | ------- | ---------------- |
| **DB-Specific**      | ❌            | ❌      | ✅               |
| **Audience**         | Business      | Analyst | DBA/Dev          |
| **Data Types**       | Generic       | Generic | MySQL-specific   |
| **Normalization**    | Basic         | 3NF ✅  | 3NF + Denorm     |
| **Primary Keys**     | Identifier    | PK ✅   | PK + Type        |
| **Foreign Keys**     | Relationships | FK ✅   | FK + Constraints |
| **Indexes**          | ❌            | ❌      | ✅               |
| **Partitioning**     | ❌            | ❌      | ✅               |
| **Easy to Change**   | ✅            | ✅      | ❌               |
| **Production-Ready** | ❌            | ❌      | ✅               |

---

## 🎯 Ví Dụ Thực Tế: orders Entity

### **1. CDM (Conceptual)**

```
Entity: Order
├─ Identifier: OrderID
├─ Attributes:
│  ├─ CustomerReference (Customer entity)
│  ├─ TotalAmount (Number)
│  ├─ Status (Text)
│  ├─ ShippingAddress (Text)
│  └─ CreatedDate (Date)
└─ Business Rules:
   • Status: pending → processing → shipped → delivered
   • TotalAmount > 0
   • Unique OrderNumber
```

### **2. LDM (Logical)**

```
Table: Order (tentative)
├─ PK: OrderID (Number)
├─ FK: CustomerID → Customer.CustomerID
├─ Attributes:
│  ├─ OrderNumber (String, Unique)
│  ├─ CustomerID (Number, FK)
│  ├─ TotalAmount (Numeric, >0)
│  ├─ Status (Enum: pending|processing|shipped|delivered)
│  ├─ ShippingAddress (String)
│  ├─ CreatedDate (Date, Default: Today)
│  └─ UpdatedDate (Date)
└─ Constraints:
   • PK: OrderID
   • FK: CustomerID → Customer
   • UNIQUE: OrderNumber
```

### **3. PDM MySQL (Physical)**

```sql
CREATE TABLE orders (
    id BIGINT(20) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id BIGINT(20) UNSIGNED NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL CHECK (total_amount > 0),
    status ENUM('pending','processing','shipped','delivered') DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_customers
        FOREIGN KEY (customer_id)
        REFERENCES customers(id) ON DELETE SET NULL,

    INDEX idx_order_number (order_number),
    INDEX idx_customer_id (customer_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

---

## 🚀 Khi Nào Cần Lặp Lại?

```
✅ Lặp lại (CDM → LDM → PDM) khi:
   • Requirement thay đổi → Review at CDM
   • Business logic thay đổi → Update CDM, re-generate LDM
   • Optimize performance → Denormalize PDM
   • Add new features → Start from CDM

❌ Không lặp lại khi:
   • Chỉ refactoring code
   • Bug fixing
   • Minor UI changes
```

---

**Last Updated**: 2026-04-16 | **Version**: 2.0 | **Database**: senhong_ocop
