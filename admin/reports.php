<?php
// admin/reports.php - التقارير والإحصائيات

require_once '../config.php';
require_once '../db_connection.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// الحصول على المعاملات
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$reportType = isset($_GET['type']) ? $_GET['type'] : 'sales';

$db = Database::getInstance();

// بيانات التقرير
$reportData = [];
$chartLabels = [];
$chartData = [];

switch ($reportType) {
    case 'sales':
        // تقرير المبيعات
        $reportData = $db->fetchAll("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as order_count,
                SUM(final_amount) as total_sales,
                AVG(final_amount) as avg_order_value
            FROM orders
            WHERE payment_status = 'paid'
            AND DATE(created_at) BETWEEN :start_date AND :end_date
            GROUP BY DATE(created_at)
            ORDER BY date
        ", ['start_date' => $startDate, 'end_date' => $endDate]);
        
        // إعداد بيانات المخطط
        foreach ($reportData as $row) {
            $chartLabels[] = $row['date'];
            $chartData[] = $row['total_sales'];
        }
        break;
        
    case 'products':
        // تقرير المنتجات
        $reportData = $db->fetchAll("
            SELECT 
                p.name,
                p.sku,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total_price) as total_revenue,
                COUNT(DISTINCT oi.order_id) as order_count
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE o.payment_status = 'paid'
            AND o.created_at BETWEEN :start_date AND :end_date
            GROUP BY p.id
            ORDER BY total_sold DESC
        ", ['start_date' => $startDate, 'end_date' => $endDate]);
        break;
        
    case 'customers':
        // تقرير العملاء
        $reportData = $db->fetchAll("
            SELECT 
                u.username,
                u.email,
                COUNT(o.id) as order_count,
                SUM(o.final_amount) as total_spent,
                MAX(o.created_at) as last_order
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id
            WHERE u.role = 'customer'
            AND o.created_at BETWEEN :start_date AND :end_date
            GROUP BY u.id
            ORDER BY total_spent DESC
        ", ['start_date' => $startDate, 'end_date' => $endDate]);
        break;
        
    case 'categories':
        // تقرير الفئات
        $reportData = $db->fetchAll("
            SELECT 
                c.name,
                COUNT(DISTINCT o.id) as order_count,
                SUM(oi.quantity) as total_sold,
                SUM(oi.total_price) as total_revenue
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id
            WHERE o.payment_status = 'paid'
            AND o.created_at BETWEEN :start_date AND :end_date
            GROUP BY c.id
            ORDER BY total_revenue DESC
        ", ['start_date' => $startDate, 'end_date' => $endDate]);
        break;
}

// الإحصائيات الإجمالية
$totalStats = $db->fetch("
    SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        SUM(o.final_amount) as total_sales,
        COUNT(DISTINCT o.user_id) as total_customers,
        AVG(o.final_amount) as avg_order_value
    FROM orders o
    WHERE o.payment_status = 'paid'
    AND o.created_at BETWEEN :start_date AND :end_date
", ['start_date' => $startDate, 'end_date' => $endDate]);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-chart-bar"></i> التقارير والإحصائيات</h1>
                <p>تحليل وأداء المتجر</p>
            </div>
            
            <!-- فلترة التقارير -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="type" class="form-label">نوع التقرير</label>
                            <select id="type" name="type" class="form-select">
                                <option value="sales" <?php echo $reportType == 'sales' ? 'selected' : ''; ?>>تقرير المبيعات</option>
                                <option value="products" <?php echo $reportType == 'products' ? 'selected' : ''; ?>>تقرير المنتجات</option>
                                <option value="customers" <?php echo $reportType == 'customers' ? 'selected' : ''; ?>>تقرير العملاء</option>
                                <option value="categories" <?php echo $reportType == 'categories' ? 'selected' : ''; ?>>تقرير الفئات</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">من تاريخ</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" 
                                   value="<?php echo $startDate; ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">إلى تاريخ</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" 
                                   value="<?php echo $endDate; ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> تصفية
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- الإحصائيات الإجمالية -->
            <div class="row mb-4">
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3>الطلبات</h3>
                            <div class="value"><?php echo $totalStats['total_orders'] ?: 0; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3>المبيعات</h3>
                            <div class="value"><?php echo number_format($totalStats['total_sales'] ?: 0, 2); ?> ر.س</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>العملاء</h3>
                            <div class="value"><?php echo $totalStats['total_customers'] ?: 0; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>متوسط الطلب</h3>
                            <div class="value"><?php echo number_format($totalStats['avg_order_value'] ?: 0, 2); ?> ر.س</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- المخطط -->
            <?php if ($reportType == 'sales' && !empty($chartData)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3>مخطط المبيعات</h3>
                </div>
                <div class="card-body">
                    <canvas id="reportChart" height="100"></canvas>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- جدول التقرير -->
            <div class="card">
                <div class="card-header">
                    <h3>تفاصيل التقرير</h3>
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> تصدير Excel
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> تصدير PDF
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="printReport()">
                            <i class="fas fa-print"></i> طباعة
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="reportTable">
                            <thead>
                                <tr>
                                    <?php if ($reportType == 'sales'): ?>
                                        <th>التاريخ</th>
                                        <th>عدد الطلبات</th>
                                        <th>إجمالي المبيعات</th>
                                        <th>متوسط قيمة الطلب</th>
                                    <?php elseif ($reportType == 'products'): ?>
                                        <th>المنتج</th>
                                        <th>الرمز</th>
                                        <th>الكمية المباعة</th>
                                        <th>إجمالي الإيرادات</th>
                                        <th>عدد الطلبات</th>
                                    <?php elseif ($reportType == 'customers'): ?>
                                        <th>اسم المستخدم</th>
                                        <th>البريد الإلكتروني</th>
                                        <th>عدد الطلبات</th>
                                        <th>إجمالي المشتريات</th>
                                        <th>آخر طلب</th>
                                    <?php elseif ($reportType == 'categories'): ?>
                                        <th>الفئة</th>
                                        <th>عدد الطلبات</th>
                                        <th>الكمية المباعة</th>
                                        <th>إجمالي الإيرادات</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($reportData)): ?>
                                    <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <?php if ($reportType == 'sales'): ?>
                                            <td><?php echo $row['date']; ?></td>
                                            <td><?php echo $row['order_count']; ?></td>
                                            <td><?php echo number_format($row['total_sales'], 2); ?> ر.س</td>
                                            <td><?php echo number_format($row['avg_order_value'], 2); ?> ر.س</td>
                                        <?php elseif ($reportType == 'products'): ?>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['sku']; ?></td>
                                            <td><?php echo $row['total_sold'] ?: 0; ?></td>
                                            <td><?php echo number_format($row['total_revenue'] ?: 0, 2); ?> ر.س</td>
                                            <td><?php echo $row['order_count'] ?: 0; ?></td>
                                        <?php elseif ($reportType == 'customers'): ?>
                                            <td><?php echo $row['username']; ?></td>
                                            <td><?php echo $row['email']; ?></td>
                                            <td><?php echo $row['order_count'] ?: 0; ?></td>
                                            <td><?php echo number_format($row['total_spent'] ?: 0, 2); ?> ر.س</td>
                                            <td><?php echo $row['last_order'] ? date('Y-m-d', strtotime($row['last_order'])) : 'لا يوجد'; ?></td>
                                        <?php elseif ($reportType == 'categories'): ?>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['order_count'] ?: 0; ?></td>
                                            <td><?php echo $row['total_sold'] ?: 0; ?></td>
                                            <td><?php echo number_format($row['total_revenue'] ?: 0, 2); ?> ر.س</td>
                                        <?php endif; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">لا توجد بيانات</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($reportData) && $reportType == 'sales'): ?>
                            <tfoot>
                                <tr>
                                    <th>الإجمالي</th>
                                    <th><?php echo array_sum(array_column($reportData, 'order_count')); ?></th>
                                    <th><?php echo number_format(array_sum(array_column($reportData, 'total_sales')), 2); ?> ر.س</th>
                                    <th><?php echo number_format(array_sum(array_column($reportData, 'total_sales')) / array_sum(array_column($reportData, 'order_count')), 2); ?> ر.س</th>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // مخطط التقرير
        <?php if ($reportType == 'sales' && !empty($chartData)): ?>
        const ctx = document.getElementById('reportChart').getContext('2d');
        const reportChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'المبيعات',
                    data: <?php echo json_encode($chartData); ?>,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' ر.س';
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        // تصدير إلى Excel
        function exportToExcel() {
            const table = document.getElementById('reportTable');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            rows.forEach(row => {
                const rowData = [];
                row.querySelectorAll('th, td').forEach(cell => {
                    rowData.push(cell.textContent.replace('ر.س', '').trim());
                });
                csv.push(rowData.join(','));
            });
            
            const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "report_<?php echo $reportType; ?>_<?php echo date('Y-m-d'); ?>.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // تصدير إلى PDF
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'pt', 'a4');
            
            doc.setFontSize(18);
            doc.text('تقرير المتجر - <?php echo SITE_NAME; ?>', 40, 40);
            
            doc.setFontSize(12);
            doc.text('نوع التقرير: <?php echo $reportType; ?>', 40, 70);
            doc.text('الفترة: من <?php echo $startDate; ?> إلى <?php echo $endDate; ?>', 40, 90);
            doc.text('تاريخ الإنشاء: <?php echo date('Y-m-d H:i:s'); ?>', 40, 110);
            
            // إضافة الجدول
            doc.autoTable({
                html: '#reportTable',
                startY: 130,
                styles: {
                    font: 'aealarabiya',
                    fontSize: 10,
                    halign: 'right'
                },
                headStyles: {
                    fillColor: [67, 97, 238],
                    textColor: 255
                }
            });
            
            doc.save('report_<?php echo $reportType; ?>_<?php echo date('Y-m-d'); ?>.pdf');
        }
        
        // طباعة التقرير
        function printReport() {
            const printContent = document.querySelector('.admin-content').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = `
                <!DOCTYPE html>
                <html lang="ar" dir="rtl">
                <head>
                    <meta charset="UTF-8">
                    <title>طباعة التقرير</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h1 { color: #333; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
                        th { background-color: #f8f9fa; }
                        .print-header { text-align: center; margin-bottom: 30px; }
                        .print-footer { margin-top: 30px; text-align: center; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>تقرير المتجر - <?php echo SITE_NAME; ?></h1>
                        <p>نوع التقرير: <?php echo $reportType; ?></p>
                        <p>الفترة: من <?php echo $startDate; ?> إلى <?php echo $endDate; ?></p>
                        <p>تاريخ الطباعة: <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                    ${printContent}
                    <div class="print-footer">
                        <p>© <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. جميع الحقوق محفوظة.</p>
                    </div>
                </body>
                </html>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
        
        // تحديث التاريخ الافتراضي
        document.getElementById('end_date').max = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>