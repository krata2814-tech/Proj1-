<?php
session_start();
require 'db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // التحقق المزدوج: بكلمة المرور 123456 أو بالتشفير
    if ($user && ($password === '123456' || password_verify($password, $user['password']))) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

        // توجيه المستخدم حسب نوع الحساب
        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] === 'staff') {
            header("Location: staff_dashboard.php");
        } else {
            header("Location: index.php"); // توجيه الزبون للمتجر مباشرة
        }
        exit();
    } else {
        $error = "بيانات الدخول غير صحيحة!";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول - متجر الإلكترونيات</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; padding: 50px; text-align: center; }
        .login-box { background: white; padding: 30px; width: 340px; margin: auto; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { background: #2563eb; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .error-msg { color: red; font-size: 14px; margin-bottom: 10px; }
        .links { margin-top: 15px; font-size: 14px; }
        .links a { color: #2563eb; text-decoration: none; font-weight: bold; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>تسجيل الدخول</h2>
        
        <?php if($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit">دخول</button>
        </form>

        <div class="links">
            <p>ليس لديك حساب؟ <a href="register.php">أنشئ حساب زبون جديد</a></p>
            <p><a href="index.php" style="color: #64748b;">⬅ تصفح المعرض كزائر</a></p>
        </div>
    </div>

</body>
</html>
