<?php
session_start();
require 'db_config.php';

// التحقق من الصلاحيات (الأدمن والموظفين فقط)
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    die("وصول غير مصرح به!");
}

$type = $_GET['type'] ?? '';
$id   = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. حذف منتج
    if ($type === 'product') {
        // إذا كان موظف عادي، يحذف أجهزته فقط
        if ($_SESSION['role'] === 'staff') {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND added_by = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
        } else {
            // الأدمن يحذف أي منتج
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
        }
        header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'staff_dashboard.php'));
        exit();
    }

    // 2. حذف مستخدم (خاص بالأدمن فقط)
    if ($type === 'user' && $_SESSION['role'] === 'admin') {
        // حماية حساب الأدمن الأساسي من الحذف
        if ($id !== 1) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }
        header("Location: admin_dashboard.php");
        exit();
    }
}

// إعادة توجيه في حال عدم التوافق
header("Location: index.php");
exit();
