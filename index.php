<?php
session_start();
require 'db_config.php';

// جلب المنتجات من قاعدة البيانات
$products = [];
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // استمرار العمل عند خطأ الاتصال
}

// صور افتراضية مباشرة وموثوقة حسَب نوع الجهاز
function getProductImage($title, $db_img) {
    $title_lower = mb_strtolower($title);

    // إذا كانت هناك صورة مضافة في قاعدة البيانات وتعمل
    if (!empty($db_img)) {
        if (strpos($db_img, 'http://') === 0 || strpos($db_img, 'https://') === 0) {
            return $db_img;
        } elseif (file_exists('uploads/' . $db_img)) {
            return 'uploads/' . $db_img;
        }
    }

    // روابط صور مباشرة وثابتة بناءً على نوع المنتج
    if (strpos($title_lower, 'شاشة') !== false || strpos($title_lower, 'screen') !== false) {
        return 'https://images.pexels.com/photos/777001/pexels-photo-777001.jpeg?auto=compress&cs=tinysrgb&w=600';
    } elseif (strpos($title_lower, 'سماعة') !== false || strpos($title_lower, 'sony') !== false) {
        return 'https://images.pexels.com/photos/3394650/pexels-photo-3394650.jpeg?auto=compress&cs=tinysrgb&w=600';
    } elseif (strpos($title_lower, 'لابتوب') !== false || strpos($title_lower, 'asus') !== false || strpos($title_lower, 'lenovo') !== false) {
        return 'https://images.pexels.com/photos/18105/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=600';
    } elseif (strpos($title_lower, 'هاتف') !== false || strpos($title_lower, 'samsung') !== false || strpos($title_lower, 'galaxy') !== false) {
        return 'https://images.pexels.com/photos/404280/pexels-photo-404280.jpeg?auto=compress&cs=tinysrgb&w=600';
    } elseif (strpos($title_lower, 'ssd') !== false || strpos($title_lower, 'قرص') !== false) {
        return 'https://images.pexels.com/photos/2582937/pexels-photo-2582937.jpeg?auto=compress&cs=tinysrgb&w=600';
    } elseif (strpos($title_lower, 'شاحن') !== false || strpos($title_lower, 'hp') !== false) {
        return 'https://images.pexels.com/photos/5208771/pexels-photo-5208771.jpeg?auto=compress&cs=tinysrgb&w=600';
    }

    // صورة تقنية افتراضية لجميع الأجهزة الأخرى
    return 'https://images.pexels.com/photos/356056/pexels-photo-356056.jpeg?auto=compress&cs=tinysrgb&w=600';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المتجر الإلكتروني المتكامل | الرئيسية</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f1f5f9; color: #334155; min-height: 100vh; display: flex; flex-direction: column; }
        header { background-color: #0f172a; color: #ffffff; padding: 18px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15); }
        .logo h1 { font-size: 24px; font-weight: 700; }
        .nav-menu { display: flex; gap: 12px; align-items: center; }
        .nav-link { color: #ffffff; text-decoration: none; padding: 9px 18px; border-radius: 6px; font-weight: 600; font-size: 14px; background-color: #334155; transition: background 0.2s; }
        .nav-link:hover { background-color: #475569; }
        .btn-cart { background-color: #16a34a; }
        .btn-cart:hover { background-color: #15803d; }
        .btn-admin { background-color: #2563eb; }
        .btn-admin:hover { background-color: #1d4ed8; }
        .btn-logout { background-color: #dc2626; }
        .btn-logout:hover { background-color: #b91c1c; }
        .main-container { max-width: 1200px; width: 90%; margin: 40px auto; flex-grow: 1; }
        .section-header { margin-bottom: 25px; border-bottom: 2px solid #cbd5e1; padding-bottom: 12px; }
        .section-header h2 { font-size: 22px; color: #0f172a; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .product-card { background: #ffffff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; border: 1px solid #e2e8f0; transition: transform 0.2s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .image-wrapper { width: 100%; height: 200px; background-color: #f8fafc; overflow: hidden; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: center; }
        .product-image { width: 100%; height: 100%; object-fit: cover; }
        .card-body { padding: 18px; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; }
        .product-title { font-size: 17px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .product-desc { font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 15px; height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .card-footer { display: flex; flex-direction: column; gap: 12px; margin-top: auto; }
        .price-tag { font-size: 20px; font-weight: 800; color: #0f172a; }
        .add-cart-btn { background-color: #2563eb; color: #ffffff; text-decoration: none; text-align: center; padding: 11px; border-radius: 6px; font-weight: 700; font-size: 15px; transition: background-color 0.2s; }
        .add-cart-btn:hover { background-color: #1d4ed8; }
        footer { background-color: #0f172a; color: #94a3b8; text-align: center; padding: 15px; margin-top: auto; font-size: 13px; }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <h1>💻 متجر الأجهزة الإلكترونية</h1>
    </div>
    <nav class="nav-menu">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="cart.php" class="nav-link btn-cart">🛒 عربة التسوق</a>
            
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="nav-link btn-admin">🛠️ لوحة الأدمن</a>
            <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] === 'staff'): ?>
                <a href="staff_dashboard.php" class="nav-link btn-admin">📋 لوحة الموظف</a>
            <?php endif; ?>

            <a href="logout.php" class="nav-link btn-logout">تسجيل الخروج</a>
        <?php else: ?>
            <a href="login.php" class="nav-link">تسجيل الدخول</a>
            <a href="register.php" class="nav-link btn-cart">إنشاء حساب جديد</a>
        <?php endif; ?>
    </nav>
</header>

<main class="main-container">
    <div class="section-header">
        <h2>📱 المنتجات والأجهزة المتاحة</h2>
    </div>

    <div class="products-grid">
        <?php foreach($products as $p): ?>
            <?php $image_src = getProductImage($p['title'], $p['image'] ?? ''); ?>
            <div class="product-card">
                <div class="image-wrapper">
                    <img 
                        src="<?= htmlspecialchars($image_src) ?>" 
                        alt="<?= htmlspecialchars($p['title']) ?>" 
                        class="product-image"
                        loading="lazy"
                    >
                </div>
                
                <div class="card-body">
                    <div>
                        <h3 class="product-title"><?= htmlspecialchars($p['title']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($p['description'] ?? '') ?></p>
                    </div>
                    
                    <div class="card-footer">
                        <div class="price-tag">$<?= number_format($p['price'], 2) ?></div>
                        <a href="add_to_cart.php?id=<?= $p['id'] ?>" class="add-cart-btn">🛒 إضافة إلى السلة</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<footer>
    <p>جميع الحقوق محفوظة &copy; <?= date('Y') ?> - مشروع متجر الإلكترونيات</p>
</footer>

</body>
</html>
