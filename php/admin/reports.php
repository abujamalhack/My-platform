<?php
require_once '../config.php';
require_once '../db_connection.php';

session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

header('Content-Type: application/json; charset=utf-8');

class ReportsAPI {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    // تقرير المبيعات
    public function getSalesReport($startDate, $endDate) {
        $stmt = $this->conn->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as orders_count,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as avg_order_value
            FROM orders
            WHERE status = 'completed'
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $report = [];
        
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }
        
        return $report;
    }
    
    // تقرير المنتجات
    public function getProductsReport($startDate, $endDate) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.id,
                p.name,
                p.category,
                COUNT(o.id) as units_sold,
                SUM(o.total_amount) as revenue,
                AVG(o.total_amount) as avg_price
            FROM products p
            LEFT JOIN orders o ON JSON_CONTAINS(o.items, CAST(p.id AS JSON), '$')
            WHERE o.status = 'completed'
            AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY p.id
            ORDER BY units_sold DESC
        ");
        
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $report = [];
        
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }
        
        return $report;
    }
    
    // تقرير العملاء
    public function getCustomersReport($startDate, $endDate) {
        $stmt = $this->conn->prepare("
            SELECT 
                c.id,
                c.name,
                c.email,
                c.phone,
                COUNT(o.id) as total_orders,
                SUM(o.total_amount) as total_spent,
                MAX(o.created_at) as last_order_date
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
            WHERE o.status = 'completed'
            AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY c.id
            ORDER BY total_spent DESC
        ");
        
        $stmt->bind_param("ss", $startDate, $endDate);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $report = [];
        
        while ($row = $result->fetch_assoc()) {
            $report[] = $row;
        }
        
        return $report;
    }
    
    // تقرير الطلبات
    public function getOrdersReport($filters) {
        $sql = "
            SELECT 
                o.*,
                c.name as customer_name,
                c.email as customer_email
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            WHERE 1=1
        ";
        
        $params = [];
        $types = "";
        
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['payment_method'])) {
            $sql .= " AND o.payment_method = ?";
            $params[] = $filters['payment_method'];
            $types .= "s";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        
        $result = $stmt->get_result();
        $report = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['items'] = json_decode($row['items'], true);
            $report[] = $row;
        }
        
        return $report;
    }
}

$reportsAPI = new ReportsAPI();

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['type'])) {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        switch ($_GET['type']) {
            case 'sales':
                $report = $reportsAPI->getSalesReport($startDate, $endDate);
                break;
                
            case 'products':
                $report = $reportsAPI->getProductsReport($startDate, $endDate);
                break;
                
            case 'customers':
                $report = $reportsAPI->getCustomersReport($startDate, $endDate);
                break;
                
            case 'orders':
                $filters = [
                    'status' => $_GET['status'] ?? null,
                    'payment_method' => $_GET['payment_method'] ?? null,
                    'date_from' => $startDate,
                    'date_to' => $endDate
                ];
                $report = $reportsAPI->getOrdersReport($filters);
                break;
                
            default:
                $report = [];
        }
        
        echo json_encode($report, JSON_UNESCAPED_UNICODE);
    }
}
?>