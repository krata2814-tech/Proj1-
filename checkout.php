<?php
session_start();
require 'db_config.php';

// التأكد من تسجيل دخول العميل
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$product_id = intval($_GET['product_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("المنتج غير موجود!");
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address        = trim($_POST['address']);
    $phone          = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'];
    $user_id        = $_SESSION['user_id'];

    // إدراج الطلب مع بيانات التوصيل والدفع
    $ins = $pdo->prepare("INSERT INTO orders (user_id, product_id, address, phone, payment_method, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    if ($ins->execute([$user_id, $product_id, $address, $phone, $payment_method])) {
        $msg = "تم إرسال طلبك بنجاح! سيتم التواصل معك لتأكيد التوصيل.";
    } else {
        $msg = "حدث خطأ أثناء إرسال الطلب.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تأكيد الطلب والتوصيل</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; padding: 20px; }
        .box { max-width: 500px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .alert { color: green; font-weight: bold; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="box">
    <h2>🚚 تأكيد الشراء والتوصيل</h2>
    <p><strong>المنتج:</strong> <?= htmlspecialchars($product['title']) ?></p>
    <p><strong>السعر الإجمالي:</strong> $<?= number_format($product['price'], 2) ?></p>
    <hr>

    <?php if($msg): ?>
        <p class="alert"><?= $msg ?></p>
        <a href="index.php">العودة للمتجر الرئيسي</a>
    <?php else: ?>
        <form method="POST">
            <div class="form-group">
                <label>عنوان التوصيل (المدينة / الشارع):</label>
                <textarea name="address" rows="3" placeholder="أدخل عنوان السكن بالتفصيل..." required></textarea>
            </div>
            <div class="form-group">
                <label>رقم الهاتف للتواصل:</label>
                <input type="text" name="phone" placeholder="077xxxxxxx" required>
            </div>
            <div class="form-group">
                <label>طريقة الدفع:</label>
                <select name="payment_method">
                    <option value="Cash on Delivery">الدفع نقداً عند الاستلام (COD)</option>
                    <option value="Local Transfer">حوالة كرييمي / محفظة إلكترونية</option>
                </select>
            </div>
            <button type="submit">إرسال وتأكيد الطلب</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
