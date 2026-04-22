# HƯỚNG DẪN: Tạo CDM Diagram cho SENHONG OCOP với PowerDesigner

## 📋 Mục Lục

1. [Tổng Quan](#tổng-quan)
2. [Các File Chuẩn Bị](#các-file-chuẩn-bị)
3. [Phương Pháp Reverse Engineer](#phương-pháp-reverse-engineer)
4. [Cấu Hình Database Connection](#cấu-hình-database-connection)
5. [Tạo CDM Diagram](#tạo-cdm-diagram)
6. [Best Practices](#best-practices)

---

## 🎯 Tổng Quan

Hệ thống SENHONG OCOP gồm:

- **Database**: MySQL (senhong_ocop)
- **Host**: 127.0.0.1 (XAMPP)
- **Port**: 3306
- **Username**: root
- **Password**: (trống)
- **33 bảng** được tổ chức thành 6 nhóm chức năng chính

### Cấu Trúc Hệ Thống:

```
1. User Management     (3 bảng)  → users, staffs, customers
2. Attendance & HR     (2 bảng)  → attendances, salaries
3. Supply Chain        (7 bảng)  → suppliers, products, imports...
4. E-Commerce          (7 bảng)  → orders, payments, returns...
5. Promotions          (3 bảng)  → discounts, usages...
6. Content Management  (8 bảng)  → blogs, reviews, contacts...
7. System Tables       (3 bảng)  → sessions, cache, jobs...
```

---

## 📁 Các File Chuẩn Bị

File chuẩn bị sẵn trong thư mục `database/`:

### 1. **senhong_ocop_CDM.sql**

- File SQL DDL hoàn chỉnh (1000+ dòng)
- Chứa tất cả CREATE TABLE statements
- Định nghĩa đầy đủ Foreign Keys, Indexes, Constraints
- Có 3 Views để hỗ trợ reporting

**Cách sử dụng:**

```sql
# Trong MySQL Workbench hoặc PowerDesigner Script Editor:
source c:\xampp\htdocs\luanvan\database\senhong_ocop_CDM.sql
```

### 2. **senhong_ocop_CDM.dbml**

- Format DBML (Database Markup Language)
- Tương thích với dbdiagram.io, ERDPlus
- Chứa toàn bộ schema và relationships
- Có thể import trực tiếp vào nhiều công cụ

### 3. **DATABASE_SCHEMA_ANALYSIS.md**

- Tài liệu hóa toàn bộ schema (400+ dòng)
- Liệt kê tất cả tables, columns, relationships
- Enum values, constraints, indexes
- Business logic notes

---

## 🔄 Phương Pháp Reverse Engineer

### **Phương Pháp 1: Reverse Engineer trực tiếp từ Database (KHUYẾN NGHỊ)**

**Ưu điểm:**

- ✅ Đảm bảo 100% chính xác với database hiện tại
- ✅ Tự động cập nhật khi database thay đổi
- ✅ Không cần import script thêm

**Bước thực hiện:**

#### Bước 1: Mở PowerDesigner

```
File → New → Data Model
Database Type: MySQL 8.0 (hoặc cao hơn)
```

#### Bước 2: Tạo Connection tới Database

```
Database → New Connection
   ├─ Connection Name: senhong_ocop_live
   ├─ DBMS: MySQL 5.1+
   ├─ Server Name: localhost (hoặc 127.0.0.1)
   ├─ Port: 3306
   ├─ User ID: root
   ├─ Password: (trống)
   ├─ Database: senhong_ocop
   └─ Test Connection
```

#### Bước 3: Reverse Engineer Database

```
Database → Reverse Engineer → From Database Connection
   ├─ Chọn connection: senhong_ocop_live
   ├─ Chọn "Generate PDM from current repository"
   └─ Chọn tất cả tables → Next → Finish
```

#### Bước 4: Tạo CDM từ PDM (Physical → Conceptual)

```
File → Generate → Conceptual Data Model from PDM
   ├─ Chọn cấp độ chi tiết
   ├─ Tùy chọn mapping
   └─ Generate
```

---

### **Phương Pháp 2: Import SQL Script**

**Bước 1:** Tạo Script trong PowerDesigner

```
File → New → Script
Paste nội dung từ: senhong_ocop_CDM.sql
```

**Bước 2:** Execute Script

```
Database → Configure DDL Generation
```

**Bước 3:** Generate PDM

```
File → Generate Physical Data Model
```

---

### **Phương Pháp 3: Sử dụng DBML và DBML Converter**

**Cách 1: Qua Online Tool**

1. Truy cập: https://www.dbml.org/cli
2. Copy nội dung từ `senhong_ocop_CDM.dbml`
3. Dán vào editor
4. Export → SQL
5. Import SQL vào PowerDesigner

**Cách 2: Dùng Command Line**

```bash
# Cài đặt DBML CLI
npm install -g @dbml/cli

# Convert DBML → SQL
dbml2sql senhong_ocop_CDM.dbml --mysql -o senhong_ocop_generated.sql

# Sau đó import SQL vào PowerDesigner
```

---

## 🔌 Cấu Hình Database Connection

### Trong PowerDesigner:

**Database → Configure Connections**

```ini
[senhong_ocop_live]
DBMS = MySQL 5.1
Driver = ODBC
ODBCDriver = MySQL ODBC 8.0 Driver
Server = localhost
Port = 3306
User = root
Password =
Database = senhong_ocop
InitialCatalog = senhong_ocop
```

### Yêu Cầu Cài Đặt:

- ✅ MySQL Server 8.0+ đang chạy
- ✅ XAMPP/MySQL Service active
- ✅ MySQL ODBC Driver cài đặt (hoặc MySQL Connector)
- ✅ PowerDesigner có quyền truy cập Database

**Kiểm tra MySQL đang chạy:**

```bash
# Trong PowerShell (Admin)
Get-Service | Select-String MySQL

# Hoặc qua MySQL Command Line
mysql -h localhost -u root -e "SELECT 1"
```

---

## 🎨 Tạo CDM Diagram

### Sau khi Reverse Engineer thành công:

#### 1. **Tổ Chức Layout**

```
View → Unified Modeling Notation
   ├─ Enable CDM View
   ├─ Configure → Adjust diagram symbols
   └─ Arrange → Auto Layout
```

#### 2. **Tô Màu theo Nhóm**

```
Entities:
  - User Management (xanh da trời)
  - Attendance & HR (xanh lá)
  - Supply Chain (cam)
  - E-Commerce (đỏ)
  - Promotions (tím)
  - Content (vàng)
```

#### 3. **Định Nghĩa Packages/Domains**

```
Packages thứ cấp:
  ├─ Domain: User
  ├─ Domain: Attendance
  ├─ Domain: Inventory
  ├─ Domain: Order
  ├─ Domain: Promotion
  └─ Domain: Content
```

#### 4. **Thêm Business Rules**

```
Edit Entity → Properties → Business Rules
Ví dụ: attendances
  - "Mỗi nhân viên, mỗi ngày, mỗi ca chỉ có 1 record"
  - "remaining_quantity <= quantity"
```

#### 5. **Tạo Cardinality Notation**

```
Mối quan hệ có thể là:
  1:1 (one-to-one)
  1:N (one-to-many) ← Phổ biến nhất
  N:N (many-to-many) ← discount_product
```

---

## 📊 Một Số Relationships Quan Trọng

### Phân Cấp Chính:

```
users (1) ──→ (N) staffs ──→ (N) attendances
                           ├──→ (N) salaries
                           └──→ (N) imports

users (1) ──→ (N) customers ──→ (N) orders
                             ├──→ (N) wishlists
                             └──→ (N) reviews

products (1) ──→ (N) product_variants ──→ (N) inventories
                                      ├──→ (N) import_items
                                      ├──→ (N) product_images
                                      └──→ (N) reviews
```

### Mối Quan Hệ Many-to-Many:

```
1. discounts (N:N) products        [junction: discount_product]
2. orders   (1:N) order_items      [chứa chi tiết]
3. reviews  (1:N) review_likes     [tracking người like]
4. imports  (1:N) import_items     [FIFO tracking]
```

---

## 🎯 Best Practices

### 1. **Naming Convention**

```
✅ Bảng (Tables):        customers, order_items (plural, snake_case)
✅ Cột (Columns):         user_id, email_verified_at (snake_case)
✅ Primary Key:          id (auto-increment bigint)
✅ Foreign Key:          {table}_id (entity_id)
✅ Indexes:              idx_{table}_{columns}
✅ Unique Constraints:   uk_{table}_{columns}
```

### 2. **Data Type Standards**

```
✅ ID fields:            BIGINT UNSIGNED
✅ Email/URL:            VARCHAR(255)
✅ Text ngắn:            VARCHAR(255)
✅ Text dài:             TEXT hoặc LONGTEXT
✅ Số tiền:              DECIMAL(12,2)
✅ Tỷ lệ/phần trăm:      DECIMAL(8,2)
✅ Boolean:              TINYINT(1)
✅ Enum:                 ENUM('option1', 'option2')
✅ Timestamp:            TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

### 3. **Foreign Key Strategy**

```
✅ Tất cả FK có ON DELETE CASCADE (để xóa liên tầng)
   NGOẠI LỆ: restrict nếu là dữ liệu tham chiếu quan trọng
             set null nếu FK optional

Ví dụ:
  - users → staffs: CASCADE (xóa user xóa staff)
  - products → category: RESTRICT (không xóa category có sản phẩm)
  - orders → customers: SET NULL (xóa khách hàng nhưng giữ order)
```

### 4. **Index Strategy**

```
✅ Tất cả FK tự động có index
✅ Các cột thường dùng trong WHERE clause
✅ Các cột dùng trong ORDER BY
✅ Composite indexes cho queries phổ biến

Ví dụ:
  - idx_orders_customer_date (customer_id, created_at)
  - idx_attendances_staff_date (staff_id, work_date)
  - idx_import_items_variant_expiry (product_variant_id, expiry_date)
```

### 5. **Unique Constraints**

```
✅ users.email                    [login]
✅ staffs.user_id                 [1:1 relationship]
✅ product_variants.sku           [product identifier]
✅ orders.order_number            [unique order number]
✅ discounts.code                 [promotion code]
✅ attendances (staff_id, work_date, shift)  [no duplicate check-in]
```

---

## 📈 Quy Mô Database

| Chỉ Số          | Giá Trị |
| --------------- | ------- |
| Số Bảng         | 33      |
| Số Cột          | 400+    |
| Số Foreign Keys | 45+     |
| Số Indexes      | 60+     |
| Số Views        | 3       |
| Enum Fields     | 25+     |

---

## 🔍 Kiểm Tra Integrity

Sau khi tạo CDM, chạy các script kiểm tra:

```sql
-- Kiểm tra tất cả Foreign Keys
SELECT TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME IS NOT NULL
AND TABLE_SCHEMA = 'senhong_ocop'
ORDER BY TABLE_NAME;

-- Kiểm tra Unique Constraints
SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE CONSTRAINT_NAME LIKE 'uk_%'
AND TABLE_SCHEMA = 'senhong_ocop';

-- Kiểm tra Indexes
SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME, NOT_NULL
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'senhong_ocop'
ORDER BY TABLE_NAME, INDEX_NAME;
```

---

## 📝 Hướng Dẫn Export CDM

**Từ PowerDesigner:**

### Export sang Các Format:

```
File → Export
   ├─ SQL Files (*.sql)           → Tạo migration script
   ├─ XML (*.xml)                 → Chia sẻ schema
   ├─ HTML Report (*.html)        → Documentation
   ├─ PNG/JPG (*.png, *.jpg)     → Hình ảnh diagram
   ├─ PDF (*.pdf)                 → In ấn
   └─ Word Document (*.docx)      → Tài liệu
```

### Tạo Documentation từ CDM:

```
Tools → Generate Documentation
   ├─ Output Format: HTML (khuyến nghị)
   ├─ Cover Page: Yes
   ├─ Table of Contents: Yes
   ├─ Include All Objects: Yes
   └─ Generate
```

---

## 🚀 Workflow Khuyến Nghị

1. **Ban đầu:**

    ```
    PowerDesigner Reverse Engineer → PDF export → Lưu trữ
    ```

2. **Khi thay đổi schema:**

    ```
    Cập nhật migration → Re-reverse engineer → Cập nhật CDM
    ```

3. **Documentation:**

    ```
    CDM → Export HTML → Chia sẻ team
    ```

4. **Phát triển:**
    ```
    CDM → Generate DDL → Migration script → Apply database
    ```

---

## ❓ FAQ

**Q: PowerDesigner không nhận được MySQL Connection?**
A: Cài MySQL ODBC Driver hoặc kiểm tra MySQL Service đang chạy

**Q: Reverse Engineer bị lỗi "Access denied"?**
A: Kiểm tra username/password, hoặc tạo user MySQL riêng với permissions

**Q: Làm sao để giữ CDM sync với database?**
A: Dùng "Refresh from Database" để cập nhật lại

**Q: Có thể collaborate on PowerDesigner CDM không?**
A: Có, lưu file trên shared folder hoặc sử dụng versioning tools (Git)

---

## 📞 Support

- **Database Config**: `.env` file
- **Schema Analysis**: `DATABASE_SCHEMA_ANALYSIS.md`
- **DDL Scripts**: `senhong_ocop_CDM.sql`
- **DBML Format**: `senhong_ocop_CDM.dbml`
- **Server**: XAMPP MySQL (localhost:3306)

---

**Cập nhật**: 16/04/2026 | **Version**: 1.0 | **Database**: senhong_ocop
