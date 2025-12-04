<?php
// admin/config.php - إعدادات لوحة التحكم

session_start();

// المسارات
define('ADMIN_ROOT', __DIR__);
define('SITE_ROOT', dirname(__DIR__));
define('DB_PATH', SITE_ROOT . '/database/store.db');

// بيانات الدخول الافتراضية
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // سيتم تشفيره

// وظائف التحقق
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function require_login() {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function admin_login($username, $password) {
    // كلمة المرور المشفرة (admin123)
    $hashed_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    
    if ($username === ADMIN_USERNAME && password_verify($password, $hashed_password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}

function admin_logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// الاتصال بقاعدة البيانات
function get_admin_db() {
    static $db = null;
    if ($db === null) {
        if (!file_exists(DB_PATH)) {
            die('قاعدة البيانات غير موجودة. قم بتشغيل الموقع الرئيسي أولاً.');
        }
        $db = new SQLite3(DB_PATH);
        $db->enableExceptions(true);
    }
    return $db;
}
?>
