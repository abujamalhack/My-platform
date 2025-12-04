// ===== بيانات المنتجات الكاملة =====
const productsDatabase = [
    // شحن الألعاب
    {
        id: 1,
        name: "Xena Live 70,000 Coins",
        price: 37.50,
        oldPrice: 45.00,
        category: "games",
        subCategory: "xena",
        image: "xena-70k",
        description: "رصيد Xena Live 70,000 عملة للعبة Xena Live",
        features: ["توصيل فوري", "ضمان استرجاع", "دعم فني 24/7"],
        deliveryTime: "فوري",
        stock: 50,
        featured: true,
        discount: 17,
        tags: ["العاب", "اكثر مبيعاً", "تخفيض"]
    },
    {
        id: 2,
        name: "Xena Live 1,511,000 Coins",
        price: 750.00,
        oldPrice: 850.00,
        category: "games",
        subCategory: "xena",
        image: "xena-1m",
        description: "رصيد Xena Live 1,511,000 عملة مع مزايا إضافية",
        features: ["توصيل فوري", "مكافأة مجانية", "دعم متقدم"],
        deliveryTime: "فوري",
        stock: 25,
        featured: true,
        discount: 12,
        tags: ["العاب", "عروض خاصة", "جديد"]
    },
    {
        id: 3,
        name: "Steam Wallet 50$",
        price: 187.50,
        oldPrice: 200.00,
        category: "games",
        subCategory: "steam",
        image: "steam-50",
        description: "بطاقة Steam Wallet بقيمة 50 دولار أمريكي",
        features: ["توصيل فوري", "متوافق عالمياً", "دعم فني"],
        deliveryTime: "فوري",
        stock: 100,
        featured: true,
        discount: 6,
        tags: ["العاب", "ستيم", "بطاقات"]
    },
    {
        id: 4,
        name: "PlayStation Network 100$",
        price: 375.00,
        oldPrice: 400.00,
        category: "games",
        subCategory: "playstation",
        image: "psn-100",
        description: "بطاقة PlayStation Network بقيمة 100 دولار",
        features: ["توصيل فوري", "للألعاب والاشتراكات", "دعم فني"],
        deliveryTime: "فوري",
        stock: 40,
        featured: false,
        discount: 6,
        tags: ["العاب", "بلايستيشن", "عروض"]
    },
    {
        id: 5,
        name: "Xbox Live 50$",
        price: 187.50,
        oldPrice: 200.00,
        category: "games",
        subCategory: "xbox",
        image: "xbox-50",
        description: "بطاقة Xbox Live بقيمة 50 دولار",
        features: ["توصيل فوري", "للألعاب والاشتراكات", "دعم فني"],
        deliveryTime: "فوري",
        stock: 60,
        featured: true,
        discount: 6,
        tags: ["العاب", "اكس بوكس", "بطاقات"]
    },
    
    // الاشتراكات
    {
        id: 6,
        name: "Netflix اشتراك 3 أشهر",
        price: 149.99,
        oldPrice: 180.00,
        category: "subscriptions",
        subCategory: "streaming",
        image: "netflix-3m",
        description: "اشتراك Netflix Premium لمدة 3 أشهر",
        features: ["عرض خاص", "4K Ultra HD", "4 شاشات متزامنة"],
        deliveryTime: "5 دقائق",
        stock: 200,
        featured: true,
        discount: 17,
        tags: ["اشتراكات", "نتفليكس", "عروض"]
    },
    {
        id: 7,
        name: "Spotify Premium سنة",
        price: 239.99,
        oldPrice: 299.99,
        category: "subscriptions",
        subCategory: "music",
        image: "spotify-year",
        description: "اشتراك Spotify Premium لمدة سنة كاملة",
        features: ["موسيقى بدون إعلانات", "تنزيل للأوفلاين", "جودة عالية"],
        deliveryTime: "10 دقائق",
        stock: 150,
        featured: true,
        discount: 20,
        tags: ["اشتراكات", "سبوتيفاي", "موسيقى"]
    },
    {
        id: 8,
        name: "Shahid VIP 6 أشهر",
        price: 199.99,
        oldPrice: 240.00,
        category: "subscriptions",
        subCategory: "arabic",
        image: "shahid-6m",
        description: "اشتراك Shahid VIP لمدة 6 أشهر",
        features: ["محتويات حصرية", "بدون إعلانات", "جودة 4K"],
        deliveryTime: "فوري",
        stock: 120,
        featured: false,
        discount: 17,
        tags: ["اشتراكات", "شاهد", "عربي"]
    },
    {
        id: 9,
        name: "Apple Music 3 أشهر",
        price: 59.99,
        oldPrice: 74.99,
        category: "subscriptions",
        subCategory: "music",
        image: "apple-music",
        description: "اشتراك Apple Music لمدة 3 أشهر",
        features: ["مكتبة ضخمة", "لا يوجد إعلانات", "جودة Lossless"],
        deliveryTime: "فوري",
        stock: 180,
        featured: true,
        discount: 20,
        tags: ["اشتراكات", "ابل", "موسيقى"]
    },
    
    // بطاقات التسوق
    {
        id: 10,
        name: "بطاقة Apple 100$",
        price: 375.00,
        oldPrice: 400.00,
        category: "shopping",
        subCategory: "apple",
        image: "apple-100",
        description: "بطاقة Apple Store بقيمة 100 دولار أمريكي",
        features: ["توصيل فوري", "صالح لجميع منتجات Apple", "بدون انتهاء"],
        deliveryTime: "فوري",
        stock: 80,
        featured: true,
        discount: 6,
        tags: ["تسوق", "ابل", "بطاقات هدايا"]
    },
    {
        id: 11,
        name: "بطاقة Google Play 50$",
        price: 187.50,
        oldPrice: 200.00,
        category: "shopping",
        subCategory: "google",
        image: "google-play-50",
        description: "بطاقة Google Play بقيمة 50 دولار أمريكي",
        features: ["توصيل فوري", "للتطبيقات والألعاب", "بدون انتهاء"],
        deliveryTime: "فوري",
        stock: 200,
        featured: true,
        discount: 6,
        tags: ["تسوق", "جوجل", "تطبيقات"]
    },
    {
        id: 12,
        name: "بطاقة Amazon 50$",
        price: 187.50,
        oldPrice: 200.00,
        category: "shopping",
        subCategory: "amazon",
        image: "amazon-50",
        description: "بطاقة Amazon بقيمة 50 دولار أمريكي",
        features: ["توصيل فوري", "للشراء من Amazon.com", "بدون انتهاء"],
        deliveryTime: "فوري",
        stock: 150,
        featured: false,
        discount: 6,
        tags: ["تسوق", "امازون", "عالمي"]
    },
    {
        id: 13,
        name: "بطاقة Nike 100$",
        price: 375.00,
        oldPrice: 400.00,
        category: "shopping",
        subCategory: "nike",
        image: "nike-100",
        description: "بطاقة Nike بقيمة 100 دولار أمريكي",
        features: ["توصيل فوري", "لشراء منتجات Nike", "بدون انتهاء"],
        deliveryTime: "فوري",
        stock: 60,
        featured: true,
        discount: 6,
        tags: ["تسوق", "نايك", "رياضة"]
    },
    
    // شحن الاتصالات
    {
        id: 14,
        name: "شحن STC 50 ريال",
        price: 50.00,
        category: "mobile",
        subCategory: "stc",
        image: "stc-50",
        description: "رصيد STC بقيمة 50 ريال سعودي",
        features: ["توصيل فوري", "لجميع باقات STC", "ضمان الفعالية"],
        deliveryTime: "فوري",
        stock: 500,
        featured: false,
        tags: ["اتصالات", "STC", "شحن"]
    },
    {
        id: 15,
        name: "شحن Mobily 100 ريال",
        price: 100.00,
        category: "mobile",
        subCategory: "mobily",
        image: "mobily-100",
        description: "رصيد Mobily بقيمة 100 ريال سعودي",
        features: ["توصيل فوري", "لجميع باقات Mobily", "ضمان الفعالية"],
        deliveryTime: "فوري",
        stock: 300,
        featured: true,
        tags: ["اتصالات", "موبايلي", "شحن"]
    },
    {
        id: 16,
        name: "شحن Zain 50 ريال",
        price: 50.00,
        category: "mobile",
        subCategory: "zain",
        image: "zain-50",
        description: "رصيد Zain بقيمة 50 ريال سعودي",
        features: ["توصيل فوري", "لجميع باقات Zain", "ضمان الفعالية"],
        deliveryTime: "فوري",
        stock: 400,
        featured: false,
        tags: ["اتصالات", "زين", "شحن"]
    }
];

// ===== سلة التسوق =====
let shoppingCart = JSON.parse(localStorage.getItem('cardy_cart')) || [];
let user = JSON.parse(localStorage.getItem('cardy_user')) || null;

// ===== تهيئة الصفحة الرئيسية =====
document.addEventListener('DOMContentLoaded', function() {
    // تهيئة المكونات
    initNavbar();
    loadFeaturedProducts();
    initSearch();
    initCart();
    initBackToTop();
    initNotifications();
    
    // تحديث عداد السلة
    updateCartCount();
    
    // عرض إشعار الترحيب
    if (!localStorage.getItem('cardy_welcome_shown')) {
        setTimeout(() => {
            showNotification('مرحباً بك في كاردي! استمتع بتجربة تسوق رقمية فريدة 🎉', 'success');
            localStorage.setItem('cardy_welcome_shown', 'true');
        }, 1000);
    }
});

// ===== تهيئة شريط التنقل =====
function initNavbar() {
    const navbar = document.querySelector('.custom-navbar');
    const cartIcon = document.querySelector('.cart-icon');
    
    // إضافة تأثير التمرير
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // تأثير عند مرور الماوس على أيقونة السلة
    if (cartIcon) {
        cartIcon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });
        
        cartIcon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    }
    
    // تهيئة القوائم المنسدلة
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', function() {
            this.querySelector('.dropdown-menu').classList.add('show');
        });
        
        dropdown.addEventListener('mouseleave', function() {
            this.querySelector('.dropdown-menu').classList.remove('show');
        });
    });
}

// ===== تحميل المنتجات المميزة =====
function loadFeaturedProducts() {
    const container = document.getElementById('featured-products-container');
    if (!container) return;
    
    const featuredProducts = productsDatabase.filter(product => product.featured);
    
    container.innerHTML = featuredProducts.map(product => `
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="product-card animate-fade-in">
                <div class="product-image position-relative">
                    <div class="product-badge">
                        ${product.discount ? `خصم ${product.discount}%` : 'الأكثر مبيعاً'}
                    </div>
                    <i class="fas ${getCategoryIcon(product.category)}"></i>
                </div>
                <div class="product-content">
                    <h5 class="product-title">${product.name}</h5>
                    <p class="product-description">${product.description}</p>
                    
                    <div class="product-price">
                        <span class="current-price">${product.price.toFixed(2)} ر.س</span>
                        ${product.oldPrice ? `
                            <span class="original-price">${product.oldPrice.toFixed(2)} ر.س</span>
                            <span class="discount">-${product.discount}%</span>
                        ` : ''}
                    </div>
                    
                    <div class="product-meta">
                        <span class="product-category">${getCategoryName(product.category)}</span>
                        <span class="product-delivery">
                            <i class="fas fa-bolt"></i>
                            ${product.deliveryTime}
                        </span>
                    </div>
                    
                    <div class="product-features">
                        ${product.features.slice(0, 2).map(feature => `
                            <span class="feature-badge">
                                <i class="fas fa-check-circle"></i>
                                ${feature}
                            </span>
                        `).join('')}
                    </div>
                    
                    <div class="product-actions">
                        <button class="btn btn-primary w-100 add-to-cart" data-id="${product.id}">
                            <i class="fas fa-cart-plus me-2"></i>
                            أضف إلى السلة
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
    
    // إضافة مستمعي الأحداث للأزرار
    container.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.dataset.id);
            addToCart(productId);
        });
    });
}

// ===== وظيفة المساعدة: الحصول على أيقونة الفئة =====
function getCategoryIcon(category) {
    const icons = {
        'games': 'fa-gamepad',
        'subscriptions': 'fa-film',
        'shopping': 'fa-shopping-bag',
        'mobile': 'fa-mobile-alt'
    };
    return icons[category] || 'fa-gift';
}

// ===== وظيفة المساعدة: الحصول على اسم الفئة =====
function getCategoryName(category) {
    const names = {
        'games': 'شحن الألعاب',
        'subscriptions': 'الاشتراكات',
        'shopping': 'بطاقات التسوق',
        'mobile': 'شحن الاتصالات'
    };
    return names[category] || 'منتجات رقمية';
}

// ===== إدارة سلة التسوق =====
function initCart() {
    // تحديث السلة في كل مرة يتم فيها تحميل الصفحة
    updateCartCount();
    
    // إضافة مستمعي الأحداث لأزرار إضافة المنتجات
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart') || 
            e.target.closest('.add-to-cart')) {
            const button = e.target.classList.contains('add-to-cart') ? 
                e.target : e.target.closest('.add-to-cart');
            const productId = parseInt(button.dataset.id);
            addToCart(productId);
        }
        
        // حذف منتج من السلة
        if (e.target.classList.contains('remove-from-cart') || 
            e.target.closest('.remove-from-cart')) {
            const button = e.target.classList.contains('remove-from-cart') ? 
                e.target : e.target.closest('.remove-from-cart');
            const productId = parseInt(button.dataset.id);
            removeFromCart(productId);
        }
        
        // زيادة الكمية
        if (e.target.classList.contains('increase-quantity') || 
            e.target.closest('.increase-quantity')) {
            const button = e.target.classList.contains('increase-quantity') ? 
                e.target : e.target.closest('.increase-quantity');
            const productId = parseInt(button.dataset.id);
            updateQuantity(productId, 1);
        }
        
        // تقليل الكمية
        if (e.target.classList.contains('decrease-quantity') || 
            e.target.closest('.decrease-quantity')) {
            const button = e.target.classList.contains('decrease-quantity') ? 
                e.target : e.target.closest('.decrease-quantity');
            const productId = parseInt(button.dataset.id);
            updateQuantity(productId, -1);
        }
    });
}

// ===== إضافة منتج إلى السلة =====
function addToCart(productId) {
    const product = productsDatabase.find(p => p.id === productId);
    if (!product) {
        showNotification('المنتج غير متوفر!', 'error');
        return;
    }
    
    // التحقق من المخزون
    if (product.stock <= 0) {
        showNotification('نفذت الكمية من هذا المنتج!', 'error');
        return;
    }
    
    const existingItem = shoppingCart.find(item => item.id === productId);
    
    if (existingItem) {
        // التحقق من عدم تجاوز المخزون
        if (existingItem.quantity >= product.stock) {
            showNotification(`لا يمكن إضافة أكثر من ${product.stock} وحدة!`, 'warning');
            return;
        }
        existingItem.quantity += 1;
    } else {
        shoppingCart.push({
            ...product,
            quantity: 1
        });
    }
    
    // حفظ في التخزين المحلي
    saveCart();
    
    // تحديث العداد
    updateCartCount();
    
    // عرض إشعار النجاح
    showNotification(`تم إضافة ${product.name} إلى السلة! 🛒`, 'success');
    
    // تأثير اهتزاز للسلة
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        cartIcon.style.transform = 'scale(1.2)';
        setTimeout(() => {
            cartIcon.style.transform = 'scale(1)';
        }, 300);
    }
}

// ===== حذف منتج من السلة =====
function removeFromCart(productId) {
    const index = shoppingCart.findIndex(item => item.id === productId);
    
    if (index !== -1) {
        const productName = shoppingCart[index].name;
        shoppingCart.splice(index, 1);
        saveCart();
        updateCartCount();
        showNotification(`تم حذف ${productName} من السلة`, 'info');
        
        // إذا كنا في صفحة السلة، نقوم بتحديث العرض
        if (window.location.pathname.includes('cart.html')) {
            loadCartItems();
        }
    }
}

// ===== تحديث كمية المنتج =====
function updateQuantity(productId, change) {
    const item = shoppingCart.find(item => item.id === productId);
    
    if (item) {
        const newQuantity = item.quantity + change;
        
        // التحقق من الحد الأدنى والأقصى
        if (newQuantity < 1) {
            removeFromCart(productId);
            return;
        }
        
        // التحقق من المخزون
        const product = productsDatabase.find(p => p.id === productId);
        if (newQuantity > product.stock) {
            showNotification(`الكمية المتاحة فقط ${product.stock} وحدة!`, 'warning');
            return;
        }
        
        item.quantity = newQuantity;
        saveCart();
        updateCartCount();
        
        // تحديث السعر الإجمالي في صفحة السلة
        if (window.location.pathname.includes('cart.html')) {
            updateCartTotal();
        }
    }
}

// ===== حفظ السلة في التخزين المحلي =====
function saveCart() {
    localStorage.setItem('cardy_cart', JSON.stringify(shoppingCart));
}

// ===== تحديث عداد السلة =====
function updateCartCount() {
    const cartCountElements = document.querySelectorAll('.cart-count');
    const totalItems = shoppingCart.reduce((sum, item) => sum + item.quantity, 0);
    
    cartCountElements.forEach(element => {
        element.textContent = totalItems;
        element.style.display = totalItems > 0 ? 'flex' : 'none';
    });
}

// ===== الحصول على إجمالي سعر السلة =====
function getCartTotal() {
    return shoppingCart.reduce((total, item) => {
        return total + (item.price * item.quantity);
    }, 0);
}

// ===== تهيئة البحث =====
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');
    
    if (!searchInput || !searchResults) return;
    
    searchInput.addEventListener('input', function(e) {
        const searchTerm = e.target.value.trim().toLowerCase();
        
        if (searchTerm.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        const results = productsDatabase.filter(product => 
            product.name.toLowerCase().includes(searchTerm) ||
            product.description.toLowerCase().includes(searchTerm) ||
            product.tags.some(tag => tag.toLowerCase().includes(searchTerm))
        ).slice(0, 5); // عرض أول 5 نتائج فقط
        
        if (results.length > 0) {
            searchResults.innerHTML = results.map(product => `
                <div class="search-result-item" data-id="${product.id}">
                    <div class="search-result-content">
                        <h6>${product.name}</h6>
                        <p class="text-muted">${product.description.substring(0, 50)}...</p>
                        <span class="price">${product.price.toFixed(2)} ر.س</span>
                    </div>
                </div>
            `).join('');
            
            searchResults.style.display = 'block';
            
            // إضافة مستمعي الأحداث للنتائج
            searchResults.querySelectorAll('.search-result-item').forEach(item => {
                item.addEventListener('click', function() {
                    const productId = parseInt(this.dataset.id);
                    const product = productsDatabase.find(p => p.id === productId);
                    
                    if (product) {
                        // عرض تفاصيل المنتج
                        showProductModal(product);
                        searchInput.value = '';
                        searchResults.style.display = 'none';
                    }
                });
            });
        } else {
            searchResults.innerHTML = `
                <div class="search-no-results">
                    <i class="fas fa-search"></i>
                    <p>لا توجد نتائج لـ "${searchTerm}"</p>
                </div>
            `;
            searchResults.style.display = 'block';
        }
    });
    
    // إخفاء النتائج عند النقر خارجها
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    
    // إرسال البحث عند الضغط على Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && this.value.trim().length >= 2) {
            window.location.href = `products.html?search=${encodeURIComponent(this.value)}`;
        }
    });
}

// ===== عرض نافذة منتج =====
function showProductModal(product) {
    // إنشاء نافذة المنتج
    const modalHTML = `
        <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${product.name}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="product-modal-image">
                                    <i class="fas ${getCategoryIcon(product.category)}"></i>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="product-modal-details">
                                    <h4>${product.name}</h4>
                                    <p class="text-muted">${product.description}</p>
                                    
                                    <div class="price-section mb-3">
                                        <span class="current-price">${product.price.toFixed(2)} ر.س</span>
                                        ${product.oldPrice ? `
                                            <span class="original-price">${product.oldPrice.toFixed(2)} ر.س</span>
                                            <span class="discount-badge">وفر ${product.discount}%</span>
                                        ` : ''}
                                    </div>
                                    
                                    <div class="features-list mb-3">
                                        <h6>المميزات:</h6>
                                        ${product.features.map(feature => `
                                            <div class="feature-item">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <span>${feature}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                    
                                    <div class="stock-info mb-3">
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i>
                                            ${product.stock > 10 ? 'متوفر' : 'كمية محدودة'}
                                        </span>
                                        <span class="delivery-time">
                                            <i class="fas fa-shipping-fast"></i>
                                            ${product.deliveryTime}
                                        </span>
                                    </div>
                                    
                                    <div class="quantity-selector mb-4">
                                        <label class="form-label">الكمية:</label>
                                        <div class="input-group" style="max-width: 150px;">
                                            <button class="btn btn-outline-secondary decrease-qty" type="button">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" value="1" min="1" max="${product.stock}" id="productQuantity">
                                            <button class="btn btn-outline-secondary increase-qty" type="button">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <button class="btn btn-primary w-100 btn-lg add-to-cart-modal" data-id="${product.id}">
                                        <i class="fas fa-cart-plus me-2"></i>
                                        أضف إلى السلة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // إضافة النافذة إلى الصفحة
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);
    
    // عرض النافذة
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
    
    // إضافة مستمعي الأحداث
    const modalElement = document.getElementById('productModal');
    
    // زيادة الكمية
    modalElement.querySelector('.increase-qty').addEventListener('click', function() {
        const input = modalElement.querySelector('#productQuantity');
        const max = parseInt(input.max);
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
        }
    });
    
    // تقليل الكمية
    modalElement.querySelector('.decrease-qty').addEventListener('click', function() {
        const input = modalElement.querySelector('#productQuantity');
        const min = parseInt(input.min);
        if (parseInt(input.value) > min) {
            input.value = parseInt(input.value) - 1;
        }
    });
    
    // إضافة إلى السلة من النافذة
    modalElement.querySelector('.add-to-cart-modal').addEventListener('click', function() {
        const productId = parseInt(this.dataset.id);
        const quantity = parseInt(modalElement.querySelector('#productQuantity').value);
        
        // إضافة الكمية المطلوبة
        for (let i = 0; i < quantity; i++) {
            addToCart(productId);
        }
        
        modal.hide();
        
        // تنظيف النافذة بعد الإغلاق
        modalElement.addEventListener('hidden.bs.modal', function() {
            modalContainer.remove();
        });
    });
}

// ===== تهيئة زر العودة للأعلى =====
function initBackToTop() {
    const backToTopButton = document.getElementById('backToTop');
    
    if (!backToTopButton) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });
    
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// ===== نظام الإشعارات =====
function initNotifications() {
    // إنشاء حاوية الإشعارات
    const notificationContainer = document.createElement('div');
    notificationContainer.id = 'notification-container';
    notificationContainer.style.cssText = `
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 9999;
        max-width: 400px;
    `;
    document.body.appendChild(notificationContainer);
}

// ===== عرض الإشعار =====
function showNotification(message, type = 'info') {
    const types = {
        'success': { icon: 'fa-check-circle', color: '#10b981', bg: '#d1fae5' },
        'error': { icon: 'fa-exclamation-circle', color: '#ef4444', bg: '#fee2e2' },
        'warning': { icon: 'fa-exclamation-triangle', color: '#f59e0b', bg: '#fef3c7' },
        'info': { icon: 'fa-info-circle', color: '#3b82f6', bg: '#dbeafe' }
    };
    
    const notificationType = types[type] || types.info;
    
    // إنشاء الإشعار
    const notification = document.createElement('div');
    notification.className = 'notification alert-dismissible fade show';
    notification.style.cssText = `
        background: ${notificationType.bg};
        color: #1f2937;
        border: none;
        border-right: 4px solid ${notificationType.color};
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        animation: slideInLeft 0.3s ease-out;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        max-width: 400px;
    `;
    
    notification.innerHTML = `
        <i class="fas ${notificationType.icon}" style="color: ${notificationType.color}; font-size: 1.25rem;"></i>
        <div style="flex: 1;">${message}</div>
        <button type="button" class="btn-close" style="padding: 0.5rem; font-size: 0.75rem;"></button>
    `;
    
    // إضافة الإشعار للحاوية
    const container = document.getElementById('notification-container');
    container.appendChild(notification);
    
    // إغلاق الإشعار تلقائياً بعد 5 ثوانٍ
    const autoClose = setTimeout(() => {
        closeNotification(notification);
    }, 5000);
    
    // إغلاق الإشعار عند النقر على زر الإغلاق
    const closeBtn = notification.querySelector('.btn-close');
    closeBtn.addEventListener('click', () => {
        clearTimeout(autoClose);
        closeNotification(notification);
    });
    
    // إغلاق الإشعار عند النقر عليه
    notification.addEventListener('click', (e) => {
        if (!e.target.classList.contains('btn-close')) {
            clearTimeout(autoClose);
            closeNotification(notification);
        }
    });
}

// ===== إغلاق الإشعار =====
function closeNotification(notification) {
    notification.style.animation = 'slideOutLeft 0.3s ease-out';
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

// إضافة أنيميشن للإغلاق
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOutLeft {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(-100%);
        }
    }
`;
document.head.appendChild(style);

// ===== إدارة المستخدم =====
function loginUser(email, password) {
    // في الواقع، هنا يجب الاتصال بالخادم
    // لكننا سنستخدم التخزين المحلي للتوضيح
    
    const userData = {
        id: 1,
        name: "مستخدم كاردي",
        email: email,
        phone: "+966500000000",
        createdAt: new Date().toISOString()
    };
    
    user = userData;
    localStorage.setItem('cardy_user', JSON.stringify(userData));
    showNotification('تم تسجيل الدخول بنجاح! 👋', 'success');
    
    return userData;
}

function logoutUser() {
    user = null;
    localStorage.removeItem('cardy_user');
    showNotification('تم تسجيل الخروج بنجاح', 'info');
}

// ===== الدفع والطلبات =====
function createOrder(paymentMethod, customerInfo) {
    if (shoppingCart.length === 0) {
        showNotification('السلة فارغة! أضف منتجات قبل إنشاء الطلب', 'warning');
        return null;
    }
    
    const orderId = 'ORD-' + Date.now().toString().slice(-8);
    const order = {
        id: orderId,
        items: [...shoppingCart],
        total: getCartTotal(),
        customer: customerInfo,
        paymentMethod: paymentMethod,
        status: 'pending',
        createdAt: new Date().toISOString(),
        deliveryMethod: 'digital'
    };
    
    // حفظ الطلب
    const orders = JSON.parse(localStorage.getItem('cardy_orders')) || [];
    orders.push(order);
    localStorage.setItem('cardy_orders', JSON.stringify(orders));
    
    // إفراغ السلة بعد إنشاء الطلب
    shoppingCart = [];
    saveCart();
    updateCartCount();
    
    return order;
}

// ===== وظائف عامة =====
function formatCurrency(amount) {
    return amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,') + ' ر.س';
}

function generateOrderNumber() {
    return 'CARDY-' + Math.random().toString(36).substr(2, 9).toUpperCase();
}

// ===== تصدير الوظائف للاستخدام في الملفات الأخرى =====
window.Cardy = {
    // البيانات
    products: productsDatabase,
    cart: shoppingCart,
    user: user,
    
    // الوظائف الأساسية
    addToCart,
    removeFromCart,
    updateQuantity,
    getCartTotal,
    updateCartCount,
    
    // الإشعارات
    showNotification,
    
    // المستخدم
    loginUser,
    logoutUser,
    
    // الطلبات
    createOrder,
    formatCurrency,
    generateOrderNumber
};

// ===== تهيئة محدد الفئات =====
function initCategoryFilters() {
    const filters = document.querySelectorAll('.category-filter');
    
    filters.forEach(filter => {
        filter.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // إزالة الفعالية من جميع الأزرار
            filters.forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('btn-outline-primary');
            });
            
            // إضافة الفعالية للزر المحدد
            this.classList.add('active');
            this.classList.remove('btn-outline-primary');
            
            // تصفية المنتجات (سيتم تنفيذها في products.js)
            if (typeof window.filterProductsByCategory === 'function') {
                window.filterProductsByCategory(category);
            }
        });
    });
}

// ===== تهيئة الصفحة عند تحميلها =====
window.addEventListener('load', function() {
    // إضافة تأثيرات للعناصر
    const animatedElements = document.querySelectorAll('.animate-fade-in');
    animatedElements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // تهيئة محددات الفئات إذا وجدت
    if (document.querySelector('.category-filter')) {
        initCategoryFilters();
    }
});