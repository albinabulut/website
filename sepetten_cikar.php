<?php
session_start();
require 'db.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    if (isset($_SESSION['user_id'])) {
        // Kullanıcı giriş yapmışsa: Veritabanındaki ilişkili tablodan (cart_items) sil
        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
    } else {
        // Kullanıcı giriş yapmamışsa: Session sepetinden sil
        if (isset($_SESSION['sepet'][$product_id])) {
            unset($_SESSION['sepet'][$product_id]);
        }
    }
}

// İşlem bitince tekrar sepet sayfasına dön
header("Location: sepet.php");
exit;
?>