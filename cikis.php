<?php
session_start();
require_once 'baglanti.php';
require_once 'SessionManager.php';

// Veritabanındaki oturumu kapat ve session'ı sonlandır
$sessionManager = new SessionManager($db);
$sessionManager->logout();

header("Location: anasayfa.php"); // Giriş sayfasına yönlendir
exit;
?>