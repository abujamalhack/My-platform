// لوحة تحكم كاردي - ملف الإدارة

document.addEventListener('DOMContentLoaded', function() {
    // تهيئة الشريط الجانبي
    initSidebar();
    
    // تهيئة الرسوم البيانية
    initCharts();
    
    // تهيئة الجداول
    initDataTables();
    
    // تهيئة النماذج
    initForms();
    
    // تحديث الإحصائيات الحية
    updateLiveStats();
});

// إدارة الشريط الجانبي
function initSidebar() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // إغلاق الشريط عند النقر خارجها في الشاشات الصغيرة
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 992) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
}

// الرسوم البيانية
function initCharts() {
    // مخطط المبيعات الشهرية
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'المبيعات (ر.س)',
                    data: [12500, 18900, 15300, 23400, 18900, 27600],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Tajawal'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('ar-SA') + ' ر.س';
                            }
                        }
                    }
                }
            }
        });
    }
    
    // مخطط الفئات
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['شحن الألعاب', 'الاشتراكات', 'بطاقات التسوق', 'شحن الاتصالات'],
                datasets: [{
                    data: [45, 25, 20, 10],
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#9b59b6',
                        '#f39c12'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Tajawal'
                            },
                            padding: 20
                        }
                    }
                }
            }
        });
    }
    
    // مخطط العملاء
    const customersCtx = document.getElementById('customersChart');
    if (customersCtx) {
        const customersChart = new Chart(customersCtx, {
            type: 'bar',
            data: {
                labels: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
                datasets: [{
                    label: 'عملاء جدد',
                    data: [65, 89, 76, 105, 92, 120],
                    backgroundColor: '#9b59b6',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        rtl: true,
                        labels: {
                            font: {
                                family: 'Tajawal'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// الجداول التفاعلية
function initDataTables() {
    // تحويل جميع الجداول لـ DataTables
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        if (table.classList.contains('data-table')) {
            // يمكنك إضافة DataTables هنا
            // مثال: new DataTable(table, { dir: 'rtl' });
        }
    });
    
    // تفعيل خيار التحديد الكلي
    const selectAllCheckbox = document.querySelector('.select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.form-check-input:not(.select-all)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
}

// النماذج
function initForms() {
    // التحقق من النماذج
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!validateForm(this)) {
                event.preventDefault();
            }
        });
    });
    
    // نموذج إضافة منتج
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // إرسال البيانات للخادم
            fetch('/php/products.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('تم إضافة المنتج بنجاح', 'success');
                    // إغلاق المودال وتحديث الصفحة
                    bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification('حدث خطأ أثناء إضافة المنتج', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('حدث خطأ في الاتصال', 'error');
            });
        });
    }
}

// التحقق من النماذج
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // إضافة رسالة الخطأ
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = 'هذا الحقل مطلوب';
            field.parentNode.appendChild(errorDiv);
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// تحديث الإحصائيات الحية
function updateLiveStats() {
    // تحديث الوقت الحالي
    function updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            const timeString = now.toLocaleTimeString('ar-SA', {
                hour12: true,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            timeElement.textContent = timeString;
        }
    }
    
    // تحديث كل ثانية
    setInterval(updateTime, 1000);
    updateTime();
    
    // جلب الإحصائيات الحية من الخادم
    fetch('/php/admin/dashboard.php')
        .then(response => response.json())
        .then(data => {
            updateStatsUI(data);
        })
        .catch(error => console.error('Error fetching stats:', error));
}

// تحديث واجهة الإحصائيات
function updateStatsUI(data) {
    // تحديث إجمالي المبيعات
    const revenueElement = document.querySelector('.stat-card.revenue .stats-number');
    if (revenueElement && data.stats) {
        revenueElement.textContent = data.stats.total_sales.toLocaleString('ar-SA') + ' ر.س';
    }
    
    // تحديث عدد الطلبات
    const ordersElement = document.querySelector('.stat-card.orders .stats-number');
    if (ordersElement && data.stats) {
        ordersElement.textContent = data.stats.total_orders.toLocaleString('ar-SA');
    }
    
    // تحديث عدد العملاء
    const customersElement = document.querySelector('.stat-card.customers .stats-number');
    if (customersElement && data.stats) {
        customersElement.textContent = data.stats.total_customers.toLocaleString('ar-SA');
    }
}

// الإشعارات
function showNotification(message, type = 'info') {
    // إنشاء عنصر الإشعار
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show notification-alert`;
    notification.innerHTML = `
        <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // تحديد موضع الإشعار
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    `;
    
    // إضافة للإستمارة
    document.body.appendChild(notification);
    
    // إزالة بعد 5 ثواني
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function getNotificationIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

// تصدير وتحميل البيانات
window.exportData = function(type) {
    let url = '/php/admin/reports.php?';
    
    switch(type) {
        case 'orders':
            url += 'type=orders&format=csv';
            break;
        case 'customers':
            url += 'type=customers&format=csv';
            break;
        case 'products':
            url += 'type=products&format=csv';
            break;
    }
    
    window.open(url, '_blank');
};

// البحث السريع
function initQuickSearch() {
    const searchInput = document.getElementById('quickSearch');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        
        if (searchTerm.length < 2) {
            hideSearchResults();
            return;
        }
        
        // البحث في الخادم
        fetch(`/php/search.php?q=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(results => {
                showSearchResults(results);
            })
            .catch(error => console.error('Search error:', error));
    });
}

function showSearchResults(results) {
    // عرض نتائج البحث
    const container = document.getElementById('searchResults');
    if (!container) return;
    
    if (results.length === 0) {
        container.innerHTML = '<div class="search-result-item">لا توجد نتائج</div>';
    } else {
        container.innerHTML = results.map(item => `
            <a href="${item.url}" class="search-result-item">
                <i class="fas fa-${item.icon} me-2"></i>
                <span>${item.title}</span>
                <small class="text-muted">${item.category}</small>
            </a>
        `).join('');
    }
    
    container.style.display = 'block';
}

function hideSearchResults() {
    const container = document.getElementById('searchResults');
    if (container) {
        container.style.display = 'none';
    }
}

// إدارة الحالة
let appState = {
    user: null,
    permissions: [],
    notifications: [],
    settings: {}
};

// تحميل إعدادات المستخدم
function loadUserSettings() {
    const savedSettings = localStorage.getItem('admin_settings');
    if (savedSettings) {
        try {
            appState.settings = JSON.parse(savedSettings);
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }
}

// حفظ الإعدادات
function saveSettings(key, value) {
    appState.settings[key] = value;
    localStorage.setItem('admin_settings', JSON.stringify(appState.settings));
}

// تعيين الوضع المظلم
function setDarkMode(enabled) {
    if (enabled) {
        document.body.classList.add('dark-mode');
        saveSettings('dark_mode', true);
    } else {
        document.body.classList.remove('dark-mode');
        saveSettings('dark_mode', false);
    }
}

// التحقق من صلاحيات المستخدم
function checkPermission(permission) {
    return appState.permissions.includes(permission) || 
           appState.user?.role === 'super_admin';
}

// تهيئة المودالات
function initModals() {
    // تعيين مودالات BS5
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });
}

// تصدير إلى PDF
function exportToPDF(elementId, filename) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // يمكنك استخدام مكتبة jsPDF هنا
    // مثال: const doc = new jsPDF();
    // doc.fromHTML(element, 15, 15);
    // doc.save(filename + '.pdf');
    
    showNotification('خاصية التصدير إلى PDF قيد التطوير', 'info');
}

// تصدير إلى Excel
function exportToExcel(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // يمكنك استخدام مكتبة SheetJS هنا
    // أو إنشاء CSV بسيط
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => col.textContent.trim());
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (navigator.msSaveBlob) {
        navigator.msSaveBlob(blob, filename + '.csv');
    } else {
        link.href = URL.createObjectURL(blob);
        link.download = filename + '.csv';
        link.click();
    }
    
    showNotification('تم تصدير البيانات بنجاح', 'success');
}

// المساعدة والوثائق
function showHelp(section) {
    const helpContent = {
        'dashboard': 'لوحة التحكم تعرض الإحصائيات والرسوم البيانية',
        'products': 'قسم إدارة المنتجات يمكنك من إضافة وتعديل المنتجات',
        'orders': 'قسم الطلبات لإدارة وتتبع جميع الطلبات',
        'customers': 'قسم العملاء لعرض وتعديل بيانات العملاء'
    };
    
    const content = helpContent[section] || 'مساعدة غير متوفرة لهذا القسم';
    alert(content);
}

// التهيئة النهائية
function finalizeInit() {
    // تحميل الإعدادات
    loadUserSettings();
    
    // تفعيل الوضع المظلم إذا كان مفعلاً
    if (appState.settings.dark_mode) {
        setDarkMode(true);
    }
    
    // تهيئة البحث السريع
    initQuickSearch();
    
    // تهيئة المودالات
    initModals();
    
    // إضافة مستمعات للأحداث
    document.addEventListener('keydown', function(event) {
        // اختصارات لوحة المفاتيح
        if (event.ctrlKey && event.key === 'k') {
            event.preventDefault();
            const searchInput = document.getElementById('quickSearch');
            if (searchInput) searchInput.focus();
        }
        
        // إغلاق النماذج بـ ESC
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                bootstrap.Modal.getInstance(modal).hide();
            });
        }
    });
}

// تشغيل التهيئة النهائية بعد تحميل الصفحة
setTimeout(finalizeInit, 100);