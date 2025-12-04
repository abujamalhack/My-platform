<?php
// admin/login.php
require_once 'config.php';

// إذا كان مسجلاً بالفعل، توجيه للوحة التحكم
if (is_admin_logged_in()) {
    header('Location: index.php');
    exit();
}

// معالجة نموذج تسجيل الدخول
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (admin_login($username, $password)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | لوحة تحكم كاردي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-box {
            background: white;
            width: 400px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            text-align: center;
        }
        .login-logo {
            color: #667eea;
            font-size: 50px;
            margin-bottom: 20px;
        }
        .login-box h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .login-box p {
            color: #666;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s;
        }
        input:focus {
            border-color: #667eea;
            outline: none;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        .credentials {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .credentials strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <i class="fas fa-credit-card"></i>
        </div>
        <h2>تسجيل الدخول</h2>
        <p>لوحة تحكم متجر كاردي</p>
        
        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">اسم المستخدم:</label>
                <input type="text" id="username" name="username" required 
                       placeholder="أدخل اسم المستخدم">
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="أدخل كلمة المرور">
            </div>
            
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </button>
        </form>
        
        <div class="credentials">
            <strong>بيانات الدخول الافتراضية:</strong><br>
            اسم المستخدم: <strong>admin</strong><br>
            كلمة المرور: <strong>admin123</strong>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="../" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> العودة إلى المتجر
            </a>
        </div>
    </div>
</body>
</html>
