<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'staff' && $_SESSION['role'] !== 'admin')) {
    die("وصول غير مصرح به!");
}

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("المنتج غير موجود!");
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $price = trim($_POST['price']);

    $upd = $pdo->prepare("UPDATE products SET title = ?, price = ? WHERE id = ?");
    if ($upd->execute([$title, $price, $id])) {
        $msg = "تم تحديث البيانات بنجاح!";
        $product['title'] = $title;
        $product['price'] = $price;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل منتج</title>
    <style>
        body { font-family: Tahoma, sans-serif; padding: 30px; background: #f8f9fa; }
        .box { max-width: 400px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; }
    </style>
</head>
<body>
<div class="box">
    <h3>✏️ تعديل بيانات الجهاز</h3>
    <?php if($msg): ?><p style="color:green;"><?= $msg ?></p><?php endif; ?>
    <form method="POST">
        <label>اسم الجهاز:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($product['title']) ?>" required>
        <label>السعر ($):</label>
        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>
        <button type="submit">حفظ التغييرات</button>
    </form>
    <br>
    <a href="staff_dashboard.php">العودة للوحة التحكم</a>
</div>
</body>
</html>
