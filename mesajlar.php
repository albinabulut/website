<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
require_once 'baglanti.php';

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Mesajlarım',
    'zaman' => date("d.m.Y H:i:s")
];

// Sadece giriş yapmış kullanıcılar görebilir
if (!isset($_SESSION['giris_yapildi']) || $_SESSION['giris_yapildi'] !== true) {
    header("Location: giris.php");
    exit;
}

$user_id = $_SESSION['kullanici_id'];

// Kullanıcının mail adresini al
$stmt = $db->prepare("SELECT mail FROM kullanici WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
$user_mail = $kullanici['mail'];

// Kullanıcının mesajlarını getir
$mesajlar = [];
try {
    $sorgu = $db->prepare("SELECT * FROM mesajlar WHERE email = :email ORDER BY tarih DESC");
    $sorgu->execute(['email' => $user_mail]);
    $mesajlar = $sorgu->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tablo henüz yoksa hata vermesin
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Mesajlarım | Arcane Portal</title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">
<style>
    body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #f0f0f0; padding: 20px; max-width: 800px; margin: 0 auto; }
    h2 { color: #f7e7a3; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
    .back-link { display: inline-block; margin-bottom: 20px; color: #818181; text-decoration: none; transition: 0.3s; }
    .back-link:hover { color: #f7e7a3; }
    
    .mesaj-kutu { background: #0d0d0d; border: 1px solid #333; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 0 15px rgba(0,0,0,0.5); }
    .mesaj-baslik { border-bottom: 1px solid #222; padding-bottom: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
    .mesaj-tarih { color: #818181; font-size: 0.9em; font-weight: bold; }
    .mesaj-icerik { color: #ccc; line-height: 1.6; white-space: pre-wrap; font-size: 1.05em; }
    
    .durum { font-weight: bold; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; }
    .durum-bekliyor { background: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
    .durum-yapildi { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
    .durum-yapilmadi { background: rgba(220, 53, 69, 0.2); color: #dc3545; border: 1px solid #dc3545; }
    
    .empty-msg { text-align: center; color: #666; padding: 40px; background: #0d0d0d; border-radius: 8px; border: 1px dashed #333; }
</style>
</head>
<body>

<a href="anasayfa.php" class="back-link">← Ana Sayfaya Dön</a>

<h2>📨 İletişim Taleplerim</h2>

<?php if (empty($mesajlar)): ?>
    <div class="empty-msg">Henüz bize ilettiğiniz bir mesajınız bulunmuyor.</div>
<?php else: ?>
    <?php foreach ($mesajlar as $m): ?>
        <div class="mesaj-kutu">
            <div class="mesaj-baslik">
                <span class="mesaj-tarih">🕒 <?= date("d.m.Y H:i:s", strtotime($m['tarih'])) ?></span>
                <?php
                $durum = isset($m['durum']) ? $m['durum'] : 'Bekliyor';
                if ($durum == 'Yapıldı') {
                    echo "<span class='durum durum-yapildi'>✅ Yapıldı</span>";
                } elseif ($durum == 'Yapılmadı') {
                    echo "<span class='durum durum-yapilmadi'>❌ Yapılmadı</span>";
                } else {
                    echo "<span class='durum durum-bekliyor'>⏳ Bekliyor</span>";
                }
                ?>
            </div>
            <div class="mesaj-icerik"><?= nl2br(htmlspecialchars($m['mesaj'])) ?></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>