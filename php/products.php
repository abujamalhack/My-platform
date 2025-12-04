<?php
require_once 'config.php';
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

class ProductsAPI {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    // جلب جميع المنتجات
    public function getProducts($category = null, $limit = null) {
        $sql = "SELECT * FROM products WHERE status = 'active'";
        
        if ($category) {
            $sql .= " AND category = ?";
        }
        
        if ($limit) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if ($category && $limit) {
            $stmt->bind_param("si", $category, $limit);
        } elseif ($category) {
            $stmt->bind_param("s", $category);
        } elseif ($limit) {
            $stmt->bind_param("i", $limit);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    // جلب منتج محدد
    public function getProduct($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // إضافة منتج جديد
    public function addProduct($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO products 
            (name, description, price, old_price, category, stock, image, delivery_type, status, featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
        ");
        
        $stmt->bind_param(
            "ssddsssss",
            $data['name'],
            $data['description'],
            $data['price'],
            $data['old_price'],
            $data['category'],
            $data['stock'],
            $data['image'],
            $data['delivery_type'],
            $data['featured']
        );
        
        return $stmt->execute();
    }
    
    // تحديث منتج
    public function updateProduct($id, $data) {
        $sql = "UPDATE products SET ";
        $params = [];
        $types = "";
        $values = [];
        
        foreach ($data as $key => $value) {
            $sql .= "$key = ?, ";
            $types .= "s";
            $values[] = $value;
        }
        
        $sql = rtrim($sql, ", ");
        $sql .= " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }
    
    // حذف منتج
    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("UPDATE products SET status = 'deleted' WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    // البحث عن منتجات
    public function searchProducts($keyword) {
        $keyword = "%$keyword%";
        $stmt = $this->conn->prepare("
            SELECT * FROM products 
            WHERE status = 'active' 
            AND (name LIKE ? OR description LIKE ? OR category LIKE ?)
        ");
        
        $stmt->bind_param("sss", $keyword, $keyword, $keyword);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $products = [];
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
}

// معالجة الطلبات
$productsAPI = new ProductsAPI();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $product = $productsAPI->getProduct($_GET['id']);
        echo json_encode($product);
    } elseif (isset($_GET['search'])) {
        $products = $productsAPI->searchProducts($_GET['search']);
        echo json_encode($products);
    } elseif (isset($_GET['category'])) {
        $limit = isset($_GET['limit']) ? $_GET['limit'] : null;
        $products = $productsAPI->getProducts($_GET['category'], $limit);
        echo json_encode($products);
    } else {
        $limit = isset($_GET['limit']) ? $_GET['limit'] : null;
        $products = $productsAPI->getProducts(null, $limit);
        echo json_encode($products);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_GET['action']) && $_GET['action'] === 'update') {
        $id = $_GET['id'];
        $result = $productsAPI->updateProduct($id, $data);
        echo json_encode(['success' => $result]);
    } else {
        $result = $productsAPI->addProduct($data);
        echo json_encode(['success' => $result, 'id' => $this->conn->insert_id]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'];
    $result = $productsAPI->deleteProduct($id);
    echo json_encode(['success' => $result]);
}
?>