<?php
// admin/products.php - إدارة المنتجات

require_once '../config.php';
require_once '../db_connection.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$db = Database::getInstance();

// معالجة العمليات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateToken($_POST['csrf_token'])) {
        $_SESSION['error'] = 'رمز التحقق غير صالح';
        redirect('products.php');
    }
    
    if (isset($_POST['delete_product'])) {
        $productId = (int)$_POST['product_id'];
        $db->delete('products', "id = $productId");
        $_SESSION['success'] = 'تم حذف المنتج بنجاح';
        redirect('products.php');
    }
    
    if (isset($_POST['bulk_action'])) {
        $action = $_POST['bulk_action'];
        $productIds = $_POST['product_ids'] ?? [];
        
        if (!empty($productIds)) {
            $ids = implode(',', array_map('intval', $productIds));
            
            switch ($action) {
                case 'delete':
                    $db->query("DELETE FROM products WHERE id IN ($ids)");
                    $_SESSION['success'] = 'تم حذف المنتجات المحددة بنجاح';
                    break;
                    
                case 'activate':
                    $db->query("UPDATE products SET status = 'active' WHERE id IN ($ids)");
                    $_SESSION['success'] = 'تم تفعيل المنتجات المحددة بنجاح';
                    break;
                    
                case 'deactivate':
                    $db->query("UPDATE products SET status = 'inactive' WHERE id IN ($ids)");
                    $_SESSION['success'] = 'تم إيقاف المنتجات المحددة بنجاح';
                    break;
                    
                case 'featured':
                    $db->query("UPDATE products SET is_featured = 1 WHERE id IN ($ids)");
                    $_SESSION['success'] = 'تم تمييز المنتجات المحددة بنجاح';
                    break;
                    
                case 'unfeatured':
                    $db->query("UPDATE products SET is_featured = 0 WHERE id IN ($ids)");
                    $_SESSION['success'] = 'تم إلغاء تمييز المنتجات المحددة بنجاح';
                    break;
            }
        }
        
        redirect('products.php');
    }
}

// الحصول على معاملات البحث والتصفية
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// بناء استعلام المنتجات
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";
        
$params = [];
$whereConditions = [];

if (!empty($search)) {
    $whereConditions[] = "(p.name LIKE :search OR p.sku LIKE :search OR p.description LIKE :search)";
    $params['search'] = "%$search%";
}

if ($category > 0) {
    $whereConditions[] = "p.category_id = :category_id";
    $params['category_id'] = $category;
}

if (!empty($status)) {
    $whereConditions[] = "p.status = :status";
    $params['status'] = $status;
}

if (!empty($whereConditions)) {
    $sql .= " AND " . implode(" AND ", $whereConditions);
}

// الحصول على العدد الإجمالي
$countSql = "SELECT COUNT(*) as total FROM products p WHERE 1=1";
if (!empty($whereConditions)) {
    $countSql .= " AND " . implode(" AND ", $whereConditions);
}

$totalResult = $db->fetch($countSql, $params);
$totalProducts = $totalResult['total'];
$totalPages = ceil($totalProducts / $perPage);

// إضافة التحديد والترتيب
$sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
$params['limit'] = $perPage;
$params['offset'] = $offset;

// الحصول على المنتجات
$products = $db->fetchAll($sql, $params);

// الحصول على الفئات للفلتر
$categories = $db->fetchAll("SELECT id, name FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <?php include 'includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1><i class="fas fa-box"></i> إدارة المنتجات</h1>
                <div class="header-actions">
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة منتج جديد
                    </a>
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="fas fa-tags"></i> إدارة الفئات
                    </a>
                </div>
            </div>
            
            <!-- رسائل النجاح والخطأ -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- أدوات البحث والفلترة -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="بحث بالاسم أو الوصف أو SKU" 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <select class="form-select" name="category">
                                <option value="0">جميع الفئات</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">جميع الحالات</option>
                                <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>نشط</option>
                                <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>غير نشط</option>
                                <option value="out_of_stock" <?php echo $status == 'out_of_stock' ? 'selected' : ''; ?>>نفذ من المخزون</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> بحث
                            </button>
                        </div>
                        
                        <div class="col-md-2">
                            <a href="products.php" class="btn btn-secondary w-100">
                                <i class="fas fa-redo"></i> إعادة تعيين
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- جدول المنتجات -->
            <div class="card">
                <div class="card-header">
                    <h3>قائمة المنتجات</h3>
                    <div class="card-actions">
                        <form method="POST" class="d-inline" id="bulkForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="input-group">
                                <select class="form-select form-select-sm" name="bulk_action" style="width: 150px;">
                                    <option value="">إجراء جماعي</option>
                                    <option value="delete">حذف المحدد</option>
                                    <option value="activate">تفعيل</option>
                                    <option value="deactivate">إيقاف</option>
                                    <option value="featured">تمييز</option>
                                    <option value="unfeatured">إلغاء التمييز</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">تطبيق</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($products)): ?>
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> لا توجد منتجات
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th width="80">الصورة</th>
                                    <th>المنتج</th>
                                    <th>الفئة</th>
                                    <th>السعر</th>
                                    <th>المخزون</th>
                                    <th>الحالة</th>
                                    <th>مميز</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="product_ids[]" value="<?php echo $product['id']; ?>" class="product-checkbox">
                                    </td>
                                    <td>
                                        <?php if (!empty($product['image'])): ?>
                                        <img src="../assets/uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-thumbnail">
                                        <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <div class="text-muted small">SKU: <?php echo htmlspecialchars($product['sku']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'غير مصنف'); ?></td>
                                    <td>
                                        <span class="text-primary"><?php echo number_format($product['price'], 2); ?> ر.س</span>
                                        <?php if ($product['discount_price']): ?>
                                        <div class="text-muted small text-decoration-line-through">
                                            <?php echo number_format($product['discount_price'], 2); ?> ر.س
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $product['stock_quantity'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($product['status']) {
                                                case 'active': echo 'success'; break;
                                                case 'inactive': echo 'secondary'; break;
                                                case 'out_of_stock': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php 
                                            $statusText = [
                                                'active' => 'نشط',
                                                'inactive' => 'غير نشط',
                                                'out_of_stock' => 'نفذ'
                                            ];
                                            echo $statusText[$product['status']] ?? $product['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['is_featured']): ?>
                                        <span class="badge bg-warning"><i class="fas fa-star"></i> مميز</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">عادي</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="../product.php?slug=<?php echo $product['slug']; ?>" target="_blank" class="btn btn-info" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger delete-btn" 
                                                    data-id="<?php echo $product['id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                    title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- الترقيم -->
                    <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    السابق
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    التالي
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- نموذج حذف المنتج -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من حذف المنتج "<span id="productName"></span>"؟</p>
                    <p class="text-danger">هذا الإجراء لا يمكن التراجع عنه.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="product_id" id="deleteProductId">
                        <input type="hidden" name="delete_product" value="1">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // تحديد/إلغاء تحديد الكل
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }
        
        // تحديث حالة تحديد الكل
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                selectAll.checked = allChecked;
            });
        });
        
        // فتح نافذة تأكيد الحذف
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                
                document.getElementById('productName').textContent = productName;
                document.getElementById('deleteProductId').value = productId;
                deleteModal.show();
            });
        });
        
        // إرسال النموذج الجماعي
        const bulkForm = document.getElementById('bulkForm');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                const action = this.querySelector('[name="bulk_action"]').value;
                const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
                
                if (!action) {
                    e.preventDefault();
                    alert('الرجاء اختيار إجراء');
                    return;
                }
                
                if (checkedBoxes.length === 0) {
                    e.preventDefault();
                    alert('الرجاء تحديد منتجات على الأقل');
                    return;
                }
                
                if (action === 'delete' && !confirm('هل أنت متأكد من حذف المنتجات المحددة؟')) {
                    e.preventDefault();
                    return;
                }
            });
        }
    });
    </script>
</body>
</html>