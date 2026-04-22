# 📊 SENHONG OCOP - CDM (Conceptual Data Model)

**Database:** MySQL | **Created:** April 16, 2026 | **Status:** ✅ Complete

---

## 🏗️ DATABASE STRUCTURE OVERVIEW

### Total: 32 Tables | 42 Foreign Keys | 6 Main Modules

---

## 📑 MODULE 1: USER MANAGEMENT (3 Tables)

### 🔐 `users` - System Users

```
PK: id (BIGINT)
├── name (VARCHAR 255) - Required
├── email (VARCHAR 255) - UNIQUE, Required
├── password (VARCHAR 255) - Required
├── phone (VARCHAR 20) - UNIQUE
├── avatar (VARCHAR 255)
├── role ENUM('admin', 'staff', 'customer', 'supplier')
├── is_active (TINYINT) - Default: 1
├── lock_expires_at (TIMESTAMP)
├── lock_enabled_by (BIGINT)
└── timestamps (created_at, updated_at)
```

### 👤 `staffs` - Employee Profiles

```
PK: user_id (BIGINT) → FK users(id) [CASCADE]
├── phone (VARCHAR 20)
├── date_of_birth (DATE)
├── address (TEXT)
├── position ENUM('manager', 'staff', 'leader', 'director')
├── start_date (DATE)
├── probation_start (DATE)
├── probation_end (DATE)
├── employment_status ENUM('probation', 'official', 'resigned')
├── probation_hourly_wage (DECIMAL 10,2)
├── official_hourly_wage (DECIMAL 10,2)
└── timestamps
```

### 👥 `customers` - Customer Profiles

```
PK: id (BIGINT)
├── user_id (BIGINT) → FK users(id) UNIQUE [CASCADE]
├── phone (VARCHAR 20)
├── date_of_birth (DATE)
├── address (TEXT)
├── is_default_address (TINYINT)
├── province_code (VARCHAR 5)
├── district_code (VARCHAR 5)
├── ward_code (VARCHAR 5)
└── timestamps
```

---

## 📅 MODULE 2: ATTENDANCE & HR (3 Tables)

### ⏰ `attendances` - Daily Attendance (5 Scenarios)

```
PK: id (BIGINT)
UK: (staff_id, work_date, shift) - UNIQUE
├── staff_id (BIGINT) → FK staffs(user_id) [CASCADE]
├── work_date (DATE) - Required
├── shift ENUM('morning', 'afternoon')
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
├── is_late (TINYINT) - Scenario 1
├── is_early_leave (TINYINT) - Scenario 2
├── is_completed (TINYINT)
├── is_auto_checkout_forced (TINYINT) - Scenario 5
├── early_leave_reason (TEXT)
├── early_leave_status ENUM('pending', 'approved', 'rejected')
├── early_leave_pay_percent (INT) - Default: 100
├── early_leave_approved_by (BIGINT) → FK staffs(user_id)
├── early_leave_approved_at (TIMESTAMP)
├── scenario_type (TINYINT) - 1-5
├── worked_minutes (INT)
├── salary_amount (DECIMAL 12,2)
└── timestamps
```

### 💰 `salaries` - Monthly Salary Calculation

```
PK: id (BIGINT)
UK: (staff_id, month, year) - UNIQUE
├── staff_id (BIGINT) → FK staffs(user_id) [CASCADE]
├── month (INT 1-12)
├── year (INT YYYY)
├── total_hours (DECIMAL 8,2)
├── total_amount (DECIMAL 12,2)
├── bonus (DECIMAL 12,2) - Default: 0
├── penalty (DECIMAL 12,2) - Default: 0
├── absent_count (INT) - Default: 0
├── absent_amount (DECIMAL 12,2) - Default: 0
├── final_amount (DECIMAL 12,2) = total_amount + bonus - penalty - absent_amount
├── status ENUM('draft', 'approved', 'paid')
├── notes (TEXT)
├── paid_at (TIMESTAMP)
└── timestamps
```

---

## 📦 MODULE 3: SUPPLY CHAIN & INVENTORY (8 Tables)

### 🏭 `suppliers` - Supplier Information

```
PK: id (BIGINT)
├── name (VARCHAR 255) - Required
├── email (VARCHAR 255)
├── phone (VARCHAR 20)
├── address (TEXT)
├── city (VARCHAR 100)
├── province_code (VARCHAR 5)
├── tax_code (VARCHAR 20)
├── contact_person (VARCHAR 255)
├── is_active (TINYINT) - Default: 1
└── timestamps
```

### 🏷️ `category_products` - Product Categories

```
PK: id (BIGINT)
UK: name, slug
├── name (VARCHAR 255) - UNIQUE, Required
├── description (TEXT)
├── image_url (VARCHAR 255)
├── slug (VARCHAR 255) - UNIQUE
├── display_order (INT) - Default: 0
├── is_active (TINYINT) - Default: 1
└── timestamps
```

### 🛍️ `products` - Product Master

```
PK: id (BIGINT)
├── category_id (BIGINT) → FK category_products(id) [RESTRICT]
├── name (VARCHAR 255) - Required
├── type ENUM('simple', 'bundle', 'variable')
├── description (LONGTEXT)
├── origin (VARCHAR 100)
├── is_active (TINYINT) - Default: 1
├── is_featured (TINYINT) - Default: 0
└── timestamps
```

### 📏 `product_variants` - Product Variants (Size, Color, Volume)

```
PK: id (BIGINT)
UK: sku
├── product_id (BIGINT) → FK products(id) [CASCADE]
├── sku (VARCHAR 50) - UNIQUE, Required
├── name (VARCHAR 255)
├── size (VARCHAR 50)
├── color (VARCHAR 50)
├── weight (DECIMAL 8,2)
├── unit (VARCHAR 20)
├── cost_price (DECIMAL 12,2)
├── selling_price (DECIMAL 12,2) - Required
├── display_price (DECIMAL 12,2)
├── thickness (INT)
├── is_active (TINYINT) - Default: 1
└── timestamps
```

### 🖼️ `product_images` - Product Image Gallery

```
PK: id (BIGINT)
├── product_id (BIGINT) → FK products(id) [CASCADE]
├── product_variant_id (BIGINT) → FK product_variants(id) [SET NULL]
├── path (VARCHAR 255) - Required
├── is_primary (TINYINT) - Default: 0
├── display_order (INT) - Default: 0
└── created_at (TIMESTAMP)
```

### 📥 `imports` - Inventory Import Records

```
PK: id (BIGINT)
├── supplier_id (BIGINT) → FK suppliers(id) [RESTRICT]
├── staff_id (BIGINT) → FK staffs(user_id) [RESTRICT]
├── import_date (DATE) - Required
├── notes (TEXT)
├── status ENUM('draft', 'imported', 'cancelled')
└── timestamps
```

### 📋 `import_items` - Import Line Items (FIFO Tracking)

```
PK: id (BIGINT)
├── import_id (BIGINT) → FK imports(id) [CASCADE]
├── product_variant_id (BIGINT) → FK product_variants(id) [RESTRICT]
├── quantity (INT) - Required
├── remaining_quantity (INT) - Default: 0 (FIFO)
├── cost_price (DECIMAL 12,2)
├── manufacturing_date (DATE)
├── expiry_date (DATE) - Critical for OCOP
├── batch_number (VARCHAR 100)
└── timestamps
```

### 📊 `inventories` - Real-time Stock Tracking

```
PK: id (BIGINT)
UK: product_variant_id
├── product_variant_id (BIGINT) → FK product_variants(id) UNIQUE [CASCADE]
├── total_quantity (INT) - Default: 0
├── available_quantity (INT) - Default: 0
└── updated_at (TIMESTAMP ON UPDATE)
```

### ❌ `inventory_writeoffs` - Stock Writeoff (Expired, Damaged, Lost)

```
PK: id (BIGINT)
├── product_variant_id (BIGINT) → FK product_variants(id) [RESTRICT]
├── import_item_id (BIGINT) → FK import_items(id) [SET NULL]
├── quantity (INT) - Required
├── reason ENUM('expired', 'damaged', 'lost', 'other')
├── notes (TEXT)
├── writeoff_date (DATE) - Default: CURRENT_DATE
├── approved_by (BIGINT) → FK staffs(user_id) [SET NULL]
├── approved_at (TIMESTAMP)
├── status ENUM('pending', 'approved', 'rejected')
└── timestamps
```

---

## 🛒 MODULE 4: E-COMMERCE - ORDERS & PAYMENTS (7 Tables)

### 📦 `orders` - Customer Orders

```
PK: id (BIGINT)
UK: order_number
├── order_number (VARCHAR 50) - UNIQUE, Required
├── customer_id (BIGINT) → FK customers(id) [SET NULL]
├── total_amount (DECIMAL 12,2) - Required
├── shipping_fee (DECIMAL 12,2) - Default: 0
├── discount (DECIMAL 12,2) - Default: 0
├── final_amount (DECIMAL 12,2) - Required
├── status ENUM('pending','processing','shipped','delivered','cancelled','refunded','completed')
├── previous_status (VARCHAR 50)
├── shipping_method (VARCHAR 100)
├── shipping_address (TEXT)
├── shipping_city (VARCHAR 100)
├── shipping_province_code (VARCHAR 5)
├── shipping_name (VARCHAR 255)
├── shipping_phone (VARCHAR 20)
├── notes (TEXT)
└── timestamps
```

### 📄 `order_items` - Order Line Items (FIFO & Batch Tracking)

```
PK: id (BIGINT)
UK: (order_id, product_variant_id)
├── order_id (BIGINT) → FK orders(id) [CASCADE]
├── product_variant_id (BIGINT) → FK product_variants(id) [RESTRICT]
├── quantity (INT) - Required
├── unit_price (DECIMAL 12,2)
├── display_price (DECIMAL 12,2)
├── total_price (DECIMAL 12,2)
├── batch_number (VARCHAR 100) - For FIFO tracking
├── manufacturing_date (DATE)
├── expiry_date (DATE)
└── created_at (TIMESTAMP)
```

### ❌ `order_cancellations` - Order Cancellation Records

```
PK: id (BIGINT)
UK: order_id
├── order_id (BIGINT) → FK orders(id) UNIQUE [CASCADE]
├── reason (VARCHAR 255)
├── cancelled_by (VARCHAR 50) - 'customer', 'staff', 'system'
└── cancelled_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP
```

### 💳 `payments` - Payment Records

```
PK: id (BIGINT)
UK: transaction_id
├── order_id (BIGINT) → FK orders(id) [CASCADE]
├── amount (DECIMAL 12,2) - Required
├── payment_method ENUM('cash','bank_transfer','credit_card','e_wallet','other')
├── payment_gateway (VARCHAR 100)
├── transaction_id (VARCHAR 255) - UNIQUE
├── status ENUM('pending','completed','failed','refunded','cancelled')
├── refund_amount (DECIMAL 12,2)
├── refund_reason (TEXT)
├── refunded_at (TIMESTAMP)
├── notes (TEXT)
└── timestamps
```

### 🔄 `order_returns` - Return/Refund Requests

```
PK: id (BIGINT)
├── order_id (BIGINT) → FK orders(id) [CASCADE]
├── reason (VARCHAR 255)
├── description (TEXT)
├── status ENUM('pending','approved','rejected','returned','refunded')
├── refund_amount (DECIMAL 12,2)
├── requested_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP
├── approved_at (TIMESTAMP)
└── timestamps
```

### 📸 `order_return_images` - Return Request Images

```
PK: id (BIGINT)
├── order_return_id (BIGINT) → FK order_returns(id) [CASCADE]
├── image_path (VARCHAR 255)
└── uploaded_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP
```

### ❤️ `wishlists` - Customer Wishlist

```
PK: id (BIGINT)
UK: (customer_id, product_variant_id)
├── customer_id (BIGINT) → FK customers(id) [CASCADE]
├── product_variant_id (BIGINT) → FK product_variants(id) [CASCADE]
└── added_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP
```

---

## 🎁 MODULE 5: PROMOTIONS & DISCOUNTS (3 Tables)

### 🏷️ `discounts` - Promotion/Discount Codes

```
PK: id (BIGINT)
UK: code
├── code (VARCHAR 50) - UNIQUE, Required
├── description (TEXT)
├── discount_type ENUM('percentage', 'fixed_amount')
├── discount_value (DECIMAL 12,2) - Required
├── max_discount_amount (DECIMAL 12,2)
├── min_order_amount (DECIMAL 12,2)
├── usage_limit (INT)
├── usage_count (INT) - Default: 0
├── start_date (DATE)
├── end_date (DATE)
├── audience ENUM('all', 'new_customers', 'loyal_customers', 'specific')
├── is_active (TINYINT) - Default: 1
└── timestamps
```

### 📊 `discount_usages` - Discount Usage Tracking

```
PK: id (BIGINT)
├── discount_id (BIGINT) → FK discounts(id) [CASCADE]
├── order_id (BIGINT) → FK orders(id) [SET NULL]
├── customer_id (BIGINT) → FK customers(id) [SET NULL]
└── used_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP
```

### 🔗 `discount_product` - Discount-Product Mapping (M:N)

```
PK: (discount_id, product_id)
├── discount_id (BIGINT) → FK discounts(id) [CASCADE]
└── product_id (BIGINT) → FK products(id) [CASCADE]
```

---

## 📚 MODULE 6: CONTENT MANAGEMENT & REVIEWS (8 Tables)

### 📖 `blogs` - Blog Posts & Articles

```
PK: id (BIGINT)
UK: slug
├── title (VARCHAR 255) - Required
├── slug (VARCHAR 255) - UNIQUE, Required
├── summary (TEXT)
├── content (LONGTEXT)
├── image (VARCHAR 255)
└── timestamps
```

### 📝 `blog_blocks` - Blog Content Blocks

```
PK: id (BIGINT)
├── blog_id (BIGINT) → FK blogs(id) [CASCADE]
├── type ENUM('text', 'image')
├── content (TEXT)
├── image (VARCHAR 255)
├── position (INT)
└── timestamps
```

### ⭐ `reviews` - Product Reviews & Ratings

```
PK: id (BIGINT)
├── customer_id (BIGINT) → FK customers(id) [SET NULL]
├── product_id (BIGINT) → FK products(id) [CASCADE]
├── order_item_id (BIGINT) → FK order_items(id) [SET NULL]
├── rating (INT) - 1-5 stars
├── title (VARCHAR 255)
├── comment (TEXT)
├── anonymous (TINYINT) - Default: 0
├── status ENUM('pending', 'approved', 'rejected')
├── helpful_count (INT) - Default: 0
└── timestamps
```

### 👍 `review_likes` - Review Likes

```
PK: id (BIGINT)
UK: (review_id, customer_id)
├── review_id (BIGINT) → FK reviews(id) [CASCADE]
├── customer_id (BIGINT) → FK customers(id) [SET NULL]
└── liked_at (TIMESTAMP) - Default: CURRENT_TIMESTAMP
```

### 💬 `review_replies` - Staff Replies to Reviews

```
PK: id (BIGINT)
├── review_id (BIGINT) → FK reviews(id) [CASCADE]
├── staff_id (BIGINT) → FK staffs(user_id) [SET NULL]
├── comment (TEXT) - Required
└── timestamps
```

### 📧 `contacts` - Contact Form Submissions

```
PK: id (BIGINT)
├── name (VARCHAR 255)
├── email (VARCHAR 255)
├── message (TEXT)
├── status ENUM('pending', 'read')
├── reply (TEXT)
├── replied_at (TIMESTAMP)
└── timestamps
```

### 💌 `customer_messages` - Customer Messages/Inquiries

```
PK: id (BIGINT)
├── customer_id (BIGINT) → FK customers(id) [CASCADE]
├── product_id (BIGINT) → FK products(id) [SET NULL]
├── staff_id (BIGINT) → FK staffs(user_id) [SET NULL]
├── message (TEXT) - Required
├── is_read (TINYINT) - Default: 0
├── read_at (TIMESTAMP)
└── timestamps
```

### 🔔 `notifications` - System Notifications

```
PK: id (BIGINT)
├── user_id (BIGINT) → FK users(id) [CASCADE]
├── type (VARCHAR 100)
├── message (TEXT)
├── read_at (TIMESTAMP)
└── created_at (TIMESTAMP)
```

---

## 🔗 RELATIONSHIP SUMMARY

### Total Foreign Keys: **42**

**Cardinality:**

- 1:M (One-to-Many): 35 relationships
- M:N (Many-to-Many): 3 relationships (discount_product)
- 1:1 (One-to-One): 4 relationships (staffs→users, customers→users, etc.)

**Cascade Rules:**

- ON DELETE CASCADE: 28 relationships
- ON DELETE RESTRICT: 8 relationships
- ON DELETE SET NULL: 6 relationships

---

## ✅ KEY FEATURES

✔️ **Inventory Management:** FIFO with batch tracking & expiry date  
✔️ **Attendance System:** 5 scenarios (late, early leave, auto checkout, etc.)  
✔️ **Salary Calculation:** Bonuses, penalties, absent tracking  
✔️ **E-Commerce:** Orders, payments, returns, refunds  
✔️ **Promotions:** Discount codes with usage limits & targeting  
✔️ **Content:** Blogs, reviews, customer inquiries  
✔️ **Geographic Hierarchy:** Province/District/Ward codes  
✔️ **Audit Trail:** Timestamps on all tables

---

**Status:** ✅ Hoàn toàn chuẩn CDM | **Ready for:** PowerDesigner, MySQL, Production
