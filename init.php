<?php
ini_set('session.cookie_httponly', 1);
session_start();

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // Localhost testlerinde https yönlendirmesi sorun yaratabilir diye yorum satırına alındı.
    // Canlıya alırken aşağıdaki iki satırın başındaki // işaretlerini kaldırın.
    // header('HTTP/1.1 301 Moved Permanently');
    // header('Location: ' . $redirect);
    // exit();
}

$host = 'localhost';
$dbname = 'cicek_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Admin hesabına geri dönme işlemi (Hesap Geçişinden Çıkış)
if (isset($_GET['switch_back']) && isset($_SESSION['original_admin_id'])) {
    $_SESSION['user_id'] = $_SESSION['original_admin_id'];
    $_SESSION['user_name'] = $_SESSION['original_admin_name'];
    $_SESSION['role'] = 'admin';
    unset($_SESSION['original_admin_id'], $_SESSION['original_admin_name']);
    header("Location: admin.php?action=users");
    exit();
}

// Kullanıcının sayfa gezintilerini session içinde takip etme
if (!isset($_SESSION['page_history'])) {
    $_SESSION['page_history'] = [];
}
$current_page = $_SERVER['REQUEST_URI'];
$current_time = date('d.m.Y H:i:s');

$last_visit = end($_SESSION['page_history']);
if (!$last_visit || $last_visit['page'] !== $current_page) {
    $_SESSION['page_history'][] = [
        'page' => $current_page,
        'time' => $current_time
    ];
    // Oturumun şişmesini önlemek için sadece son 20 hareketi tutalım
    if (count($_SESSION['page_history']) > 20) {
        array_shift($_SESSION['page_history']);
    }
}