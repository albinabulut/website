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

// --- ÜRÜN EKLEME İŞLEMİ ---
if (isset($_POST['sepete_ekle'])) {
    $urun_id = (int)$_POST['urun_id'];
    $urun_ad = $_POST['urun_ad'];
    $fiyat = $_POST['fiyat'];
    $resim = $_POST['resim'];

    // Sepette zaten var mı kontrol et
    $kontrol = $db->prepare("SELECT id, adet FROM sepet WHERE user_id = :uid AND urun_id = :urun_id");
    $kontrol->execute(['uid' => $user_id, 'urun_id' => $urun_id]);
    $mevcut = $kontrol->fetch(PDO::FETCH_ASSOC);

    if ($mevcut) {
        // Varsa adeti arttır
        $guncelle = $db->prepare("UPDATE sepet SET adet = adet + 1 WHERE id = :id");
        $guncelle->execute(['id' => $mevcut['id']]);
    } else {
        // Yoksa yeni ekle
        $ekle = $db->prepare("INSERT INTO sepet (user_id, urun_id, urun_ad, fiyat, resim, adet) VALUES (:uid, :urun_id, :ad, :fiyat, :resim, 1)");
        $ekle->execute(['uid' => $user_id, 'urun_id' => $urun_id, 'ad' => $urun_ad, 'fiyat' => $fiyat, 'resim' => $resim]);
    }
    $uyari = "Ürün sepete eklendi!";
}

// --- ÜRÜN SİLME İŞLEMİ ---
if (isset($_GET['sil'])) {
    $sil_id = $_GET['sil'];
    $sil = $db->prepare("DELETE FROM sepet WHERE id = :id AND user_id = :uid");
    $sil->execute(['id' => $sil_id, 'uid' => $user_id]);
    header("Location: sepet.php");
    exit;
}

// --- SEPETİ LİSTELE ---
$sorgu = $db->prepare("SELECT * FROM sepet WHERE user_id = :uid");
$sorgu->execute(['uid' => $user_id]);
$urunler = $sorgu->fetchAll(PDO::FETCH_ASSOC);

$toplam_tutar = 0;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Sepetim | Arcane Store</title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">
<style>
    body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #f0f0f0; display: flex; justify-content: center; padding-top: 50px; }
    .cart-box { background-color: #0d0d0d; padding: 30px; border-radius: 12px; width: 700px; border: 1px solid #28a745; box-shadow: 0 0 20px rgba(40, 167, 69, 0.3); }
    h2 { color: #f7e7a3; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th { text-align: left; color: #818181; padding-bottom: 10px; border-bottom: 1px solid #333; }
    td { padding: 15px 0; border-bottom: 1px solid #222; vertical-align: middle; }
    .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #333; margin-right: 10px; }
    .btn-sil { color: #dc3545; text-decoration: none; font-weight: bold; border: 1px solid #dc3545; padding: 5px 10px; border-radius: 4px; transition: 0.3s; }
    .btn-sil:hover { background-color: #dc3545; color: white; }
    .total-row { text-align: right; font-size: 1.2em; color: #f7e7a3; padding-top: 20px; }
    .back-link { display: inline-block; margin-top: 20px; color: #818181; text-decoration: none; }
    .back-link:hover { color: #f7e7a3; }
    .checkout-btn { background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-left: 10px; }
    .checkout-btn:hover { background-color: #32d65b; }
    .alert { background: rgba(40,167,69,0.2); color: #28a745; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #28a745; }
</style>
</head>
<body>

<div class="cart-box">
    <h2>🛒 Alışveriş Sepetim</h2>
    
    <?php if($uyari): ?>
        <div class="alert"><?php echo $uyari; ?></div>
    <?php endif; ?>

    <?php if (count($urunler) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th>Fiyat</th>
                    <th>Adet</th>
                    <th>Toplam</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($urunler as $urun): 
                    $satir_toplam = $urun['fiyat'] * $urun['adet'];
                    $toplam_tutar += $satir_toplam;
                ?>
                <tr>
                    <td style="display: flex; align-items: center;">
                        <img src="<?php echo $urun['resim']; ?>" class="img-thumb">
                        <?php echo htmlspecialchars($urun['urun_ad']); ?>
                    </td>
                    <td><?php echo number_format($urun['fiyat'], 2); ?> ₺</td>
                    <td>x<?php echo $urun['adet']; ?></td>
                    <td style="color: #28a745;"><?php echo number_format($satir_toplam, 2); ?> ₺</td>
                    <td><a href="?sil=<?php echo $urun['id']; ?>" class="btn-sil" onclick="return confirm('Silmek istediğine emin misin?')">Sil</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-row">
            Genel Toplam: <b><?php echo number_format($toplam_tutar, 2); ?> ₺</b>
            <br><br>
            <a href="odeme.php" class="checkout-btn">Ödemeyi Tamamla</a>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: #666; padding: 20px;">Sepetinizde henüz ürün yok.</p>
    <?php endif; ?>

    <a href="anasayfa.php" class="back-link">← Alışverişe Devam Et</a>
</div>

</body>
</html>