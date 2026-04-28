<?php
session_start();
require 'db.php';

if(isset($_GET['product_id'])) {
    $is_ajax = isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1;
    $response = ['success' => false, 'message' => 'Ürün eklenemedi.'];
    $product_id = intval($_GET['product_id']);
    $success = false;
    
    // Kullanıcı giriş yapmışsa: İlişkili tabloya (cart_items) kaydet
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // Eğer ürün zaten sepette varsa miktarını artırır (1. miktar = 1, sonrası Update)
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, miktar) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE miktar = miktar + 1");
        if ($stmt->execute([$user_id, $product_id])) {
            $success = true;
        }
    } else {
        // Kullanıcı giriş yapmamışsa: Session bazlı sepete kaydet
        if(!isset($_SESSION['sepet'])) {
            $_SESSION['sepet'] = [];
        }
        $_SESSION['sepet'][$product_id] = ($_SESSION['sepet'][$product_id] ?? 0) + 1;
        $success = true;
    }

    if ($success) {
        $response = ['success' => true, 'message' => 'Ürün sepete başarıyla eklendi!'];
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

// İşlem sonrası sepet sayfasına yönlendir
header("Location: sepet.php");
exit;
?>