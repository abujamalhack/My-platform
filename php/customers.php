<?php
require_once 'config.php';
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

class CustomersAPI {
    private $conn;
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    // تسجيل عميل جديد
    public function registerCustomer($data) {
        // التحقق من وجود العميل مسبقاً
        $stmt = $this->conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'error' => 'البريد الإلكتروني مسجل مسبقاً'];
        }
        
        $stmt = $this->conn->prepare("
            INSERT INTO customers 
            (name, email, phone, password, status, registration_date)
            VALUES (?, ?, ?, ?, 'active', NOW())
        ");
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bind_param(
            "ssss",
            $data['name'],
            $data['email'],
            $data['phone'],
            $hashedPassword
        );
        
        if ($stmt->execute()) {
            $customerId = $this->conn->insert_id;
            return [
                'success' => true,
                'customer_id' => $customerId,
                'message' => 'تم التسجيل بنجاح'
            ];
        }
        
        return ['success' => false, 'error' => 'حدث خطأ أثناء التسجيل'];
    }
    
    // تسجيل الدخول
    public function loginCustomer($email, $password) {
        $stmt = $this->conn->prepare("
            SELECT id, name, email, phone, password, status 
            FROM customers 
            WHERE email = ? AND status = 'active'
        ");
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'error' => 'البريد الإلكتروني غير مسجل'];
        }
        
        $customer = $result->fetch_assoc();
        
        if (password_verify($password, $customer['password'])) {
            // إنشاء رمز جلسة
            $token = bin2hex(random_bytes(32));
            
            // حفظ الرمز في قاعدة البيانات
            $stmt = $this->conn->prepare("
                INSERT INTO customer_sessions 
                (customer_id, token, expires_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
            ");
            
            $stmt->bind_param("is", $customer['id'], $token);
            $stmt->execute();
            
            unset($customer['password']);
            
            return [
                'success' => true,
                'token' => $token,
                'customer' => $customer
            ];
        }
        
        return ['success' => false, 'error' => 'كلمة المرور غير صحيحة'];
    }
    
    // جلب معلومات العميل
    public function getCustomer($customerId) {
        $stmt = $this->conn->prepare("
            SELECT id, name, email, phone, status, registration_date,
                   (SELECT COUNT(*) FROM orders WHERE customer_id = customers.id) as total_orders,
                   (SELECT SUM(total_amount) FROM orders WHERE customer_id = customers.id AND status = 'completed') as total_spent
            FROM customers 
            WHERE id = ?
        ");
        
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        
        if ($customer) {
            // جلب آخر الطلبات
            $stmt = $this->conn->prepare("
                SELECT * FROM orders 
                WHERE customer_id = ? 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            
            $stmt->bind_param("i", $customerId);
            $stmt->execute();
            
            $orders = $stmt->get_result();
            $customer['recent_orders'] = [];
            
            while ($order = $orders->fetch_assoc()) {
                $order['items'] = json_decode($order['items'], true);
                $customer['recent_orders'][] = $order;
            }
        }
        
        return $customer;
    }
    
    // تحديث معلومات العميل
    public function updateCustomer($customerId, $data) {
        $sql = "UPDATE customers SET ";
        $params = [];
        $types = "";
        
        foreach ($data as $key => $value) {
            if ($key !== 'password') {
                $sql .= "$key = ?, ";
                $params[] = $value;
                $types .= "s";
            }
        }
        
        $sql = rtrim($sql, ", ");
        $sql .= " WHERE id = ?";
        $types .= "i";
        $params[] = $customerId;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        return $stmt->execute();
    }
    
    // تغيير كلمة المرور
    public function changePassword($customerId, $currentPassword, $newPassword) {
        // التحقق من كلمة المرور الحالية
        $stmt = $this->conn->prepare("SELECT password FROM customers WHERE id = ?");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $customer = $result->fetch_assoc();
        
        if (!password_verify($currentPassword, $customer['password'])) {
            return ['success' => false, 'error' => 'كلمة المرور الحالية غير صحيحة'];
        }
        
        // تحديث كلمة المرور
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $customerId);
        
        return [
            'success' => $stmt->execute(),
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ];
    }
    
    // جلب جميع العملاء (للوحة التحكم)
    public function getAllCustomers($filters = []) {
        $sql = "SELECT * FROM customers WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $sql .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "sss";
        }
        
        $sql .= " ORDER BY registration_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            unset($row['password']);
            $customers[] = $row;
        }
        
        return $customers;
    }
}

$customersAPI = new CustomersAPI();

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $customer = $customersAPI->getCustomer($_GET['id']);
        echo json_encode($customer);
    } else {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $customers = $customersAPI->getAllCustomers($filters);
        echo json_encode($customers);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'login':
                $result = $customersAPI->loginCustomer($data['email'], $data['password']);
                echo json_encode($result);
                break;
                
            case 'update':
                $result = $customersAPI->updateCustomer($_GET['id'], $data);
                echo json_encode(['success' => $result]);
                break;
                
            case 'change_password':
                $result = $customersAPI->changePassword(
                    $_GET['id'], 
                    $data['current_password'], 
                    $data['new_password']
                );
                echo json_encode($result);
                break;
                
            default:
                $result = $customersAPI->registerCustomer($data);
                echo json_encode($result);
        }
    } else {
        $result = $customersAPI->registerCustomer($data);
        echo json_encode($result);
    }
}
?>