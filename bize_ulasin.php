<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
require_once 'baglanti.php';
require_once 'MailManager.php'; // Mail fonksiyonunu dahil et

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Bize Ulaşın',
    'zaman' => date("d.m.Y H:i:s")
];

$uyari = "";
$uyari_tur = "";

// Kullanıcı giriş yapmışsa bilgileri otomatik doldur
$form_email = "";
if (isset($_SESSION['kullanici_id'])) {
    $stmt = $db->prepare("SELECT mail FROM kullanici WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['kullanici_id']]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($kullanici) {
        $form_email = $kullanici['mail'];
    }
}

if (isset($_POST['gonder'])) {
    $email = htmlspecialchars(trim($_POST['email']));
    $mesaj = htmlspecialchars(trim($_POST['mesaj']));

    if (!empty($email) && !empty($mesaj)) {
        // Veritabanı tablosu yoksa otomatik oluştur
        $db->exec("CREATE TABLE IF NOT EXISTS mesajlar (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            mesaj TEXT NOT NULL,
            durum VARCHAR(50) DEFAULT 'Bekliyor',
            tarih DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Mesajı Admin Paneli için veritabanına kaydet
        try {
            $ekle = $db->prepare("INSERT INTO mesajlar (email, mesaj) VALUES (:email, :mesaj)");
            $ekle->execute(['email' => $email, 'mesaj' => $mesaj]);
        } catch (PDOException $e) {
            // Tablo eski yapıda kaldıysa (ad NOT NULL) hata vermemesi için uyumluluk modu
            $ekle = $db->prepare("INSERT INTO mesajlar (ad, email, konu, mesaj) VALUES ('Belirtilmedi', :email, '', :mesaj)");
            $ekle->execute(['email' => $email, 'mesaj' => $mesaj]);
        }

        // Mail Gönderme İşlemi (Doğrudan yöneticiye gider)
        $alici = "albinabulut@gmail.com"; // Site yöneticisi veya destek ekibi e-postası
        $baslik = "Siteden Yeni İletişim Formu Mesajı";
        $icerik = "<b>E-posta:</b> $email<br><br><b>Mesaj:</b><br>" . nl2br($mesaj);

        // PHPMailer ile Gönderim
        if (mailGonder($alici, "Site Yöneticisi", $baslik, $icerik)) {
            $uyari = "Mesajınız bize başarıyla ulaştı. En kısa sürede size geri dönüş yapacağız.";
            $uyari_tur = "basari";
        } else {
            $uyari = "Mesaj gönderilirken teknik bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.";
            $uyari_tur = "hata";
        }
    } else {
        $uyari = "Lütfen zorunlu alanları doldurunuz.";
        $uyari_tur = "hata";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Bize Ulaşın | Arcane Portal</title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">
<style>
    body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #f0f0f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
    .contact-box { background-color: #0d0d0d; padding: 40px; border-radius: 12px; box-shadow: 0 0 30px rgba(40, 167, 69, 0.4); width: 500px; border: 1px solid #28a745; }
    h2 { color: #f7e7a3; margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px; margin-bottom: 20px; text-align: center; text-transform: uppercase; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; color: #ccc; font-weight: bold; }
    input, textarea { width: 100%; padding: 10px; background-color: #111; border: 1px solid #333; color: #f0f0f0; border-radius: 4px; box-sizing: border-box; }
    input:focus, textarea:focus { border-color: #28a745; outline: none; }
    button { width: 100%; padding: 12px; background-color: #28a745; color: #0d0d0d; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1.1em; transition: 0.3s; margin-top: 10px; }
    button:hover { background-color: #32d65b; }
    .uyari { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
    .uyari.basari { background-color: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
    .uyari.hata { background-color: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid #dc3545; }
    .back-link { display: block; margin-top: 20px; text-align: center; color: #818181; text-decoration: none; transition: 0.3s; }
    .back-link:hover { color: #f7e7a3; }
    .info-text { text-align: center; margin-bottom: 20px; color: #888; font-size: 0.9em; }
</style>
</head>
<body>

<div class="contact-box">
    <h2>🤝 Bize Ulaşın</h2>
    
    <p class="info-text">Görüş, öneri veya destek taleplerinizi aşağıdaki formu kullanarak doğrudan site yönetimine iletebilirsiniz.</p>

    <?php if ($uyari != "") echo "<div class='uyari $uyari_tur'>$uyari</div>"; ?>

    <form method="post">
        <div class="form-group">
            <label>E-posta Adresiniz *</label>
            <input type="email" name="email" placeholder="ornek@mail.com" value="<?php echo htmlspecialchars($form_email); ?>" required>
        </div>
        <div class="form-group">
            <label>Mesajınız *</label>
            <textarea name="mesaj" rows="5" placeholder="Bize iletmek istediğiniz mesaj..." required></textarea>
        </div>
        <button type="submit" name="gonder">Mesajı Gönder</button>
    </form>

    <a href="anasayfa.php" class="back-link">← Ana Sayfaya Dön</a>
</div>

</body>
</html>