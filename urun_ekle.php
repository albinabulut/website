<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
require_once 'baglanti.php';

// Giriş kontrolü
if (!isset($_SESSION['giris_yapildi']) || $_SESSION['giris_yapildi'] !== true) {
    header("Location: giris.php");
    exit;
}

// Sadece Admin (ID: 9) ürün ekleyebilir
if ($_SESSION['kullanici_id'] != 9) {
    header("Location: anasayfa.php");
    exit;
}

$uyari = "";
$uyari_tur = "";

if (isset($_POST['urun_ekle'])) {
    $ad = $_POST['ad'];
    $fiyat = $_POST['fiyat'];
    $resim = $_POST['resim']; // Resim yolu veya URL'si

    if (!empty($ad) && !empty($fiyat) && !empty($resim)) {
        $stmt = $db->prepare("INSERT INTO urunler (ad, fiyat, resim) VALUES (:ad, :fiyat, :resim)");
        $sonuc = $stmt->execute(['ad' => $ad, 'fiyat' => $fiyat, 'resim' => $resim]);

        if ($sonuc) {
            $uyari = "Ürün başarıyla mağazaya eklendi!";
            $uyari_tur = "basari";
        } else {
            $uyari = "Ürün eklenirken bir hata oluştu.";
            $uyari_tur = "hata";
        }
    } else {
        $uyari = "Lütfen tüm alanları doldurunuz.";
        $uyari_tur = "hata";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Ürün Ekle | Zaun Pazarı</title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">
<style>
    body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #f0f0f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
    .box { background-color: #0d0d0d; padding: 40px; border-radius: 12px; width: 400px; border: 1px solid #28a745; box-shadow: 0 0 25px rgba(40, 167, 69, 0.4); }
    h2 { color: #f7e7a3; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-top: 0; text-align: center; }
    input { width: 100%; padding: 12px; margin-bottom: 15px; background-color: #111; border: 1px solid #333; color: #f0f0f0; border-radius: 4px; box-sizing: border-box; }
    input:focus { border-color: #28a745; outline: none; }
    button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1.1em; transition: 0.3s; }
    button:hover { background-color: #32d65b; }
    .uyari { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
    .uyari.basari { background-color: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
    .uyari.hata { background-color: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid #dc3545; }
    .back-link { display: block; margin-top: 20px; text-align: center; color: #818181; text-decoration: none; }
    .back-link:hover { color: #f7e7a3; }
</style>
</head>
<body>

<div class="box">
    <h2>📦 Yeni Ürün Ekle</h2>
    
    <?php if ($uyari != "") echo "<div class='uyari $uyari_tur'>$uyari</div>"; ?>

    <form method="post">
        <label>Ürün Adı:</label>
        <input type="text" name="ad" placeholder="Örn: Jinx Duvar Posteri" required>
        
        <label>Fiyat (₺):</label>
        <input type="number" step="0.01" name="fiyat" placeholder="Örn: 150.00" required>
        
        <label>Resim Yolu / URL:</label>
        <input type="text" name="resim" placeholder="Örn: img/yeni_urun.jpg" required>
        
        <button type="submit" name="urun_ekle">Mağazaya Ekle</button>
    </form>

    <a href="anasayfa.php" class="back-link">← Ana Sayfaya Dön</a>
</div>

</body>
</html>