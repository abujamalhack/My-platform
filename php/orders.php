<?php
require_once 'config.php';
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

class OrdersAPI {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    // إنشاء طلب جديد
    public function createOrder($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO orders 
            (order_number, customer_id, customer_email, customer_phone, 
             total_amount, status, payment_method, payment_status, items)
            VALUES (?, ?, ?, ?, ?, 'pending', ?, 'pending', ?)
        ");
        
        $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt->bind_param(
            "sisssds",
            $orderNumber,
            $data['customer_id'],
            $data['customer_email'],
            $data['customer_phone'],
            $data['total_amount'],
            $data['payment_method'],
            json_encode($data['items'])
        );
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'order_id' => $this->conn->insert_id,
                'order_number' => $orderNumber
            ];
        }
        
        return ['success' => false];
    }
    
    // جلب طلبات العميل
    public function getCustomerOrders($customerId) {
        $stmt = $this->conn->prepare("
            SELECT * FROM orders 
            WHERE customer_id = ? 
            ORDER BY created_at DESC
        ");
        
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $orders = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['items'] = json_decode($row['items'], true);
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    // جلب طلب محدد
    public function getOrder($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if ($order) {
            $order['items'] = json_decode($order['items'], true);
        }
        
        return $order;
    }
    
    // تحديث حالة الطلب
    public function updateOrderStatus($orderId, $status) {
        $stmt = $this->conn->prepare("
            UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?
        ");
        
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }
    
    // تحديث حالة الدفع
    public function updatePaymentStatus($orderId, $paymentStatus, $transactionId = null) {
        $stmt = $this->conn->prepare("
            UPDATE orders 
            SET payment_status = ?, transaction_id = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->bind_param("ssi", $paymentStatus, $transactionId, $orderId);
        return $stmt->execute();
    }
    
    // جلب جميع الطلبات (للوحة التحكم)
    public function getAllOrders($filters = []) {
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $row['items'] = json_decode($row['items'], true);
            $orders[] = $row;
        }
        
        return $orders;
    }
    
    // إحصائيات الطلبات
    public function getOrderStats() {
        $stats = [];
        
        // إجمالي الطلبات
        $result = $this->conn->query("SELECT COUNT(*) as total FROM orders");
        $stats['total_orders'] = $result->fetch_assoc()['total'];
        
        // إجمالي المبيعات
        $result = $this->conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
        $stats['total_sales'] = $result->fetch_assoc()['total'] ?: 0;
        
        // الطلبات حسب الحالة
        $result = $this->conn->query("
            SELECT status, COUNT(*) as count 
            FROM orders 
            GROUP BY status
        ");
        
        while ($row = $result->fetch_assoc()) {
            $stats['status_counts'][$row['status']] = $row['count'];
        }
        
        return $stats;
    }
}

$ordersAPI = new OrdersAPI();

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['customer_id'])) {
        $orders = $ordersAPI->getCustomerOrders($_GET['customer_id']);
        echo json_encode($orders);
    } elseif (isset($_GET['id'])) {
        $order = $ordersAPI->getOrder($_GET['id']);
        echo json_encode($order);
    } elseif (isset($_GET['stats'])) {
        $stats = $ordersAPI->getOrderStats();
        echo json_encode($stats);
    } else {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];
        
        $orders = $ordersAPI->getAllOrders($filters);
        echo json_encode($orders);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'update_status':
                $result = $ordersAPI->updateOrderStatus($_GET['id'], $data['status']);
                echo json_encode(['success' => $result]);
                break;
                
            case 'update_payment':
                $result = $ordersAPI->updatePaymentStatus(
                    $_GET['id'], 
                    $data['payment_status'], 
                    $data['transaction_id'] ?? null
                );
                echo json_encode(['success' => $result]);
                break;
                
            default:
                $result = $ordersAPI->createOrder($data);
                echo json_encode($result);
        }
    } else {
        $result = $ordersAPI->createOrder($data);
        echo json_encode($result);
    }
}
?>