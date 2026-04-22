# 📊 SENHONG OCOP - CDM QUICK REFERENCE CARD

## 🎯 3 Cách Tạo CDM trong PowerDesigner

### ✅ CÁCH 1: Reverse Engineer từ Database (KHUYẾN NGHỊ)

```
1. File → New → Data Model → Chọn MySQL 8.0
2. Database → New Connection
   ├─ Name: senhong_ocop_live
   ├─ Server: localhost
   ├─ Port: 3306
   ├─ User: root
   ├─ Password: (trống)
   └─ Database: senhong_ocop
3. Database → Reverse Engineer → From Database Connection
4. Chọn tất cả tables → Finish
5. File → Generate → Conceptual Data Model from PDM
```

**Thời gian**: 5-10 phút | **Độ chính xác**: 100%

---

### ✅ CÁCH 2: Import SQL Script

```
1. Mở PowerDesigner
2. File → New → Script
3. Copy nội dung từ: senhong_ocop_CDM.sql
4. Dán vào Script editor
5. Database → Configure DDL Generation
6. File → Generate Physical Data Model
```

**Thời gian**: 3-5 phút | **Độ chính xác**: 99%

---

### ✅ CÁCH 3: Convert DBML → SQL

```bash
# Cài npm (nếu chưa có)
npm install -g @dbml/cli

# Convert
dbml2sql senhong_ocop_CDM.dbml --mysql -o output.sql

# Rồi import vào PowerDesigner (xem Cách 2)
```

**Thời gian**: 2-3 phút | **Độ chính xác**: 98%

---

## 📂 Files Sử Dụng

| File                          | Mục Đích               | Format             |
| ----------------------------- | ---------------------- | ------------------ |
| `senhong_ocop_CDM.sql`        | Tạo schema từ SQL      | .sql (1000+ lines) |
| `senhong_ocop_CDM.dbml`       | Sử dụng với DBML tools | .dbml (300+ lines) |
| `POWERDESIGNER_CDM_GUIDE.md`  | Hướng dẫn chi tiết     | .md (500+ lines)   |
| `DATABASE_SCHEMA_ANALYSIS.md` | Tài liệu schema        | .md (400+ lines)   |

📁 **Vị trí**: `c:\xampp\htdocs\luanvan\database\`

---

## 🗂️ 33 Bảng Chính (Phân Nhóm)

### 👥 User Management (3 bảng)

- `users` - Tài khoản người dùng (admin, staff, customer, supplier)
- `staffs` - Thông tin nhân viên (1:1 với users)
- `customers` - Thông tin khách hàng (1:1 với users)

### 📅 Attendance & HR (2 bảng)

- `attendances` - Check-in/out, scenario detection, early leave
- `salaries` - Tính lương với bonus/penalty/absent

### 📦 Supply Chain (7 bảng)

- `suppliers` - Nhà cung cấp
- `category_products` - Phân loại sản phẩm
- `products` - Sản phẩm (simple/bundle/variable)
- `product_variants` - Biến thể sản phẩm (SKU)
- `product_images` - Hình ảnh sản phẩm
- `imports` - Phiếu nhập hàng
- `import_items` - Chi tiết nhập (FIFO tracking)
- `inventories` - Tồn kho theo variant
- `inventory_writeoffs` - Xóa hàng (expired/damaged/lost)

### 🛍️ E-Commerce (7 bảng)

- `orders` - Đơn hàng (7 status)
- `order_items` - Chi tiết đơn hàng
- `payments` - Thanh toán (5 status)
- `order_cancellations` - Hủy đơn
- `order_returns` - Trả hàng
- `order_return_images` - Hình ảnh trả hàng
- `wishlists` - Danh sách yêu thích

### 💰 Promotions (3 bảng)

- `discounts` - Chương trình giảm giá
- `discount_usages` - Lịch sử sử dụng
- `discount_product` - Sản phẩm áp dụng (N:N)

### 📝 Content Management (8 bảng)

- `blogs` - Bài viết blog
- `blog_blocks` - Khối nội dung blog
- `reviews` - Đánh giá sản phẩm
- `review_likes` - Like đánh giá
- `review_replies` - Trả lời đánh giá
- `contacts` - Liên hệ từ khách hàng
- `customer_messages` - Chat/message khách hàng
- `notifications` - Thông báo

### 🛠️ System Tables (3 bảng)

- `sessions` - Session người dùng
- `cache` - Cache hệ thống
- `jobs` - Job queue

---

## 🔗 Quan Hệ Chính (Relationships)

### Phân Cấp:

```
users (1) ──→ (N) staffs ──→ (N) attendances
           ├──→ (N) salaries
           └──→ (N) imports

users (1) ──→ (N) customers ──→ (N) orders
                             ├──→ (N) wishlists
                             └──→ (N) reviews
```

### Khóa Ngoại Quan Trọng:

```
- staffs.user_id → users.id (ON DELETE CASCADE)
- attendances.staff_id → staffs.user_id (ON DELETE CASCADE)
- orders.customer_id → customers.id (ON DELETE SET NULL)
- order_items.order_id → orders.id (ON DELETE CASCADE)
- products.category_id → category_products.id (ON DELETE RESTRICT)
- discount_product.discount_id ↔ product_id (N:N)
```

### Cardinality:

- **1:1** → staffs, customers
- **1:N** → orders, inventory, reviews (phổ biến)
- **N:N** → discount_product, reviews_likes

---

## 🔧 Database Config

```ini
# Từ .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=senhong_ocop
DB_USERNAME=root
DB_PASSWORD=(trống)
```

**Kiểm tra connection:**

```bash
mysql -h 127.0.0.1 -u root -e "SELECT 1"
# Nếu OK sẽ thấy: 1
```

---

## 📋 Schema Highlights

### Unique Constraints:

```
✅ users.email
✅ product_variants.sku
✅ orders.order_number
✅ discounts.code
✅ wishlists (customer_id, product_variant_id)
✅ attendances (staff_id, work_date, shift)
```

### Key Indexes:

```
✅ idx_orders_customer_date (customer_id, created_at)
✅ idx_attendances_staff_date (staff_id, work_date)
✅ idx_import_items_variant_expiry (product_variant_id, expiry_date)
✅ idx_inventory_available (available_quantity)
```

### Enums (Chọn Lựa):

```
users.role: 'admin', 'staff', 'customer', 'supplier'
orders.status: 'pending', 'processing', 'shipped', 'delivered',
               'cancelled', 'refunded', 'completed'
payments.method: 'cash', 'bank_transfer', 'credit_card', 'e_wallet', 'other'
attendances.shift: 'morning', 'afternoon'
discounts.discount_type: 'percentage', 'fixed_amount'
```

---

## ⚡ Quick Commands

### MySQL Verify Tables:

```sql
SHOW TABLES FROM senhong_ocop;
SHOW TABLE STATUS FROM senhong_ocop;
SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'senhong_ocop';
```

### Check Foreign Keys:

```sql
SELECT CONSTRAINT_NAME, TABLE_NAME, REFERENCED_TABLE_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'senhong_ocop'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### Generate Diagram SQL:

```sql
SELECT CONCAT('SELECT * FROM ', TABLE_NAME, ';')
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'senhong_ocop'
ORDER BY TABLE_NAME;
```

---

## 📊 Statistics

| Metric             | Count           |
| ------------------ | --------------- |
| Tables             | 33 (+ 3 system) |
| Columns            | 400+            |
| Foreign Keys       | 45+             |
| Indexes            | 60+             |
| Unique Constraints | 8+              |
| Enum Fields        | 25+             |
| Views              | 3               |

---

## 🎓 Tips & Tricks

### PowerDesigner:

1. **Auto Layout**: View → Arrange → Auto Layout (tổ chức diagram)
2. **Color by Group**: Phải click mỗi entity → Properties → Color
3. **Export Diagram**: File → Export → PNG/PDF
4. **Generate SQL**: File → Generate DDL Scripts
5. **Refresh from DB**: Tools → Refresh from Database

### DBML Tools:

- **dbdiagram.io**: Paste DBML → tạo diagram online
- **ERDPlus**: Import/edit DBML
- **Terminal**: `dbml2sql file.dbml --mysql -o output.sql`

---

## ✓ Checklist Sebelum Deploy

- [ ] Reverse engineer thành công
- [ ] Tất cả 33 tables hiển thị trong diagram
- [ ] 45+ relationships được vẽ chính xác
- [ ] Kiểm tra primary keys (tất cả bảng)
- [ ] Kiểm tra foreign keys (constraints)
- [ ] Validate enums và data types
- [ ] Export CDM thành PDF/PNG
- [ ] Lưu PowerDesigner project (.pdm file)
- [ ] Backup SQL script
- [ ] Chia sẻ documentation với team

---

## 📞 Quick Support

**Problem**: "Connection refused"
**Fix**: Kiểm tra MySQL service: `services.msc`, search MySQL, verify "Running"

**Problem**: "Access denied for user 'root'"
**Fix**: Kiểm tra password sau dấu `=` trong `DB_PASSWORD`, thường trống

**Problem**: "Table not found"
**Fix**: Kiểm tra `DB_DATABASE=senhong_ocop` chính xác, database có tồn tại

**Problem**: "Diagram quá lớn/lộn xộn"
**Fix**: Dùng View → Arrange → Auto Layout, hoặc tạo multiple diagrams theo nhóm

---

**Last Updated**: 2026-04-16 | **Version**: 1.0 | **Database**: senhong_ocop (MySQL 8.0)

⚠️ **IMPORTANT**: Luôn backup `.pdm` file sau khi tạo CDM!
