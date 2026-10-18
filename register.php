<?php
session_start();
require 'db_config.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    // فحص هل البريد مستخدم سابقاً
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        $msg = "البريد الإلكتروني مستخدم بالفعل!";
    } else {
        // إنشاء الحساب كعميل تلقائياً role = customer
        $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_protected) VALUES (?, ?, ?, 'customer', 0)");
        
        if ($stmt->execute([$name, $email, $hashed_pass])) {
            echo "<script>
                alert('تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.');
                window.location.href = 'login.php';
            </script>";
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب جديد - زبون</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f7f6; padding: 50px; text-align: center; }
        .box { background: white; padding: 30px; width: 340px; margin: auto; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        input, button { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #218838; }
        a { color: #007bff; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>إنشاء حساب زبون جديد</h2>
        <?php if($msg): ?><p style="color:red;"><?= $msg ?></p><?php endif; ?>
        <form method="POST">
            <input type="text" name="name" placeholder="الاسم الكامل" required>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit">تسجيل الحساب</button>
        </form>
        <br>
        <a href="login.php">لديك حساب بالفعل؟ سجل دخولك هنا</a>
    </div>
</body>
</html>
