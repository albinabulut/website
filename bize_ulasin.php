<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require 'header.php';

// PHPMailer dosyalarını projeye dahil ediyoruz
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isim = trim($_POST['isim'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $konu = trim($_POST['konu'] ?? 'Belirtilmedi');
    $mesaj = trim($_POST['mesaj'] ?? '');

    // Gelen mesajı önce veritabanına kaydedelim. E-posta gönderimi başarısız olsa bile mesaj kaybolmaz.
    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (isim, email, konu, mesaj) VALUES (?, ?, ?, ?)");
        $stmt->execute([$isim, $email, $konu, $mesaj]);
        $mesaj_kaydedildi = true;
    } catch (PDOException $e) {
        // Veritabanı hatası durumunda en azından e-posta göndermeyi denesin.
        // Normalde bu hatayı loglamak iyi bir pratiktir.
        $mesaj_kaydedildi = false;
    }

    // PHPMailer ile Gerçek Mail Gönderme İşlemi
    $mail = new PHPMailer(true);
    try {
        // Sunucu ayarları (Kendi Gmail bilgilerini buraya girmelisin)
        $mail->setLanguage('tr'); // Hata mesajlarını Türkçe yapmak için
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gamzekaragoz800@gmail.com'; // DİKKAT: Kendi Gmail adresinizi yazın
        $mail->Password   = 'wtewdfiabkvccngp';   // DİKKAT: Google'dan alınan 16 haneli Uygulama Şifresini yazın
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Alıcı ve Gönderen Ayarları
        $mail->setFrom('gamzekaragoz800@gmail.com', 'GK Takı İletişim Formu'); // Gönderici (Username ile aynı olmalı)
        $mail->addAddress('albinabulut@gmail.com'); // Mesajın kime gideceği
        $mail->addReplyTo($email, $isim); // Yanıtla dendiğinde müşterinin mailine gitsin

        // İçerik
        $mail->isHTML(true);
        $mail->Subject = 'Siteden Yeni Mesaj: ' . htmlspecialchars($konu);
        $mail->Body    = "<strong>Gönderen İsim:</strong> " . htmlspecialchars($isim) . "<br><strong>E-Posta:</strong> " . htmlspecialchars($email) . "<br><strong>Konu:</strong> " . htmlspecialchars($konu) . "<br><br><strong>Mesaj:</strong><br>" . nl2br(htmlspecialchars($mesaj));

        $mail->send();
        echo "<p style='color:green;'>Mesajınız başarıyla gönderildi! Size en kısa sürede dönüş yapacağız.</p>";
    } catch (Exception $e) {
        $hata_mesaji = "Mesaj gönderilirken bir hata oluştu. Hata: {$mail->ErrorInfo}";
        // E-posta gitmese bile veritabanına kaydedildiyse kullanıcıya bunu bildirelim.
        if ($mesaj_kaydedildi) {
            $hata_mesaji = "E-posta gönderilemedi ancak mesajınız sistemimize kaydedildi. En kısa sürede size ulaşacağız. (Hata: {$mail->ErrorInfo})";
        }
        echo "<p style='color:red;'>{$hata_mesaji}</p>";
    }
}

$otomatik_isim = '';
$otomatik_email = '';
if (isset($_SESSION['user_id'])) {
    $otomatik_isim = $_SESSION['ad_soyad'] ?? '';
    // Veritabanından kullanıcının e-posta adresini çekiyoruz
    $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user_data = $stmt_user->fetch();
    if ($user_data) {
        $otomatik_email = $user_data['email'];
    }
}
?>

<h2>Bize Ulaşın</h2>
<form method="POST">
    <input type="text" name="isim" placeholder="İsminiz" value="<?= htmlspecialchars($otomatik_isim) ?>" required><br><br>
    <input type="email" name="email" placeholder="E-posta Adresiniz" value="<?= htmlspecialchars($otomatik_email) ?>" required><br><br>
    <input type="text" name="konu" placeholder="Mesajınızın Konusu" required><br><br>
    <textarea name="mesaj" placeholder="Mesajınız..." rows="5" required></textarea><br><br>
    <button type="submit" class="btn">Gönder</button>
</form>

<?php require 'footer.php'; ?>