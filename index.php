<?php
// index.php
require_once 'php/config.php';
$db = get_db();

// جلب المنتجات المميزة
$products = $db->query("
    SELECT * FROM products 
    WHERE is_active = 1 
    ORDER BY created_at DESC 
    LIMIT 8
");

// جلب الفئات
$categories = $db->query("
    SELECT * FROM categories 
    ORDER BY name ASC
");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كاردي - متجر البطاقات الرقمية</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 0 0 50px 50px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        .product-card {
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s;
            background: white;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 100%;
            height: 150px;
            background: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #667eea;
        }
        .price {
            color: #2ecc71;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-buy {
            background: #2ecc71;
        }
        .categories {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .category-btn {
            background: #f8f9fa;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            color: #333;
            border: 2px solid transparent;
        }
        .category-btn:hover {
            border-color: #3498db;
        }
    </style>
</head>
<body>
    <!-- الهيدر -->
    <header style="background: white; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="color: #667eea;">
                <i class="fas fa-credit-card"></i> كاردي
            </h1>
            <nav>
                <a href="index.php" style="margin: 0 15px; text-decoration: none; color: #333;">الرئيسية</a>
                <a href="products.php" style="margin: 0 15px; text-decoration: none; color: #333;">المنتجات</a>
                <a href="cart.php" style="margin: 0 15px; text-decoration: none; color: #333;">السلة</a>
                <a href="admin/" style="margin: 0 15px; text-decoration: none; color: #667eea; font-weight: bold;">لوحة التحكم</a>
            </nav>
        </div>
    </header>

    <!-- قسم البطل -->
    <section class="hero">
        <h1 style="font-size: 2.5rem; margin-bottom: 20px;">
            اشتر بطاقاتك الرقمية بأمان وسرعة فائقة
        </h1>
        <p style="font-size: 1.2rem; margin-bottom: 30px; max-width: 700px; margin: 0 auto 30px;">
            أكثر منصة لبيع البطاقات الرقمية في الوطن العربي. ألعاب، اشتراكات، منصات، بطاقات هدايا والكثير أكثر. توصيل فوري وآمن.
        </p>
        <a href="products.php" class="btn" style="padding: 15px 40px; font-size: 1.1rem;">
            <i class="fas fa-shopping-cart"></i> ابدأ التسوق الآن
        </a>
    </section>

    <!-- الفئات -->
    <section style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">✨ فئات المنتجات</h2>
        <div class="categories">
            <?php while ($category = $categories->fetchArray(SQLITE3_ASSOC)): ?>
            <a href="products.php?category=<?php echo urlencode($category['slug']); ?>" class="category-btn">
                <i class="fas fa-tag"></i> <?php echo htmlspecialchars($category['name']); ?>
            </a>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- المنتجات المميزة -->
    <section style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">🔥 منتجات مميزة</h2>
        <div class="products-grid">
            <?php while ($product = $products->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-gift"></i>
                </div>
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p style="color: #666; height: 60px; overflow: hidden;">
                    <?php echo htmlspecialchars($product['description']); ?>
                </p>
                <div class="price"><?php echo format_price($product['price']); ?></div>
                <div style="margin: 15px 0; color: #888;">
                    <i class="fas fa-cubes"></i> متوفر: <?php echo $product['stock']; ?>
                </div>
                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-buy">
                    <i class="fas fa-cart-plus"></i> أضف إلى السلة
                </button>
                <a href="product-details.php?id=<?php echo $product['id']; ?>" style="display: block; margin-top: 10px; color: #3498db;">
                    <i class="fas fa-eye"></i> تفاصيل
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            <a href="products.php" class="btn" style="padding: 12px 30px;">
                <i class="fas fa-list"></i> عرض جميع المنتجات
            </a>
        </div>
    </section>

    <!-- الإحصائيات -->
    <section style="background: #f8f9fa; padding: 60px 20px; margin-top: 60px;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; color: #667eea; font-weight: bold;">+50,000</div>
                <div style="color: #666;">عميل سعيد</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; color: #2ecc71; font-weight: bold;">+200</div>
                <div style="color: #666;">نوع بطاقة</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; color: #e74c3c; font-weight: bold;">24/7</div>
                <div style="color: #666;">دعم فني</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 2.5rem; color: #f39c12; font-weight: bold;">99%</div>
                <div style="color: #666;">رضا العملاء</div>
            </div>
        </div>
    </section>

    <!-- الفوتر -->
    <footer style="background: #2c3e50; color: white; padding: 40px 20px; margin-top: 60px;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
            <div>
                <h3><i class="fas fa-credit-card"></i> كاردي</h3>
                <p>متجر البطاقات الرقمية الأول في الوطن العربي</p>
            </div>
            <div>
                <h4>روابط سريعة</h4>
                <a href="index.php" style="color: #ecf0f1; display: block; margin: 5px 0;">الرئيسية</a>
                <a href="products.php" style="color: #ecf0f1; display: block; margin: 5px 0;">المنتجات</a>
                <a href="contact.php" style="color: #ecf0f1; display: block; margin: 5px 0;">اتصل بنا</a>
            </div>
            <div>
                <h4>التواصل</h4>
                <p><i class="fas fa-phone"></i> +966778657708</p>
                <p><i class="fas fa-envelope"></i> info@cardy.com</p>
                <p><i class="fas fa-whatsapp"></i> +967778657708</p>
            </div>
        </div>
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #34495e;">
            © 2024 كاردي. جميع الحقوق محفوظة.
        </div>
    </footer>

    <script>
        function addToCart(productId) {
            fetch('php/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تمت إضافة المنتج إلى السلة');
                    updateCartCount();
                } else {
                    alert('حدث خطأ: ' + data.message);
                }
            });
        }

        function updateCartCount() {
            // تحديث عداد السلة
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = parseInt(cartCount.textContent || 0) + 1;
            }
        }
    </script>
</body>
</html>
