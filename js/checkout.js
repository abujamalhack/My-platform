// معالجة الدفع - كاردي

class CheckoutManager {
    constructor() {
        this.orderData = {};
        this.paymentMethod = 'mada';
        this.isProcessing = false;
        this.init();
    }
    
    init() {
        this.loadCart();
        this.setupPaymentMethods();
        this.setupFormValidation();
        this.setupEventListeners();
        this.updateOrderSummary();
    }
    
    // تحميل بيانات السلة
    loadCart() {
        const cart = JSON.parse(localStorage.getItem('cardy_cart')) || [];
        const coupon = JSON.parse(localStorage.getItem('cardy_coupon')) || null;
        
        this.orderData.cart = cart;
        this.orderData.coupon = coupon;
        
        // حساب المجاميع
        this.calculateTotals();
    }
    
    // حساب المجاميع
    calculateTotals() {
        if (!this.orderData.cart) return;
        
        const subtotal = this.orderData.cart.reduce((sum, item) => 
            sum + (item.price * item.quantity), 0);
        
        let discount = 0;
        if (this.orderData.coupon) {
            const coupon = this.orderData.coupon;
            if (coupon.discount_type === 'percentage') {
                discount = subtotal * (coupon.discount_value / 100);
                if (coupon.max_discount_amount) {
                    discount = Math.min(discount, coupon.max_discount_amount);
                }
            } else {
                discount = coupon.discount_value;
            }
        }
        
        const tax = (subtotal - discount) * 0.15; // 15% ضريبة
        const total = subtotal - discount + tax;
        
        this.orderData.subtotal = subtotal;
        this.orderData.discount = discount;
        this.orderData.tax = tax;
        this.orderData.total = total;
    }
    
    // تحديث ملخص الطلب
    updateOrderSummary() {
        this.calculateTotals();
        
        // تحديث العناصر
        const elements = {
            'orderSubtotal': this.orderData.subtotal?.toFixed(2) || '0.00',
            'orderDiscount': this.orderData.discount?.toFixed(2) || '0.00',
            'orderTax': this.orderData.tax?.toFixed(2) || '0.00',
            'orderTotal': this.orderData.total?.toFixed(2) || '0.00'
        };
        
        for (const [id, value] of Object.entries(elements)) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value + ' ر.س';
            }
        }
        
        // تحديث قائمة المنتجات
        const itemsContainer = document.getElementById('orderItems');
        if (itemsContainer && this.orderData.cart) {
            itemsContainer.innerHTML = this.orderData.cart.map(item => `
                <div class="order-item">
                    <div class="item-info">
                        <span class="item-name">${item.name}</span>
                        <span class="item-quantity">× ${item.quantity}</span>
                    </div>
                    <div class="item-price">${(item.price * item.quantity).toFixed(2)} ر.س</div>
                </div>
            `).join('');
        }
    }
    
    // إعداد طرق الدفع
    setupPaymentMethods() {
        const methods = document.querySelectorAll('input[name="payment_method"]');
        const cardDetails = document.getElementById('cardDetails');
        
        methods.forEach(method => {
            method.addEventListener('change', (e) => {
                this.paymentMethod = e.target.value;
                
                // إظهار/إخفاء تفاصيل البطاقة
                if (cardDetails) {
                    cardDetails.style.display = 
                        this.paymentMethod === 'mada' ? 'block' : 'none';
                }
                
                // تحديث النص على زر الدفع
                const submitBtn = document.getElementById('completeOrder');
                if (submitBtn) {
                    const methodText = this.getPaymentMethodText(this.paymentMethod);
                    submitBtn.innerHTML = `
                        <i class="fas fa-lock me-2"></i>
                        تأكيد الطلب والدفع بـ ${methodText}
                    `;
                }
            });
        });
    }
    
    // الحصول على نص طريقة الدفع
    getPaymentMethodText(method) {
        const methods = {
            'mada': 'مدى',
            'apple_pay': 'Apple Pay',
            'stc_pay': 'STC Pay',
            'bank_transfer': 'تحويل بنكي',
            'paypal': 'PayPal'
        };
        
        return methods[method] || 'طريقة الدفع';
    }
    
    // إعداد التحقق من النموذج
    setupFormValidation() {
        const form = document.getElementById('checkoutForm');
        if (!form) return;
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (this.isProcessing) return;
            
            if (this.validateForm()) {
                this.processOrder();
            }
        });
    }
    
    // التحقق من صحة النموذج
    validateForm() {
        const form = document.getElementById('checkoutForm');
        const inputs = form.querySelectorAll('[required]');
        let isValid = true;
        
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
            input.classList.remove('is-valid');
            
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                this.showError(input, 'هذا الحقل مطلوب');
                isValid = false;
            } else if (input.type === 'email' && !this.validateEmail(input.value)) {
                input.classList.add('is-invalid');
                this.showError(input, 'البريد الإلكتروني غير صالح');
                isValid = false;
            } else if (input.type === 'tel' && !this.validatePhone(input.value)) {
                input.classList.add('is-invalid');
                this.showError(input, 'رقم الهاتف غير صالح');
                isValid = false;
            } else {
                input.classList.add('is-valid');
            }
        });
        
        // التحقق من الاتفاقيات
        const agreeTerms = document.getElementById('agreeTerms');
        if (agreeTerms && !agreeTerms.checked) {
            this.showNotification('يجب الموافقة على الشروط والأحكام', 'error');
            isValid = false;
        }
        
        // التحقق من السلة
        if (!this.orderData.cart || this.orderData.cart.length === 0) {
            this.showNotification('سلة التسوق فارغة', 'error');
            isValid = false;
        }
        
        return isValid;
    }
    
    // التحقق من البريد الإلكتروني
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // التحقق من رقم الهاتف
    validatePhone(phone) {
        const re = /^(009665|9665|\+9665|05)([0-9]{8})$/;
        return re.test(phone);
    }
    
    // إظهار خطأ للحقل
    showError(input, message) {
        // إزالة أي رسائل خطأ سابقة
        const existingError = input.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // إضافة رسالة الخطأ الجديدة
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }
    
    // معالجة الطلب
    async processOrder() {
        if (this.isProcessing) return;
        
        this.isProcessing = true;
        
        // جمع بيانات النموذج
        const form = document.getElementById('checkoutForm');
        const formData = new FormData(form);
        const formObject = Object.fromEntries(formData.entries());
        
        // إعداد بيانات الطلب
        const orderData = {
            customer: {
                full_name: formObject.full_name,
                email: formObject.email,
                phone: formObject.phone,
                country: formObject.country
            },
            payment: {
                method: this.paymentMethod,
                details: this.paymentMethod === 'mada' ? {
                    card_number: formObject.card_number,
                    expiry_date: formObject.expiry_date,
                    cvv: formObject.cvv
                } : {}
            },
            order: {
                items: this.orderData.cart,
                subtotal: this.orderData.subtotal,
                discount: this.orderData.discount,
                tax: this.orderData.tax,
                total: this.orderData.total,
                coupon: this.orderData.coupon?.code,
                notes: formObject.notes
            }
        };
        
        try {
            // إرسال الطلب للخادم
            const response = await this.sendOrderToServer(orderData);
            
            if (response.success) {
                // نجاح - إظهار تأكيد
                this.showOrderConfirmation(response.order);
                
                // تفريغ السلة
                localStorage.removeItem('cardy_cart');
                localStorage.removeItem('cardy_coupon');
                localStorage.removeItem('order_total');
                
                // تحديث عداد السلة
                this.updateCartCount(0);
            } else {
                throw new Error(response.error || 'فشل في معالجة الطلب');
            }
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.isProcessing = false;
        }
    }
    
    // إرسال الطلب للخادم
    async sendOrderToServer(orderData) {
        // محاكاة طلب للخادم
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // في الواقع، ستكون request حقيقية للخادم
                // fetch('/php/orders.php', { method: 'POST', body: JSON.stringify(orderData) })
                
                const orderNumber = 'ORD-' + Date.now().toString().slice(-8);
                
                resolve({
                    success: true,
                    order: {
                        number: orderNumber,
                        id: Math.floor(Math.random() * 10000),
                        total: orderData.order.total,
                        date: new Date().toLocaleString('ar-SA'),
                        items: orderData.order.items.length
                    }
                });
            }, 1500);
        });
    }
    
    // إظهار تأكيد الطلب
    showOrderConfirmation(order) {
        const modal = new bootstrap.Modal(document.getElementById('orderConfirmModal'));
        const orderNumberElement = document.getElementById('orderNumber');
        
        if (orderNumberElement) {
            orderNumberElement.innerHTML = `رقم الطلب: <strong>${order.number}</strong>`;
        }
        
        modal.show();
        
        // إرسال بريد إلكتروني تأكيدي (محاكاة)
        this.sendConfirmationEmail(order);
    }
    
    // إرسال بريد تأكيدي
    sendConfirmationEmail(order) {
        console.log('تم إرسال تأكيد الطلب للبريد الإلكتروني:', order);
        // في الواقع، سيكون هناك request للخادم لإرسال البريد
    }
    
    // تحديث عداد السلة
    updateCartCount(count) {
        document.querySelectorAll('.cart-count').forEach(element => {
            element.textContent = count;
        });
    }
    
    // إعداد مستمعي الأحداث
    setupEventListeners() {
        // تحديث الملخص عند تغيير الكمية
        document.addEventListener('cartUpdated', () => {
            this.loadCart();
            this.updateOrderSummary();
        });
        
        // نسخ رقم الحساب البنكي
        const copyBankBtn = document.getElementById('copyBankNumber');
        if (copyBankBtn) {
            copyBankBtn.addEventListener('click', () => {
                const bankNumber = 'SA1234567890123456789012';
                navigator.clipboard.writeText(bankNumber)
                    .then(() => this.showNotification('تم نسخ رقم الحساب', 'success'))
                    .catch(() => this.showNotification('فشل النسخ', 'error'));
            });
        }
        
        // حفظ بيانات العميل
        const saveInfoCheckbox = document.getElementById('saveInfo');
        if (saveInfoCheckbox) {
            saveInfoCheckbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.saveCustomerInfo();
                }
            });
        }
        
        // تحميل بيانات العميل المحفوظة
        this.loadCustomerInfo();
    }
    
    // حفظ بيانات العميل
    saveCustomerInfo() {
        const form = document.getElementById('checkoutForm');
        const formData = new FormData(form);
        const customerInfo = {};
        
        for (const [key, value] of formData.entries()) {
            if (['full_name', 'email', 'phone', 'country'].includes(key)) {
                customerInfo[key] = value;
            }
        }
        
        localStorage.setItem('customer_info', JSON.stringify(customerInfo));
    }
    
    // تحميل بيانات العميل المحفوظة
    loadCustomerInfo() {
        const savedInfo = localStorage.getItem('customer_info');
        if (savedInfo) {
            try {
                const customerInfo = JSON.parse(savedInfo);
                const form = document.getElementById('checkoutForm');
                
                for (const [key, value] of Object.entries(customerInfo)) {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = value;
                    }
                }
            } catch (error) {
                console.error('Error loading customer info:', error);
            }
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
}

// تهيئة مدير الدفع
document.addEventListener('DOMContentLoaded', () => {
    const checkoutManager = new CheckoutManager();
    
    // تصدير للاستخدام العالمي
    window.checkoutManager = checkoutManager;
});

// معالجة الدفع عبر STC Pay
function processSTCPay(amount, orderId) {
    // محاكاة STC Pay SDK
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                success: true,
                transactionId: 'STC-' + Date.now(),
                amount: amount,
                orderId: orderId
            });
        }, 2000);
    });
}

// معالجة الدفع عبر Apple Pay
function processApplePay(amount, orderId) {
    // محاكاة Apple Pay SDK
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                success: true,
                transactionId: 'APL-' + Date.now(),
                amount: amount,
                orderId: orderId
            });
        }, 2000);
    });
}

// معالجة الدفع عبر PayPal
function processPayPal(amount, orderId) {
    // محاكاة PayPal SDK
    return new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                success: true,
                transactionId: 'PPL-' + Date.now(),
                amount: amount,
                orderId: orderId
            });
        }, 2000);
    });
}