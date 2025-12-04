<?php
// index.php - الصفحة الرئيسية
require_once 'php/config.php';
$db = get_db();

// جلب المنتجات
$products = $db->query("
    SELECT * FROM products 
    WHERE is_active = 1 
    ORDER BY created_at DESC 
    LIMIT 8
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كاردي - متجر البطاقات الرقمية</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* إضافة أنماط مخصصة */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        .product-card {
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- هيكل الموقع -->
    <?php include 'partials/header.php'; ?>
    
    <main>
        <h1>مرحباً بك في متجر كاردي</h1>
        
        <div class="products-grid">
            <?php while ($product = $products->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="product-card">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p class="price"><?php echo format_price($product['price']); ?></p>
                <button onclick="addToCart(<?php echo $product['id']; ?>)">أضف إلى السلة</button>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
    
    <?php include 'partials/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
