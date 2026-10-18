<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    die("وصول غير مصرح به!");
}

// اعتماد الطلب وتحديث حالته
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
    $stmt->execute([$order_id]);
    header("Location: orders.php");
    exit();
}

// جلب جميع الطلبات المباشرة من جدول orders
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الطلبات والشحن</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 1100px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; font-size: 14px; }
        th { background: #0f172a; color: white; }
        .status-pending { color: #d97706; font-weight: bold; }
        .status-completed { color: #16a34a; font-weight: bold; }
        .btn { padding: 6px 12px; color: white; background: #2563eb; text-decoration: none; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>📦 إدارة طلبات المشتريات والتوصيل</h2>
    <a href="admin_dashboard.php">🏠 العودة للوحة التحكم</a>
    <table>
        <tr>
            <th>رقم الطلب</th>
            <th>العميل / المنتج</th>
            <th>عنوان التوصيل</th>
            <th>رقم التواصل</th>
            <th>طريقة الدفع</th>
            <th>الحالة</th>
            <th>الإجراء</th>
        </tr>
        <?php if(!empty($orders)): ?>
            <?php foreach($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['product_name'] ?? $o['title'] ?? 'طلب منتج') ?></td>
                <td><?= htmlspecialchars($o['address'] ?? 'غير محدد') ?></td>
                <td><?= htmlspecialchars($o['phone'] ?? 'غير محدد') ?></td>
                <td><?= htmlspecialchars($o['payment_method'] ?? 'الدفع عند الاستلام') ?></td>
                <td>
                    <?php if(($o['status'] ?? '') == 'completed'): ?>
                        <span class="status-completed">مكتمل ومُسلم</span>
                    <?php else: ?>
                        <span class="status-pending">قيد التجهيز</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if(($o['status'] ?? '') != 'completed'): ?>
                        <a href="orders.php?action=complete&id=<?= $o['id'] ?>" class="btn">اعتماد وتوصيل</a>
                    <?php else: ?>
                        تم التوصيل
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">لا توجد طلبات مسجلة حالياً.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
