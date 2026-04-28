<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
require_once 'baglanti.php';

// Giriş kontrolü
if (!isset($_SESSION['giris_yapildi']) || $_SESSION['giris_yapildi'] !== true) {
    header("Location: giris.php");
    exit;
}

$user_id = $_SESSION['kullanici_id'];
$uyari = "";
$islem_basarili = false;

// Sepet Toplamını Hesapla
$sorgu = $db->prepare("SELECT * FROM sepet WHERE user_id = :uid");
$sorgu->execute(['uid' => $user_id]);
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

$toplam_tutar = 0;
foreach ($urunler as $urun) {
    $toplam_tutar += $urun['fiyat'] * $urun['adet'];
}

// Sepet boşsa sepete geri gönder (Ödeme yapıldıktan sonra sayfa yenilenirse hata vermesin diye)
if ($toplam_tutar == 0 && !isset($_POST['odeme_yap'])) {
    header("Location: sepet.php");
    exit;
}

// Ödeme İşlemi
if (isset($_POST['odeme_yap'])) {
    $kart_isim = $_POST['kart_isim'];
    $kart_no = $_POST['kart_no'];
    $son_kullanim = $_POST['son_kullanim'];
    $cvv = $_POST['cvv'];

    if (!empty($kart_isim) && !empty($kart_no) && !empty($son_kullanim) && !empty($cvv)) {
        // Burada normalde banka API'sine istek atılır.
        // Biz başarılı kabul edip sepeti boşaltıyoruz.
        
        // 1. Sepetteki ürünleri 'siparisler' tablosuna taşı
        foreach ($urunler as $urun) {
            $toplam = $urun['fiyat'] * $urun['adet'];
            $ekle = $db->prepare("INSERT INTO siparisler (user_id, urun_id, urun_ad, fiyat, adet, toplam_tutar, durum) VALUES (:uid, :urun_id, :ad, :fiyat, :adet, :toplam, 'Hazırlanıyor')");
            $ekle->execute([
                'uid' => $user_id,
                'urun_id' => $urun['urun_id'],
                'ad' => $urun['urun_ad'],
                'fiyat' => $urun['fiyat'],
                'adet' => $urun['adet'],
                'toplam' => $toplam
            ]);
        }

        // 2. Sepeti boşalt
        $sil = $db->prepare("DELETE FROM sepet WHERE user_id = :uid");
        $sil->execute(['uid' => $user_id]);

        $islem_basarili = true;
    } else {
        $uyari = "Lütfen tüm ödeme bilgilerini doldurunuz.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Ödeme Ekranı | Arcane Store</title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">
<style>
    body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #f0f0f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
    .payment-box { background-color: #0d0d0d; padding: 40px; border-radius: 12px; width: 450px; border: 1px solid #28a745; box-shadow: 0 0 25px rgba(40, 167, 69, 0.4); }
    h2 { color: #f7e7a3; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-top: 0; text-align: center; }
    .total-amount { text-align: center; font-size: 1.5em; color: #28a745; margin: 20px 0; font-weight: bold; }
    input { width: 100%; padding: 12px; margin-bottom: 15px; background-color: #111; border: 1px solid #333; color: #f0f0f0; border-radius: 4px; box-sizing: border-box; }
    input:focus { border-color: #28a745; outline: none; }
    .row { display: flex; gap: 10px; }
    button { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1.1em; transition: 0.3s; }
    button:hover { background-color: #32d65b; }
    .uyari { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; background-color: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid #dc3545; }
    .success-message { text-align: center; }
    .success-icon { font-size: 50px; color: #28a745; display: block; margin-bottom: 20px; }
    .back-link { display: block; margin-top: 20px; text-align: center; color: #818181; text-decoration: none; }
    .back-link:hover { color: #f7e7a3; }
</style>
</head>
<body>

<div class="payment-box">
    <?php if ($islem_basarili): ?>
        <div class="success-message">
            <span class="success-icon">✔</span>
            <h2>Ödeme Başarılı!</h2>
            <p>Siparişiniz alınmıştır. Zaun kuryeleri yola çıkmak üzere.</p>
            <a href="anasayfa.php" class="back-link">Ana Sayfaya Dön</a>
        </div>
    <?php else: ?>
        <h2>💳 Güvenli Ödeme</h2>
        
        <div class="total-amount">
            Toplam: <?php echo number_format($toplam_tutar, 2); ?> ₺
        </div>

        <?php if ($uyari != "") echo "<div class='uyari'>$uyari</div>"; ?>

        <form method="post">
            <label>Kart Üzerindeki İsim</label>
            <input type="text" name="kart_isim" placeholder="Ad Soyad" required>
            
            <label>Kart Numarası</label>
            <input type="text" name="kart_no" placeholder="0000 0000 0000 0000" maxlength="19" required>
            
            <div class="row">
                <div style="flex: 1;">
                    <label>Son Kullanma</label>
                    <input type="text" name="son_kullanim" placeholder="AA/YY" maxlength="5" required>
                </div>
                <div style="flex: 1;">
                    <label>CVV</label>
                    <input type="text" name="cvv" placeholder="123" maxlength="3" required>
                </div>
            </div>
            
            <button type="submit" name="odeme_yap">Ödemeyi Onayla</button>
        </form>

        <a href="sepet.php" class="back-link">← Sepete Dön</a>
    <?php endif; ?>
</div>

</body>
</html>