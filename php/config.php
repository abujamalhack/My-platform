<?php
// php/config.php - إصدار معدل للعمل مع SQLite على Replit

// === إعدادات SQLite على Replit ===
define('DB_PATH', __DIR__ . '/../database/store.db');
define('DB_TYPE', 'sqlite');
define('SITE_ROOT', dirname(__DIR__));

// === إعدادات الموقع ===
define('SITE_NAME', 'كاردي - متجر البطاقات الرقمية');
define('SITE_URL', 'https://b940b34b-39c4-4c71-9325-f0825054be62-00-30hy451ztrgmp.sisko.replit.dev');
define('SITE_EMAIL', 'info@cardy.com');
define('SITE_PHONE', '+966778657708');
define('WHATSAPP_NUMBER', '+967778657708');
define('SUPPORT_EMAIL', 'support@cardy.com');

// === إعدادات التطبيق ===
define('CURRENCY', 'ريال');
define('TAX_RATE', 15);
define('MIN_ORDER_AMOUNT', 10);

// === إعدادات الجلسة ===
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// === تصحيح الأخطاء ===
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === منطقة التوقيت ===
date_default_timezone_set('Asia/Riyadh');

// === الترميز ===
header('Content-Type: text/html; charset=utf-8');

// === دوال المساعدة ===
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function format_price($price) {
    return number_format($price, 2) . ' ' . CURRENCY;
}

function get_db() {
    static $db = null;
    if ($db === null) {
        $db = new SQLite3(DB_PATH);
        $db->enableExceptions(true);
        $db->exec('PRAGMA foreign_keys = ON');
    }
    return $db;
}

// === CORS Headers ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
?>
