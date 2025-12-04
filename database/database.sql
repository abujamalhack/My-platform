-- قاعدة بيانات متجر البطاقات الرقمية - كاردي
-- MySQL 5.7 أو أحدث

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS cardy_store;
USE cardy_store;

-- جدول الفئات
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    parent_id INT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- جدول المنتجات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    old_price DECIMAL(10,2),
    cost_price DECIMAL(10,2),
    category_id INT,
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    stock_status ENUM('in_stock', 'out_of_stock', 'pre_order') DEFAULT 'in_stock',
    delivery_type ENUM('instant', 'manual', 'both') DEFAULT 'instant',
    delivery_time VARCHAR(100) DEFAULT 'فوري',
    featured BOOLEAN DEFAULT FALSE,
    best_seller BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- جدول صور المنتجات
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- جدول العملاء
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    country VARCHAR(100) DEFAULT 'السعودية',
    city VARCHAR(100),
    address TEXT,
    postal_code VARCHAR(20),
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(15,2) DEFAULT 0.00,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول الطلبات
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_refunded') DEFAULT 'pending',
    payment_details TEXT,
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'on_hold') DEFAULT 'pending',
    notes TEXT,
    admin_notes TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- جدول تفاصيل الطلب
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    delivery_code TEXT,
    delivery_status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    delivery_sent_at TIMESTAMP NULL,
    delivery_delivered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- جدول جلسات الدفع
CREATE TABLE payment_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) UNIQUE NOT NULL,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'SAR',
    payment_method VARCHAR(50) NOT NULL,
    payment_gateway VARCHAR(50),
    gateway_transaction_id VARCHAR(100),
    gateway_response TEXT,
    status ENUM('pending', 'completed', 'failed', 'expired') DEFAULT 'pending',
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- جدول الكوبونات
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount_amount DECIMAL(10,2),
    usage_limit INT,
    usage_limit_per_user INT DEFAULT 1,
    used_count INT DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول استخدام الكوبونات
CREATE TABLE coupon_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_id INT NOT NULL,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- جدول العملات
CREATE TABLE currencies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(3) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    rate DECIMAL(10,4) DEFAULT 1.0000,
    is_default BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول المراجعات والتقييمات
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    title VARCHAR(255),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    helpful_count INT DEFAULT 0,
    not_helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- جدول إعدادات الموقع
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json', 'array') DEFAULT 'text',
    category VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول الإشعارات
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    user_type ENUM('customer', 'admin') DEFAULT 'customer',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'order', 'payment') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- جدول سجل النشاطات
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    user_type ENUM('customer', 'admin', 'system') DEFAULT 'system',
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المسؤولين
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'manager', 'support') DEFAULT 'admin',
    permissions JSON,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول التذاكر والدعم الفني
CREATE TABLE support_tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    department ENUM('technical', 'billing', 'sales', 'general') DEFAULT 'general',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES admins(id) ON DELETE SET NULL
);

-- جدول ردود التذاكر
CREATE TABLE ticket_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    user_type ENUM('customer', 'admin') NOT NULL,
    message TEXT NOT NULL,
    attachments TEXT,
    is_internal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
);

-- إدراج البيانات الأولية

-- إدراج الفئات
INSERT INTO categories (name, slug, description, icon, sort_order) VALUES
('شحن الألعاب', 'games', 'رصيد وشحن لأشهر الألعاب الإلكترونية', 'fa-gamepad', 1),
('الاشتراكات الرقمية', 'subscriptions', 'اشتراكات المنصات والخدمات الرقمية', 'fa-film', 2),
('بطاقات التسوق', 'shopping', 'بطاقات هدايا للتسوق الإلكتروني', 'fa-shopping-bag', 3),
('شحن الاتصالات', 'mobile', 'رصيد لشركات الاتصال والهواتف', 'fa-mobile-alt', 4),
('بطاقات الألعاب', 'gift-cards', 'بطاقات ألعاب للبلايستيشن والاكس بوكس', 'fa-gamepad', 5);

-- إدراج المنتجات
INSERT INTO products (name, slug, description, price, old_price, category_id, delivery_type, featured, best_seller) VALUES
('Xena Live 70,000 Coins', 'xena-live-70000-coins', 'رصيد Xena Live 70,000 عملة مع توصيل فوري', 37.50, 45.00, 1, 'instant', TRUE, TRUE),
('Xena Live 1,511,000 Coins', 'xena-live-1511000-coins', 'رصيد Xena Live 1,511,000 عملة مع توصيل فوري', 750.00, 850.00, 1, 'instant', TRUE, TRUE),
('Netflix اشتراك 3 أشهر', 'netflix-3-months', 'اشتراك Netflix لمدة 3 أشهر كاملة', 149.99, NULL, 2, 'instant', TRUE, TRUE),
('بطاقة Apple 100$', 'apple-card-100', 'بطاقة Apple Store بقيمة 100 دولار أمريكي', 375.00, NULL, 3, 'instant', TRUE, FALSE),
('بطاقة Google Play 50$', 'google-play-50', 'بطاقة Google Play بقيمة 50 دولار أمريكي', 187.50, NULL, 3, 'instant', FALSE, TRUE),
('Steam Wallet 20$', 'steam-wallet-20', 'محفظة Steam بقيمة 20 دولار أمريكي', 75.00, 85.00, 1, 'instant', FALSE, TRUE),
('STC شحن 50 ريال', 'stc-50', 'رصيد STC بقيمة 50 ريال سعودي', 50.00, NULL, 4, 'instant', TRUE, FALSE),
('Shahid VIP شهر', 'shahid-vip-month', 'اشتراك Shahid VIP لمدة شهر كامل', 39.99, NULL, 2, 'instant', FALSE, TRUE);

-- إدراج العملات
INSERT INTO currencies (code, name, symbol, rate, is_default) VALUES
('SAR', 'الريال السعودي', 'ر.س', 1.0000, TRUE),
('USD', 'الدولار الأمريكي', '$', 3.7500, FALSE),
('EUR', 'اليورو الأوروبي', '€', 4.1000, FALSE);

-- إدراج الكوبونات
INSERT INTO coupons (code, name, discount_type, discount_value, min_order_amount, usage_limit, start_date, end_date) VALUES
('WELCOME10', 'كوبون ترحيبي', 'percentage', 10.00, 50.00, 1000, '2024-01-01', '2024-12-31'),
('SAVE20SAR', 'تخفيض 20 ريال', 'fixed', 20.00, 100.00, 500, '2024-01-01', '2024-06-30'),
('SUMMER15', 'عرض الصيف', 'percentage', 15.00, 200.00, 300, '2024-06-01', '2024-09-30');

-- إدراج إعدادات الموقع
INSERT INTO settings (setting_key, setting_value, setting_type, category, description) VALUES
('site_name', 'كاردي', 'text', 'general', 'اسم الموقع'),
('site_url', 'https://cardy.com', 'text', 'general', 'رابط الموقع'),
('site_email', 'info@cardy.com', 'text', 'general', 'البريد الإلكتروني'),
('site_phone', '+966778657708', 'text', 'general', 'رقم الهاتف'),
('whatsapp_number', '+967778657708', 'text', 'general', 'رقم الواتساب'),
('support_email', 'support@cardy.com', 'text', 'general', 'بريد الدعم الفني'),
('default_currency', 'SAR', 'text', 'currency', 'العملة الافتراضية'),
('tax_rate', '15', 'number', 'tax', 'نسبة الضريبة المضافة'),
('min_order_amount', '10', 'number', 'orders', 'الحد الأدنى للطلب'),
('free_shipping_amount', '200', 'number', 'shipping', 'حد الشحن المجاني'),
('payment_methods', '["mada", "apple_pay", "paypal", "stc_pay", "bank_transfer"]', 'json', 'payment', 'طرق الدفع المتاحة'),
('social_links', '{"facebook": "https://facebook.com/cardy", "twitter": "https://twitter.com/cardy", "instagram": "https://instagram.com/cardy", "whatsapp": "https://wa.me/967778657708"}', 'json', 'social', 'روابط وسائل التواصل'),
('working_hours', '{"saturday": "24 ساعة", "sunday": "24 ساعة", "monday": "24 ساعة", "tuesday": "24 ساعة", "wednesday": "24 ساعة", "thursday": "24 ساعة", "friday": "24 ساعة"}', 'json', 'general', 'أوقات العمل');

-- إدراج المسؤولين
INSERT INTO admins (username, email, password, full_name, role, status) VALUES
('admin', 'admin@cardy.com', '$2y$10$YourHashedPasswordHere', 'مدير النظام', 'super_admin', 'active'),
('manager', 'manager@cardy.com', '$2y$10$YourHashedPasswordHere', 'مدير المتجر', 'manager', 'active'),
('support', 'support@cardy.com', '$2y$10$YourHashedPasswordHere', 'فريق الدعم', 'support', 'active');

-- إنشاء الفهارس لتحسين الأداء
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created ON orders(created_at);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_payment_sessions_status ON payment_sessions(status);
CREATE INDEX idx_coupons_status ON coupons(status);