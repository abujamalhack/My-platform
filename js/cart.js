// إدارة سلة التسوق - كاردي

class CartManager {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('cardy_cart')) || [];
        this.coupon = JSON.parse(localStorage.getItem('cardy_coupon')) || null;
        this.init();
    }
    
    init() {
        this.renderCart();
        this.updateCartCount();
        this.setupEventListeners();
    }
    
    // تحديث عداد السلة
    updateCartCount() {
        const count = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        document.querySelectorAll('.cart-count').forEach(element => {
            element.textContent = count;
        });
        
        // إظهار/إخفاء سلة فارغة
        const emptyCart = document.getElementById('cartEmpty');
        const cartItems = document.getElementById('cartItems');
        
        if (this.cart.length === 0) {
            if (emptyCart) emptyCart.style.display = 'block';
            if (cartItems) cartItems.style.display = 'none';
        } else {
            if (emptyCart) emptyCart.style.display = 'none';
            if (cartItems) cartItems.style.display = 'block';
        }
        
        // تحديث زر الدفع
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = this.cart.length === 0;
        }
    }
    
    // إضافة منتج للسلة
    addToCart(product, quantity = 1) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                ...product,
                quantity: quantity,
                added_at: new Date().toISOString()
            });
        }
        
        this.saveCart();
        this.renderCart();
        this.showNotification('تم إضافة المنتج إلى السلة', 'success');
    }
    
    // تحديث كمية المنتج
    updateQuantity(productId, newQuantity) {
        if (newQuantity < 1) {
            this.removeFromCart(productId);
            return;
        }
        
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            item.quantity = newQuantity;
            this.saveCart();
            this.renderCart();
        }
    }
    
    // إزالة منتج من السلة
    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
        this.renderCart();
        this.showNotification('تم إزالة المنتج من السلة', 'info');
    }
    
    // تفريغ السلة
    clearCart() {
        this.cart = [];
        this.coupon = null;
        this.saveCart();
        localStorage.removeItem('cardy_coupon');
        this.renderCart();
        this.showNotification('تم تفريغ السلة', 'info');
    }
    
    // حفظ السلة في localStorage
    saveCart() {
        localStorage.setItem('cardy_cart', JSON.stringify(this.cart));
        this.updateCartCount();
    }
    
    // عرض محتويات السلة
    renderCart() {
        const container = document.getElementById('cartItems');
        const summaryContainer = document.getElementById('orderSummary');
        
        if (!container && !summaryContainer) return;
        
        const itemsHTML = this.cart.map(item => `
            <div class="cart-item" data-id="${item.id}">
                <div class="row align-items-center">
                    <div class="col-md-2 col-4">
                        <div class="cart-item-image">
                            <i class="fas fa-${item.icon || 'gamepad'} fa-2x"></i>
                        </div>
                    </div>
                    <div class="col-md-4 col-8">
                        <h5 class="cart-item-title">${item.name}</h5>
                        <p class="cart-item-desc text-muted">${item.description || ''}</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="cart-item-price">
                            <span class="price">${item.price.toFixed(2)} ر.س</span>
                            ${item.old_price ? `
                                <small class="old-price">${item.old_price.toFixed(2)} ر.س</small>
                            ` : ''}
                        </div>
                    </div>
                    <div class="col-md-2 col-4">
                        <div class="quantity-control">
                            <button class="btn btn-sm btn-outline-secondary decrease" data-id="${item.id}">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control quantity-input" 
                                   value="${item.quantity}" min="1" data-id="${item.id}">
                            <button class="btn btn-sm btn-outline-secondary increase" data-id="${item.id}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-1 col-2">
                        <button class="btn btn-sm btn-danger remove" data-id="${item.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
        if (container) {
            container.innerHTML = itemsHTML;
        }
        
        if (summaryContainer) {
            summaryContainer.innerHTML = this.cart.map(item => `
                <div class="order-item">
                    <div class="item-name">${item.name} × ${item.quantity}</div>
                    <div class="item-price">${(item.price * item.quantity).toFixed(2)} ر.س</div>
                </div>
            `).join('');
        }
        
        this.updateTotals();
    }
    
    // تحديث المجاميع
    updateTotals() {
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const taxRate = 0.15; // 15%
        const discount = this.coupon ? this.calculateDiscount(subtotal) : 0;
        const tax = (subtotal - discount) * taxRate;
        const total = subtotal - discount + tax;
        
        // تحديث القيم في الواجهة
        const elements = {
            'subtotal': subtotal,
            'discount': discount,
            'tax': tax,
            'total': total,
            'orderSubtotal': subtotal,
            'orderDiscount': discount,
            'orderTax': tax,
            'orderTotal': total
        };
        
        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value.toFixed(2) + ' ر.س';
            }
        }
        
        // حفظ الإجمالي للدفع
        localStorage.setItem('order_total', total.toFixed(2));
    }
    
    // حساب الخصم
    calculateDiscount(subtotal) {
        if (!this.coupon) return 0;
        
        const coupon = this.coupon;
        
        if (coupon.min_order_amount && subtotal < coupon.min_order_amount) {
            this.coupon = null;
            localStorage.removeItem('cardy_coupon');
            this.showNotification('لا يمكن تطبيق الكوبون، الحد الأدنى للطلب غير محقق', 'warning');
            return 0;
        }
        
        if (coupon.discount_type === 'percentage') {
            const discount = subtotal * (coupon.discount_value / 100);
            return coupon.max_discount_amount ? 
                   Math.min(discount, coupon.max_discount_amount) : discount;
        } else {
            return coupon.discount_value;
        }
    }
    
    // تطبيق كوبون
    applyCoupon(code) {
        // في الواقع، ستطلب من الخادم التحقق من الكوبون
        // هذا مثال تجريبي
        
        const coupons = {
            'WELCOME10': {
                code: 'WELCOME10',
                discount_type: 'percentage',
                discount_value: 10,
                min_order_amount: 50
            },
            'SAVE20': {
                code: 'SAVE20',
                discount_type: 'fixed',
                discount_value: 20,
                min_order_amount: 100
            }
        };
        
        const coupon = coupons[code];
        
        if (coupon) {
            this.coupon = coupon;
            localStorage.setItem('cardy_coupon', JSON.stringify(coupon));
            this.updateTotals();
            this.showNotification('تم تطبيق الكوبون بنجاح', 'success');
            
            const message = document.getElementById('couponMessage');
            if (message) {
                message.innerHTML = `
                    <div class="alert alert-success">
                        تم تطبيق الكوبون ${coupon.code} بنجاح
                    </div>
                `;
            }
            
            return true;
        } else {
            this.showNotification('كوبون غير صالح', 'error');
            
            const message = document.getElementById('couponMessage');
            if (message) {
                message.innerHTML = `
                    <div class="alert alert-danger">
                        كوبون غير صالح أو منتهي الصلاحية
                    </div>
                `;
            }
            
            return false;
        }
    }
    
    // إعداد مستمعي الأحداث
    setupEventListeners() {
        // زيادة الكمية
        document.addEventListener('click', (e) => {
            if (e.target.closest('.increase')) {
                const button = e.target.closest('.increase');
                const productId = parseInt(button.dataset.id);
                const item = this.cart.find(item => item.id === productId);
                if (item) {
                    this.updateQuantity(productId, item.quantity + 1);
                }
            }
            
            // تقليل الكمية
            if (e.target.closest('.decrease')) {
                const button = e.target.closest('.decrease');
                const productId = parseInt(button.dataset.id);
                const item = this.cart.find(item => item.id === productId);
                if (item) {
                    this.updateQuantity(productId, item.quantity - 1);
                }
            }
            
            // إزالة المنتج
            if (e.target.closest('.remove')) {
                const button = e.target.closest('.remove');
                const productId = parseInt(button.dataset.id);
                this.removeFromCart(productId);
            }
        });
        
        // تغيير الكمية يدوياً
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('quantity-input')) {
                const input = e.target;
                const productId = parseInt(input.dataset.id);
                const newQuantity = parseInt(input.value) || 1;
                this.updateQuantity(productId, newQuantity);
            }
        });
        
        // تطبيق الكوبون
        const applyCouponBtn = document.getElementById('applyCoupon');
        if (applyCouponBtn) {
            applyCouponBtn.addEventListener('click', () => {
                const codeInput = document.getElementById('couponCode');
                const code = codeInput.value.trim().toUpperCase();
                
                if (code) {
                    this.applyCoupon(code);
                } else {
                    this.showNotification('الرجاء إدخال كود الخصم', 'warning');
                }
            });
        }
        
        // إكمال الطلب
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', (e) => {
                if (this.cart.length === 0) {
                    e.preventDefault();
                    this.showNotification('سلة التسوق فارغة', 'warning');
                }
            });
        }
    }
    
    // إظهار الإشعارات
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} notification`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
    
    // جلب ملخص الطلب للدفع
    getOrderSummary() {
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const discount = this.coupon ? this.calculateDiscount(subtotal) : 0;
        const tax = (subtotal - discount) * 0.15;
        const total = subtotal - discount + tax;
        
        return {
            items: this.cart.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: item.quantity,
                total: item.price * item.quantity
            })),
            subtotal: subtotal,
            discount: discount,
            tax: tax,
            total: total,
            coupon: this.coupon ? this.coupon.code : null
        };
    }
}

// تهيئة مدير السلة
let cartManager = null;

document.addEventListener('DOMContentLoaded', () => {
    cartManager = new CartManager();
    
    // إضافة منتج للسلة من الصفحات الأخرى
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const productId = parseInt(button.dataset.id);
            const productName = button.dataset.name;
            const productPrice = parseFloat(button.dataset.price);
            const productDesc = button.dataset.desc || '';
            
            cartManager.addToCart({
                id: productId,
                name: productName,
                price: productPrice,
                description: productDesc,
                icon: button.dataset.icon || 'gamepad'
            });
        });
    });
    
    // تفريغ السلة
    const clearCartBtn = document.getElementById('clearCart');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', () => {
            if (confirm('هل تريد تفريغ سلة التسوق؟')) {
                cartManager.clearCart();
            }
        });
    }
});

// تصدير للاستخدام في ملفات أخرى
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CartManager;
}