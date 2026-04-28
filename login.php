<?php
require 'db.php';
require 'header.php';

// Beni Hatırla (Çerez) kontrolü
if(!isset($_SESSION['user_id']) && isset($_COOKIE['gk_user'])) {
    $_SESSION['user_id'] = $_COOKIE['gk_user'];
    // Normalde burada veritabanından rol de çekilir, basit tutmak için geçiyoruz.
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($sifre, $user['sifre'])) {
        // SESSION atamaları
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['ad_soyad'] = $user['ad_soyad'];
        $_SESSION['rol'] = $user['rol']; // Multisession / Yetki Kontrolü

        // Çerez oluşturma (Kullanıcı Beni Hatırla derse 30 gün boyunca sistemde kalır)
        if(isset($_POST['beni_hatirla'])) {
            setcookie('gk_user', $user['id'], time() + (86400 * 30), "/"); 
        }

        echo "<script>window.location.href='index.php';</script>";
        exit;
    } else {
        echo "<p style='color:red;'>Hatalı e-posta veya şifre!</p>";
    }
}
?>

<h2>Üye Girişi</h2>
<form method="POST">
    <input type="email" name="email" placeholder="E-posta Adresiniz" required><br><br>
    <input type="password" name="sifre" placeholder="Şifreniz" required><br><br>
    <label><input type="checkbox" name="beni_hatirla"> Beni Hatırla</label><br><br>
    <button type="submit">Giriş Yap</button>
</form>

<?php require 'footer.php'; ?>