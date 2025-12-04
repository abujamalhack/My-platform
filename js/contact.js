// اتصال - كاردي

class ContactManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupForm();
        this.setupEventListeners();
    }
    
    // إعداد النموذج
    setupForm() {
        const form = document.getElementById('contactForm');
        if (!form) return;
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm();
        });
    }
    
    // إرسال النموذج
    async submitForm() {
        const form = document.getElementById('contactForm');
        const formData = new FormData(form);
        const formObject = Object.fromEntries(formData.entries());
        
        // التحقق من النموذج
        if (!this.validateForm(formObject)) {
            return;
        }
        
        // تعطيل الزر أثناء الإرسال
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري الإرسال...';
        submitBtn.disabled = true;
        
        try {
            // في الواقع، سيكون هناك request للخادم
            // هذا مثال تجريبي
            await this.sendContactMessage(formObject);
            
            this.showSuccess('تم إرسال رسالتك بنجاح! سنقوم بالرد في أقرب وقت.');
            form.reset();
            
        } catch (error) {
            this.showError('حدث خطأ أثناء إرسال الرسالة. الرجاء المحاولة مرة أخرى.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    // التحقق من النموذج
    validateForm(data) {
        let isValid = true;
        
        // التحقق من الاسم
        if (!data.name || data.name.trim().length < 2) {
            this.showFieldError('name', 'الرجاء إدخال اسم صحيح');
            isValid = false;
        }
        
        // التحقق من البريد الإلكتروني
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!data.email || !emailRegex.test(data.email)) {
            this.showFieldError('email', 'الرجاء إدخال بريد إلكتروني صحيح');
            isValid = false;
        }
        
        // التحقق من الرسالة
        if (!data.message || data.message.trim().length < 10) {
            this.showFieldError('message', 'الرجاء إدخال رسالة مفصلة');
            isValid = false;
        }
        
        return isValid;
    }
    
    // إظهار خطأ للحقل
    showFieldError(fieldName, message) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        field.classList.add('is-invalid');
        
        // إزالة أي رسائل خطأ سابقة
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // إضافة رسالة الخطأ الجديدة
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }
    
    // إرسال رسالة الاتصال
    async sendContactMessage(data) {
        // محاكاة إرسال للخادم
        return new Promise((resolve) => {
            setTimeout(() => {
                console.log('تم إرسال رسالة الاتصال:', data);
                
                // إرسال إشعار للواتساب (محاكاة)
                this.sendWhatsAppNotification(data);
                
                resolve({
                    success: true,
                    message: 'تم استلام رسالتك بنجاح'
                });
            }, 1500);
        });
    }
    
    // إرسال إشعار واتساب
    sendWhatsAppNotification(data) {
        const message = `رسالة جديدة من ${data.name} (${data.email}): ${data.message.substring(0, 100)}...`;
        const whatsappUrl = `https://wa.me/967778657708?text=${encodeURIComponent(message)}`;
        
        // في الإنتاج الحقيقي، سيكون هناك request للخادم
        console.log('إشعار واتساب:', whatsappUrl);
    }
    
    // إظهار رسالة نجاح
    showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const form = document.getElementById('contactForm');
        form.parentNode.insertBefore(alert, form);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    // إظهار رسالة خطأ
    showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const form = document.getElementById('contactForm');
        form.parentNode.insertBefore(alert, form);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    // إعداد مستمعي الأحداث
    setupEventListeners() {
        // إزالة أخطاء التحقق عند الكتابة
        const form = document.getElementById('contactForm');
        if (form) {
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    input.classList.remove('is-invalid');
                    
                    const errorDiv = input.parentNode.querySelector('.invalid-feedback');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                });
            });
        }
        
        // نسخ معلومات الاتصال
        this.setupCopyButtons();
        
        // تحميل بيانات المستخدم المحفوظة
        this.loadSavedInfo();
    }
    
    // إعداد أزرار النسخ
    setupCopyButtons() {
        const copyButtons = document.querySelectorAll('[data-copy]');
        copyButtons.forEach(button => {
            button.addEventListener('click', () => {
                const text = button.dataset.copy;
                this.copyToClipboard(text);
            });
        });
    }
    
    // نسخ للناقل
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.showNotification('تم النسخ بنجاح', 'success');
        } catch (error) {
            this.showNotification('فشل النسخ', 'error');
        }
    }
    
    // تحميل المعلومات المحفوظة
    loadSavedInfo() {
        const savedInfo = localStorage.getItem('customer_info');
        if (savedInfo) {
            try {
                const info = JSON.parse(savedInfo);
                const form = document.getElementById('contactForm');
                
                if (form) {
                    const nameInput = form.querySelector('[name="name"]');
                    const emailInput = form.querySelector('[name="email"]');
                    const phoneInput = form.querySelector('[name="phone"]');
                    
                    if (nameInput && info.full_name) nameInput.value = info.full_name;
                    if (emailInput && info.email) emailInput.value = info.email;
                    if (phoneInput && info.phone) phoneInput.value = info.phone;
                }
            } catch (error) {
                console.error('Error loading saved info:', error);
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

// تهيئة مدير الاتصال
document.addEventListener('DOMContentLoaded', () => {
    const contactManager = new ContactManager();
});

// دالة لفتح دردشة الواتساب
function openWhatsAppChat() {
    const message = 'مرحباً، أود الاستفسار عن...';
    const url = `https://wa.me/967778657708?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}

// دالة لفتح خريطة الموقع
function openLocationMap() {
    const url = 'https://maps.google.com/?q=الرياض، المملكة العربية السعودية';
    window.open(url, '_blank');
}

// دالة لإرسال بريد إلكتروني
function sendEmail() {
    const subject = 'استفسار عن خدمة';
    const body = 'مرحباً،\n\nأود الاستفسار عن...';
    const url = `mailto:support@cardy.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = url;
}