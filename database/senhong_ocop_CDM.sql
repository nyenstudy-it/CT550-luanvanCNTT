-- ========================================
-- SENHONG OCOP SYSTEM - CDM (Conceptual Data Model)
-- Database: senhong_ocop
-- MySQL Version: 8.0+
-- Created for: PowerDesigner Reverse Engineer
-- ========================================

-- ========================================
-- 1. USER MANAGEMENT ENTITIES
-- ========================================

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) UNIQUE,
    avatar VARCHAR(255),
    role ENUM('admin', 'staff', 'customer', 'supplier') DEFAULT 'customer',
    is_active TINYINT(1) DEFAULT 1,
    lock_expires_at TIMESTAMP NULL,
    lock_enabled_by BIGINT UNSIGNED NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS staffs (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    phone VARCHAR(20),
    date_of_birth DATE,
    address TEXT,
    position ENUM('manager', 'staff', 'leader', 'director') DEFAULT 'staff',
    start_date DATE,
    probation_start DATE,
    probation_end DATE,
    employment_status ENUM('probation', 'official', 'resigned') DEFAULT 'probation',
    probation_hourly_wage DECIMAL(10, 2),
    official_hourly_wage DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_staffs_users FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_position (position),
    INDEX idx_employment_status (employment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED UNIQUE,
    phone VARCHAR(20),
    date_of_birth DATE,
    address TEXT,
    is_default_address TINYINT(1) DEFAULT 0,
    province_code VARCHAR(5),
    district_code VARCHAR(5),
    ward_code VARCHAR(5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_customers_users FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_province (province_code),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 2. ATTENDANCE & HR MANAGEMENT
-- ========================================

CREATE TABLE IF NOT EXISTS attendances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_id BIGINT UNSIGNED NOT NULL,
    work_date DATE NOT NULL,
    shift ENUM('morning', 'afternoon') NOT NULL,
    
    -- Expected times
    expected_check_in TIME NOT NULL,
    expected_check_out TIME NOT NULL,
    
    -- Actual check-in
    check_in TIME,
    check_in_ip VARCHAR(45),
    check_in_latitude DECIMAL(10, 7),
    check_in_longitude DECIMAL(10, 7),
    check_in_network_type VARCHAR(50),
    check_in_distance_meters DECIMAL(8, 2),
    check_in_verification_method VARCHAR(20),
    
    -- Actual check-out
    check_out TIME,
    check_out_ip VARCHAR(45),
    check_out_latitude DECIMAL(10, 7),
    check_out_longitude DECIMAL(10, 7),
    check_out_network_type VARCHAR(50),
    check_out_distance_meters DECIMAL(8, 2),
    check_out_verification_method VARCHAR(20),
    
    -- Status flags
    is_late TINYINT(1) DEFAULT 0,
    is_early_leave TINYINT(1) DEFAULT 0,
    is_completed TINYINT(1) DEFAULT 0,
    is_auto_checkout_forced TINYINT(1) DEFAULT 0,
    
    -- Early leave details
    early_leave_reason TEXT,
    early_leave_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    early_leave_pay_percent INT(11) DEFAULT 100,
    early_leave_approved_by BIGINT UNSIGNED,
    early_leave_approved_at TIMESTAMP NULL,
    
    -- Scenario classification (auto-detection)
    scenario_type TINYINT(3) UNSIGNED,
    worked_minutes INT,
    
    -- Financial
    salary_amount DECIMAL(12, 2),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_attendances_staffs FOREIGN KEY (staff_id) 
        REFERENCES staffs(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_attendances_approved_by FOREIGN KEY (early_leave_approved_by) 
        REFERENCES staffs(user_id) ON DELETE SET NULL,
    UNIQUE KEY uk_staff_date_shift (staff_id, work_date, shift),
    INDEX idx_work_date (work_date),
    INDEX idx_is_complete (is_completed),
    INDEX idx_scenario_type (scenario_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS salaries (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_id BIGINT UNSIGNED NOT NULL,
    month INT(2),
    year INT(4),
    total_hours DECIMAL(8, 2),
    total_amount DECIMAL(12, 2),
    bonus DECIMAL(12, 2) DEFAULT 0,
    penalty DECIMAL(12, 2) DEFAULT 0,
    absent_count INT DEFAULT 0,
    absent_amount DECIMAL(12, 2) DEFAULT 0,
    final_amount DECIMAL(12, 2),
    status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
    notes TEXT,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_salaries_staffs FOREIGN KEY (staff_id) 
        REFERENCES staffs(user_id) ON DELETE CASCADE,
    UNIQUE KEY uk_staff_month_year (staff_id, month, year),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 3. SUPPLY CHAIN & INVENTORY
-- ========================================

CREATE TABLE IF NOT EXISTS suppliers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    province_code VARCHAR(5),
    tax_code VARCHAR(20),
    contact_person VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS category_products (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('simple', 'bundle', 'variable') DEFAULT 'simple',
    description LONGTEXT,
    origin VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) 
        REFERENCES category_products(id) ON DELETE RESTRICT,
    INDEX idx_category_id (category_id),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_variants (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255),
    size VARCHAR(50),
    color VARCHAR(50),
    weight DECIMAL(8, 2),
    unit VARCHAR(20),
    cost_price DECIMAL(12, 2),
    selling_price DECIMAL(12, 2) NOT NULL,
    display_price DECIMAL(12, 2),
    thickness INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_variants_products FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_sku (sku),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_images (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED,
    path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_images_products FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_images_variants FOREIGN KEY (product_variant_id) 
        REFERENCES product_variants(id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_is_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS imports (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    supplier_id BIGINT UNSIGNED NOT NULL,
    staff_id BIGINT UNSIGNED NOT NULL,
    import_date DATE NOT NULL,
    notes TEXT,
    status ENUM('draft', 'imported', 'cancelled') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_imports_suppliers FOREIGN KEY (supplier_id) 
        REFERENCES suppliers(id) ON DELETE RESTRICT,
    CONSTRAINT fk_imports_staffs FOREIGN KEY (staff_id) 
        REFERENCES staffs(user_id) ON DELETE RESTRICT,
    INDEX idx_import_date (import_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS import_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    import_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    remaining_quantity INT NOT NULL DEFAULT 0,
    cost_price DECIMAL(12, 2),
    manufacturing_date DATE,
    expiry_date DATE,
    batch_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_import_items_imports FOREIGN KEY (import_id) 
        REFERENCES imports(id) ON DELETE CASCADE,
    CONSTRAINT fk_import_items_variants FOREIGN KEY (product_variant_id) 
        REFERENCES product_variants(id) ON DELETE RESTRICT,
    CONSTRAINT check_remaining_qty CHECK (remaining_quantity <= quantity),
    INDEX idx_import_id (import_id),
    INDEX idx_expiry_date (expiry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_variant_id BIGINT UNSIGNED NOT NULL UNIQUE,
    total_quantity INT DEFAULT 0,
    available_quantity INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_inventories_variants FOREIGN KEY (product_variant_id) 
        REFERENCES product_variants(id) ON DELETE CASCADE,
    INDEX idx_available_qty (available_quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventory_writeoffs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_variant_id BIGINT UNSIGNED NOT NULL,
    import_item_id BIGINT UNSIGNED,
    quantity INT NOT NULL,
    reason ENUM('expired', 'damaged', 'lost', 'other') DEFAULT 'damaged',
    notes TEXT,
    writeoff_date DATE DEFAULT CURRENT_DATE,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_writeoffs_variants FOREIGN KEY (product_variant_id) 
        REFERENCES product_variants(id) ON DELETE RESTRICT,
    CONSTRAINT fk_writeoffs_import_items FOREIGN KEY (import_item_id) 
        REFERENCES import_items(id) ON DELETE SET NULL,
    CONSTRAINT fk_writeoffs_approved_by FOREIGN KEY (approved_by) 
        REFERENCES staffs(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_writeoff_date (writeoff_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 4. E-COMMERCE: ORDERS & PAYMENTS
-- ========================================

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id BIGINT UNSIGNED,
    total_amount DECIMAL(12, 2) NOT NULL,
    shipping_fee DECIMAL(12, 2) DEFAULT 0,
    discount DECIMAL(12, 2) DEFAULT 0,
    final_amount DECIMAL(12, 2) NOT NULL,
    
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'completed') DEFAULT 'pending',
    previous_status VARCHAR(50),
    
    shipping_method VARCHAR(100),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_province_code VARCHAR(5),
    shipping_name VARCHAR(255),
    shipping_phone VARCHAR(20),
    
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_orders_customers FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_customer_id (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12, 2),
    display_price DECIMAL(12, 2),
    total_price DECIMAL(12, 2),
    batch_number VARCHAR(100),
    manufacturing_date DATE,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_order_items_orders FOREIGN KEY (order_id) 
        REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_variants FOREIGN KEY (product_variant_id) 
        REFERENCES product_variants(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_cancellations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL UNIQUE,
    reason VARCHAR(255),
    cancelled_by VARCHAR(50),
    cancelled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_cancellations_orders FOREIGN KEY (order_id) 
        REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_cancelled_at (cancelled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'e_wallet', 'other') DEFAULT 'cash',
    payment_gateway VARCHAR(100),
    transaction_id VARCHAR(255) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
    
    refund_amount DECIMAL(12, 2),
    refund_reason TEXT,
    refunded_at TIMESTAMP NULL,
    
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_payments_orders FOREIGN KEY (order_id) 
        REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_returns (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    reason VARCHAR(255),
    description TEXT,
    status ENUM('pending', 'approved', 'rejected', 'returned', 'refunded') DEFAULT 'pending',
    refund_amount DECIMAL(12, 2),
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_returns_orders FOREIGN KEY (order_id) 
        REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_return_images (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_return_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_return_images_returns FOREIGN KEY (order_return_id) 
        REFERENCES order_returns(id) ON DELETE CASCADE,
    INDEX idx_return_id (order_return_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS wishlists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    product_variant_id BIGINT UNSIGNED NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_wishlists_customers FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlists_variants FOREIGN KEY (product_variant_id) 
        REFERENCES product_variants(id) ON DELETE CASCADE,
    UNIQUE KEY uk_customer_variant (customer_id, product_variant_id),
    INDEX idx_customer_id (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 5. PROMOTIONS & DISCOUNTS
-- ========================================

CREATE TABLE IF NOT EXISTS discounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') DEFAULT 'percentage',
    discount_value DECIMAL(12, 2) NOT NULL,
    max_discount_amount DECIMAL(12, 2),
    min_order_amount DECIMAL(12, 2),
    usage_limit INT,
    usage_count INT DEFAULT 0,
    start_date DATE,
    end_date DATE,
    audience ENUM('all', 'new_customers', 'loyal_customers', 'specific') DEFAULT 'all',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS discount_usages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    discount_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED,
    customer_id BIGINT UNSIGNED,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_usages_discounts FOREIGN KEY (discount_id) 
        REFERENCES discounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_usages_orders FOREIGN KEY (order_id) 
        REFERENCES orders(id) ON DELETE SET NULL,
    CONSTRAINT fk_usages_customers FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_discount_id (discount_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS discount_product (
    discount_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (discount_id, product_id),
    CONSTRAINT fk_disc_prod_discounts FOREIGN KEY (discount_id) 
        REFERENCES discounts(id) ON DELETE CASCADE,
    CONSTRAINT fk_disc_prod_products FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 6. CONTENT MANAGEMENT & REVIEWS
-- ========================================

CREATE TABLE IF NOT EXISTS blogs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    summary TEXT,
    content LONGTEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_blocks (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    blog_id BIGINT UNSIGNED NOT NULL,
    type ENUM('text', 'image') DEFAULT 'text',
    content TEXT,
    image VARCHAR(255),
    position INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_blocks_blogs FOREIGN KEY (blog_id) 
        REFERENCES blogs(id) ON DELETE CASCADE,
    INDEX idx_blog_id (blog_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED,
    product_id BIGINT UNSIGNED NOT NULL,
    order_item_id BIGINT UNSIGNED,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    comment TEXT,
    anonymous TINYINT(1) DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_reviews_customers FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_reviews_products FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_order_items FOREIGN KEY (order_item_id) 
        REFERENCES order_items(id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS review_likes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    review_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED,
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_likes_reviews FOREIGN KEY (review_id) 
        REFERENCES reviews(id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_customers FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE SET NULL,
    UNIQUE KEY uk_review_customer_like (review_id, customer_id),
    INDEX idx_review_id (review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS review_replies (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    review_id BIGINT UNSIGNED NOT NULL,
    staff_id BIGINT UNSIGNED,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_replies_reviews FOREIGN KEY (review_id) 
        REFERENCES reviews(id) ON DELETE CASCADE,
    CONSTRAINT fk_replies_staffs FOREIGN KEY (staff_id) 
        REFERENCES staffs(user_id) ON DELETE SET NULL,
    INDEX idx_review_id (review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contacts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255),
    message TEXT,
    status ENUM('pending', 'read') DEFAULT 'pending',
    reply TEXT,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED,
    staff_id BIGINT UNSIGNED,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_messages_customers FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_products FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE SET NULL,
    CONSTRAINT fk_messages_staffs FOREIGN KEY (staff_id) 
        REFERENCES staffs(user_id) ON DELETE SET NULL,
    INDEX idx_customer_id (customer_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(100),
    message TEXT,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_notifications_users FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_read_at (read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- 7. APPLICATION TABLES
-- ========================================

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255),
    created_at TIMESTAMP NULL,
    
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload LONGTEXT,
    last_activity INT,
    
    CONSTRAINT fk_sessions_users FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cache (
    key_col VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT,
    expiration INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cache_locks (
    key_col VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255),
    expiration INT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255),
    payload LONGTEXT,
    attempts TINYINT UNSIGNED,
    reserved_at INT UNSIGNED,
    available_at INT UNSIGNED,
    created_at INT UNSIGNED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- INDEXES FOR PERFORMANCE
-- ========================================

-- Composite indexes for common queries
CREATE INDEX idx_orders_customer_date ON orders(customer_id, created_at);
CREATE INDEX idx_order_items_order_variant ON order_items(order_id, product_variant_id);
CREATE INDEX idx_import_items_variant_expiry ON import_items(product_variant_id, expiry_date);
CREATE INDEX idx_attendances_staff_date ON attendances(staff_id, work_date);
CREATE INDEX idx_inventory_available ON inventories(available_quantity);

-- ========================================
-- VIEWS FOR REPORTING (OPTIONAL)
-- ========================================

CREATE OR REPLACE VIEW vw_staff_attendance_summary AS
SELECT 
    s.user_id,
    u.name as staff_name,
    COUNT(a.id) as total_records,
    SUM(CASE WHEN a.is_completed = 1 THEN 1 ELSE 0 END) as completed_records,
    SUM(CASE WHEN a.is_late = 1 THEN 1 ELSE 0 END) as late_count,
    SUM(CASE WHEN a.is_early_leave = 1 THEN 1 ELSE 0 END) as early_leave_count
FROM staffs s
JOIN users u ON s.user_id = u.id
LEFT JOIN attendances a ON s.user_id = a.staff_id
GROUP BY s.user_id, u.name;

CREATE OR REPLACE VIEW vw_product_inventory_status AS
SELECT 
    pv.id,
    p.name as product_name,
    pv.sku,
    pv.selling_price,
    i.total_quantity,
    i.available_quantity,
    (i.total_quantity - i.available_quantity) as reserved_quantity,
    CASE 
        WHEN i.available_quantity = 0 THEN 'Out of Stock'
        WHEN i.available_quantity < 10 THEN 'Low Stock'
        ELSE 'In Stock'
    END as stock_status
FROM product_variants pv
JOIN products p ON pv.product_id = p.id
LEFT JOIN inventories i ON pv.id = i.product_variant_id;

CREATE OR REPLACE VIEW vw_order_summary AS
SELECT 
    o.id,
    o.order_number,
    u.name as customer_name,
    o.final_amount,
    o.status,
    COUNT(oi.id) as item_count,
    o.created_at
FROM orders o
LEFT JOIN customers c ON o.customer_id = c.id
LEFT JOIN users u ON c.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- ========================================
-- END OF CDM SCHEMA
-- Generated: 2026-04-16
-- For: PowerDesigner Reverse Engineer
-- ========================================
