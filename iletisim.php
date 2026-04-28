<?php
require 'init.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$mesaj_durum = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isim = htmlspecialchars(trim($_POST['isim']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $alici_email = filter_var(trim($_POST['alici_email']), FILTER_SANITIZE_EMAIL);
    $konu = htmlspecialchars(trim($_POST['konu']));
    $mesaj = htmlspecialchars(trim($_POST['mesaj']));
 
    $mail = new PHPMailer(true);
 
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cebecicicek73@gmail.com'; // Kendi Gmail adresinizi yazın
        $mail->Password   = 'holunfqjljalqwiy';   // Uygulama şifrenizi yazın
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';
 
        $mail->setFrom($email, $isim);
        $mail->addAddress($alici_email);
        $mail->addReplyTo($email, $isim);
 
        $mail->isHTML(false);
        $mail->Subject = $konu;
        $mail->Body    = "Gönderen: $isim\nE-posta: $email\n\nMesaj:\n$mesaj";
 
        $mail->send();
        $mesaj_durum = '<div class="alert alert-success">Mesajınız başarıyla gönderildi.</div>';
    } catch (Exception $e) {
        $mesaj_durum = '<div class="alert alert-danger">Mesaj gönderilemedi. Hata: ' . htmlspecialchars($mail->ErrorInfo) . '</div>';
    }
}

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
            <div class="card-header bg-pink text-white"><h4>İletişim</h4></div>
            <div class="card-body">
                <p class="text-center mb-4">İstediğiniz bir e-posta adresine mesaj gönderebilirsiniz.</p>
                <?= $mesaj_durum; ?>
                <form action="iletisim.php" method="POST">
                    <div class="mb-3"><label>Adınız Soyadınız</label><input type="text" name="isim" class="form-control" value="<?= htmlspecialchars($default_name) ?>" required></div>
                    <div class="mb-3"><label>E-posta Adresiniz (Gönderen)</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($default_email) ?>" required></div>
                    <div class="mb-3"><label>Alıcı E-posta Adresi</label><input type="email" name="alici_email" class="form-control" required placeholder="Mesajın gideceği e-posta adresi"></div>
                    <div class="mb-3"><label>Konu</label><input type="text" name="konu" class="form-control" required></div>
                    <div class="mb-3"><label>Mesajınız</label><textarea name="mesaj" class="form-control" rows="5" required></textarea></div>
                    <button type="submit" class="btn btn-pink w-100">Mesajı Gönder</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>