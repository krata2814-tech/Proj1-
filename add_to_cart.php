<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_GET['id'] ?? 0);

if ($product_id > 0) {
    // التحقق مما إذا كان المنتج موجوداً في السلة مسبقاً
    $check = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $check->execute([$user_id, $product_id]);
    $item = $check->fetch();

    if ($item) {
        // زيادة الكمية
        $update = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $update->execute([$item['id']]);
    } else {
        // إضافة المنتج كعنصر جديد
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->execute([$user_id, $product_id]);
    }
}

header("Location: cart.php");
exit();
?>
