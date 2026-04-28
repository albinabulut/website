<?php
ob_start();
session_start();
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Hesap Değiştirme',
    'zaman' => date("d.m.Y H:i:s")
];

require_once 'baglanti.php';
require_once 'MailManager.php';

// Sadece Admin (ID: 9) veya yetkili geçiş yapmış hesap erişebilir
$is_admin = (isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] == 9) || (isset($_SESSION['is_admin_ghost']) && $_SESSION['is_admin_ghost'] === true);
if (!$is_admin) {
    header("Location: anasayfa.php");
    exit;
}

// Oturum durumunu kontrol et
$oturum_acik = isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true;
$mevcut_ad = $oturum_acik ? $_SESSION['kullanici_ad'] : 'Giriş Yapılmadı';

// Tüm kullanıcıları listele
$kullanicilar = $db->query("SELECT id, ad, soyad, mail FROM kullanici ORDER BY ad ASC")->fetchAll(PDO::FETCH_ASSOC);

$uyari = "";

if (isset($_POST['gecis'])) {
    $hedef_id = $_POST['hedef_kullanici'] ?? '';

    if (!empty($hedef_id)) {
        $sorgu = "SELECT * FROM kullanici WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($sorgu);
        $stmt->bindParam(':id', $hedef_id);
        $stmt->execute();
        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($kullanici) {
            // Parola sormadan direkt geçiş işlemleri
                // Admin Paneli ve Session Yönetimi
                require_once 'SessionManager.php';
                $sessionManager = new SessionManager($db);
                
                // login() fonksiyonu eski session verilerini temizler (session_unset)
                // ve veritabanındaki eski oturumu kapatır.
                $sessionManager->login($kullanici['id']);

                // Yeni Session Değerlerini Ata
                $_SESSION['giris_yapildi'] = true;
                $_SESSION['kullanici_id'] = $kullanici['id'];
                $_SESSION['kullanici_ad'] = $kullanici['ad'];
                
                // Admin hesabına geri dönülmüyorsa hayalet (ghost) yetkisini koru
                if ($kullanici['id'] != 9) {
                    $_SESSION['is_admin_ghost'] = true;
                }
                
                // Mail Gönderimi
                $konu = "Hesap Değişikliği Bildirimi";
                $zaman = date("d.m.Y H:i:s");
                $mesaj = "Merhaba {$kullanici['ad']},<br>Hesabınıza <b>$zaman</b> tarihinde yönetici tarafından geçiş yapıldı.";
                mailGonder($kullanici['mail'], $kullanici['ad'], $konu, $mesaj);

                session_write_close();
                header("Location: anasayfa.php");
                exit; 
        } else {
            $uyari = "Kullanıcı bulunamadı.";
        }
    } else {
        $uyari = "Lütfen bir kullanıcı seçiniz.";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Hesap Değiştir | Arcane Portal</title>
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
        .switch-box {
            background-color: #0d0d0d;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(255, 193, 7, 0.4); /* Sarı Gölge */
            width: 380px;
            text-align: center;
            border: 1px solid #ffc107;
        }
        h2 { color: #f7e7a3; margin-bottom: 20px; border-bottom: 2px solid #ffc107; padding-bottom: 10px; }
        .current-user { background: #222; padding: 10px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #333; }
        .current-user span { color: #28a745; font-weight: bold; }
        select {
            width: 100%; padding: 12px; margin-bottom: 15px;
            background-color: #2a2a2a; border: 1px solid #333; color: #f0f0f0; border-radius: 6px;
        }
        select:focus { border-color: #ffc107; outline: none; }
        button {
            background-color: #ffc107; color: #000; padding: 12px; width: 100%;
            border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1.1em;
            transition: 0.3s;
        }
        button:hover { background-color: #e0a800; }
        .uyari { color: #dc3545; margin-bottom: 15px; display: block; }
        .back-link { display: block; margin-top: 20px; color: #818181; text-decoration: none; }
        .back-link:hover { color: #f7e7a3; }
    </style>
</head>
<body>

<div class="switch-box">
    <h2>🔄 Hesap Değiştir</h2>
    
    <div class="current-user">
        Aktif Hesap: <span><?php echo htmlspecialchars($mevcut_ad); ?></span>
    </div>

    <?php if ($uyari != "") echo "<b class='uyari'>$uyari</b>"; ?>

    <form method="post">
        <select name="hedef_kullanici" required>
            <option value="">-- Geçiş Yapılacak Hesabı Seçin --</option>
            <?php foreach($kullanicilar as $k): ?>
                <option value="<?php echo $k['id']; ?>" <?php echo ($k['id'] == $_SESSION['kullanici_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($k['ad'] . ' ' . $k['soyad'] . ' (' . $k['mail'] . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button name="gecis">Geçiş Yap</button>
    </form>

    <a href="anasayfa.php" class="back-link">İptal Et ve Dön</a>
</div>

</body>
</html>