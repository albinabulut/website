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
    'sayfa' => 'İletişim',
    'zaman' => date("d.m.Y H:i:s")
];

$uyari = "";
$uyari_tur = "";

// Kullanıcı giriş yapmışsa bilgileri otomatik doldur
$form_ad = "";
$form_email = "";
if (isset($_SESSION['kullanici_id'])) {
    $stmt = $db->prepare("SELECT ad, soyad, mail FROM kullanici WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['kullanici_id']]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($kullanici) {
        $form_ad = $kullanici['ad'] . " " . $kullanici['soyad'];
        $form_email = $kullanici['mail'];
    }
}

if (isset($_POST['gonder'])) {
    $ad = htmlspecialchars(trim($_POST['ad']));
    $email = htmlspecialchars(trim($_POST['email']));
    $hedef_email = htmlspecialchars(trim($_POST['hedef_email']));
    $konu = htmlspecialchars(trim($_POST['konu']));
    $mesaj = htmlspecialchars(trim($_POST['mesaj']));

    if (!empty($ad) && !empty($email) && !empty($hedef_email) && !empty($mesaj)) {
        // Mail Gönderme İşlemi
        $alici = $hedef_email;
        $baslik = "Arcane Portal İletişim: " . $konu;
        $icerik = "<b>Gönderen:</b> $ad<br><b>E-posta:</b> $email<br><br><b>Mesaj:</b><br>" . nl2br($mesaj);

        // PHPMailer ile Gönderim
        if (mailGonder($alici, "Alıcı", $baslik, $icerik)) {
            $uyari = "Mesajınız başarıyla $alici adresine gönderildi.";
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
<title>İletişim | Arcane Portal</title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #1a1a1a;
        color: #f0f0f0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }
    .contact-box {
        background-color: #0d0d0d;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 30px rgba(40, 167, 69, 0.4);
        width: 500px;
        border: 1px solid #28a745;
    }
    h2 {
        color: #f7e7a3;
        margin-top: 0;
        border-bottom: 2px solid #28a745;
        padding-bottom: 10px;
        margin-bottom: 20px;
        text-align: center;
        text-transform: uppercase;
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        color: #ccc;
        font-weight: bold;
    }
    input, textarea {
        width: 100%;
        padding: 10px;
        background-color: #111;
        border: 1px solid #333;
        color: #f0f0f0;
        border-radius: 4px;
        box-sizing: border-box;
    }
    input:focus, textarea:focus {
        border-color: #28a745;
        outline: none;
    }
    button {
        width: 100%;
        padding: 12px;
        background-color: #28a745;
        color: #0d0d0d;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        font-size: 1.1em;
        transition: 0.3s;
        margin-top: 10px;
    }
    button:hover {
        background-color: #32d65b;
    }
    .uyari {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        text-align: center;
    }
    .uyari.basari {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
        border: 1px solid #28a745;
    }
    .uyari.hata {
        background-color: rgba(220, 53, 69, 0.2);
        color: #ff6b6b;
        border: 1px solid #dc3545;
    }
    .back-link {
        display: block;
        margin-top: 20px;
        text-align: center;
        color: #818181;
        text-decoration: none;
        transition: 0.3s;
    }
    .back-link:hover {
        color: #f7e7a3;
    }
    .info-text {
        text-align: center;
        margin-bottom: 20px;
        color: #888;
        font-size: 0.9em;
    }
</style>
</head>
<body>

<div class="contact-box">
    <h2>📬 İletişim</h2>
    
    <p class="info-text">Sorularınız, önerileriniz veya işbirlikleri için aşağıdaki formu doldurabilirsiniz.</p>

    <?php if ($uyari != "") echo "<div class='uyari $uyari_tur'>$uyari</div>"; ?>

    <form method="post">
        <div class="form-group">
            <label>Ad Soyad</label>
            <input type="text" name="ad" placeholder="Adınız Soyadınız" value="<?php echo htmlspecialchars($form_ad); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Gönderen E-posta Adresi</label>
            <input type="email" name="email" placeholder="ornek@mail.com" value="<?php echo htmlspecialchars($form_email); ?>" required>
        </div>

        <div class="form-group">
            <label>Alıcı E-posta Adresi (Kime Gönderilecek)</label>
            <input type="email" name="hedef_email" placeholder="alici@mail.com" required>
        </div>

        <div class="form-group">
            <label>Konu</label>
            <input type="text" name="konu" placeholder="Mesajınızın konusu">
        </div>

        <div class="form-group">
            <label>Mesajınız</label>
            <textarea name="mesaj" rows="5" placeholder="Bize iletmek istediğiniz mesaj..." required></textarea>
        </div>

        <button type="submit" name="gonder">Gönder</button>
    </form>

    <a href="anasayfa.php" class="back-link">← Ana Sayfaya Dön</a>
</div>

</body>
</html>