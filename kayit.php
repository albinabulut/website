<?php
session_start(); 
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Kayıt Sayfası',
    'zaman' => date("d.m.Y H:i:s")
];

// DB bağlantısını dahil et
require_once 'baglanti.php';
require_once 'MailManager.php'; // Mail fonksiyonunu dahil et
require_once 'SmsManager.php';  // SMS fonksiyonunu dahil et


$uyari = "";

// ---------------------------------------------
// PHP: COOKIE GET VE FORM VERİLERİNİ BAŞLATMA
// ---------------------------------------------
// POST verisi gelmediyse, Cookie'den okunan verileri varsayılan değer olarak kullan.
$cookie_ad = $_COOKIE['kayit_ad'] ?? '';
$cookie_soyad = $_COOKIE['kayit_soyad'] ?? '';
$cookie_mail = $_COOKIE['kayit_mail'] ?? '';
$cookie_telefon = $_COOKIE['kayit_telefon'] ?? '';
$cookie_sifre = $_COOKIE['kayit_sifre'] ?? '';

// Değişkenleri POST'tan al (varsa) veya Cookie'den al (yoksa)
$girilen_ad = trim($_POST['ad'] ?? $cookie_ad);
$girilen_soyad = trim($_POST['soyad'] ?? $cookie_soyad);
$girilen_sifre = $_POST['sifre'] ?? $cookie_sifre;
$girilen_telefon = trim($_POST['telefon'] ?? $cookie_telefon);
$girilen_mail = trim($_POST['mail'] ?? $cookie_mail);
// ---------------------------------------------


if (isset($_POST['gonder'])) {
    
    // Formdan gelen güncel verileri al
    $girilen_ad = trim($_POST['ad'] ?? '');
    $girilen_soyad = trim($_POST['soyad'] ?? '');
    $girilen_sifre = $_POST['sifre'] ?? '';
    $girilen_telefon = trim($_POST['telefon'] ?? '');
    $girilen_mail = trim($_POST['mail'] ?? '');

    // ---------------------------------------------
    // PHP: COOKIE SET (Veriyi yaz)
    // ---------------------------------------------
    $cookie_suresi = time() + 86400; // 1 gün = 86400 saniye
    setcookie('kayit_ad', $girilen_ad, $cookie_suresi, "/");
    setcookie('kayit_soyad', $girilen_soyad, $cookie_suresi, "/");
    setcookie('kayit_mail', $girilen_mail, $cookie_suresi, "/");
    setcookie('kayit_telefon', $girilen_telefon, $cookie_suresi, "/");
    setcookie('kayit_sifre', $girilen_sifre, $cookie_suresi, "/");
    // ---------------------------------------------
    
    // Boş alan kontrolü
    $bos_alanlar = [];
    if (empty($girilen_ad)) $bos_alanlar[] = "Ad";
    if (empty($girilen_soyad)) $bos_alanlar[] = "Soyad";
    if (empty($girilen_mail)) $bos_alanlar[] = "E-posta";
    if (empty($girilen_sifre)) $bos_alanlar[] = "Şifre";

    if (!empty($bos_alanlar)) {
        $uyari = "Lütfen şu alanları doldurunuz: " . implode(", ", $bos_alanlar);
    } else {
        if (!filter_var($girilen_mail, FILTER_VALIDATE_EMAIL)) {
            $uyari = "Geçerli bir E-posta adresi giriniz.";
        } elseif (strlen($girilen_sifre) < 6) {
            $uyari = "Şifreniz en az 6 karakter olmalı.";
        } else {

                // Şifreleme iptal edildi, düz metin olarak kaydedilecek
                
                $sorgu = "INSERT INTO kullanici (ad, soyad, mail, telefon, sifre) 
                              VALUES (:ad, :soyad, :mail, :telefon, :sifre)";

                try {
                    $stmt = $db->prepare($sorgu);
                    
                    // Verileri bağla
                    $stmt->bindParam(':ad', $girilen_ad);
                    $stmt->bindParam(':soyad', $girilen_soyad);
                    $stmt->bindParam(':mail', $girilen_mail);
                    $stmt->bindParam(':telefon', $girilen_telefon);
                    $stmt->bindParam(':sifre', $girilen_sifre);
                    
                    if ($stmt->execute()) {
                        
                        // --- HOŞ GELDİN MAİLİ GÖNDER ---
                        $konu = "Arcane Portal'a Hoş Geldin!";
                        $mesaj = "<h3>Merhaba $girilen_ad,</h3><p>Zaun ve Piltover arasındaki köprüye hoş geldin. Kaydın başarıyla oluşturuldu.</p><p>İyi eğlenceler!</p>";
                        
                        // Maili gönder (Sonuç kullanıcıya yansımaz, arka planda çalışır)
                        mailGonder($girilen_mail, $girilen_ad, $konu, $mesaj);
                        // -------------------------------

                        // --- SMS GÖNDER (SİMÜLASYON) ---
                        smsGonder($girilen_telefon, "Aramıza hoş geldin $girilen_ad! Zaun'daki yeni kimliğin oluşturuldu.");
                        // -------------------------------

                        $uyari = "Kayıt Başarılı! Artık <a href='giris.php'>Piltover'a Giriş Yap</a>abilirsin.";
                        
                        // Başarılı kayıtta cookie'leri sil (form temiz kalsın)
                        //setcookie('kayit_ad', '', time() - 3600, "/");
                        //setcookie('kayit_soyad', '', time() - 3600, "/");
                        //setcookie('kayit_mail', '', time() - 3600, "/");
                        //setcookie('kayit_telefon', '', time() - 3600, "/");
                        
                        $girilen_ad = $girilen_soyad = $girilen_telefon = $girilen_mail = "";
                    }
                } catch (PDOException $e) {
                    // E-posta zaten kayıtlıysa (Hata Kodu 23000)
                    if ($e->getCode() == '23000') {
                        $uyari = "Bu e-posta adresi Alt Şehir kayıtlarında zaten mevcut.";
                    } else 
                    {
                        $uyari = "Veritabanı hatası oluştu: " . $e->getMessage();
                    }
                }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaun Yeni Kimlik Kaydı</title>
    <link rel="icon" href="img/logo.jpg" type="image/jpeg">
    <style>
        /* CSS kodları önceki gibi kalır. */
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
        .register-box {
            background-color: #0d0d0d;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(40, 167, 69, 0.6); 
            width: 380px;
            text-align: center;
            border: 1px solid #333;
        }
        h2 {
            color: #f7e7a3; 
            margin-bottom: 30px;
            text-transform: uppercase;
            border-bottom: 2px solid #28a745; 
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
            border-color: #28a745; 
        }
        form button {
            background-color: #28a745; 
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
            background-color: #3bbf58;
        }
        .uyari {
            color: #dc3545; 
            margin-bottom: 15px;
            display: block;
        }
        p {
            color: #818181;
            margin-top: 20px;
        }
        a {
            color: #f7e7a3; 
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
            color: #28a745;
        }
        .btn-anasayfa {
    display: inline-block;
    width: calc(100% - 24px); /* Inputlarla aynı hizaya getirir */
    padding: 10px;
    margin-top: 15px;
    background-color: transparent;
    color: #818181;
    border: 1px solid #28a745; /* Zaun Yeşili çerçeve */
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: all 0.3s;
}

.btn-anasayfa:hover {
    background-color: #28a745; /* Hover durumunda yeşil dolgu */
    color: white;
    text-decoration: none;
}

/* Buton ve form arası çizgi için */
.divider {
    margin-top: 15px;
    border-top: 1px solid #333;
    padding-top: 5px;
}
    </style>
</head>
<body>

<div class="register-box">
    <h2>Zaun Yeni Kimlik Kaydı</h2>
    <?php
    if ($uyari != "") {
        echo "<b class='uyari'>$uyari</b>";
    }
    ?>

    <form method="post">
        <input type="text" name="ad" placeholder="Ad (Örn: Vi)" value="<?php echo htmlspecialchars($girilen_ad); ?>">
        <input type="text" name="soyad" placeholder="Soyad (Örn: Kiramman)" value="<?php echo htmlspecialchars($girilen_soyad); ?>">
        <input type="text" name="mail" placeholder="E-posta" value="<?php echo htmlspecialchars($girilen_mail); ?>">
        <input type="text" name="telefon" placeholder="Telefon (Opsiyonel)" value="<?php echo htmlspecialchars($girilen_telefon); ?>">
        <input type="text" name="sifre" placeholder="Şifre (Min. 6 Karakter)" value="<?php echo htmlspecialchars($girilen_sifre); ?>">
        <button name="gonder">Kayıt Ol</button>
    </form>
    
    <div class="divider">
        <a href="anasayfa.php" class="btn-anasayfa">Zaun'dan Ayrıl (Anasayfa)</a>
    </div>

    <p>Zaten kimliğin var mı? <a href="giris.php">Giriş Yap</a></p>
</div>

</body>
</html>