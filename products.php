<?php
// products.php
require_once 'php/config.php';
$db = get_db();

// جلب الفئة إذا كانت محددة
$category_filter = '';
$category_name = 'جميع المنتجات';

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_slug = sanitize_input($_GET['category']);
    $category_filter = " WHERE category = '$category_slug'";
    
    // جلب اسم الفئة
    $cat_result = $db->querySingle("SELECT name FROM categories WHERE slug = '$category_slug'", true);
    if ($cat_result) {
        $category_name = $cat_result['name'];
    }
}

// جلب المنتجات
$products = $db->query("
    SELECT * FROM products 
    WHERE is_active = 1
    $category_filter
    ORDER BY created_at DESC
");

// جلب الفئات للفلتر
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category_name; ?> - كاردي</title>
    <style>
        /* نفس الأنماط السابقة */
        .filter-bar {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <header>...</header>
    
    <main style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h1><?php echo $category_name; ?></h1>
        
        <!-- فلتر الفئات -->
        <div class="filter-bar">
            <strong>الفئات:</strong>
            <a href="products.php" style="margin: 0 10px;">الكل</a>
            <?php while ($cat = $categories->fetchArray(SQLITE3_ASSOC)): ?>
            <a href="products.php?category=<?php echo urlencode($cat['slug']); ?>" 
               style="margin: 0 10px; <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['slug']) ? 'color: #3498db; font-weight: bold;' : ''; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
            </a>
            <?php endwhile; ?>
        </div>
        
        <!-- عرض المنتجات -->
        <div class="products-grid">
            <?php 
            $has_products = false;
            while ($product = $products->fetchArray(SQLITE3_ASSOC)): 
                $has_products = true;
            ?>
            <div class="product-card">
                <!-- كود المنتج -->
            </div>
            <?php endwhile; ?>
            
            <?php if (!$has_products): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                <i class="fas fa-box-open" style="font-size: 60px; color: #ddd;"></i>
                <h3>لا توجد منتجات في هذه الفئة</h3>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <footer>...</footer>
</body>
</html>
