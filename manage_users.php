<?php
session_start();
require 'db_config.php';

// حصر الصلاحية بالمدير فقط
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("وصول غير مصرح به! هذه الصفحة مخصصة لمدير النظام فقط.");
}

$msg = '';

// 1. حذف مستخدم (عميل أو موظف)
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $del = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $del->execute([$user_id]);
    $msg = "تم حذف الحساب بنجاح.";
}

// 2. إضافة مستخدم جديد (موظف / عميل)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $ins = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($ins->execute([$name, $email, $password, $role])) {
        $msg = "تمت إضافة المستخدم بنجاح.";
    }
}

// 3. تعديل دور أو بيانات مستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $user_id = intval($_POST['user_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    $up = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    $up->execute([$name, $email, $role, $user_id]);
    $msg = "تم تعديل بيانات الحساب بنجاح.";
}

// جلب كل المستخدمين
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة كافة المستخدمين والموظفين</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; padding: 20px; }
        .box { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background: #0f172a; color: white; }
        .form-inline { display: flex; gap: 10px; margin-bottom: 20px; background: #eee; padding: 15px; border-radius: 6px; }
        input, select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 8px 12px; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-del { background: #dc2626; }
        .alert { background: #d1e7dd; color: #0f5132; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="box">
    <h2>👥 لوحة التحكم بالمستخدمين والموظفين (حصرياً للأدمن)</h2>
    <a href="admin_dashboard.php" class="btn">🏠 العودة للوحة التحكم الرئيسية</a>
    <br><br>

    <?php if($msg): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <h3>➕ إضافة موظف أو عميل جديد</h3>
    <form method="POST" class="form-inline">
        <input type="text" name="name" placeholder="الاسم الكامل" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <select name="role">
            <option value="staff">موظف (Staff)</option>
            <option value="customer">عميل (Customer)</option>
            <option value="admin">مدير (Admin)</option>
        </select>
        <button type="submit" name="add_user" class="btn">إضافة الحساب</button>
    </form>

    <h3>📋 قائمة كافة الحسابات المسجلة</h3>
    <table>
        <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>الصلاحية / الدور</th>
            <th>الإجراءات (تعديل / حذف)</th>
        </tr>
        <?php foreach($users as $u): ?>
        <tr>
            <form method="POST">
                <td><?= $u['id'] ?></td>
                <td><input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" required></td>
                <td><input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required></td>
                <td>
                    <select name="role">
                        <option value="customer" <?= $u['role']=='customer'?'selected':'' ?>>عميل</option>
                        <option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>موظف</option>
                        <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>مدير</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" name="edit_user" class="btn">حفظ التعديل</button>
                    <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <a href="manage_users.php?delete=<?= $u['id'] ?>" class="btn btn-del" onclick="return confirm('هل أنت تأكد من حذف هذا الحساب؟');">حذف</a>
                    <?php endif; ?>
                </td>
            </form>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
