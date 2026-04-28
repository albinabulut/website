<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Profil',
    'zaman' => date("d.m.Y H:i:s")
];

if (!isset($_SESSION['giris_yapildi'])) {
    header("Location: giris.php");
    exit;
}

$kullanici_ad = $_SESSION['kullanici_ad'] ?? 'Bilinmeyen';
$kullanici_id = $_SESSION['kullanici_id'] ?? '-';
$giris_zamani = $_SESSION['giris_zamani'] ?? 'Bilinmiyor';
$ip = ($_SERVER['REMOTE_ADDR'] == '::1') ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
$tarayici = $_SERVER['HTTP_USER_AGENT'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Arcane Profil | <?php echo htmlspecialchars($kullanici_ad); ?></title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">

<style>
body {
    font-family: Arial, sans-serif;
    background-color: #1a1a1a;
    color: #f0f0f0;
    margin: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.profil-box {
    background-color: #0d0d0d;
    width: 520px;
    padding: 40px;
    border-radius: 14px;
    border: 1px solid #333;
    box-shadow: 0 0 30px rgba(26,135,28,0.6);
}

h2 {
    text-align: center;
    color: #f7e7a3;
    margin-bottom: 30px;
    border-bottom: 2px solid #21871a;
    padding-bottom: 10px;
}

.kart {
    background-color: #1a1a1a;
    border: 1px solid #333;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
}

.kart b {
    color: #21871a;
}

.kart span {
    display: block;
    margin-top: 5px;
    font-size: 0.95em;
    color: #ccc;
}

.alt-menu {
    margin-top: 25px;
    border-top: 1px solid #333;
    padding-top: 15px;
    display: flex;
    justify-content: space-between;
}

.alt-menu a {
    text-decoration: none;
    font-weight: bold;
    color: #f7e7a3;
    transition: 0.3s;
}

.alt-menu a:hover {
    color: #21871a;
}
</style>
</head>

<body>

<div class="profil-box">
    <h2>🧬 Kullanıcı Profili</h2>

    <div class="kart">
        <b>👤 Kod Adı</b>
        <span><?php echo htmlspecialchars($kullanici_ad); ?></span>
    </div>

    <div class="kart">
        <b>🆔 Kullanıcı ID</b>
        <span><?php echo $kullanici_id; ?></span>
    </div>

    <div class="kart">
        <b>⏱️ Giriş Zamanı</b>
        <span><?php echo $giris_zamani; ?></span>
    </div>

    <div class="kart">
        <b>🌐 IP Adresi</b>
        <span><?php echo $ip; ?></span>
    </div>

    <div class="kart">
        <b>🖥️ Tarayıcı Bilgisi</b>
        <span><?php echo htmlspecialchars($tarayici); ?></span>
    </div>

    <div class="alt-menu">
        <a href="anasayfa.php">← Anasayfa</a>
        <a href="session.php">İzleme Paneli →</a>
    </div>
</div>

</body>
</html>
