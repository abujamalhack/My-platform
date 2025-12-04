<?php
// admin/index.php - لوحة التحكم الرئيسية
require_once 'config.php';
require_login();

$db = get_admin_db();

// جلب الإحصائيات
$products_count = $db->querySingle("SELECT COUNT(*) FROM products");
$orders_count = $db->querySingle("SELECT COUNT(*) FROM orders");
$customers_count = $db->querySingle("SELECT COUNT(*) FROM customers");
$revenue = $db->querySingle("SELECT SUM(total_amount) FROM orders WHERE status='completed'");

// جلب الطلبات الحديثة
$recent_orders = $db->query("
    SELECT o.id, o.order_number, c.name as customer, o.total_amount, o.status, o.created_at
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

// جلب المنتجات قليلة المخزون
$low_stock = $db->query("
    SELECT * FROM products 
    WHERE stock < 10 AND stock > 0
    ORDER BY stock ASC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | كاردي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fb;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #2c3e50, #34495e);
            color: white;
            height: 100vh;
            position: fixed;
            right: 0;
            top: 0;
            padding: 20px 0;
        }
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #3a506b;
        }
        .sidebar-header h2 {
            color: #ecf0f1;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        .sidebar-header p {
            color: #95a5a6;
            font-size: 0.9rem;
        }
        .nav-links {
            padding: 20px 0;
        }
        .nav-item {
            padding: 15px 25px;
            border-bottom: 1px solid #3a506b;
        }
        .nav-item a {
            color: #bdc3c7;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        .nav-item a i {
            margin-left: 10px;
            width: 20px;
        }
        .nav-item a:hover, .nav-item.active a {
            color: white;
            padding-right: 10px;
        }
        .main-content {
            margin-right: 250px;
            padding: 30px;
            width: 100%;
        }
        .header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #2c3e50;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-left: 20px;
            color: white;
        }
        .stat-info h3 {
            color: #666;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .card-header h2 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: right;
            color: #666;
            border-bottom: 2px solid #eee;
        }
        table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #2ecc71; color: white; }
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- الشريط الجانبي -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>🚀 كاردي</h2>
            <p>لوحة التحكم</p>
        </div>
        
        <div class="nav-links">
            <div class="nav-item active">
                <a href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>الرئيسية</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="products.php">
                    <i class="fas fa-box"></i>
                    <span>المنتجات</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>الطلبات</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="customers.php">
                    <i class="fas fa-users"></i>
                    <span>العملاء</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="payments.php">
                    <i class="fas fa-credit-card"></i>
                    <span>المدفوعات</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="../" target="_blank">
                    <i class="fas fa-store"></i>
                    <span>زيارة المتجر</span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>تسجيل الخروج</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <!-- رأس الصفحة -->
        <div class="header">
            <h1>مرحباً بك، <?php echo $_SESSION['admin_username']; ?> 👋</h1>
            <div class="user-info">
                <div>
                    <strong>آخر دخول:</strong>
                    <p style="color: #666; font-size: 0.9rem;"><?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                </a>
            </div>
        </div>
        
        <!-- إحصائيات سريعة -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>المنتجات</h3>
                    <div class="stat-number"><?php echo $products_count; ?></div>
                    <p style="color: #666; font-size: 0.9rem;">منتج متوفر</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3>الطلبات</h3>
                    <div class="stat-number"><?php echo $orders_count; ?></div>
                    <p style="color: #666; font-size: 0.9rem;">طلب جديد</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>العملاء</h3>
                    <div class="stat-number"><?php echo $customers_count; ?></div>
                    <p style="color: #666; font-size: 0.9rem;">عميل مسجل</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h3>الإيرادات</h3>
                    <div class="stat-number"><?php echo number_format($revenue ?: 0, 2); ?> ريال</div>
                    <p style="color: #666; font-size: 0.9rem;">إجمالي المبيعات</p>
                </div>
            </div>
        </div>
        
        <!-- الطلبات الحديثة -->
        <div class="card">
            <div class="card-header">
                <h2>📦 أحدث الطلبات</h2>
                <a href="orders.php" class="btn btn-primary">عرض الكل</a>
            </div>
            
            <?php if ($orders_count > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>العميل</th>
                        <th>المبلغ</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php echo $order['customer'] ?: 'زائر'; ?></td>
                        <td><?php echo number_format($order['total_amount'], 2); ?> ريال</td>
                        <td>
                            <span class="status status-<?php echo $order['status']; ?>">
                                <?php 
                                $statuses = [
                                    'pending' => 'قيد الانتظار',
                                    'completed' => 'مكتمل',
                                    'cancelled' => 'ملغي'
                                ];
                                echo $statuses[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">
                    <i class="fas fa-info-circle"></i> لا توجد طلبات بعد
                </p>
            <?php endif; ?>
        </div>
        
        <!-- المنتجات قليلة المخزون -->
        <?php if ($low_stock && $low_stock->fetchArray(SQLITE3_ASSOC) !== false): ?>
        <div class="card">
            <div class="card-header">
                <h2>⚠️ منتجات قليلة المخزون</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>اسم المنتج</th>
                        <th>الفئة</th>
                        <th>السعر</th>
                        <th>المخزون</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $low_stock->reset();
                    while ($product = $low_stock->fetchArray(SQLITE3_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo $product['category']; ?></td>
                        <td><?php echo number_format($product['price'], 2); ?> ريال</td>
                        <td>
                            <span style="color: <?php echo $product['stock'] < 5 ? '#e74c3c' : '#f39c12'; ?>; font-weight: bold;">
                                <?php echo $product['stock']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- إجراءات سريعة -->
        <div class="card">
            <div class="card-header">
                <h2>⚡ إجراءات سريعة</h2>
            </div>
            
            <div class="quick-actions">
                <a href="products.php?action=add" class="btn btn-success">
                    <i class="fas fa-plus"></i> إضافة منتج جديد
                </a>
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> عرض جميع الطلبات
                </a>
                <a href="customers.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> إدارة العملاء
                </a>
                <a href="../" target="_blank" class="btn">
                    <i class="fas fa-eye"></i> معاينة المتجر
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // تحديث الوقت الحي
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = 
                now.toLocaleTimeString('ar-SA');
        }
        setInterval(updateTime, 1000);
    </script>
</body>
</html>
