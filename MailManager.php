<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Ortam değişkenlerini yükle (.env)
require_once 'env_loader.php';

// PHPMailer dosyalarını otomatik bul (Klasör adı veya yapısı farklı olabilir)
$paths = [
    __DIR__ . '/PHPMailer/src/Exception.php',       // Standart yapı (Önerilen)
    __DIR__ . '/PHPMailer-master/src/Exception.php',// GitHub'dan inen orijinal isim
    __DIR__ . '/PHPMailer/Exception.php'            // Dosyalar direkt klasördeyse
];

$found = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        $dir = dirname($path);
        require $dir . '/Exception.php';
        require $dir . '/PHPMailer.php';
        require $dir . '/SMTP.php';
        $found = true;
        break;
    }
}

if (!$found) {
    die("HATA: PHPMailer dosyaları bulunamadı! Lütfen proje klasöründe 'PHPMailer' veya 'PHPMailer-master' klasörü olduğundan emin olun.");
}

function mailGonder($aliciEmail, $aliciAd, $konu, $mesaj) {
    $mail = new PHPMailer(true);

    try {
        // --- Sunucu Ayarları ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USER'] ?? ''; // .env dosyasından al
        $mail->Password   = $_ENV['MAIL_PASS'] ?? ''; // .env dosyasından al

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setLanguage('tr'); // Hata mesajları Türkçe

        // --- Alıcı ve Gönderen Ayarları ---
        $mail->setFrom('albinabulut@gmail.com', 'Arcane Portal');
        $mail->addAddress($aliciEmail, $aliciAd);

        // --- İçerik Ayarları ---
        $mail->isHTML(true);
        $mail->Subject = $konu;
        $mail->Body    = $mesaj;
        $mail->AltBody = strip_tags($mesaj);

        $mail->send();
        return true; // Başarılı
    } catch (Exception $e) {
        // Hata durumunda fatal error verme, log tut ve false dön
        $hata_mesaji = "Mail Hatası: " . $e->getMessage() . " | Detay: {$mail->ErrorInfo} - Tarih: " . date("d.m.Y H:i:s") . PHP_EOL;
        file_put_contents("mail_hatalari.log", $hata_mesaji, FILE_APPEND);
        return false; // Başarısız
    }
}
?>