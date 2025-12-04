// نظام الدفع الإلكتروني
class PaymentSystem {
    constructor() {
        this.selectedMethod = null;
        this.orderData = null;
        this.paymentSession = null;
        this.initPaymentMethods();
        this.initPaymentForm();
    }

    initPaymentMethods() {
        const paymentMethods = document.querySelectorAll('.payment-method');
        
        paymentMethods.forEach(method => {
            method.addEventListener('click', () => {
                // إزالة التحديد من جميع الطرق
                paymentMethods.forEach(m => m.classList.remove('selected'));
                
                // تحديد الطريقة المختارة
                method.classList.add('selected');
                this.selectedMethod = method.dataset.method;
                
                // إظهار تفاصيل طريقة الدفع المحددة
                this.showPaymentDetails(this.selectedMethod);
            });
        });
    }

    showPaymentDetails(method) {
        // إخفاء جميع تفاصيل طرق الدفع
        const allDetails = document.querySelectorAll('.payment-details');
        allDetails.forEach(detail => detail.style.display = 'none');
        
        // إظهار تفاصيل طريقة الدفع المحددة
        const targetDetails = document.getElementById(`${method}-details`);
        if (targetDetails) {
            targetDetails.style.display = 'block';
        }
    }

    initPaymentForm() {
        const paymentForm = document.getElementById('payment-form');
        if (paymentForm) {
            paymentForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                if (!this.selectedMethod) {
                    this.showError('الرجاء اختيار طريقة دفع');
                    return;
                }
                
                // جمع بيانات الدفع
                const paymentData = this.collectPaymentData();
                
                // التحقق من صحة البيانات
                if (!this.validatePaymentData(paymentData)) {
                    return;
                }
                
                // إجراء الدفع
                await this.processPayment(paymentData);
            });
        }
    }

    collectPaymentData() {
        const data = {
            method: this.selectedMethod,
            amount: this.getOrderTotal(),
            orderId: this.getOrderId()
        };
        
        // جمع بيانات إضافية حسب طريقة الدفع
        switch(this.selectedMethod) {
            case 'mada':
                data.cardNumber = document.getElementById('mada-card-number').value;
                data.expiryDate = document.getElementById('mada-expiry').value;
                data.cvv = document.getElementById('mada-cvv').value;
                data.cardHolder = document.getElementById('mada-card-holder').value;
                break;
                
            case 'apple_pay':
                data.applePayToken = document.getElementById('apple-pay-token').value;
                break;
                
            case 'paypal':
                data.paypalEmail = document.getElementById('paypal-email').value;
                break;
                
            case 'stc_pay':
                data.stcPhone = document.getElementById('stc-phone').value;
                break;
                
            case 'bank_transfer':
                data.bankName = document.getElementById('bank-name').value;
                data.accountNumber = document.getElementById('account-number').value;
                data.transferDate = document.getElementById('transfer-date').value;
                data.receiptNumber = document.getElementById('receipt-number').value;
                break;
        }
        
        return data;
    }

    validatePaymentData(data) {
        // التحقق من البيانات الأساسية
        if (!data.method || !data.amount || !data.orderId) {
            this.showError('بيانات الدفع غير مكتملة');
            return false;
        }
        
        // التحقق حسب طريقة الدفع
        switch(data.method) {
            case 'mada':
                if (!this.validateMadaCard(data.cardNumber)) {
                    this.showError('رقم بطاقة مدى غير صالح');
                    return false;
                }
                if (!this.validateExpiryDate(data.expiryDate)) {
                    this.showError('تاريخ انتهاء الصلاحية غير صالح');
                    return false;
                }
                if (!this.validateCVV(data.cvv)) {
                    this.showError('رمز CVV غير صالح');
                    return false;
                }
                break;
                
            case 'stc_pay':
                if (!this.validatePhoneNumber(data.stcPhone)) {
                    this.showError('رقم هاتف STC Pay غير صالح');
                    return false;
                }
                break;
                
            case 'bank_transfer':
                if (!data.bankName || !data.accountNumber || !data.transferDate) {
                    this.showError('بيانات التحويل البنكي غير مكتملة');
                    return false;
                }
                break;
        }
        
        return true;
    }

    async processPayment(paymentData) {
        try {
            // إظهار حالة التحميل
            this.showLoading(true);
            
            // إنشاء جلسة دفع
            const sessionResponse = await fetch('php/payments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'create_session',
                    order_id: paymentData.orderId,
                    payment_method: paymentData.method,
                    amount: paymentData.amount
                })
            });
            
            const sessionResult = await sessionResponse.json();
            
            if (!sessionResult.success) {
                throw new Error(sessionResult.error || 'فشل في إنشاء جلسة الدفع');
            }
            
            this.paymentSession = sessionResult.session_id;
            
            // معالجة الدفع حسب الطريقة
            let paymentResult;
            
            switch(paymentData.method) {
                case 'mada':
                    paymentResult = await this.processMadaPayment(paymentData);
                    break;
                    
                case 'paypal':
                    paymentResult = await this.processPayPalPayment(paymentData);
                    break;
                    
                case 'apple_pay':
                    paymentResult = await this.processApplePay(paymentData);
                    break;
                    
                case 'stc_pay':
                    paymentResult = await this.processStcPay(paymentData);
                    break;
                    
                case 'bank_transfer':
                    paymentResult = await this.processBankTransfer(paymentData);
                    break;
            }
            
            if (paymentResult.success) {
                // توجيه إلى صفحة التأكيد
                this.redirectToConfirmation(paymentResult.transaction_id);
            } else {
                throw new Error(paymentResult.error || 'فشلت عملية الدفع');
            }
            
        } catch (error) {
            console.error('Payment error:', error);
            this.showError(error.message || 'حدث خطأ أثناء معالجة الدفع');
        } finally {
            this.showLoading(false);
        }
    }

    async processMadaPayment(cardData) {
        const response = await fetch('php/payments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'process_mada',
                session_id: this.paymentSession,
                card_data: cardData
            })
        });
        
        return await response.json();
    }

    async processPayPalPayment(paymentData) {
        // استخدام PayPal SDK
        return await window.paypal.Buttons({
            createOrder: (data, actions) => {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: paymentData.amount
                        }
                    }]
                });
            },
            onApprove: async (data, actions) => {
                const details = await actions.order.capture();
                
                const response = await fetch('php/payments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'process_paypal',
                        session_id: this.paymentSession,
                        paypal_order_id: details.id
                    })
                });
                
                return await response.json();
            }
        }).render('#paypal-button-container');
    }

    async processApplePay(paymentData) {
        // تنفيذ Apple Pay
        if (!window.ApplePaySession) {
            throw new Error('Apple Pay غير مدعوم في هذا المتصفح');
        }
        
        const session = new ApplePaySession(6, {
            countryCode: 'SA',
            currencyCode: 'SAR',
            supportedNetworks: ['mada', 'visa', 'masterCard'],
            merchantCapabilities: ['supports3DS'],
            total: {
                label: 'كاردي',
                amount: paymentData.amount.toString()
            }
        });
        
        // معالجة الأحداث
        session.onvalidatemerchant = async (event) => {
            const validationResponse = await this.validateApplePayMerchant(event.validationURL);
            session.completeMerchantValidation(validationResponse);
        };
        
        session.onpaymentauthorized = async (event) => {
            const result = await this.processApplePayAuthorization(event.payment);
            
            if (result.success) {
                session.completePayment(ApplePaySession.STATUS_SUCCESS);
                return result;
            } else {
                session.completePayment(ApplePaySession.STATUS_FAILURE);
                throw new Error(result.error);
            }
        };
        
        session.begin();
    }

    async processStcPay(paymentData) {
        // محاكاة دفع STC Pay
        return {
            success: true,
            transaction_id: 'STC-' + Date.now()
        };
    }

    async processBankTransfer(paymentData) {
        const response = await fetch('php/payments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'bank_transfer',
                session_id: this.paymentSession,
                transfer_data: {
                    bank_name: paymentData.bankName,
                    account_number: paymentData.accountNumber,
                    transfer_date: paymentData.transferDate,
                    receipt_number: paymentData.receiptNumber
                }
            })
        });
        
        return await response.json();
    }

    validateMadaCard(cardNumber) {
        // التحقق من رقم بطاقة مدى (تبدأ بـ 4)
        const cleaned = cardNumber.replace(/\s/g, '');
        return /^4[0-9]{15}$/.test(cleaned);
    }

    validateExpiryDate(expiry) {
        const [month, year] = expiry.split('/');
        const currentYear = new Date().getFullYear() % 100;
        const currentMonth = new Date().getMonth() + 1;
        
        const expMonth = parseInt(month);
        const expYear = parseInt(year);
        
        if (expMonth < 1 || expMonth > 12) return false;
        if (expYear < currentYear) return false;
        if (expYear === currentYear && expMonth < currentMonth) return false;
        
        return true;
    }

    validateCVV(cvv) {
        return /^[0-9]{3,4}$/.test(cvv);
    }

    validatePhoneNumber(phone) {
        const cleaned = phone.replace(/[^0-9+]/g, '');
        return /^[\+]?[0-9]{10,15}$/.test(cleaned);
    }

    getOrderTotal() {
        const totalElement = document.querySelector('.order-total');
        if (totalElement) {
            const totalText = totalElement.textContent;
            const totalValue = totalText.replace(/[^0-9.]/g, '');
            return parseFloat(totalValue);
        }
        return 0;
    }

    getOrderId() {
        return document.querySelector('input[name="order_id"]')?.value || 
               new URLSearchParams(window.location.search).get('order_id');
    }

    showError(message) {
        const errorDiv = document.getElementById('payment-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            // إخفاء الخطأ بعد 5 ثوانٍ
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        } else {
            alert(message);
        }
    }

    showLoading(show) {
        const loadingOverlay = document.getElementById('payment-loading');
        const submitButton = document.getElementById('payment-submit');
        
        if (loadingOverlay) {
            loadingOverlay.style.display = show ? 'flex' : 'none';
        }
        
        if (submitButton) {
            submitButton.disabled = show;
            submitButton.innerHTML = show ? 
                '<i class="fas fa-spinner fa-spin"></i> جاري المعالجة...' : 
                '<i class="fas fa-lock"></i> تأكيد الدفع';
        }
    }

    redirectToConfirmation(transactionId) {
        window.location.href = `confirmation.html?transaction=${transactionId}`;
    }
}

// تهيئة نظام الدفع عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    const paymentSystem = new PaymentSystem();
    window.paymentSystem = paymentSystem;
});