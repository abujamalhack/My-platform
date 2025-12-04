-- init.sql - تهيئة جداول قاعدة البيانات

-- جدول المنتجات
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    category TEXT,
    image TEXT,
    stock INTEGER DEFAULT 100,
    is_active INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول العملاء
CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE,
    phone TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول المستخدمين (للتسجيل)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    phone TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الطلبات
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_number TEXT UNIQUE,
    customer_id INTEGER,
    total_amount REAL NOT NULL,
    status TEXT DEFAULT 'pending',
    payment_method TEXT,
    transaction_id TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- جدول عناصر الطلب
CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER,
    product_id INTEGER,
    quantity INTEGER,
    price REAL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- جدول الفئات
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- إدراج فئات افتراضية
INSERT OR IGNORE INTO categories (name, slug) VALUES
('ألعاب', 'games'),
('اشتراكات', 'subscriptions'),
('متاجر', 'stores'),
('خدمات', 'services');

-- إدراج منتجات افتراضية
INSERT OR IGNORE INTO products (name, description, price, category, image, stock) VALUES
('بطاقة ستيم 50$', 'بطاقة رصيد ستيم بقيمة 50 دولار', 50, 'ألعاب', 'steam.jpg', 50),
('نيتفلكس شهري', 'اشتراك نيتفلكس لمدة شهر', 35, 'اشتراكات', 'netflix.jpg', 100),
('بطاقة آيتونز 25$', 'رصيد آيتونز بقيمة 25 دولار', 25, 'متاجر', 'itunes.jpg', 75),
('بلايستيشن بلس 3 أشهر', 'اشتراك بلايستيشن لمدة 3 أشهر', 180, 'ألعاب', 'playstation.jpg', 30),
('بطاقة جوجل بلاي 100$', 'رصيد جوجل بلاي', 100, 'متاجر', 'googleplay.jpg', 40),
('أمازون 200$', 'بطاقة هدايا أمازون', 200, 'متاجر', 'amazon.jpg', 25),
('بطاقة Xbox Live 60$', 'بطاقة رصيد Xbox Live', 60, 'ألعاب', 'xbox.jpg', 60),
('بطاقة Spotify 6 أشهر', 'اشتراك Spotify لمدة 6 أشهر', 120, 'اشتراكات', 'spotify.jpg', 80);

-- إدراج مستخدم مسؤول افتراضي
-- كلمة المرور: admin123 (مشفرة)
INSERT OR IGNORE INTO users (username, email, password, phone) VALUES
('admin', 'admin@cardy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+966500000000');

-- إدراج طلبات تجريبية
INSERT OR IGNORE INTO customers (name, email, phone) VALUES
('أحمد محمد', 'ahmed@example.com', '+966511111111'),
('سارة خالد', 'sara@example.com', '+966522222222');

INSERT OR IGNORE INTO orders (order_number, customer_id, total_amount, status, payment_method) VALUES
('ORD-2024-001', 1, 150, 'completed', 'credit_card'),
('ORD-2024-002', 2, 85, 'pending', 'mada');

INSERT OR IGNORE INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 2, 50),
(1, 3, 1, 25),
(2, 2, 1, 35),
(2, 6, 1, 50);

-- جدول لتتبع الزيارات (اختياري)
CREATE TABLE IF NOT EXISTS visits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page TEXT,
    ip_address TEXT,
    user_agent TEXT,
    visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
