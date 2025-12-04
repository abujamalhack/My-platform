<?php
// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cardy_store');

// إعدادات الموقع
define('SITE_NAME', 'كاردي - متجر البطاقات الرقمية');
define('SITE_URL', 'https://cardy.com');
define('SITE_EMAIL', 'info@cardy.com');
define('SITE_PHONE', '+966778657708');
define('WHATSAPP_NUMBER', '+967778657708');
define('SUPPORT_EMAIL', 'support@cardy.com');

// إعدادات الأمان
define('JWT_SECRET', 'your_jwt_secret_key_here_2024_cardy_store');
define('ENCRYPTION_KEY', 'your_encryption_key_here_32_chars_long');
define('ALLOWED_IPS', ['127.0.0.1', '::1']);

// إعدادات الدفع
define('MADA_API_KEY', 'your_mada_api_key');
define('PAYPAL_CLIENT_ID', 'your_paypal_client_id');
define('PAYPAL_SECRET', 'your_paypal_secret');
define('STRIPE_PUBLIC_KEY', 'your_stripe_public_key');
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');

// إعدادات البريد الإلكتروني
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_email_password');
define('SMTP_SECURE', 'tls');

// إعدادات التطبيق
define('CURRENCY', 'SAR');
define('TAX_RATE', 15); // 15%
define('MIN_ORDER_AMOUNT', 10);
define('FREE_SHIPPING_AMOUNT', 200);

// إعدادات الجلسة
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// تصحيح الأخطاء (تعطيل في الإنتاج)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// منطقة التوقيت
date_default_timezone_set('Asia/Riyadh');

// الترميز
header('Content-Type: text/html; charset=utf-8');

// منع الوصول المباشر
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// دالة لحماية المدخلات
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// دالة لتوليد رمز عشوائي
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// دالة للتحقق من البريد الإلكتروني
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// دالة للتحقق من رقم الهاتف السعودي
function validate_saudi_phone($phone) {
    $pattern = '/^(009665|9665|\\+9665|05)([0-9]{8})$/';
    return preg_match($pattern, $phone);
}

// دالة لتشفير البيانات
function encrypt_data($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// دالة لفك تشفير البيانات
function decrypt_data($data) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

// دالة لتنسيق السعر
function format_price($price) {
    return number_format($price, 2, '.', ',') . ' ر.س';
}

// دالة لإرسال البريد الإلكتروني
function send_email($to, $subject, $message, $headers = '') {
    if (empty($headers)) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . SUPPORT_EMAIL . "\r\n";
    }
    
    return mail($to, $subject, $message, $headers);
}

// دالة للتحقق من صلاحيات المستخدم
function check_permission($required_role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $roles = ['support' => 1, 'manager' => 2, 'admin' => 3, 'super_admin' => 4];
    
    if (!isset($roles[$_SESSION['user_role']]) || !isset($roles[$required_role])) {
        return false;
    }
    
    return $roles[$_SESSION['user_role']] >= $roles[$required_role];
}

// دالة للسجلات
function log_activity($action, $description, $metadata = []) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO activity_logs 
        (user_id, user_type, action, description, metadata) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $user_id = $_SESSION['user_id'] ?? null;
    $user_type = isset($_SESSION['user_role']) ? 'admin' : 'customer';
    $meta_json = json_encode($metadata);
    
    $stmt->bind_param("issss", $user_id, $user_type, $action, $description, $meta_json);
    $stmt->execute();
}

// معالجة الطلبات CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    exit(0);
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
?>