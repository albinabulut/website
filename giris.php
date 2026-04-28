<?php
ob_start();
// Session'ı başlat
session_start();
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Giriş Sayfası',
    'zaman' => date("d.m.Y H:i:s")
];

// DB bağlantısını dahil et (OOP'ye geçmediğimiz için bu haliyle kalır)
require_once 'baglanti.php';
require_once 'MailManager.php'; // Mail fonksiyonunu dahil et


$uyari = "";

// Form gönderimi kontrolü
if (isset($_POST['giris'])) {
    
    // Güvenli veri alımı (GET işlemi)
    $girilen_mail = trim($_POST['mail'] ?? '');
    $girilen_sifre = $_POST['sifre'] ?? ''; 

    if (!empty($girilen_mail) && !empty($girilen_sifre)) {

        // DB sorgusu
        $sorgu = "SELECT * FROM kullanici WHERE mail = :mail LIMIT 1";
        $stmt = $db->prepare($sorgu);
        $stmt->bindParam(':mail', $girilen_mail);
        $stmt->execute();
        
        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($kullanici) {
            
            // Şifre doğrulama (password_verify)
            if ($girilen_sifre === $kullanici['sifre']) {
                
                // SessionManager tüm oturum işlemlerini yönetecek
                require_once 'SessionManager.php';
                $sessionManager = new SessionManager($db);
                try {
                    $sessionManager->login($kullanici['id']);

                    // --- GÜVENLİK MAİLİ GÖNDER ---
                    $konu = "Hesabınıza Giriş Yapıldı";
                    $zaman = date("d.m.Y H:i:s");
                    $mesaj = "Merhaba {$kullanici['ad']},<br>Hesabınıza <b>$zaman</b> tarihinde yeni bir giriş yapıldı.<br>Eğer bu siz değilseniz şifrenizi değiştirin.";
                    mailGonder($kullanici['mail'], $kullanici['ad'], $konu, $mesaj);
                    // -----------------------------

                    session_write_close();
                    header("Location: anasayfa.php");
                    exit;
                } catch (Exception $e) {
                    $uyari = $e->getMessage();
                }
                
            } else {
                $uyari = "Hatalı Hextech anahtarı (şifre) girdiniz.";
            }

        } else {
            $uyari = "Bu kimlik (e-posta) Piltover kayıtlarında bulunamadı.";
        }

    } else {
        $uyari = "Lütfen tüm kontrol alanlarını doldurunuz.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piltover Kontrol Noktası</title>
    <link rel="icon" href="img/logo.jpg" type="image/jpeg">
    <style>
        /* Ana Sayfa ile Tam Uyumlu Tema */
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a; /* Anasayfa Arkaplanı */
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-box {
            background-color: #0d0d0d; /* Navbar ile aynı koyu arka plan */
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(26, 135, 28, 0.6); /* Piltover Mavisi/Hextech Gölgesi */
            width: 380px;
            text-align: center;
            border: 1px solid #333;
        }
        h2 {
            color: #f7e7a3; /* Altın Rengi Başlık */
            margin-bottom: 30px;
            text-transform: uppercase;
            border-bottom: 2px solid #21871aff; /* Piltover Mavisi çizgi */
            padding-bottom: 10px;
        }
        form input[type="text"],
        form input[type="password"] {
            width: calc(100% - 22px);
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #333;
            border-radius: 6px;
            background-color: #2a2a2a;
            color: #f0f0f0;
            outline: none;
            transition: border-color 0.3s;
        }
        form input[type="text"]:focus,
        form input[type="password"]:focus {
            border-color: #1a871aff; /* Odaklanınca mavi çerçeve */
        }
        form button {
            background-color: #21871aff; /* Piltover Mavisi buton */
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        form button:hover {
            background-color: #409b3fff;
        }
        .uyari {
            color: #dc3545; /* Kırmızı hata mesajı (Anasayfa Çıkış butonu rengiyle uyumlu) */
            margin-bottom: 15px;
            display: block;
        }
        p {
            color: #818181; /* Yan menü rengiyle uyumlu */
            margin-top: 20px;
        }
        a {
            color: #f7e7a3; /* Altın Rengi Link */
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
            color: #1a871cff;
        }
        .btn-anasayfa {
    display: inline-block;
    width: calc(100% - 24px); /* Inputlarla aynı genişlik */
    padding: 10px;
    background-color: transparent;
    color: #818181;
    border: 1px solid #21871aff;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-anasayfa:hover {
    background-color: #1a871eff;
    color: white;
    text-decoration: none;
}

.password-wrapper { position: relative; width: 100%; margin-bottom: 20px; }
.password-wrapper input { width: 100% !important; margin-bottom: 0 !important; padding-right: 40px; box-sizing: border-box; }
.toggle-eye {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    cursor: pointer; color: #888; width: 24px; height: 24px; user-select: none;
}
    </style>
</head>
<body>

<div class="login-box">
    <h2>Giriş Kontrol Noktası</h2>
    <?php
    if ($uyari != "") {
        echo "<b class='uyari'>$uyari</b>";
    }
    ?>
<form method="post">
        <input type="text" name="mail" placeholder="Kimlik Numarası (E-posta)" value="<?php echo htmlspecialchars($_POST['mail'] ?? ''); ?>">
        <div class="password-wrapper">
            <input type="password" name="sifre" id="sifreInput" placeholder="Hextech Anahtarı (Şifre)">
            <span class="toggle-eye" onclick="togglePassword()">
                <!-- Kapalı Göz (Varsayılan) -->
                <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                <!-- Açık Göz (Gizli) -->
                <svg id="eye-open" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </span>
        </div>
        <div style="text-align: right; margin-bottom: 15px;">
            <a href="sifremi_unuttum.php" style="font-size: 0.9em; color: #818181;">Şifremi Unuttum?</a>
        </div>
        <button name="giris">Giriş Yap</button>
    </form>

    <div style="margin-top: 15px; border-top: 1px solid #333; padding-top: 15px;">
        <a href="anasayfa.php" class="btn-anasayfa">Anasayfaya Dön</a>
    </div>

    <p>Kayıtlı kimliğin yok mu? <a href="kayit.php">Yeni Kayıt Oluştur</a></p>
</div>

<script>
function togglePassword() {
    var input = document.getElementById("sifreInput");
    var eyeClosed = document.getElementById("eye-closed");
    var eyeOpen = document.getElementById("eye-open");

    if (input.type === "password") {
        input.type = "text";
        eyeClosed.style.display = "none";
        eyeOpen.style.display = "block";
    } else {
        input.type = "password";
        eyeOpen.style.display = "none";
        eyeClosed.style.display = "block";
    }
}
</script>
</body>
</html>