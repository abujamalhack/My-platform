<?php
require_once '../config.php';
require_once '../db_connection.php';

session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// جلب الإحصائيات
function getDashboardStats() {
    $stats = [];
    
    // إجمالي المبيعات
    $result = Database::fetchOne("
        SELECT SUM(total_amount) as total_sales 
        FROM orders 
        WHERE status = 'completed' 
        AND MONTH(created_at) = MONTH(CURRENT_DATE())
    ");
    $stats['monthly_sales'] = $result['total_sales'] ?? 0;
    
    // إجمالي الطلبات
    $result = Database::fetchOne("
        SELECT COUNT(*) as total_orders 
        FROM orders 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    ");
    $stats['monthly_orders'] = $result['total_orders'] ?? 0;
    
    // العملاء الجدد
    $result = Database::fetchOne("
        SELECT COUNT(*) as new_customers 
        FROM customers 
        WHERE MONTH(registration_date) = MONTH(CURRENT_DATE())
    ");
    $stats['new_customers'] = $result['new_customers'] ?? 0;
    
    // المنتجات
    $result = Database::fetchOne("
        SELECT COUNT(*) as total_products 
        FROM products 
        WHERE status = 'active'
    ");
    $stats['total_products'] = $result['total_products'] ?? 0;
    
    // الطلبات الحالية
    $result = Database::fetchOne("
        SELECT COUNT(*) as pending_orders 
        FROM orders 
        WHERE status IN ('pending', 'processing')
    ");
    $stats['pending_orders'] = $result['pending_orders'] ?? 0;
    
    // إحصائيات المنتجات المباعة
    $result = Database::fetchAll("
        SELECT p.name, COUNT(oi.product_id) as sold_count
        FROM products p
        LEFT JOIN (
            SELECT JSON_EXTRACT(items, '$[*].product_id') as product_id
            FROM orders
            WHERE status = 'completed'
        ) oi ON FIND_IN_SET(p.id, REPLACE(REPLACE(REPLACE(oi.product_id, '[', ''), ']', ''), '"', ''))
        GROUP BY p.id
        ORDER BY sold_count DESC
        LIMIT 10
    ");
    $stats['top_products'] = $result;
    
    return $stats;
}

// جلب الطلبات الحديثة
function getRecentOrders($limit = 10) {
    return Database::fetchAll("
        SELECT o.*, c.name as customer_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC
        LIMIT ?
    ", [$limit], 'i');
}

// جلب الإحصائيات الشهرية
function getMonthlyStats() {
    $stats = Database::fetchAll("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as orders_count,
            SUM(total_amount) as total_sales
        FROM orders
        WHERE status = 'completed'
        AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month
    ");
    
    return $stats;
}

// جلب الإحصائيات
$dashboardStats = getDashboardStats();
$recentOrders = getRecentOrders(5);
$monthlyStats = getMonthlyStats();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'stats' => $dashboardStats,
    'recent_orders' => $recentOrders,
    'monthly_stats' => $monthlyStats
], JSON_UNESCAPED_UNICODE);
?>