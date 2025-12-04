<?php
require_once 'config.php';
require_once 'db_connection.php';

header('Content-Type: application/json; charset=utf-8');

class PaymentsAPI {
    private $conn;
    private $madaApiKey = 'YOUR_MADA_API_KEY';
    private $applePayMerchantId = 'YOUR_APPLE_PAY_ID';
    private $paypalClientId = 'YOUR_PAYPAL_CLIENT_ID';
    private $paypalSecret = 'YOUR_PAYPAL_SECRET';
    
    public function __construct() {
        $this->conn = Database::getConnection();
    }
    
    // إنشاء جلسة دفع
    public function createPaymentSession($orderId, $paymentMethod, $amount) {
        $paymentSessionId = 'PAY-' . bin2hex(random_bytes(16));
        
        $stmt = $this->conn->prepare("
            INSERT INTO payment_sessions 
            (session_id, order_id, payment_method, amount, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->bind_param("siss", $paymentSessionId, $orderId, $paymentMethod, $amount);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'session_id' => $paymentSessionId,
                'payment_url' => $this->getPaymentUrl($paymentMethod, $paymentSessionId, $amount)
            ];
        }
        
        return ['success' => false, 'error' => 'فشل في إنشاء جلسة الدفع'];
    }
    
    // معالجة دفع مدى
    public function processMadaPayment($sessionId, $cardData) {
        // هنا سيتم دمج مع بوابة مدى الرسمية
        // هذا مثال تجريبي
        
        $stmt = $this->conn->prepare("
            SELECT * FROM payment_sessions 
            WHERE session_id = ? AND status = 'pending'
        ");
        
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $session = $result->fetch_assoc();
        
        if (!$session) {
            return ['success' => false, 'error' => 'جلسة الدفع غير صالحة'];
        }
        
        // محاكاة عملية الدفع
        $paymentSuccess = true; // في الواقع، ستكون نتيجة من بوابة الدفع
        
        if ($paymentSuccess) {
            $transactionId = 'TXN-' . bin2hex(random_bytes(8));
            
            // تحديث حالة جلسة الدفع
            $stmt = $this->conn->prepare("
                UPDATE payment_sessions 
                SET status = 'completed', 
                    transaction_id = ?,
                    completed_at = NOW()
                WHERE session_id = ?
            ");
            
            $stmt->bind_param("ss", $transactionId, $sessionId);
            $stmt->execute();
            
            // تحديث حالة الطلب
            $stmt = $this->conn->prepare("
                UPDATE orders 
                SET payment_status = 'paid', 
                    transaction_id = ?,
                    status = 'processing'
                WHERE id = ?
            ");
            
            $stmt->bind_param("si", $transactionId, $session['order_id']);
            $stmt->execute();
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => 'تمت عملية الدفع بنجاح'
            ];
        } else {
            return ['success' => false, 'error' => 'فشلت عملية الدفع'];
        }
    }
    
    // معالجة دفع باي بال
    public function processPayPalPayment($sessionId, $paypalOrderId) {
        // محاكاة تأكيد دفع PayPal
        // في الواقع، ستستخدم PayPal SDK
        
        $stmt = $this->conn->prepare("
            UPDATE payment_sessions 
            SET status = 'completed', 
                transaction_id = ?,
                completed_at = NOW()
            WHERE session_id = ?
        ");
        
        $stmt->bind_param("ss", $paypalOrderId, $sessionId);
        $stmt->execute();
        
        return ['success' => true, 'transaction_id' => $paypalOrderId];
    }
    
    // معالجة تحويل بنكي
    public function processBankTransfer($sessionId, $transferData) {
        $stmt = $this->conn->prepare("
            UPDATE payment_sessions 
            SET status = 'pending_verification', 
                bank_receipt = ?,
                bank_name = ?,
                transfer_date = ?,
                updated_at = NOW()
            WHERE session_id = ?
        ");
        
        $stmt->bind_param(
            "ssss",
            $transferData['receipt_image'],
            $transferData['bank_name'],
            $transferData['transfer_date'],
            $sessionId
        );
        
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => 'تم تسجيل التحويل البنكي، في انتظار التحقق'
        ];
    }
    
    // التحقق من حالة الدفع
    public function checkPaymentStatus($sessionId) {
        $stmt = $this->conn->prepare("
            SELECT ps.*, o.order_number 
            FROM payment_sessions ps
            JOIN orders o ON ps.order_id = o.id
            WHERE ps.session_id = ?
        ");
        
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        
        return $payment;
    }
    
    // استرداد المدفوعات
    public function refundPayment($transactionId, $amount) {
        // تنفيذ عملية الاسترداد حسب طريقة الدفع
        // هذا مثال تجريبي
        
        $stmt = $this->conn->prepare("
            UPDATE payment_sessions 
            SET status = 'refunded', 
                refund_amount = ?,
                refund_date = NOW()
            WHERE transaction_id = ?
        ");
        
        $stmt->bind_param("ds", $amount, $transactionId);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'تم استرداد المبلغ بنجاح'];
    }
    
    // الحصول على رابط الدفع
    private function getPaymentUrl($method, $sessionId, $amount) {
        $baseUrl = 'https://yourdomain.com/payment/';
        
        switch ($method) {
            case 'mada':
                return $baseUrl . "mada/?session=$sessionId";
            case 'apple_pay':
                return $baseUrl . "apple-pay/?session=$sessionId";
            case 'paypal':
                return $baseUrl . "paypal/?session=$sessionId&amount=$amount";
            case 'stc_pay':
                return $baseUrl . "stc-pay/?session=$sessionId";
            default:
                return $baseUrl . "checkout/?session=$sessionId";
        }
    }
    
    // جلب تقارير المدفوعات
    public function getPaymentReports($filters = []) {
        $sql = "SELECT * FROM payment_sessions WHERE 1=1";
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
        
        if (!empty($filters['method'])) {
            $sql .= " AND payment_method = ?";
            $params[] = $filters['method'];
            $types .= "s";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        return $payments;
    }
}

$paymentsAPI = new PaymentsAPI();

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['session_id'])) {
        $status = $paymentsAPI->checkPaymentStatus($_GET['session_id']);
        echo json_encode($status);
    } elseif (isset($_GET['reports'])) {
        $filters = [
            'status' => $_GET['status'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'method' => $_GET['method'] ?? null
        ];
        
        $reports = $paymentsAPI->getPaymentReports($filters);
        echo json_encode($reports);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'create_session':
                $result = $paymentsAPI->createPaymentSession(
                    $data['order_id'],
                    $data['payment_method'],
                    $data['amount']
                );
                echo json_encode($result);
                break;
                
            case 'process_mada':
                $result = $paymentsAPI->processMadaPayment($data['session_id'], $data['card_data']);
                echo json_encode($result);
                break;
                
            case 'process_paypal':
                $result = $paymentsAPI->processPayPalPayment($data['session_id'], $data['paypal_order_id']);
                echo json_encode($result);
                break;
                
            case 'bank_transfer':
                $result = $paymentsAPI->processBankTransfer($data['session_id'], $data['transfer_data']);
                echo json_encode($result);
                break;
                
            case 'refund':
                $result = $paymentsAPI->refundPayment($data['transaction_id'], $data['amount']);
                echo json_encode($result);
                break;
        }
    }
}
?>