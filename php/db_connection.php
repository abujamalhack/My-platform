<?php
// php/db_connection.php - معدل للـ SQLite

require_once __DIR__ . '/config.php';

class DatabaseConnection {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $this->db = get_db();
        $this->create_tables();
        $this->seed_data();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    private function create_tables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                category TEXT,
                image TEXT,
                stock INTEGER DEFAULT 100,
                is_active INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE,
                phone TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number TEXT UNIQUE,
                customer_id INTEGER,
                total_amount REAL NOT NULL,
                status TEXT DEFAULT 'pending',
                payment_method TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER,
                product_id INTEGER,
                quantity INTEGER,
                price REAL,
                FOREIGN KEY (order_id) REFERENCES orders(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )"
        ];
        
        foreach ($tables as $table) {
            $this->db->exec($table);
        }
    }
    
    private function seed_data() {
        // التحقق مما إذا كانت الجداول فارغة
        $count = $this->db->querySingle("SELECT COUNT(*) FROM products");
        
        if ($count == 0) {
            $products = [
                ['بطاقة ستيم 50$', 'بطاقة رصيد ستيم بقيمة 50 دولار', 50, 'ألعاب', 'steam.jpg'],
                ['نيتفلكس شهري', 'اشتراك نيتفلكس لمدة شهر', 35, 'اشتراكات', 'netflix.jpg'],
                ['بطاقة آيتونز 25$', 'رصيد آيتونز بقيمة 25 دولار', 25, 'متاجر', 'itunes.jpg'],
                ['بلايستيشن بلس 3 أشهر', 'اشتراك بلايستيشن لمدة 3 أشهر', 180, 'ألعاب', 'playstation.jpg'],
                ['بطاقة جوجل بلاي 100$', 'رصيد جوجل بلاي', 100, 'متاجر', 'googleplay.jpg'],
                ['أمازون 200$', 'بطاقة هدايا أمازون', 200, 'متاجر', 'amazon.jpg']
            ];
            
            $stmt = $this->db->prepare("
                INSERT INTO products (name, description, price, category, image) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($products as $product) {
                $stmt->bindValue(1, $product[0], SQLITE3_TEXT);
                $stmt->bindValue(2, $product[1], SQLITE3_TEXT);
                $stmt->bindValue(3, $product[2], SQLITE3_FLOAT);
                $stmt->bindValue(4, $product[3], SQLITE3_TEXT);
                $stmt->bindValue(5, $product[4], SQLITE3_TEXT);
                $stmt->execute();
            }
        }
    }
}

// كائن قاعدة البيانات العام
$db = DatabaseConnection::getInstance()->getConnection();
?>
