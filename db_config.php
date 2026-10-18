<?php
$host = 'sql102.infinityfree.com';
$db   = 'if0_42732437_electronics_store_db';
$user = 'if0_42732437';
$pass = 'Y3sECYctdxn122';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>
