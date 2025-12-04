// تتبع الطلبات - كاردي

class OrderTracking {
    constructor() {
        this.orders = this.getSampleOrders();
        this.init();
    }
    
    init() {
        this.setupForm();
        this.setupEventListeners();
    }
    
    // بيانات تجريبية للطلبات
    getSampleOrders() {
        return {
            'ORD-123456': {
                number: 'ORD-123456',
                date: '2024-01-15 14:30',
                customer: 'أحمد محمد',
                email: 'ahmed@example.com',
                phone: '+966551234567',
                status: 'completed',
                status_text: 'مكتمل',
                total: '375.00 ر.س',
                payment_method: 'مدى',
                payment_status: 'مدفوع',
                items: [
                    { name: 'Xena Live 70,000 Coins', price: '37.50 ر.س', quantity: 1 },
                    { name: 'بطاقة Apple 100$', price: '375.00 ر.س', quantity: 1 }
                ],
                timeline: [
                    { step: 'تم إنشاء الطلب', time: '2024-01-15 14:30', status: 'completed' },
                    { step: 'تم تأكيد الدفع', time: '2024-01-15 14:32', status: 'completed' },
                    { step: 'تم معالجة الطلب', time: '2024-01-15 14:35', status: 'completed' },
                    { step: 'تم إرسال البطاقات', time: '2024-01-15 14:40', status: 'completed' },
                    { step: 'تم التسليم', time: '2024-01-15 14:45', status: 'completed' }
                ]
            },
            'ORD-789012': {
                number: 'ORD-789012',
                date: '2024-01-14 10:15',
                customer: 'فاطمة أحمد',
                email: 'fatima@example.com',
                phone: '+966552345678',
                status: 'processing',
                status_text: 'قيد المعالجة',
                total: '149.99 ر.س',
                payment_method: 'Apple Pay',
                payment_status: 'مدفوع',
                items: [
                    { name: 'Netflix اشتراك 3 أشهر', price: '149.99 ر.س', quantity: 1 }
                ],
                timeline: [
                    { step: 'تم إنشاء الطلب', time: '2024-01-14 10:15', status: 'completed' },
                    { step: 'تم تأكيد الدفع', time: '2024-01-14 10:16', status: 'completed' },
                    { step: 'تم معالجة الطلب', time: '2024-01-14 10:20', status: 'in_progress' },
                    { step: 'سيتم إرسال البطاقات', time: 'قريباً', status: 'pending' },
                    { step: 'التسليم', time: 'قريباً', status: 'pending' }
                ]
            }
        };
    }
    
    // إعداد النموذج
    setupForm() {
        const form = document.getElementById('trackingForm');
        if (!form) return;
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.trackOrder();
        });
    }
    
    // تتبع الطلب
    trackOrder() {
        const input = document.getElementById('orderNumber');
        const orderNumber = input.value.trim().toUpperCase();
        
        if (!orderNumber) {
            this.showError('الرجاء إدخال رقم الطلب');
            return;
        }
        
        // في الواقع، سيكون هناك request للخادم
        // هذا مثال تجريبي
        
        const order = this.orders[orderNumber];
        
        if (order) {
            this.displayOrder(order);
        } else {
            this.showError('رقم الطلب غير صحيح. تأكد من الرقم وحاول مرة أخرى.');
        }
    }
    
    // عرض تفاصيل الطلب
    displayOrder(order) {
        const resultsContainer = document.getElementById('trackingResults');
        
        if (!resultsContainer) return;
        
        // إنشاء محتوى النتائج
        const content = `
            <div class="tracking-card">
                <div class="tracking-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4>رقم الطلب: ${order.number}</h4>
                            <p class="text-muted mb-0">${order.date}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="order-status status-${order.status}">${order.status_text}</span>
                        </div>
                    </div>
                </div>
                
                <div class="tracking-body">
                    <!-- معلومات العميل -->
                    <div class="customer-info mb-4">
                        <h5><i class="fas fa-user me-2"></i>معلومات العميل</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>الاسم:</strong> ${order.customer}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>البريد:</strong> ${order.email}</p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>الهاتف:</strong> ${order.phone}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تفاصيل الطلب -->
                    <div class="order-details mb-4">
                        <h5><i class="fas fa-shopping-cart me-2"></i>تفاصيل الطلب</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>المنتج</th>
                                        <th>السعر</th>
                                        <th>الكمية</th>
                                        <th>المجموع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${order.items.map(item => `
                                        <tr>
                                            <td>${item.name}</td>
                                            <td>${item.price}</td>
                                            <td>${item.quantity}</td>
                                            <td>${item.price}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>المجموع النهائي:</strong></td>
                                        <td><strong>${order.total}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- معلومات الدفع -->
                    <div class="payment-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>طريقة الدفع:</strong> ${order.payment_method}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>حالة الدفع:</strong> <span class="text-success">${order.payment_status}</span></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- خط سير الطلب -->
                    <div class="order-timeline">
                        <h5><i class="fas fa-map-marker-alt me-2"></i>خط سير الطلب</h5>
                        <div class="timeline">
                            ${order.timeline.map((step, index) => `
                                <div class="timeline-step ${step.status}">
                                    <div class="timeline-icon">
                                        <i class="fas fa-${this.getStepIcon(step.status)}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6>${step.step}</h6>
                                        <p class="text-muted">${step.time}</p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    
                    <!-- أزرار الإجراءات -->
                    <div class="tracking-actions mt-4">
                        <button class="btn btn-outline-primary me-2" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>
                            طباعة الفاتورة
                        </button>
                        <button class="btn btn-primary" id="contactSupport">
                            <i class="fas fa-headset me-2"></i>
                            الاتصال بالدعم
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        resultsContainer.innerHTML = content;
        resultsContainer.style.display = 'block';
        
        // إضافة مستمع للزر
        const contactBtn = document.getElementById('contactSupport');
        if (contactBtn) {
            contactBtn.addEventListener('click', () => {
                window.location.href = 'contact.html';
            });
        }
        
        // التمرير للنتائج
        resultsContainer.scrollIntoView({ behavior: 'smooth' });
    }
    
    // الحصول على أيقونة الخطوة
    getStepIcon(status) {
        switch(status) {
            case 'completed': return 'check-circle';
            case 'in_progress': return 'sync-alt';
            default: return 'clock';
        }
    }
    
    // إظهار خطأ
    showError(message) {
        const resultsContainer = document.getElementById('trackingResults');
        
        if (!resultsContainer) return;
        
        resultsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
            </div>
        `;
        
        resultsContainer.style.display = 'block';
        
        // إخفاء بعد 5 ثواني
        setTimeout(() => {
            resultsContainer.style.display = 'none';
        }, 5000);
    }
    
    // إعداد مستمعي الأحداث
    setupEventListeners() {
        // البحث بالضغط على Enter
        const orderInput = document.getElementById('orderNumber');
        if (orderInput) {
            orderInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.trackOrder();
                }
            });
        }
        
        // تحميل رقم الطلب من URL
        this.loadOrderFromURL();
    }
    
    // تحميل رقم الطلب من URL
    loadOrderFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const orderNumber = urlParams.get('order');
        
        if (orderNumber) {
            const input = document.getElementById('orderNumber');
            if (input) {
                input.value = orderNumber;
                setTimeout(() => this.trackOrder(), 500);
            }
        }
    }
}

// تهيئة التتبع
document.addEventListener('DOMContentLoaded', () => {
    const orderTracking = new OrderTracking();
});

// دالة مساعدة للطباعة
function printOrder() {
    const printContent = document.querySelector('.tracking-card');
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = printContent.outerHTML;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}