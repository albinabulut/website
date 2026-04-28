<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Istanbul');

// Ortam değişkenlerini yükle (.env)
require_once 'env_loader.php';

// --- SSL / HTTPS ZORLAMA ---
// Eğer bağlantı güvenli değilse (HTTP), HTTPS'e yönlendir.
if (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
    // Localhost'ta geliştirme yaparken bu satırı yorum satırı yapabilirsiniz.
    // header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    // exit();
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'uzay';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Hatası: " . $e->getMessage());
}
?>
