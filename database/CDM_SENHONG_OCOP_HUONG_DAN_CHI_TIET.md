# 📊 HƯỚNG DẪN CHI TIẾT VẼ CDM HỆ THỐNG SENHONG OCOP

**Ngày tạo:** 16/04/2026  
**Database:** senhong_ocop (MySQL)  
**Tổng số bảng:** 32 tables | **Tổng số Foreign Keys:** 42  
**Mục đích:** Hướng dẫn chi tiết cách vẽ sơ đồ CDM chuẩn cho hệ thống bán hàng OCOP sử dụng database senhong_ocop

---

## I. GIỚI THIỆU HỆ THỐNG SENHONG OCOP

### 1. Khái Quát về Hệ Thống

- **Tên hệ thống:** Senhong OCOP - Hệ thống quản lý bán hàng OCOP online
- **Loại hệ thống:** E-commerce + Warehouse Management + HR Management
- **Phạm vi:** Quản lý người dùng, nhân viên, hàng hóa, kho, đơn hàng, và quản lý công
- **Người dùng chính:** Admin, Staff, Customer, Supplier
- **Đặc điểm:** Theo dõi công nhân viên chi tiết, quản lý kho một cách chính xác, xử lý đơn hàng toàn vẹn

### 2. 6 Modules Chính

1. **User Management** - Quản lý người dùng, nhân viên
2. **Attendance & HR** - Quản lý chấm công, tính lương
3. **Inventory & Supply Chain** - Quản lý kho, nhập hàng
4. **E-Commerce & Orders** - Quản lý bán hàng, đơn hàng
5. **Promotions & Discounts** - Quản lý khuyến mãi, mã giảm giá
6. **Content & Interaction** - Quản lý blog, đánh giá, tin nhắn

---

## II. PHÂN TÍCH THỰC THỂ (ENTITIES)

### **MODULE 1: USER MANAGEMENT (3 Thực Thể)**

#### 1. **USERS** - Người dùng hệ thống

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── name (VARCHAR 255) - Required - Simple, Single-valued
├── email (VARCHAR 255) - UNIQUE, Required - Key attribute
├── password (VARCHAR 255) - Required
├── phone (VARCHAR 20) - UNIQUE
├── avatar (VARCHAR 255)
├── role (ENUM) - Giá trị: admin, staff, customer, supplier
├── is_active (TINYINT) - Default: 1 - Boolean
├── lock_expires_at (TIMESTAMP)
├── lock_enabled_by (BIGINT) - FK
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Bảng gốc lưu trữ tất cả người dùng của hệ thống
- Mỗi người dùng được phân loại theo role
- Hỗ trợ khóa tài khoản khi cần thiết
```

#### 2. **STAFFS** - Hồ sơ nhân viên

```
🔑 Khóa chính: user_id (BIGINT) → FK users(id)

📋 Thuộc tính:
├── user_id (PK, FK) - One-to-one relationship
├── phone (VARCHAR 20)
├── date_of_birth (DATE)
├── address (TEXT) - Composite attribute (có thể chia: số nhà, đường, thành phố)
├── position (ENUM) - manager, staff, leader, director
├── start_date (DATE)
├── probation_start (DATE)
├── probation_end (DATE)
├── employment_status (ENUM) - probation, official, resigned
├── probation_hourly_wage (DECIMAL 10,2)
├── official_hourly_wage (DECIMAL 10,2)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Lưu trữ thông tin chi tiết của nhân viên
- Mối quan hệ 1:1 với USERS (từ user_id)
- Theo dõi tình trạng thử việc (probation)
- Quản lý mức lương theo giai đoạn
```

#### 3. **CUSTOMERS** - Hồ sơ khách hàng

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa ngoài: user_id (BIGINT) → FK users(id) [UNIQUE]

📋 Thuộc tính:
├── id (PK, BIGINT)
├── user_id (FK, UNIQUE)
├── phone (VARCHAR 20)
├── date_of_birth (DATE)
├── address (TEXT) - Composite
├── is_default_address (TINYINT)
├── province_code (VARCHAR 5)
├── district_code (VARCHAR 5)
├── ward_code (VARCHAR 5)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Lưu trữ thông tin chi tiết của khách hàng
- Mối quan hệ 1:1 với USERS qua user_id
- Hỗ trợ địa chỉ mặc định
- Lưu mã địa chỉ hành chính (tỉnh, huyện, phường)
```

### **MODULE 2: ATTENDANCE & SALARY (2 Thực Thể)**

#### 4. **ATTENDANCES** - Chấm công hàng ngày

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: (staff_id, work_date, shift) - Composite key

📋 Thuộc tính:
├── id (PK, BIGINT)
├── staff_id (FK) → staffs(user_id) - Required
├── work_date (DATE) - Required - Simple, Single-valued
├── shift (ENUM) - morning, afternoon
├── expected_check_in (TIME)
├── expected_check_out (TIME)
├── check_in (TIME)
├── check_in_ip (VARCHAR 45)
├── check_in_latitude (DECIMAL 10,7)
├── check_in_longitude (DECIMAL 10,7)
├── check_in_network_type (VARCHAR 50)
├── check_in_distance_meters (DECIMAL 8,2)
├── check_in_verification_method (VARCHAR 20)
├── check_out (TIME)
├── is_late (TINYINT) - Scenario 1: Đi muộn
├── is_early_leave (TINYINT) - Scenario 2: Về sớm
├── is_completed (TINYINT) - Đã hoàn thành chấm công
├── is_auto_checkout_forced (TINYINT) - Scenario 5: Tự động checkout
├── early_leave_reason (TEXT)
├── early_leave_status (ENUM) - pending, approved, rejected
├── early_leave_pay_percent (INT) - Default: 100
├── early_leave_approved_by (BIGINT) → FK staffs(user_id)
├── early_leave_approved_at (TIMESTAMP)
├── scenario_type (TINYINT) - 1-5: Loại kịch bản
├── worked_minutes (INT) - Derived attribute (tính từ check_in, check_out)
├── salary_amount (DECIMAL 12,2) - Derived attribute (tính từ worked_minutes)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Theo dõi chấm công hàng ngày của nhân viên
- 5 Kịch bản riêng biệt:
  * Scenario 1: Đi làm bình thường
  * Scenario 2: Đi muộn
  * Scenario 3: Về sớm
  * Scenario 4: Cả đi muộn và về sớm
  * Scenario 5: Tự động checkout (quên checkout)
- Lưu trữ dữ liệu GPS, địa chỉ IP để xác minh
- Tính toán lương dựa trên giờ làm việc
```

#### 5. **SALARIES** - Lương tháng

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: (staff_id, month, year)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── staff_id (FK) → staffs(user_id) - Required
├── month (INT 1-12) - Required
├── year (INT YYYY) - Required
├── total_hours (DECIMAL 8,2) - Tính từ attendances
├── total_amount (DECIMAL 12,2) - Derived: total_hours * hourly_wage
├── bonus (DECIMAL 12,2) - Default: 0
├── penalty (DECIMAL 12,2) - Default: 0
├── absent_count (INT) - Default: 0
├── absent_amount (DECIMAL 12,2) - Default: 0
├── final_amount (DECIMAL 12,2) - Derived: total_amount + bonus - penalty - absent_amount
├── status (ENUM) - draft, approved, paid
├── notes (TEXT)
├── paid_at (TIMESTAMP)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Tính lương hàng tháng cho mỗi nhân viên
- Mối quan hệ 1:N với STAFFS (một nhân viên có nhiều lương hàng tháng)
- Tính toán từ dữ liệu Attendance
- Hỗ trợ thêm/trừ bonus/penalty
- Học kỳ xử lý (draft → approved → paid)
```

### **MODULE 3: INVENTORY & SUPPLY CHAIN (6 Thực Thể)**

#### 6. **SUPPLIERS** - Nhà cung cấp

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── name (VARCHAR 255) - Required - Simple, Single-valued
├── email (VARCHAR 255)
├── phone (VARCHAR 20)
├── address (TEXT) - Composite
├── city (VARCHAR 100)
├── province_code (VARCHAR 5)
├── tax_code (VARCHAR 20)
├── contact_person (VARCHAR 255)
├── is_active (TINYINT) - Default: 1
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Thông tin nhà cung cấp hàng hóa
- Lưu trữ thông tin thuế, địa chỉ
- Có thể khóa nhà cung cấp nếu không còn hợp tác
```

#### 7. **CATEGORY_PRODUCTS** - Danh mục sản phẩm

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: name, slug

📋 Thuộc tính:
├── id (PK, BIGINT)
├── name (VARCHAR 255) - UNIQUE, Required - Simple, Single-valued
├── description (TEXT)
├── image_url (VARCHAR 255)
├── slug (VARCHAR 255) - UNIQUE - Derived attribute
├── display_order (INT) - Default: 0
├── is_active (TINYINT) - Default: 1
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Phân loại sản phẩm (cà phê, trà, hạt cacao, v.v.)
- Slug dùng cho URL (ví dụ: "ca-phe-den")
- Hỗ trợ hiển thị theo thứ tự
```

#### 8. **PRODUCTS** - Sản phẩm chính

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── category_id (FK) → category_products(id) - Required
├── name (VARCHAR 255) - Required - Simple, Single-valued
├── type (ENUM) - simple, bundle, variable - Multi-valued possible
├── description (LONGTEXT)
├── origin (VARCHAR 100)
├── is_active (TINYINT) - Default: 1
├── is_featured (TINYINT) - Default: 0
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Sản phẩm chính trong hệ thống
- Mối quan hệ N:1 với CATEGORY_PRODUCTS
- Loại sản phẩm: simple (bình thường), variable (có biến thể)
- Có thể hiển thị nổi bật trên website
```

#### 9. **PRODUCT_VARIANTS** - Biến thể sản phẩm

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: sku

📋 Thuộc tính:
├── id (PK, BIGINT)
├── product_id (FK) → products(id) - Required
├── sku (VARCHAR 50) - UNIQUE, Required - Key attribute
├── name (VARCHAR 255) - Composite with product name
├── size (VARCHAR 50) - Multi-valued (có thể có nhiều size)
├── color (VARCHAR 50) - Multi-valued
├── weight (DECIMAL 8,2)
├── unit (VARCHAR 20) - kg, g, liter, v.v.
├── cost_price (DECIMAL 12,2)
├── selling_price (DECIMAL 12,2) - Required
├── display_price (DECIMAL 12,2) - Giá hiển thị (có thể khác selling_price)
├── thickness (INT)
├── is_active (TINYINT) - Default: 1
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Biến thể của sản phẩm (kích cỡ, màu sắc, khối lượng)
- Mỗi variant có SKU riêng (ví dụ: CP-001-500G-TRANG)
- Theo dõi giá vốn (cost) và giá bán (selling)
- Hỗ trợ giá hiển thị khác (sale price)
```

#### 10. **PRODUCT_IMAGES** - Hình ảnh sản phẩm

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── product_id (FK) → products(id) - Required
├── product_variant_id (FK) → product_variants(id) [SET NULL]
├── path (VARCHAR 255) - Required - Simple, Single-valued
├── is_primary (TINYINT) - Default: 0 - Boolean
├── display_order (INT) - Default: 0
└── created_at (TIMESTAMP)

🏷️ Mô tả:
- Thư viện hình ảnh cho sản phẩm
- Mỗi ảnh liên kết với sản phẩm, có thể liên kết thêm với variant
- Ảnh chính (primary) dùng làm ảnh đại diện
- Theo thứ tự hiển thị (display_order)
```

#### 11. **IMPORTS** - Nhập hàng từ nhà cung cấp

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── supplier_id (FK) → suppliers(id) - Required
├── staff_id (FK) → staffs(user_id) - Required - Người nhập hàng
├── import_date (DATE) - Required - Simple, Single-valued
├── notes (TEXT)
├── status (ENUM) - draft, imported, cancelled
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Ghi nhận lần nhập hàng từ nhà cung cấp
- Mối quan hệ N:1 với SUPPLIERS
- Mối quan hệ N:1 với STAFFS (nhân viên nhập hàng)
- Mỗi lần nhập hàng có nhiều chi tiết (import_items)
```

#### 12. **IMPORT_ITEMS** - Chi tiết nhập hàng

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── import_id (FK) → imports(id) - Required
├── product_variant_id (FK) → product_variants(id) - Required
├── quantity (INT) - Required - Simple, Single-valued
├── remaining_quantity (INT) - Default: 0 - For FIFO tracking
├── cost_price (DECIMAL 12,2)
├── manufacturing_date (DATE)
├── expiry_date (DATE) - Critical for OCOP products
├── batch_number (VARCHAR 100)
└── created_at (TIMESTAMP)

🏷️ Mô tả:
- Chi tiết từng sản phẩm trong lần nhập
- Theo dõi FIFO (First In First Out) - remaining_quantity
- Ghi nhận ngày sản xuất, hết hạn (quan trọng với hàng OCOP)
- Theo dõi lô hàng (batch_number) để truy vết
```

#### 13. **INVENTORIES** - Tồn kho thời gian thực

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: product_variant_id

📋 Thuộc tính:
├── id (PK, BIGINT)
├── product_variant_id (FK, UNIQUE) → product_variants(id) - Required
├── total_quantity (INT) - Default: 0 - Total stock
├── available_quantity (INT) - Default: 0 - Stock available for sale
└── updated_at (TIMESTAMP ON UPDATE)

🏷️ Mô tả:
- Theo dõi tồn kho thời gian thực của mỗi variant
- total_quantity: tổng số lượng trong kho
- available_quantity: số lượng có sẵn để bán (có thể < total do pre-order)
- Mối quan hệ 1:1 với PRODUCT_VARIANTS
- Cập nhật tự động mỗi khi có thay đổi
```

#### 14. **INVENTORY_WRITEOFFS** - Loại bỏ tồn kho

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── product_variant_id (FK) → product_variants(id) - Required
├── import_item_id (FK) → import_items(id) [SET NULL]
├── quantity (INT) - Required - Simple, Single-valued
├── reason (ENUM) - expired, damaged, lost, other
├── notes (TEXT)
├── writeoff_date (DATE) - Default: CURRENT_DATE
├── approved_by (BIGINT) → FK staffs(user_id) [SET NULL]
├── approved_at (TIMESTAMP)
├── status (ENUM) - pending, approved, rejected
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Ghi nhận hàng bị mất, hỏng, hoặc hết hạn
- Liên kết với lô hàng gốc (import_item_id)
- Cần duyệt phê chuẩn trước khi loại bỏ
- Theo dõi lý do loại bỏ để phân tích
```

### **MODULE 4: E-COMMERCE & ORDERS (7 Thực Thể)**

#### 15. **ORDERS** - Đơn hàng khách hàng

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: order_number

📋 Thuộc tính:
├── id (PK, BIGINT)
├── order_number (VARCHAR 50) - UNIQUE, Required - Key attribute
├── customer_id (FK) → customers(id) [SET NULL]
├── total_amount (DECIMAL 12,2) - Required - Derived
├── shipping_fee (DECIMAL 12,2) - Default: 0
├── discount (DECIMAL 12,2) - Default: 0
├── final_amount (DECIMAL 12,2) - Required - Derived: total - discount + shipping
├── status (ENUM) - pending, processing, shipped, delivered, cancelled, refunded, completed
├── previous_status (VARCHAR 50) - Trạng thái trước (cho audit trail)
├── shipping_method (VARCHAR 100)
├── shipping_address (TEXT) - Composite
├── shipping_city (VARCHAR 100)
├── shipping_province_code (VARCHAR 5)
├── shipping_name (VARCHAR 255)
├── shipping_phone (VARCHAR 20)
├── notes (TEXT)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Đơn hàng khách hàng chính
- Mối quan hệ N:1 với CUSTOMERS
- Theo dõi trạng thái từ pending → completed
- Lưu trữ thông tin giao hàng độc lập
- Tính toán tổng tiền dựa trên order_items
```

#### 16. **ORDER_ITEMS** - Chi tiết đơn hàng

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: (order_id, product_variant_id)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── order_id (FK) → orders(id) - Required
├── product_variant_id (FK) → product_variants(id) - Required
├── quantity (INT) - Required - Simple, Single-valued
├── unit_price (DECIMAL 12,2)
├── display_price (DECIMAL 12,2)
├── total_price (DECIMAL 12,2) - Derived: quantity * unit_price
├── batch_number (VARCHAR 100) - FIFO tracking
├── manufacturing_date (DATE)
├── expiry_date (DATE)
└── created_at (TIMESTAMP)

🏷️ Mô tả:
- Chi tiết các sản phẩm trong đơn hàng
- Mối quan hệ N:M giữa ORDERS và PRODUCT_VARIANTS
- Lưu trữ giá tại thời điểm đặt (để biết giá đã thay đổi hay không)
- Theo dõi batch và hạn sử dụng (FIFO)
```

#### 17. **ORDER_CANCELLATIONS** - Hủy đơn hàng

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: order_id

📋 Thuộc tính:
├── id (PK, BIGINT)
├── order_id (FK, UNIQUE) → orders(id) - Required
├── reason (VARCHAR 255)
├── cancelled_by (VARCHAR 50) - customer, staff, system
└── cancelled_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP

🏷️ Mô tả:
- Ghi nhận hủy đơn hàng
- Mối quan hệ 1:1 với ORDERS
- Lưu trữ lý do và người hủy để audit trail
```

#### 18. **PAYMENTS** - Thanh toán

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: transaction_id

📋 Thuộc tính:
├── id (PK, BIGINT)
├── order_id (FK) → orders(id) - Required
├── amount (DECIMAL 12,2) - Required
├── payment_method (ENUM) - cash, bank_transfer, credit_card, e_wallet, other
├── payment_gateway (VARCHAR 100)
├── transaction_id (VARCHAR 255) - UNIQUE - Key attribute
├── status (ENUM) - pending, completed, failed, refunded, cancelled
├── refund_amount (DECIMAL 12,2)
├── refund_reason (TEXT)
├── refunded_at (TIMESTAMP)
├── notes (TEXT)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Ghi nhận thanh toán cho đơn hàng
- Mối quan hệ N:1 với ORDERS
- Hỗ trợ refund quản lý
- Theo dõi phương thức thanh toán
```

#### 19. **ORDER_RETURNS** - Trả hàng / hoàn tiền

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── order_id (FK) → orders(id) - Required
├── reason (VARCHAR 255)
├── description (TEXT)
├── status (ENUM) - pending, approved, rejected, returned, refunded
├── refund_amount (DECIMAL 12,2)
├── requested_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP
├── approved_at (TIMESTAMP)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Yêu cầu trả hàng / hoàn tiền
- Mối quan hệ N:1 với ORDERS (một đơn có thể có nhiều yêu cầu trả)
- Theo dõi lý do trả hàng
- Xác minh và duyệt phê chuẩn trước khi hoàn tiền
```

#### 20. **ORDER_RETURN_IMAGES** - Hình ảnh trả hàng

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── order_return_id (FK) → order_returns(id) - Required
├── image_path (VARCHAR 255)
└── uploaded_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP

🏷️ Mô tả:
- Hình ảnh minh chứng cho yêu cầu trả hàng
- Mối quan hệ N:1 với ORDER_RETURNS
- Hỗ trợ nhiều hình ảnh cho mỗi yêu cầu trả
```

#### 21. **WISHLISTS** - Danh sách yêu thích

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: (customer_id, product_variant_id)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── customer_id (FK) → customers(id) - Required
├── product_variant_id (FK) → product_variants(id) - Required
└── added_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP

🏷️ Mô tả:
- Danh sách các sản phẩm khách hàng yêu thích
- Mối quan hệ M:N giữa CUSTOMERS và PRODUCT_VARIANTS
- Bảng kết hợp (junction table)
```

### **MODULE 5: PROMOTIONS & DISCOUNTS (3 Thực Thể)**

#### 22. **DISCOUNTS** - Mã giảm giá / khuyến mãi

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: code

📋 Thuộc tính:
├── id (PK, BIGINT)
├── code (VARCHAR 50) - UNIQUE, Required - Key attribute
├── description (TEXT)
├── discount_type (ENUM) - percentage, fixed_amount
├── discount_value (DECIMAL 12,2) - Required
├── max_discount_amount (DECIMAL 12,2)
├── min_order_amount (DECIMAL 12,2)
├── usage_limit (INT)
├── usage_count (INT) - Default: 0 - Derived
├── start_date (DATE)
├── end_date (DATE)
├── audience (ENUM) - all, new_customers, loyal_customers, specific
├── is_active (TINYINT) - Default: 1
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Mã khuyến mãi / giảm giá
- Hỗ trợ giảm theo % hoặc số tiền cố định
- Giới hạn số lần sử dụng
- Áp dụng cho nhóm khách hàng cụ thể
- Có ngày bắt đầu và kết thúc
```

#### 23. **DISCOUNT_USAGES** - Lịch sử sử dụng mã giảm giá

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── discount_id (FK) → discounts(id) - Required
├── order_id (FK) → orders(id) [SET NULL]
├── customer_id (FK) → customers(id) [SET NULL]
└── used_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP

🏷️ Mô tả:
- Theo dõi lịch sử sử dụng mã giảm giá
- Mối quan hệ N:1 với DISCOUNTS
- Mối quan hệ N:1 với ORDERS (khi áp dụng cho đơn hàng)
- Mối quan hệ N:1 với CUSTOMERS (khi không có đơn hàng)
```

#### 24. **DISCOUNT_PRODUCT** - Mã giảm giá cho sản phẩm cụ thể

```
🔑 Khóa chính: (discount_id, product_id) - Composite PK

📋 Thuộc tính:
├── discount_id (FK, PK) → discounts(id)
└── product_id (FK, PK) → products(id)

🏷️ Mô tả:
- Bảng kết hợp (junction table) cho mối quan hệ M:N
- Giữa DISCOUNTS và PRODUCTS
- Cho phép mỗi mã giảm giá áp dụng cho nhiều sản phẩm
- Mỗi sản phẩm có thể được áp dụng với nhiều mã giảm giá
```

### **MODULE 6: CONTENT & CUSTOMER INTERACTION (7 Thực Thể)**

#### 25. **BLOGS** - Bài viết blog

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: slug

📋 Thuộc tính:
├── id (PK, BIGINT)
├── title (VARCHAR 255) - Required - Simple, Single-valued
├── slug (VARCHAR 255) - UNIQUE, Required - Derived (URL-friendly name)
├── summary (TEXT)
├── content (LONGTEXT)
├── image (VARCHAR 255)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Bài viết blog / bài báo
- Slug dùng cho URL (ví dụ: "huong-dan-chon-ca-phe-tot")
- Có thể có nhiều block nội dung (blog_blocks)
```

#### 26. **BLOG_BLOCKS** - Khối nội dung blog

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── blog_id (FK) → blogs(id) - Required
├── type (ENUM) - text, image
├── content (TEXT)
├── image (VARCHAR 255)
├── position (INT)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Chia bài blog thành các khối nội dung
- Mỗi khối có thể chứa text hoặc image
- Theo thứ tự hiển thị (position)
- Mối quan hệ N:1 với BLOGS
```

#### 27. **REVIEWS** - Đánh giá sản phẩm

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── customer_id (FK) → customers(id) [SET NULL]
├── product_id (FK) → products(id) - Required
├── order_item_id (FK) → order_items(id) [SET NULL]
├── rating (INT 1-5) - Required - Simple, Single-valued
├── title (VARCHAR 255)
├── comment (TEXT)
├── anonymous (TINYINT) - Default: 0 - Boolean
├── status (ENUM) - pending, approved, rejected
├── helpful_count (INT) - Default: 0 - Derived
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Đánh giá và nhận xét sản phẩm từ khách hàng
- Mối quan hệ N:1 với CUSTOMERS (nếu không ẩn danh)
- Mối quan hệ N:1 với PRODUCTS
- Đánh giá liên kết với ORDER_ITEMS cụ thể
- Cần duyệt phê chuẩn trước hiển thị
```

#### 28. **REVIEW_LIKES** - Thích đánh giá

```
🔑 Khóa chính: id (BIGINT)
🔑 Khóa duy nhất: (review_id, customer_id)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── review_id (FK) → reviews(id) - Required
├── customer_id (FK) → customers(id) [SET NULL]
└── liked_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP

🏷️ Mô tả:
- Ghi nhận khách hàng thích đánh giá nào
- Mối quan hệ N:M giữa CUSTOMERS và REVIEWS
- Bảng kết hợp (junction table)
- Cập nhật helpful_count trong REVIEWS
```

#### 29. **REVIEW_REPLIES** - Phản hồi đánh giá

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── review_id (FK) → reviews(id) - Required
├── staff_id (FK) → staffs(user_id) [SET NULL]
├── comment (TEXT) - Required - Simple, Single-valued
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Phản hồi của staff đối với đánh giá
- Mối quan hệ N:1 với REVIEWS
- Mỗi đánh giá có thể có nhiều phản hồi
- Phản hồi của nhân viên cụ thể
```

#### 30. **CONTACTS** - Form liên hệ

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── name (VARCHAR 255) - Simple, Single-valued
├── email (VARCHAR 255)
├── message (TEXT)
├── status (ENUM) - pending, read
├── reply (TEXT)
├── replied_at (TIMESTAMP)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Lưu trữ tin nhắn từ form liên hệ trên website
- Không liên kết với user (vì có thể từ anonymous)
- Theo dõi trạng thái phản hồi
```

#### 31. **CUSTOMER_MESSAGES** - Tin nhắn khách hàng

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── customer_id (FK) → customers(id) - Required
├── product_id (FK) → products(id) [SET NULL]
├── staff_id (FK) → staffs(user_id) [SET NULL]
├── message (TEXT) - Required - Simple, Single-valued
├── is_read (TINYINT) - Default: 0
├── read_at (TIMESTAMP)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Chat trực tiếp giữa khách hàng và staff
- Mối quan hệ N:1 với CUSTOMERS
- Mỗi tin nhắn có thể liên quan đến sản phẩm cụ thể
- Staff nhất định xử lý tin nhắn
```

#### 32. **NOTIFICATIONS** - Thông báo hệ thống

```
🔑 Khóa chính: id (BIGINT)

📋 Thuộc tính:
├── id (PK, BIGINT)
├── user_id (FK) → users(id) - Required
├── type (VARCHAR 100)
├── message (TEXT)
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)

🏷️ Mô tả:
- Thông báo hệ thống gửi đến người dùng
- Mối quan hệ N:1 với USERS
- Thể loại: order_created, payment_confirmed, delivery_status, etc.
```

---

## III. CHI TIẾT CÁC LOẠI THUỘC TÍNH

### 1. Thuộc Tính Đơn (Simple) vs Phức Tạp (Composite)

#### Ví dụ Thuộc Tính Đơn:

```
✓ name - Chỉ là tên, không chia thành phần chỉ tiết nhỏ
✓ email - Email đơn thuần
✓ phone - Số điện thoại
```

#### Ví dụ Thuộc Tính Phức Tạp (Composite):

```
address (Composite)
├── street_number (số nhà)
├── street_name (tên đường)
├── district (huyện)
├── city (thành phố)
└── postal_code (mã bưu điện)

person_name (Composite)
├── first_name (tên)
├── last_name (họ)
└── middle_name (tên đệm)
```

### 2. Thuộc Tính Đơn Trị (Single-valued) vs Đa Trị (Multi-valued)

#### Ví dụ Thuộc Tính Đơn Trị:

```
✓ email - Mỗi khách hàng chỉ có 1 email chính
✓ date_of_birth - Chỉ có 1 ngày sinh
✓ selling_price - Mỗi variant có 1 giá bán
```

#### Ví dụ Thuộc Tính Đa Trị:

```
phone_numbers
├── 0961234567
├── 0987654321
└── 0912345678

images (của product)
├── image1.jpg
├── image2.jpg
└── image3.jpg
```

### 3. Thuộc Tính Dẫn Xuất (Derived)

```
Ví dụ Derived Attributes:
✓ age - Tính từ date_of_birth = TODAY() - date_of_birth
✓ worked_minutes - Tính từ check_in, check_out
✓ salary_amount - Tính từ worked_minutes * hourly_wage
✓ final_amount (ORDER) - Tính từ: total_amount - discount + shipping_fee
✓ final_amount (SALARY) - Tính từ: total_amount + bonus - penalty - absent_amount
✓ total_price (ORDER_ITEM) - Tính từ: quantity * unit_price
```

### 4. Khóa Chính (Primary Key Attributes)

```
Ví dụ Key Attributes:
✓ id - Auto-increment BIGINT
✓ email - UNIQUE key
✓ order_number - UNIQUE key
✓ sku (product_variant) - UNIQUE key
✓ code (discount) - UNIQUE key
✓ slug (blog, category_product) - UNIQUE key
✓ transaction_id (payment) - UNIQUE key
```

---

## IV. CHI TIẾT CÁC MỐI QUAN HỆ

### 1. Mối Quan Hệ 1:1 (One-to-One)

```
USERS (1) ←──── has ←──── (1) STAFFS
USERS (1) ←──── has ←──── (1) CUSTOMERS
ORDERS (1) ←──── has ←──── (1) PAYMENTS
ORDERS (1) ←──── has ←──── (1) ORDER_CANCELLATIONS
INVENTORIES (1) ←──── tracks ←──── (1) PRODUCT_VARIANTS
```

**Ký hiệu Chen:**

```
USERS ─────────1:1─────────── STAFFS
```

**Ký hiệu UML:**

```
* USERS ──── 1 has 1 ──── STAFFS
```

### 2. Mối Quan Hệ 1:M (One-to-Many)

```
STAFFS (1) ─────── has many ─────── (M) ATTENDANCES
STAFFS (1) ─────── has many ─────── (M) SALARIES
SUPPLIERS (1) ─────── has many ─────── (M) IMPORTS
CATEGORY_PRODUCTS (1) ─────── has many ─────── (M) PRODUCTS
PRODUCTS (1) ─────── has many ─────── (M) PRODUCT_VARIANTS
PRODUCTS (1) ─────── has many ─────── (M) PRODUCT_IMAGES
PRODUCTS (1) ─────── has many ─────── (M) REVIEWS
CUSTOMERS (1) ─────── places ─────── (M) ORDERS
ORDERS (1) ─────── contains ─────── (M) ORDER_ITEMS
CUSTOMERS (1) ─────── writes ─────── (M) REVIEWS
```

**Ký hiệu Chen:**

```
STAFFS ────────1:M────────── ATTENDANCES
```

**Ký hiệu UML:**

```
* STAFFS ──── 1 has * ──── ATTENDANCES
```

### 3. Mối Quan Hệ M:N (Many-to-Many)

```
PRODUCTS (M) ◄──── applied to ────► (N) DISCOUNTS
           (qua bảng DISCOUNT_PRODUCT)

CUSTOMERS (M) ◄──── likes ────► (N) REVIEWS
           (qua bảng REVIEW_LIKES)

CUSTOMERS (M) ◄──── adds to wishlist ────► (N) PRODUCT_VARIANTS
           (qua bảng WISHLISTS)

ORDERS (M) ◄──── contains ────► (N) PRODUCT_VARIANTS
           (qua bảng ORDER_ITEMS)
```

**Ký hiệu Chen:**

```
PRODUCTS ────────M:N────────── DISCOUNTS
```

**Ký hiệu UML:**

```
* PRODUCTS ──── * applied_to * ──── DISCOUNTS
```

### 4. Participation (Tham gia): Toàn Phần vs Bộ Phận

#### Toàn Phần (Total Participation):

```
Mỗi STAFFS phải có ít nhất một ATTENDANCE (nhân viên phải chấm công)
    ═══════════════════════════════════════
Mỗi IMPORT_ITEM phải thuộc một IMPORT (dòng nhập phải có đơn nhập)
    ═══════════════════════════════════════
Mỗi ORDER_ITEM phải thuộc một ORDER (sản phẩm trong đơn phải có đơn đó)
    ═══════════════════════════════════════
```

#### Bộ Phận (Partial Participation):

```
Không phải CUSTOMER nào cũng có ORDER (một số chỉ browse)
    ───────────────────────────
Không phải PRODUCTS nào cũng có REVIEW (một số chưa bán)
    ───────────────────────────
Không phải SUPPLIER nào cũng có IMPORT (một số cũ không còn dùng)
    ───────────────────────────
```

---

## V. CARDINALITY & CONSTRAINTS CHI TIẾT

### Bảng Cardinality của Senhong OCOP:

| Mối Quan Hệ | From Entity       | Relationship   | To Entity           | Cardinality | Participation   | Foreign Key                         |
| ----------- | ----------------- | -------------- | ------------------- | ----------- | --------------- | ----------------------------------- |
| 1           | USERS             | has            | STAFFS              | 1:1         | Partial-Total   | staffs.user_id                      |
| 2           | USERS             | has            | CUSTOMERS           | 1:1         | Partial-Total   | customers.user_id                   |
| 3           | STAFFS            | logs           | ATTENDANCES         | 1:M         | Total-Total     | attendances.staff_id                |
| 4           | STAFFS            | earns          | SALARIES            | 1:M         | Total-Total     | salaries.staff_id                   |
| 5           | STAFFS            | approves       | INVENTORY_WRITEOFFS | 1:M         | Partial-Partial | inventory_writeoffs.approved_by     |
| 6           | SUPPLIERS         | supplies       | IMPORTS             | 1:M         | Total-Partial   | imports.supplier_id                 |
| 7           | STAFFS            | processes      | IMPORTS             | 1:M         | Total-Partial   | imports.staff_id                    |
| 8           | IMPORTS           | includes       | IMPORT_ITEMS        | 1:M         | Total-Total     | import_items.import_id              |
| 9           | PRODUCT_VARIANTS  | has_stock      | INVENTORIES         | 1:1         | Total-Total     | inventories.product_variant_id      |
| 10          | CATEGORY_PRODUCTS | contains       | PRODUCTS            | 1:M         | Total-Partial   | products.category_id                |
| 11          | PRODUCTS          | has            | PRODUCT_VARIANTS    | 1:M         | Total-Total     | product_variants.product_id         |
| 12          | PRODUCTS          | has            | PRODUCT_IMAGES      | 1:M         | Total-Partial   | product_images.product_id           |
| 13          | PRODUCT_VARIANTS  | in             | IMPORT_ITEMS        | 1:M         | Partial-Partial | import_items.product_variant_id     |
| 14          | PRODUCTS          | categorized_in | DISCOUNTS           | M:N         | Partial-Partial | discount_product (junction)         |
| 15          | CUSTOMERS         | places         | ORDERS              | 1:M         | Partial-Total   | orders.customer_id                  |
| 16          | ORDERS            | contains       | ORDER_ITEMS         | 1:M         | Total-Total     | order_items.order_id                |
| 17          | PRODUCT_VARIANTS  | ordered_in     | ORDER_ITEMS         | 1:M         | Partial-Partial | order_items.product_variant_id      |
| 18          | ORDERS            | cancelled_by   | ORDER_CANCELLATIONS | 1:1         | Partial-Partial | order_cancellations.order_id        |
| 19          | ORDERS            | pays_via       | PAYMENTS            | 1:M         | Total-Total     | payments.order_id                   |
| 20          | ORDERS            | return_request | ORDER_RETURNS       | 1:M         | Partial-Partial | order_returns.order_id              |
| 21          | ORDER_RETURNS     | has_images     | ORDER_RETURN_IMAGES | 1:M         | Partial-Partial | order_return_images.order_return_id |
| 22          | CUSTOMERS         | adds_to        | WISHLISTS           | 1:M         | Partial-Partial | wishlists.customer_id               |
| 23          | PRODUCT_VARIANTS  | in_wishlist    | WISHLISTS           | 1:M         | Partial-Partial | wishlists.product_variant_id        |
| 24          | DISCOUNTS         | applied_in     | DISCOUNT_USAGES     | 1:M         | Partial-Partial | discount_usages.discount_id         |
| 25          | CUSTOMERS         | reviews        | PRODUCTS            | M:N         | Partial-Partial | reviews (via review direct FK)      |
| 26          | PRODUCTS          | reviewed_by    | REVIEWS             | 1:M         | Partial-Partial | reviews.product_id                  |
| 27          | REVIEWS           | liked_by       | REVIEW_LIKES        | 1:M         | Partial-Partial | review_likes.review_id              |
| 28          | CUSTOMERS         | likes_review   | REVIEW_LIKES        | 1:M         | Partial-Partial | review_likes.customer_id            |
| 29          | REVIEWS           | replied_by     | REVIEW_REPLIES      | 1:M         | Partial-Partial | review_replies.review_id            |
| 30          | BLOGS             | contains       | BLOG_BLOCKS         | 1:M         | Total-Total     | blog_blocks.blog_id                 |
| 31          | USERS             | receives       | NOTIFICATIONS       | 1:M         | Partial-Partial | notifications.user_id               |

---

## VI. HƯỚNG DẪN TỪNG BƯỚC VẼ CDM SENHONG OCOP

### Bước 1: Vẽ Module 1 - User Management

**Các thực thể:**

```
┌──────────────┐
│    USERS     │         ┌──────────────┐     ┌──────────────┐
│ (Users)      │         │   STAFFS     │     │  CUSTOMERS   │
├──────────────┤         │ (Employees)  │     │ (Customers)  │
│ • id (PK)    │ 1─────1:1 └──────────────┘    │              │
│ • name       │           └────────────────1:1─┤ • id (PK)    │
│ • email      │                              │ • user_id(FK)│
│ • password   │                              └──────────────┘
│ • phone      │
│ • role       │
│ • is_active  │
└──────────────┘
```

**Các constraint:**

- USERS.email UNIQUE
- USERS.phone UNIQUE
- STAFFS.user_id UNIQUE (1:1 relationship)
- CUSTOMERS.user_id UNIQUE (1:1 relationship)

### Bước 2: Vẽ Module 2 - Attendance & HR

**Các thực thể và mối quan hệ:**

```
┌──────────────┐
│   STAFFS     │
├──────────────┤
│ • user_id    │
│ • name       │ (from users)
│ • position   │
│ • start_date │
└────────┬─────┘
         │ 1:M
         │ logs
         ▼
   ┌──────────────────┐
   │   ATTENDANCES    │
   ├──────────────────┤
   │ • id (PK)        │
   │ • staff_id (FK)  │
   │ • work_date      │
   │ • shift          │
   │ • check_in/out   │
   │ • is_late        │ ◄── Scenario fields
   │ • is_early_leave │
   │ • scenario_type  │
   └──────────────────┘

┌──────────────┐
│   STAFFS     │
├──────────────┤
│ • user_id    │
└────────┬─────┘
         │ 1:M
         │ earns
         ▼
   ┌──────────────────┐
   │    SALARIES      │
   ├──────────────────┤
   │ • id (PK)        │
   │ • staff_id (FK)  │
   │ • month, year    │
   │ • total_hours    │
   │ • total_amount   │
   │ • bonus/penalty  │
   │ • final_amount   │
   │ • status         │
   └──────────────────┘
```

**Unique Constraint:**

- ATTENDANCES: (staff_id, work_date, shift) UNIQUE - Đảm bảo không nhập chấm công 2 lần cung 1 nhân viên
- SALARIES: (staff_id, month, year) UNIQUE - Mỗi nhân viên 1 lương/tháng

### Bước 3: Vẽ Module 3 - Inventory & Supply Chain

**Các thực thể:**

```
┌─────────────────────────────────────────────────────────────┐
│                    SUPPLIERS                                │
│ (Nhà cung cấp)                                              │
├─────────────────────────────────────────────────────────────┤
│ • id (PK)                                                   │
│ • name, email, phone                                        │
│ • address (Composite: street, city, province_code)         │
│ • tax_code, contact_person                                 │
└──────────────┬──────────────────────────────────────────────┘
               │ 1:M
               │ supplies
               ▼
┌──────────────────────────────────────────────────────────────┐
│                     IMPORTS                                  │
│ (Đơn nhập hàng từ supplier)                                 │
├──────────────────────────────────────────────────────────────┤
│ • id (PK)                                                   │
│ • supplier_id (FK) - RESTRICT                              │
│ • staff_id (FK) - Người nhập                               │
│ • import_date - Required                                    │
│ • status ∈ {draft, imported, cancelled}                    │
└──────────────┬──────────────────────────────────────────────┘
               │ 1:M
               │ includes
               ▼
┌──────────────────────────────────────────────────────────────┐
│                   IMPORT_ITEMS                               │
│ (Chi tiết nhập: từng sản phẩm trong đơn nhập)              │
├──────────────────────────────────────────────────────────────┤
│ • id (PK)                                                   │
│ • import_id (FK) - CASCADE                                 │
│ • product_variant_id (FK) - RESTRICT                       │
│ • quantity, cost_price                                      │
│ • manufacturing_date, expiry_date - CRITICAL               │
│ • batch_number, remaining_quantity (FIFO)                  │
└────────────────┬─────────┬────────────────────────────────┘
                 │         │ N:1
                 │         │
         ┌───────┘         ▼
         │        ┌──────────────────────┐
         │        │ PRODUCT_VARIANTS     │
         │        ├──────────────────────┤
         │        │ • id (PK)            │
         │        │ • sku (UNIQUE KEY)   │
         │        │ • product_id (FK)    │
         │        │ • size, color, unit  │
         │        │ • cost_price         │
         │        │ • selling_price      │
         │        └────────┬──────┬──────┘
         │                 │      │
         │            1:1  │      │ 1:M
         │                 ▼      ▼
         │        ┌──────────────────────┐
         └───────►│  INVENTORIES         │
                  ├──────────────────────┤
                  │ • id (PK)            │
                  │ • product_variant_id │
                  │ • total_quantity     │
                  │ • available_quantity │
                  └──────────────────────┘

┌──────────────────────────────────────┐
│    INVENTORY_WRITEOFFS               │
│ (Loại bỏ: hỏng, hết hạn, mất)       │
├──────────────────────────────────────┤
│ • id (PK)                            │
│ • product_variant_id (FK)            │
│ • quantity                           │
│ • reason ∈ {expired, damaged, lost}  │
│ • approved_by (FK → staffs.user_id)  │
│ • status ∈ {pending, approved, ...}  │
└──────────────────────────────────────┘
```

### Bước 4: Vẽ Module 4 - E-Commerce & Orders

**Các thực thể và mối quan hệ:**

```
┌─────────────────────────────────────┐
│         CUSTOMERS                   │
├─────────────────────────────────────┤
│ • id (PK)                           │
│ • user_id (FK) 1:1 UNIQUE (users)   │
│ • phone, address                    │
│ • province_code, district_code      │
└────────────┬────────────────────────┘
             │ 1:M
             │ places
             ▼
┌───────────────────────────────────┐
│        ORDERS                     │
│ (Đơn hàng khách hàng)            │
├───────────────────────────────────┤
│ • id (PK)                         │
│ • order_number (UNIQUE KEY)       │
│ • customer_id (FK)                │
│ • total_amount (calculated)       │
│ • shipping_fee, discount          │
│ • final_amount (derived)          │
│ • status ∈ {pending, shipped...}  │
│ • shipping_address (composite)    │
└────────────┬────────────────────────┘
             │ 1:M
             │ contains
             ▼
  ┌────────────────────────────────┐
  │     ORDER_ITEMS                │
  │ (Chi tiết sản phẩm trong đơn)  │
  ├────────────────────────────────┤
  │ • id (PK)                      │
  │ • order_id (FK) CASCADE        │
  │ • product_variant_id (FK)      │
  │ • quantity (required)          │
  │ • unit_price, total_price      │
  │ • batch_number, expiry_date    │
  └────────────────────────────────┘

┌───────────────────────────────────┐
│  ORDER_CANCELLATIONS              │
│  (Hủy đơn hàng)                  │
├───────────────────────────────────┤
│ • id (PK)                         │
│ • order_id (FK) 1:1 UNIQUE        │
│ • reason, cancelled_by            │
│ • cancelled_at                    │
└───────────────────────────────────┘

┌───────────────────────────────────┐
│      PAYMENTS                     │
│  (Thanh toán)                    │
├───────────────────────────────────┤
│ • id (PK)                         │
│ • order_id (FK) 1:M               │
│ • amount, payment_method          │
│ • transaction_id (UNIQUE)         │
│ • status ∈ {pending, completed}   │
└───────────────────────────────────┘

┌───────────────────────────────────┐
│     ORDER_RETURNS                 │
│  (Yêu cầu trả hàng)              │
├───────────────────────────────────┤
│ • id (PK)                         │
│ • order_id (FK) 1:M               │
│ • reason (returned reason)        │
│ • status ∈ {pending, returned}    │
│ • refund_amount                   │
└────────────┬──────────────────────┘
             │ 1:M
             │ has_images
             ▼
┌───────────────────────────────────┐
│  ORDER_RETURN_IMAGES              │
│  (Hình ảnh trả hàng)             │
├───────────────────────────────────┤
│ • id (PK)                         │
│ • order_return_id (FK) CASCADE    │
│ • image_path                      │
└───────────────────────────────────┘

┌───────────────────────────────────┐
│       WISHLISTS                   │
│  (Danh sách yêu thích)           │
├───────────────────────────────────┤
│ • id (PK)                         │
│ • customer_id (FK)  ────────┐     │
│ • product_variant_id (FK)   │ M:N │
│ (UNIQUE: customer_id,       │     │
│    product_variant_id)      │     │
│ • added_at                  │     │
└───────────────────────────────────┘
```

### Bước 5: Vẽ Module 5 - Promotions & Discounts

**Các thực thể:**

```
┌────────────────────────────────────┐
│       DISCOUNTS                    │
│ (Mã giảm giá)                     │
├────────────────────────────────────┤
│ • id (PK)                          │
│ • code (UNIQUE KEY)                │
│ • discount_type ∈ {%, fixed}       │
│ • discount_value                   │
│ • min_order_amount                 │
│ • usage_limit, usage_count         │
│ • start_date, end_date             │
│ • is_active                        │
└────┬─────────────┬────────────────┘
     │ 1:M         │ M:N
     │             │
     ▼             ▼
┌──────────────────────────────┐   ┌──────────────────────┐
│   DISCOUNT_USAGES            │   │  DISCOUNT_PRODUCT    │
│  (Lịch sử sử dụng)          │   │  (M:N junction)      │
├──────────────────────────────┤   ├──────────────────────┤
│ • id (PK)                    │   │ • discount_id (FK,PK)│
│ • discount_id (FK) CASCADE   │   │ • product_id (FK,PK) │
│ • order_id (FK) SET NULL     │   └──────────────────────┘
│ • customer_id (FK) SET NULL  │
│ • used_at                    │
└──────────────────────────────┘
```

### Bước 6: Vẽ Module 6 - Content & Interaction

**Các thực thể:**

```
┌──────────────────────────┐
│       BLOGS              │
├──────────────────────────┤
│ • id (PK)                │
│ • title                  │
│ • slug (UNIQUE)          │
│ • content                │
│ • image                  │
└────────┬─────────────────┘
         │ 1:M
         │ contains
         ▼
┌──────────────────────────┐
│    BLOG_BLOCKS           │
│   (Khối nội dung)       │
├──────────────────────────┤
│ • id (PK)                │
│ • blog_id (FK) CASCADE   │
│ • type ∈ {text, image}   │
│ • content                │
│ • position               │
└──────────────────────────┘

┌──────────────────────────┐
│      PRODUCTS            │
├──────────────────────────┤
│ • id (PK)                │
│ • name                   │
└────────┬─────────────────┘
         │ 1:M
         │ reviewed_by
         ▼
┌──────────────────────────────┐
│      REVIEWS                 │
│  (Đánh giá sản phẩm)        │
├──────────────────────────────┤
│ • id (PK)                    │
│ • product_id (FK) CASCADE    │
│ • customer_id (FK) SET NULL  │
│ • rating (1-5)               │
│ • comment                    │
│ • status ∈ {pending, ap...}  │
└────────┬────────────┬────────┘
         │ 1:M        │ 1:M
         │            │
    ┌────▼────┐   ┌───▼──────────────┐
    │ REVIEW   │   │  REVIEW_LIKES    │
    │ REPLIES  │   │  (M:N junction)  │
    ├──────────┤   ├──────────────────┤
    │ • id (PK)│   │ • review_id (FK) │
    │ • review │   │ • customer_id(FK)│
    │_id (FK)  │   │ (UNIQUE pair)    │
    │ • staff_ │   └──────────────────┘
    │id (FK)   │
    │ • comment│
    └──────────┘

┌──────────────────────────┐
│     CONTACTS             │
│  (Form liên hệ)         │
├──────────────────────────┤
│ • id (PK)                │
│ • name, email            │
│ • message                │
│ • status ∈ {pending...}  │
│ • reply                  │
└──────────────────────────┘

┌──────────────────────────────┐
│   CUSTOMER_MESSAGES          │
│  (Chat khách hàng-staff)    │
├──────────────────────────────┤
│ • id (PK)                    │
│ • customer_id (FK) CASCADE   │
│ • product_id (FK) SET NULL   │
│ • staff_id (FK) SET NULL     │
│ • message                    │
│ • is_read, read_at           │
└──────────────────────────────┘

┌──────────────────────────────┐
│    NOTIFICATIONS             │
│  (Thông báo hệ thống)       │
├──────────────────────────────┤
│ • id (PK)                    │
│ • user_id (FK) CASCADE       │
│ • type (VARCHAR)             │
│ • message                    │
└──────────────────────────────┘
```

---

## VII. CHI TIẾT VỀ BẢNG KẾT HỢP (JUNCTION TABLES)

### 1. DISCOUNT_PRODUCT (M:N giữa DISCOUNTS ↔ PRODUCTS)

```
Tại sao cần?
- Một mã giảm giá có thể áp dụng cho nhiều sản phẩm
- Một sản phẩm có thể áp dụng bởi nhiều mã giảm giá

Ví dụ:
- Mã SUMMER20 (giảm 20%) áp dụng cho: Cà phê, Trà, Cacao
- Sản phẩm "Cà phê Robusta" áp dụng: SUMMER20, NEWUSER, LOYAL10

Cấu trúc:
┌─────────────────────────┐              ┌─────────────────────┐
│      DISCOUNTS          │              │      PRODUCTS       │
├─────────────────────────┤              ├─────────────────────┤
│ • id (PK)               │              │ • id (PK)           │
│ • code (UNIQUE)         │              │ • name              │
│ • discount_value        │──────────────│ • type              │
│ • start_date/end_date   │ M:N via      │ • description       │
└─────────────────────────┘ junction     └─────────────────────┘
                                   │
                     ┌─────────────▼──────────┐
                     │  DISCOUNT_PRODUCT      │
                     │  (Junction Table)      │
                     ├──────────────────────┤
                     │ • discount_id (FK,PK)│
                     │ • product_id (FK,PK) │
                     └──────────────────────┘
```

### 2. REVIEW_LIKES (M:N giữa REVIEWS ↔ CUSTOMERS)

```
Tại sao cần?
- Khách hàng A thích đánh giá X, Y, Z
- Đánh giá X được thích bởi khách hàng A, B, C

Cấu trúc:
┌──────────────────────┐              ┌──────────────────────┐
│     REVIEWS          │              │    CUSTOMERS         │
├──────────────────────┤              ├──────────────────────┤
│ • id (PK)            │              │ • id (PK)            │
│ • product_id (FK)    │              │ • user_id (FK) 1:1   │
│ • rating             │──────────────│ • phone, address     │
│ • comment            │ M:N via      │ • is_default_address │
│ • helpful_count      │ junction     └──────────────────────┘
└──────────────────────┘
                           │
                ┌──────────▼─────────┐
                │   REVIEW_LIKES     │
                │  (Junction Table)  │
                ├──────────────────┤
                │ • review_id (FK) │
                │ • customer_id(FK)│
                │ (UNIQUE pair)    │
                └──────────────────┘
```

### 3. WISHLISTS (M:N giữa CUSTOMERS ↔ PRODUCT_VARIANTS)

```
Tại sao cần?
- Khách hàng có thể add nhiều sản phẩm vào wishlist
- Sản phẩm có thể được add bởi nhiều khách hàng

Cấu trúc:
┌──────────────────────┐           ┌──────────────────────┐
│    CUSTOMERS         │           │  PRODUCT_VARIANTS    │
├──────────────────────┤           ├──────────────────────┤
│ • id (PK)            │           │ • id (PK)            │
│ • user_id (FK) 1:1   │           │ • product_id (FK)    │
│ • phone, address     │──────────►│ • sku (UNIQUE)       │
└──────────────────────┘ M:N via   │ • size, color        │
                        junction   │ • selling_price      │
                           │       └──────────────────────┘
                ┌──────────▼──────────────┐
                │   WISHLISTS            │
                │  (Junction Table)      │
                ├──────────────────────┤
                │ • customer_id (FK)   │
                │ • product_variant_id │
                │   (FK)               │
                │ • added_at           │
                │ (UNIQUE pair)        │
                └──────────────────────┘
```

### 4. ORDER_ITEMS (M:N giữa ORDERS ↔ PRODUCT_VARIANTS)

```
Tại sao cần?
- Một đơn hàng có nhiều sản phẩm
- Một sản phẩm được bán trong nhiều đơn hàng

Đặc biệt:
- Lưu trữ giá tại thời điểm đặt hàng (unit_price)
- Lưu trữ batch_number, expiry_date cho FIFO tracking

Cấu trúc:
┌──────────────────────┐           ┌──────────────────────┐
│      ORDERS          │           │  PRODUCT_VARIANTS    │
├──────────────────────┤           ├──────────────────────┤
│ • id (PK)            │           │ • id (PK)            │
│ • order_number (UK)  │           │ • product_id (FK)    │
│ • customer_id (FK)   │           │ • sku (UNIQUE)       │
│ • total_amount       │──────────►│ • selling_price      │
│ • status             │ M:N via   │ • unit, weight       │
└──────────────────────┘ junction  └──────────────────────┘
                           │
                ┌──────────▼──────────────┐
                │   ORDER_ITEMS          │
                │  (Junction Table)      │
                ├──────────────────────┤
                │ • order_id (FK)      │
                │ • product_variant_id │
                │   (FK)               │
                │ • quantity           │
                │ • unit_price         │
                │ • total_price        │
                │ • batch_number       │
                │ • expiry_date (FIFO) │
                │ (UNIQUE: order_id,   │
                │   product_variant_id)│
                └──────────────────────┘
```

---

## VIII. CONSTRAINTS VÀ RÀNG BUỘC CHI TIẾT

### 1. Primary Key Constraints

```sql
-- Simple PK
ALTER TABLE users ADD PRIMARY KEY (id);
ALTER TABLE staffs ADD PRIMARY KEY (user_id);

-- Composite PK (Junction tables)
ALTER TABLE discount_product ADD PRIMARY KEY (discount_id, product_id);
ALTER TABLE review_likes ADD PRIMARY KEY (review_id, customer_id);
ALTER TABLE wishlists ADD PRIMARY KEY (customer_id, product_variant_id);
```

### 2. Unique Constraints

```sql
-- Single column unique
ALTER TABLE users ADD UNIQUE KEY unique_email (email);
ALTER TABLE users ADD UNIQUE KEY unique_phone (phone);
ALTER TABLE category_products ADD UNIQUE KEY unique_slug (slug);
ALTER TABLE discounts ADD UNIQUE KEY unique_code (code);

-- Composite unique
ALTER TABLE attendances ADD UNIQUE KEY unique_attendance
    (staff_id, work_date, shift);
ALTER TABLE salaries ADD UNIQUE KEY unique_salary
    (staff_id, month, year);
```

### 3. Foreign Key Constraints với Actions

```sql
-- ON DELETE CASCADE (khi xóa cha, xóa con)
ALTER TABLE staffs ADD CONSTRAINT fk_staffs_users
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE attendances ADD CONSTRAINT fk_attendances_staffs
    FOREIGN KEY (staff_id) REFERENCES staffs(user_id) ON DELETE CASCADE;

-- ON DELETE SET NULL (khi xóa cha, set NULL)
ALTER TABLE customers ADD CONSTRAINT fk_customers_users
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE order_returns ADD CONSTRAINT fk_order_returns
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL;

-- ON DELETE RESTRICT (không cho phép xóa nếu có con)
ALTER TABLE products ADD CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES category_products(id) ON DELETE RESTRICT;
ALTER TABLE imports ADD CONSTRAINT fk_imports_suppliers
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT;
```

### 4. Not Null Constraints

```
✓ users.name NOT NULL
✓ products.name NOT NULL
✓ attendances.staff_id NOT NULL
✓ orders.customer_id - Có thể NULL (anonymous order)
✓ import_items.expiry_date - Có thể NULL (hàng ko có hạn)
```

### 5. Check Constraints / Enum Constraints

```
✓ users.role IN ('admin', 'staff', 'customer', 'supplier')
✓ staffs.position IN ('manager', 'staff', 'leader', 'director')
✓ staffs.employment_status IN ('probation', 'official', 'resigned')
✓ attendances.shift IN ('morning', 'afternoon')
✓ attendances.scenario_type IN (1, 2, 3, 4, 5)
✓ salaries.status IN ('draft', 'approved', 'paid')
✓ orders.status IN ('pending', 'processing', 'shipped', ...)
✓ reviews.rating BETWEEN 1 AND 5
```

---

## IX. CÁC BẢNG LIÊN QUAN VÀ DEPENDENCY

### Flow từ Nhập Hàng đến Bán Hàng

```
SUPPLIERS (nhà cung cấp)
    ↓
IMPORTS (đơn nhập) ← STAFFS (nhân viên nhập)
    ↓
IMPORT_ITEMS (chi tiết nhập, lô hàng)
    ↓
PRODUCT_VARIANTS (sản phẩm biến thể)
    ↓
INVENTORIES (tồn kho)
    ↓
ORDER_ITEMS (được đặt hàng từ)
    ↓
ORDERS (đơn hàng khách)
    ↓
PAYMENTS (thanh toán)
```

### Flow Từ Attendance đến Salary

```
STAFFS (nhân viên)
    ↓
ATTENDANCES (chấm công hàng ngày, 5 scenario)
    Scenario 1: Normal
    Scenario 2: Late arrival
    Scenario 3: Early leave
    Scenario 4: Late + Early leave
    Scenario 5: Auto check-out forced
    ↓
SALARIES (tính lương)
    Cộng: total_hours * hourly_wage + bonus
    Trừ: penalty + absent_amount
    = final_amount
```

---

## X. LỖI THƯỜNG GẶP KHI VẼ CDM SENHONG

### 1. Lỗi về Thực Thể

```
✗ Quên thực thể PRODUCT_IMAGES (nếu hỗ trợ nhiều ảnh)
✗ Quên phân biệt STAFFS vs CUSTOMERS (hai loại user khác nhau)
✗ Quên INVENTORIES - nên có bảng riêng để track tồn kho thời gian thực
✗ Nhập nhằng IMPORT_ITEMS vs ORDER_ITEMS (different purposes)
✗ Không tách DISCOUNT_PRODUCT riêng - nên là junction table
```

### 2. Lỗi về Cardinality

```
✗ ATTENDANCES: Sai cardinality (phải là 1:M, không phải 1:1)
✗ ORDERS: customer_id có thể NULL (anonymous order)
✗ SALARIES: Nên là 1:M (một nhân viên nhiều lương/tháng)
✗ PAYMENTS: 1 order có thể có nhiều payments (refund + new payment)
✗ ORDER_RETURNS: 1 order có thể có nhiều return requests
```

### 3. Lỗi về Constraints

```
✗ Quên UNIQUE trên: email, phone, sku, code, slug, transaction_id
✗ Quên Composite KEY: (staff_id, work_date, shift) cho ATTENDANCES
✗ Quên Composite KEY: (staff_id, month, year) cho SALARIES
✗ FK actions không hợp lý:
  - RESTRICT cho SUPPLIER khi có IMPORTS (OK)
  - CASCADE cho ORDERS khi xóa (xóa kèm ORDER_ITEMS) (OK)
```

### 4. Lỗi Đặt Tên

```
✗ is_completed vs completed - Nên thống nhất (dùng is_ prefix cho boolean)
✗ working_minutes vs worked_minutes - Nên thống nhất
✗ remaining_quantity vs quantity_remaining - Nên thống nhất
✓ Tốt: is_late, is_active, is_primary, is_read, is_featured
✓ Tốt: user_id, staff_id, customer_id (thống nhất)
```

---

## XI. CHECKLIST VẼ CDM SENHONG OCOP

- [ ] **Module 1: USER MANAGEMENT**
    - [ ] USERS entity với 11 attributes
    - [ ] STAFFS entity 1:1 với USERS
    - [ ] CUSTOMERS entity 1:1 với USERS
    - [ ] Unique constraints: email, phone (users), user_id (staffs, customers)

- [ ] **Module 2: ATTENDANCE & HR**
    - [ ] ATTENDANCES với 5 scenario fields
    - [ ] Composite key: (staff_id, work_date, shift)
    - [ ] SALARIES 1:M với STAFFS
    - [ ] Composite key: (staff_id, month, year)

- [ ] **Module 3: INVENTORY & SUPPLY CHAIN**
    - [ ] SUPPLIERS entity
    - [ ] IMPORTS 1:M từ SUPPLIERS
    - [ ] IMPORT_ITEMS 1:M từ IMPORTS
    - [ ] PRODUCT_VARIANTS với SKU (UNIQUE)
    - [ ] INVENTORIES 1:1 với PRODUCT_VARIANTS
    - [ ] INVENTORY_WRITEOFFS với reason enum
    - [ ] CATEGORY_PRODUCTS (sắp xếp)

- [ ] **Module 4: E-COMMERCE & ORDERS**
    - [ ] ORDERS từ CUSTOMERS (nullable customer_id)
    - [ ] ORDER_ITEMS M:N với PRODUCT_VARIANTS
    - [ ] ORDER_CANCELLATIONS 1:1 với ORDERS
    - [ ] PAYMENTS (có thể N:1 với ORDERS)
    - [ ] ORDER_RETURNS 1:M với ORDERS
    - [ ] ORDER_RETURN_IMAGES cho hình ảnh trả
    - [ ] WISHLISTS M:N (junction table)

- [ ] **Module 5: PROMOTIONS**
    - [ ] DISCOUNTS với code (UNIQUE)
    - [ ] DISCOUNT_USAGES lịch sử sử dụng
    - [ ] DISCOUNT_PRODUCT (M:N junction)

- [ ] **Module 6: CONTENT**
    - [ ] BLOGS với slug (UNIQUE)
    - [ ] BLOG_BLOCKS 1:M từ BLOGS
    - [ ] REVIEWS 1:M từ PRODUCTS
    - [ ] REVIEW_LIKES (M:N junction)
    - [ ] REVIEW_REPLIES 1:M từ REVIEWS
    - [ ] CONTACTS (form)
    - [ ] CUSTOMER_MESSAGES (chat)
    - [ ] NOTIFICATIONS

- [ ] **Foreign Keys & Actions**
    - [ ] CASCADE cho: staffs→users, attendances→staffs, salaries→staffs
    - [ ] RESTRICT cho: suppliers→imports, category→products
    - [ ] SET NULL cho: customers→users, reviews→customer_id

- [ ] **Derived Attributes**
    - [ ] worked_minutes (từ check_in, check_out)
    - [ ] salary_amount (từ worked_minutes)
    - [ ] final_amount (order = total - discount + shipping)
    - [ ] final_amount (salary = total + bonus - penalty - absent)
    - [ ] helpful_count (count của likes)
    - [ ] usage_count (count của usages)

- [ ] **Composite Attributes**
    - [ ] address (street, city, province_code, ...)
    - [ ] shipping_address (trong ORDERS)

---

## XII. TÀI LIỆU THAM KHẢO

- **Database Schema Description:** CDM_SENHONG_OCOP_COMPLETE.md
- **Relationship Documentation:** DATABASE_SCHEMA_ANALYSIS.md
- **Tools:** Lucidchart, Draw.io, MySQL Workbench, PowerDesigner
- **Standards:** Chen's ER Model, UML Class Diagram

---

**Lưu ý cuối cùng:**
CDM của Senhong OCOP là một hệ thống phức tạp với 32 bảng chính. Khi vẽ CDM:

1. Bắt đầu bằng các entity chính (USERS, PRODUCTS, ORDERS)
2. Từ từ thêm các mối quan hệ
3. Xác định cardinality chính xác
4. Kiểm tra các unique constraints
5. Đảm bảo các derived attributes được ghi chú
6. Xác nhận với team về các business rules (ví dụ: 5 scenarios attendance)

Một CDM tốt sẽ giúp thiết kế LDM (Logical Data Model) dễ dàng hơn và giảm lỗi trong implementation.
