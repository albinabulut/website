<?php
ob_start();
session_start();
require_once 'baglanti.php';
require_once 'SessionManager.php';

// Zaten admin olarak giriş yapılmışsa direkt panele at
if (isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true) {
    if (isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] == 9) {
        header("Location: admin_sessions.php");
        exit;
    }
}

$uyari = "";

if (isset($_POST['giris'])) {
    $mail = trim($_POST['mail'] ?? '');
    $sifre = $_POST['sifre'];

    if (!empty($mail) && !empty($sifre)) {
        // Kullanıcıyı bul
        $stmt = $db->prepare("SELECT * FROM kullanici WHERE mail = :mail");
        $stmt->execute(['mail' => $mail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Şifre kontrolü (Veritabanı kurulumunda şifreler düz metin kaydedildiği için direkt karşılaştırıyoruz)
            if ($sifre == $user['sifre']) {
                
                // Admin Yetki Kontrolü (Sadece ID: 9)
                if ((int)$user['id'] == 9) {
                    
                    // SessionManager tüm oturum işlemlerini yönetecek
                    $sessionManager = new SessionManager($db);
                    try {
                        $sessionManager->login($user['id']);

                        // Direkt Admin Paneline Yönlendir
                        header("Location: admin_sessions.php");
                        exit;
                    } catch (Exception $e) {
                        $uyari = $e->getMessage();
                    }
                 } else {
                    $uyari = "⛔ Bu hesabın yönetici yetkisi yok!";
                }
            } else {
                $uyari = "❌ Hatalı şifre.";
            }
        } else {
            $uyari = "❌ Kullanıcı bulunamadı.";
        }
    } else {
        $uyari = "⚠️ Lütfen bilgileri doldurun.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Girişi | Arcane</title>
    <link rel="icon" href="img/logo.jpg" type="image/jpeg">
    <style>
        body { background-color: #050505; color: #f0f0f0; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #111; padding: 40px; border: 1px solid #dc3545; border-radius: 10px; width: 350px; text-align: center; box-shadow: 0 0 30px rgba(220, 53, 69, 0.3); }
        h2 { color: #dc3545; margin-bottom: 20px; border-bottom: 1px solid #dc3545; padding-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #222; border: 1px solid #444; color: white; border-radius: 5px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #dc3545; }
        button { width: 100%; padding: 12px; background: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-top: 15px; font-size: 1.1em; transition: 0.3s; }
        button:hover { background: #a71d2a; }
        .uyari { color: #ff6b6b; margin-bottom: 15px; display: block; font-weight: bold; }
        a { color: #666; text-decoration: none; font-size: 0.9em; display: block; margin-top: 20px; transition: 0.3s; }
        a:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🛡️ Yönetici Girişi</h2>
        <?php if($uyari) echo "<span class='uyari'>$uyari</span>"; ?>
        <form method="post">
            <input type="text" name="mail" placeholder="Yönetici E-posta" required>
            <input type="password" name="sifre" placeholder="Güvenlik Şifresi" required>
            <button type="submit" name="giris">Panele Eriş</button>
        </form>
        <a href="anasayfa.php">← Siteye Dön</a>
    </div>
</body>
</html>