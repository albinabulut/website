<?php
require 'init.php';

// PHPMailer sınıflarını dahil et
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer dosyalarını manuel olarak dahil et
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mesaj_durum = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isim = htmlspecialchars(trim($_POST['isim']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $konu = htmlspecialchars(trim($_POST['konu']));
    $mesaj = htmlspecialchars(trim($_POST['mesaj']));
 
    $mail = new PHPMailer(true);
 
    try {
        // Sunucu Ayarları
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // SMTP sunucunuzun adresi (Örn: Gmail için)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mail@gmail.com'; // SMTP kullanıcı adınız (Gmail adresiniz)
        $mail->Password   = 'uygulamaşifre';   // SMTP şifreniz (Gmail için uygulama şifresi)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';
 
        // Alıcılar
        $mail->setFrom($email, $isim); // Gönderen
        $mail->addAddress('albinabulut@gmail.com', 'Çiçek Sepetim Yöneticisi'); // Alıcı (Mesajların geleceği kendi adresiniz)
        $mail->addReplyTo($email, $isim); // Yanıt adresi
 
        // İçerik
        $mail->isHTML(false); // E-postayı düz metin olarak ayarla
        $mail->Subject = $konu;
        $mail->Body    = "Gönderen: $isim\nE-posta: $email\n\nMesaj:\n$mesaj";
 
        $mail->send();
        $mesaj_durum = '<div class="alert alert-success">Mesajınız başarıyla gönderildi.</div>';
    } catch (Exception $e) {
        $mesaj_durum = '<div class="alert alert-danger">Mesaj gönderilemedi. Hata: ' . htmlspecialchars($mail->ErrorInfo) . '</div>';
    }
}

// Kullanıcı giriş yapmışsa bilgilerini otomatik doldurmak için veritabanından çekelim
$default_name = $_SESSION['user_name'] ?? '';
$default_email = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
    if ($user_data) {
        $default_email = $user_data['email'];
    }
}

require 'header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-pink text-white"><h4>Bize Ulaşın</h4></div>
            <div class="card-body">
                <p class="text-center mb-4">Bizimle iletişime geçmek için aşağıdaki formu doldurabilirsiniz.</p>
                <?= $mesaj_durum; ?>
                <form action="bize_ulasin.php" method="POST">
                    <div class="mb-3"><label>Adınız Soyadınız</label><input type="text" name="isim" class="form-control" value="<?= htmlspecialchars($default_name) ?>" required></div>
                    <div class="mb-3"><label>E-posta Adresiniz</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($default_email) ?>" required></div>
                    <div class="mb-3"><label>Konu</label><input type="text" name="konu" class="form-control" required></div>
                    <div class="mb-3"><label>Mesajınız</label><textarea name="mesaj" class="form-control" rows="5" required></textarea></div>
                    <button type="submit" class="btn btn-pink w-100">Mesajı Gönder</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>
