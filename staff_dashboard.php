<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    die("وصول غير مصرح به!");
}

$current_user_id = $_SESSION['user_id'];
$msg = '';

// إنشاء مجلد uploads تلقائياً إذا لم يكن موجوداً على الاستضافة
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// إضافة منتج جديد مع رفع الصورة محلياً
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $title = trim($_POST['title']);
    $price = trim($_POST['price']);
    $image_url = '';

    // معالجة الملف المرفوع
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // الأنواع المسموح بها
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($file_ext, $allowed_exts)) {
            // توليد اسم فريد للصورة لتفادي تكرار الأسماء
            $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
            $target_path   = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $image_url = $target_path;
            } else {
                $msg = "فشل نقل الملف المرفوع إلى السيرفر.";
            }
        } else {
            $msg = "امتداد الصورة غير مسموح به! المسموح: JPG, PNG, WEBP, GIF";
        }
    } else {
        $msg = "يرجى اختيار صورة للجهاز.";
    }

    // حفظ البيانات في قاعدة البيانات عند نجاح رفع الصورة
    if (empty($msg) && !empty($image_url)) {
        $ins = $pdo->prepare("INSERT INTO products (title, price, image_url, added_by) VALUES (?, ?, ?, ?)");
        if ($ins->execute([$title, $price, $image_url, $current_user_id])) {
            $msg = "تم إضافة الجهاز وصورته بنجاح!";
        } else {
            $msg = "حدث خطأ أثناء حفظ البيانات في قاعدة البيانات.";
        }
    }
}

// عزل البيانات: جلب المنتجات المضافة بواسطة هذا الفني فقط
$stmt = $pdo->prepare("SELECT * FROM products WHERE added_by = ? ORDER BY id DESC");
$stmt->execute([$current_user_id]);
$my_products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة الفنيين - إدارة المنتجات</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f8f9fa; padding: 25px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input[type="text"], input[type="number"], input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #0f172a; color: white; }
        .img-preview { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
        .alert { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>🔧 لوحة التحكم - الفني: <?= htmlspecialchars($_SESSION['name']) ?></h2>
        <div>
            <a href="index.php" style="margin-left: 10px; text-decoration: none; color: #007bff;">🏠 المعرض</a>
            <a href="logout.php" style="color: red; text-decoration: none;">تسجيل الخروج</a>
        </div>
    </div>

    <?php if($msg): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <h3>➕ إضافة جهاز / قطعة صيانة جديدة</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>اسم الجهاز / القطعة:</label>
            <input type="text" name="title" required placeholder="مثال: شاشة لابتوب Lenovo 15.6">
        </div>
        <div class="form-group">
            <label>السعر ($):</label>
            <input type="number" step="0.01" name="price" required placeholder="مثال: 65.00">
        </div>
        <div class="form-group">
            <label>اختر صورة من جهازك:</label>
            <input type="file" name="image" accept="image/*" required>
        </div>
        <button type="submit" name="add_product">حفظ وإضافة للمتجر</button>
    </form>

    <hr style="margin: 30px 0;">

    <h3>📦 الأجهزة المضافة بواسطتك فقط</h3>
    <table>
        <tr>
            <th>الصورة</th>
            <th>رقم الجهاز</th>
            <th>اسم الجهاز</th>
            <th>السعر</th>
            <th>الإجراءات</th>
        </tr>
        <?php if(count($my_products) > 0): ?>
            <?php foreach($my_products as $p): ?>
            <tr>
                <td>
                    <?php if(!empty($p['image_url'])): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" class="img-preview">
                    <?php else: ?>
                        <span>بدون صورة</span>
                    <?php endif; ?>
                </td>
                <td>#<?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><strong><?= number_format($p['price'], 2) ?> $</strong></td>
                <td><a href="edit_product.php?id=<?= $p['id'] ?>" style="color: #007bff; font-weight: bold;">تعديل</a></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">لم تقم بإضافة أي أجهزة حتى الآن.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
