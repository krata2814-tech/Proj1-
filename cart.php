<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// حذف عنصر من السلة
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $del = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $del->execute([$cart_id, $user_id]);
    header("Location: cart.php");
    exit();
}

// جلب كافة المنتجات في السلة للعميل
$stmt = $pdo->prepare("SELECT cart.id as cart_id, cart.quantity, products.* 
                       FROM cart 
                       JOIN products ON cart.product_id = products.id 
                       WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// تنفيذ وإرسال طلبات السلة
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($cart_items)) {
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'];

    foreach ($cart_items as $item) {
        $ins = $pdo->prepare("INSERT INTO orders (user_id, product_id, address, phone, payment_method, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $ins->execute([$user_id, $item['id'], $address, $phone, $payment_method]);
    }

    // تفريغ السلة بعد إتمام الطلب
    $clear = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear->execute([$user_id]);

    $msg = "تم تأكيد طلبك بنجاح ونقله لقسم التنفيذ!";
    $cart_items = [];
    $total_price = 0;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عربة التسوق</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #0f172a; color: white; }
        .btn-del { color: red; text-decoration: none; font-weight: bold; }
        .btn-confirm { background: #16a34a; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 16px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        .alert { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>🛒 عربة التسوق الخاص بك</h2>
    <a href="index.php">🛍️ العودة لمتابعة التسوق</a>
    <br><br>

    <?php if($msg): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <?php if(!empty($cart_items)): ?>
        <table>
            <tr>
                <th>المنتج</th>
                <th>السعر الفردي</th>
                <th>الكمية</th>
                <th>الإجمالي</th>
                <th>إجراء</th>
            </tr>
            <?php foreach($cart_items as $ci): ?>
            <tr>
                <td><?= htmlspecialchars($ci['title']) ?></td>
                <td>$<?= number_format($ci['price'], 2) ?></td>
                <td><?= $ci['quantity'] ?></td>
                <td>$<?= number_format($ci['price'] * $ci['quantity'], 2) ?></td>
                <td><a href="cart.php?remove=<?= $ci['cart_id'] ?>" class="btn-del">حذف</a></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="3">المبلغ الإجمالي المترتب:</th>
                <th colspan="2">$<?= number_format($total_price, 2) ?></th>
            </tr>
        </table>

        <h3>🚚 تفاصيل الشحن وتأكيد الطلب</h3>
        <form method="POST">
            <div class="form-group">
                <label>عنوان التوصيل (المدينة / الشارع):</label>
                <textarea name="address" rows="2" required placeholder="أدخل العنوان التفصيلي..."></textarea>
            </div>
            <div class="form-group">
                <label>رقم التواصل:</label>
                <input type="text" name="phone" required placeholder="077xxxxxxx">
            </div>
            <div class="form-group">
                <label>طريقة الدفع:</label>
                <select name="payment_method">
                    <option value="Cash on Delivery">الدفع نقداً عند الاستلام (COD)</option>
                    <option value="Local Transfer">حوالة مصرفية / محفظة إلكترونية</option>
                </select>
            </div>
            <button type="submit" class="btn-confirm">تأكيد وإرسال الطلب الآن</button>
        </form>
    <?php else: ?>
        <p>عربة التسوق فارغة حالياً.</p>
    <?php endif; ?>
</div>
</body>
</html>
