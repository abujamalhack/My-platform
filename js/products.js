// ===== تهيئة صفحة المنتجات =====
document.addEventListener('DOMContentLoaded', function() {
    // التحقق من المعلمات في URL
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');
    const search = urlParams.get('search');
    
    // تهيئة الفلاتر
    initFilters();
    
    // تحميل المنتجات
    let productsToShow = [...window.Cardy.products];
    
    // تطبيق الفلاتر من URL
    if (category) {
        document.getElementById('categoryFilter').value = category;
        productsToShow = filterByCategory(productsToShow, category);
    }
    
    if (search) {
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.value = search;
        }
        productsToShow = filterBySearch(productsToShow, search);
    }
    
    // عرض المنتجات
    displayProducts(productsToShow);
    
    // تحديث معلومات النتائج
    updateResultsInfo(productsToShow);
    
    // تهيئة الترقيم الصفحي
    initPagination(productsToShow);
    
    // تهيئة أزرار العرض (شبكة/قائمة)
    initViewButtons();
    
    // إضافة مستمعي الأحداث للفلاتر
    document.querySelectorAll('.filter-group select').forEach(select => {
        select.addEventListener('change', handleFilterChange);
    });
    
    document.getElementById('applyFilters').addEventListener('click', handleFilterChange);
    document.getElementById('resetFilters').addEventListener('click', resetFilters);
});

// ===== تهيئة الفلاتر =====
function initFilters() {
    // تعيين القيم الافتراضية للفلاتر المتقدمة
    document.querySelectorAll('.advanced-filters input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', handleFilterChange);
    });
}

// ===== معالجة تغيير الفلاتر =====
function handleFilterChange() {
    let filteredProducts = [...window.Cardy.products];
    
    // فلترة حسب الفئة
    const category = document.getElementById('categoryFilter').value;
    if (category !== 'all') {
        filteredProducts = filterByCategory(filteredProducts, category);
    }
    
    // فلترة حسب السعر
    const price = document.getElementById('priceFilter').value;
    if (price !== 'all') {
        filteredProducts = filterByPrice(filteredProducts, price);
    }
    
    // فلترة حسب وقت التوصيل
    const delivery = document.getElementById('deliveryFilter').value;
    if (delivery !== 'all') {
        filteredProducts = filterByDelivery(filteredProducts, delivery);
    }
    
    // فلترة حسب المزود
    const providerCheckboxes = document.querySelectorAll('.advanced-filters input[type="checkbox"][value*="provider"]:checked');
    if (providerCheckboxes.length > 0) {
        const providers = Array.from(providerCheckboxes).map(cb => cb.value);
        filteredProducts = filterByProvider(filteredProducts, providers);
    }
    
    // فلترة حسب العروض
    const offerCheckboxes = document.querySelectorAll('.advanced-filters input[type="checkbox"][value*="filter"]:checked');
    if (offerCheckboxes.length > 0) {
        offerCheckboxes.forEach(checkbox => {
            filteredProducts = filterByOffer(filteredProducts, checkbox.value);
        });
    }
    
    // فلترة حسب المخزون
    const stockCheckbox = document.getElementById('filterStock');
    if (stockCheckbox && stockCheckbox.checked) {
        filteredProducts = filteredProducts.filter(product => product.stock > 0);
    }
    
    // ترتيب المنتجات
    const sortBy = document.getElementById('sortFilter').value;
    filteredProducts = sortProducts(filteredProducts, sortBy);
    
    // عرض المنتجات المصفاة
    displayProducts(filteredProducts);
    
    // تحديث معلومات النتائج
    updateResultsInfo(filteredProducts);
    
    // إعادة تهيئة الترقيم الصفحي
    initPagination(filteredProducts);
}

// ===== فلترة حسب الفئة =====
function filterByCategory(products, category) {
    return products.filter(product => product.category === category);
}

// ===== فلترة حسب السعر =====
function filterByPrice(products, priceRange) {
    switch(priceRange) {
        case '0-50':
            return products.filter(p => p.price <= 50);
        case '50-100':
            return products.filter(p => p.price > 50 && p.price <= 100);
        case '100-500':
            return products.filter(p => p.price > 100 && p.price <= 500);
        case '500+':
            return products.filter(p => p.price > 500);
        default:
            return products;
    }
}

// ===== فلترة حسب وقت التوصيل =====
function filterByDelivery(products, deliveryTime) {
    if (deliveryTime === 'instant') {
        return products.filter(p => p.deliveryTime === 'فوري');
    } else if (deliveryTime === '5min') {
        return products.filter(p => p.deliveryTime.includes('5 دقائق'));
    } else if (deliveryTime === '10min') {
        return products.filter(p => p.deliveryTime.includes('10 دقائق'));
    }
    return products;
}

// ===== فلترة حسب المزود =====
function filterByProvider(products, providers) {
    return products.filter(product => {
        const productName = product.name.toLowerCase();
        return providers.some(provider => productName.includes(provider));
    });
}

// ===== فلترة حسب العرض =====
function filterByOffer(products, offerType) {
    switch(offerType) {
        case 'discount':
            return products.filter(p => p.discount && p.discount > 0);
        case 'featured':
            return products.filter(p => p.featured);
        case 'new':
            // نفترض أن المنتجات الجديدة هي تلك التي أضيفت مؤخراً
            // في هذا المثال، نستخدم id > 10 كمنتجات جديدة
            return products.filter(p => p.id > 10);
        default:
            return products;
    }
}

// ===== فلترة حسب البحث =====
function filterBySearch(products, searchTerm) {
    const term = searchTerm.toLowerCase();
    return products.filter(product => 
        product.name.toLowerCase().includes(term) ||
        product.description.toLowerCase().includes(term) ||
        product.tags.some(tag => tag.toLowerCase().includes(term)) ||
        product.category.toLowerCase().includes(term)
    );
}

// ===== ترتيب المنتجات =====
function sortProducts(products, sortBy) {
    const sorted = [...products];
    
    switch(sortBy) {
        case 'price_asc':
            return sorted.sort((a, b) => a.price - b.price);
        case 'price_desc':
            return sorted.sort((a, b) => b.price - a.price);
        case 'name_asc':
            return sorted.sort((a, b) => a.name.localeCompare(b.name, 'ar'));
        case 'name_desc':
            return sorted.sort((a, b) => b.name.localeCompare(a.name, 'ar'));
        case 'newest':
            return sorted.sort((a, b) => b.id - a.id);
        default:
            // الترتيب الافتراضي: الأكثر مبيعاً (في هذا المثال، المنتجات المميزة أولاً)
            return sorted.sort((a, b) => {
                if (a.featured && !b.featured) return -1;
                if (!a.featured && b.featured) return 1;
                return 0;
            });
    }
}

// ===== عرض المنتجات =====
function displayProducts(products, page = 1) {
    const container = document.getElementById('productsContainer');
    if (!container) return;
    
    const productsPerPage = 12;
    const startIndex = (page - 1) * productsPerPage;
    const endIndex = startIndex + productsPerPage;
    const paginatedProducts = products.slice(startIndex, endIndex);
    
    if (paginatedProducts.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">لم يتم العثور على منتجات</h4>
                <p class="text-muted">جرب تعديل خيارات الفلترة أو البحث</p>
                <button class="btn btn-primary mt-3" id="resetFiltersBtn">
                    إعادة تعيين الفلاتر
                </button>
            </div>
        `;
        
        document.getElementById('resetFiltersBtn').addEventListener('click', resetFilters);
        return;
    }
    
    // التحقق من وضع العرض (شبكة أو قائمة)
    const isGridView = !document.getElementById('gridView').classList.contains('active');
    
    if (isGridView) {
        // عرض شبكة
        container.innerHTML = paginatedProducts.map(product => `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="product-card animate-fade-in">
                    <div class="product-image position-relative">
                        ${product.discount ? `
                            <div class="product-badge">
                                خصم ${product.discount}%
                            </div>
                        ` : ''}
                        <i class="fas ${getCategoryIcon(product.category)}"></i>
                    </div>
                    <div class="product-content">
                        <h5 class="product-title">${product.name}</h5>
                        <p class="product-description">${product.description}</p>
                        
                        <div class="product-price">
                            <span class="current-price">${product.price.toFixed(2)} ر.س</span>
                            ${product.oldPrice ? `
                                <span class="original-price">${product.oldPrice.toFixed(2)} ر.س</span>
                            ` : ''}
                        </div>
                        
                        <div class="product-meta">
                            <span class="product-category">${getCategoryName(product.category)}</span>
                            <span class="product-delivery">
                                <i class="fas fa-bolt"></i>
                                ${product.deliveryTime}
                            </span>
                        </div>
                        
                        <div class="product-features mb-3">
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
    } else {
        // عرض قائمة
        container.innerHTML = paginatedProducts.map(product => `
            <div class="col-12 mb-4">
                <div class="product-card product-list-item">
                    <div class="row g-0">
                        <div class="col-md-3">
                            <div class="product-image h-100">
                                ${product.discount ? `
                                    <div class="product-badge">
                                        خصم ${product.discount}%
                                    </div>
                                ` : ''}
                                <i class="fas ${getCategoryIcon(product.category)}"></i>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="product-content h-100 p-4">
                                <div class="row h-100">
                                    <div class="col-md-8">
                                        <h5 class="product-title">${product.name}</h5>
                                        <p class="product-description">${product.description}</p>
                                        
                                        <div class="product-features mb-3">
                                            ${product.features.map(feature => `
                                                <span class="feature-badge">
                                                    <i class="fas fa-check-circle"></i>
                                                    ${feature}
                                                </span>
                                            `).join('')}
                                        </div>
                                        
                                        <div class="product-meta">
                                            <span class="product-category">${getCategoryName(product.category)}</span>
                                            <span class="product-delivery">
                                                <i class="fas fa-bolt"></i>
                                                ${product.deliveryTime}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="product-price text-center mb-3">
                                            <span class="current-price d-block">${product.price.toFixed(2)} ر.س</span>
                                            ${product.oldPrice ? `
                                                <span class="original-price">${product.oldPrice.toFixed(2)} ر.س</span>
                                            ` : ''}
                                        </div>
                                        
                                        <div class="product-actions">
                                            <button class="btn btn-primary w-100 add-to-cart" data-id="${product.id}">
                                                <i class="fas fa-cart-plus me-2"></i>
                                                أضف إلى السلة
                                            </button>
                                            <button class="btn btn-outline-primary w-100 mt-2 view-details" data-id="${product.id}">
                                                <i class="fas fa-eye me-2"></i>
                                                عرض التفاصيل
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        
        // إضافة مستمعي الأحداث لأزرار عرض التفاصيل
        container.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const productId = parseInt(this.dataset.id);
                const product = window.Cardy.products.find(p => p.id === productId);
                if (product) {
                    showProductModal(product);
                }
            });
        });
    }
    
    // إضافة مستمعي الأحداث لأزرار إضافة إلى السلة
    container.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = parseInt(this.dataset.id);
            window.Cardy.addToCart(productId);
        });
    });
}

// ===== تحديث معلومات النتائج =====
function updateResultsInfo(products) {
    const resultsCount = document.getElementById('resultsCount');
    const totalProducts = document.getElementById('totalProducts');
    
    if (resultsCount && totalProducts) {
        resultsCount.textContent = products.length;
        totalProducts.textContent = products.length;
    }
}

// ===== تهيئة الترقيم الصفحي =====
function initPagination(products) {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;
    
    const productsPerPage = 12;
    const totalPages = Math.ceil(products.length / productsPerPage);
    
    if (totalPages <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // زر السابق
    paginationHTML += `
        <li class="page-item">
            <a class="page-link" href="#" data-page="prev">
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
    `;
    
    // أرقام الصفحات
    for (let i = 1; i <= totalPages; i++) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
    }
    
    // زر التالي
    paginationHTML += `
        <li class="page-item">
            <a class="page-link" href="#" data-page="next">
                <i class="fas fa-chevron-left"></i>
            </a>
        </li>
    `;
    
    pagination.innerHTML = paginationHTML;
    
    // إضافة مستمعي الأحداث
    pagination.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const currentPage = parseInt(pagination.querySelector('.page-link.active')?.textContent) || 1;
            let targetPage;
            
            if (this.dataset.page === 'prev') {
                targetPage = Math.max(1, currentPage - 1);
            } else if (this.dataset.page === 'next') {
                targetPage = Math.min(totalPages, currentPage + 1);
            } else {
                targetPage = parseInt(this.dataset.page);
            }
            
            // تحديث العرض
            displayProducts(products, targetPage);
            
            // تحديث حالة الترقيم
            updatePaginationState(targetPage);
        });
    });
    
    // تعيين الصفحة الأولى كفعالة
    updatePaginationState(1);
}

// ===== تحديث حالة الترقيم =====
function updatePaginationState(currentPage) {
    const pagination = document.getElementById('pagination');
    if (!pagination) return;
    
    // إزالة الفعالية من جميع الأزرار
    pagination.querySelectorAll('.page-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // إضافة الفعالية للزر الحالي
    const activeButton = pagination.querySelector(`.page-link[data-page="${currentPage}"]`);
    if (activeButton) {
        activeButton.parentElement.classList.add('active');
    }
}

// ===== تهيئة أزرار العرض =====
function initViewButtons() {
    const gridViewBtn = document.getElementById('gridView');
    const listViewBtn = document.getElementById('listView');
    
    if (!gridViewBtn || !listViewBtn) return;
    
    gridViewBtn.addEventListener('click', function() {
        this.classList.add('active');
        listViewBtn.classList.remove('active');
        handleFilterChange(); // إعادة عرض المنتجات بوضع الشبكة
    });
    
    listViewBtn.addEventListener('click', function() {
        this.classList.add('active');
        gridViewBtn.classList.remove('active');
        handleFilterChange(); // إعادة عرض المنتجات بوضع القائمة
    });
}

// ===== إعادة تعيين الفلاتر =====
function resetFilters() {
    // إعادة تعيين الفلاتر الأساسية
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('priceFilter').value = 'all';
    document.getElementById('deliveryFilter').value = 'all';
    document.getElementById('sortFilter').value = 'default';
    
    // إعادة تعيين الفلاتر المتقدمة
    document.querySelectorAll('.advanced-filters input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // إغلاق قسم الفلتر المتقدم
    const moreFilters = document.getElementById('moreFilters');
    if (moreFilters && moreFilters.classList.contains('show')) {
        moreFilters.classList.remove('show');
    }
    
    // إعادة تحميل جميع المنتجات
    const products = [...window.Cardy.products];
    displayProducts(products);
    updateResultsInfo(products);
    initPagination(products);
}

// ===== وظائف المساعدة =====
function getCategoryIcon(category) {
    const icons = {
        'games': 'fa-gamepad',
        'subscriptions': 'fa-film',
        'shopping': 'fa-shopping-bag',
        'mobile': 'fa-mobile-alt'
    };
    return icons[category] || 'fa-gift';
}

function getCategoryName(category) {
    const names = {
        'games': 'شحن الألعاب',
        'subscriptions': 'الاشتراكات',
        'shopping': 'بطاقات التسوق',
        'mobile': 'شحن الاتصالات'
    };
    return names[category] || 'منتجات رقمية';
}

// ===== عرض نافذة المنتج =====
function showProductModal(product) {
    // هذه الوظيفة موجودة في main.js
    if (typeof window.showProductModal === 'function') {
        window.showProductModal(product);
    }
}

// ===== تصدير الوظائف =====
window.filterProductsByCategory = function(category) {
    const products = [...window.Cardy.products];
    const filteredProducts = filterByCategory(products, category);
    displayProducts(filteredProducts);
    updateResultsInfo(filteredProducts);
    initPagination(filteredProducts);
};