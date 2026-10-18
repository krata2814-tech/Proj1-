<?php
session_start();
require 'db_config.php';

// التحقق من صلاحيات الأدمن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// إضافة منتج جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $image_name = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('prod_') . '.' . $ext;
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image_name);
    }

    $stmt = $pdo->prepare("INSERT INTO products (title, description, price, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $price, $image_name]);
    header('Location: admin_dashboard.php');
    exit;
}

// حذف منتج
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: admin_dashboard.php');
    exit;
}

// جلب البيانات والإحصائيات
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_products = count($products);
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة الإدارة الرئيسية</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; color: #334155; padding: 20px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .nav-btns { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .btn { padding: 10px 18px; border-radius: 6px; text-decoration: none; color: white; font-weight: bold; font-size: 14px; border: none; cursor: pointer; }
        .btn-blue { background: #2563eb; }
        .btn-green { background: #16a34a; }
        .btn-red { background: #dc2626; }
        .btn-dark { background: #475569; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #0f172a; color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h3 { font-size: 14px; color: #94a3b8; margin-bottom: 8px; }
        .stat-card p { font-size: 24px; font-weight: bold; }
        .form-section, .table-section { background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: right; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #0f172a; color: white; }
        .admin-img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-bar">
        <h2>🛠️ لوحة الإدارة الرئيسية - متجر الإلكترونيات</h2>
        <a href="logout.php" class="btn btn-red">تسجيل الخروج</a>
    </div>

    <div class="nav-btns">
        <a href="manage_users.php" class="btn btn-green">👥 إدارة الموظفين والعملاء</a>
        <a href="admin_orders.php" class="btn btn-blue">📦 إدارة واستعراض الطلبات</a>
        <a href="index.php" class="btn btn-dark">🛒 عرض الواجهة العامة للمتجر</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>إجمالي المنتجات</h3>
            <p><?= $total_products ?></p>
        </div>
        <div class="stat-card">
            <h3>المستخدمين والموظفين</h3>
            <p><?= $total_users ?></p>
        </div>
        <div class="stat-card">
            <h3>إجمالي الطلبات</h3>
            <p><?= $total_orders ?></p>
        </div>
    </div>

    <div class="form-section">
        <h3>➕ إضافة منتج إلكتروني جديد</h3>
        <form method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
            <div class="form-group">
                <label>اسم المنتج:</label>
                <input type="text" name="title" required placeholder="مثال: شاشة سامسونج 27 بوصة">
            </div>
            <div class="form-group">
                <label>الوصف:</label>
                <textarea name="description" rows="2" placeholder="أدخل المواصفات الفنية للمنتج..."></textarea>
            </div>
            <div class="form-group">
                <label>السعر ($):</label>
                <input type="number" step="0.01" name="price" required placeholder="0.00">
            </div>
            <div class="form-group">
                <label>صورة المنتج:</label>
                <input type="file" name="image" accept="image/*">
            </div>
            <button type="submit" name="add_product" class="btn btn-green">حفظ ونشر المنتج</button>
        </form>
    </div>

    <div class="table-section">
        <h3>📦 المنتجات المسجلة في المتجر</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>الصورة</th>
                    <th>اسم المنتج</th>
                    <th>السعر</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $p): 
                    // تحديد الصورة الافتراضية بحسب اسم المنتج إن لم تكن هناك صورة مرفوعة
                    $display_img = '';
                    if (!empty($p['image'])) {
                        $display_img = 'uploads/' . $p['image'];
                    } else {
                        $t = mb_strtolower($p['title']);
                        if (mb_strpos($t, 'شاشة') !== false || mb_strpos($t, 'screen') !== false || mb_strpos($t, 'curved') !== false) {
                            $display_img = 'https://images.pexels.com/photos/777001/pexels-photo-777001.jpeg?auto=compress&cs=tinysrgb&w=150';
                        } elseif (mb_strpos($t, 'سماعة') !== false || mb_strpos($t, 'sony') !== false) {
                            $display_img = 'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg?auto=compress&cs=tinysrgb&w=150';
                        } elseif (mb_strpos($t, 'لابتوب') !== false || mb_strpos($t, 'asus') !== false || mb_strpos($t, 'lenovo') !== false) {
                            $display_img = 'https://images.pexels.com/photos/18105/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=150';
                        } elseif (mb_strpos($t, 'هاتف') !== false || mb_strpos($t, 'samsung') !== false || mb_strpos($t, 'galaxy') !== false) {
                            $display_img = 'https://images.pexels.com/photos/404280/pexels-photo-404280.jpeg?auto=compress&cs=tinysrgb&w=150';
                        } elseif (mb_strpos($t, 'ssd') !== false || mb_strpos($t, 'قرص') !== false) {
                            $display_img = 'https://images.pexels.com/photos/2582937/pexels-photo-2582937.jpeg?auto=compress&cs=tinysrgb&w=150';
                        } elseif (mb_strpos($t, 'شاحن') !== false || mb_strpos($t, 'hp') !== false) {
                            $display_img = 'https://images.pexels.com/photos/5208771/pexels-photo-5208771.jpeg?auto=compress&cs=tinysrgb&w=150';
                        } else {
                            $display_img = 'https://images.pexels.com/photos/356056/pexels-photo-356056.jpeg?auto=compress&cs=tinysrgb&w=150';
                        }
                    }
                ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($display_img) ?>" class="admin-img-thumb" alt="صورة">
                        </td>
                        <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-blue" style="padding: 5px 10px; font-size: 12px;">تعديل</a>
                            <a href="admin_dashboard.php?delete=<?= $p['id'] ?>" class="btn btn-red" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('هل أنت تأكد من الحذف؟')">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
