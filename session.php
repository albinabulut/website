<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Session Paneli',
    'zaman' => date("d.m.Y H:i:s")
];

if (!isset($_SESSION['giris_zamani'])) {
    $_SESSION['giris_zamani'] = date("d.m.Y H:i:s");
}
?>




<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Piltover İzleme Merkezi</title>
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

.panel {
    background-color: #0d0d0d;
    padding: 40px;
    border-radius: 12px;
    width: 500px;
    border: 1px solid #333;
    box-shadow: 0 0 25px rgba(26,135,28,0.6);
}

h2 {
    color: #f7e7a3;
    text-align: center;
    border-bottom: 2px solid #21871aff;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.kutu {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #333;
}

.kutu b {
    color: #21871aff;
}

ul {
    padding-left: 20px;
}

li {
    margin-bottom: 6px;
}

a {
    display: block;
    margin-top: 15px;
    text-align: center;
    color: #f7e7a3;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    color: #21871aff;
}
</style>
</head>

<body>

<div class="panel">
    <h2>🔍 Kullanıcı İzleme Paneli</h2>

    <div class="kutu">
        <b>Kullanıcı:</b> <?php echo $_SESSION['kullanici_ad'] ?? 'Ziyaretçi'; ?><br>
        <b>Giriş Zamanı:</b> <?php echo $_SESSION['giris_zamani']; ?><br>
        <b>IP Adresi:</b> <?php echo ($_SERVER['REMOTE_ADDR'] == '::1') ? '127.0.0.1' : $_SERVER['REMOTE_ADDR']; ?><br>
        <b>Tarayıcı:</b> <?php echo $_SERVER['HTTP_USER_AGENT']; ?>
    </div>

    <div class="kutu">
        <b>📍 Ziyaret Edilen Sayfalar</b>
        <ul>
            <?php
            
            if (!empty($_SESSION['izlenen_sayfalar'])) {
                foreach ($_SESSION['izlenen_sayfalar'] as $kayit) {
                    echo "<li>{$kayit['sayfa']} – {$kayit['zaman']}</li>";
                }
            } else {
                echo "<li>Henüz kayıt yok</li>";
            }
            ?>
        </ul>
    </div>

    <a href="anasayfa.php">← Kontrol Noktasına Dön</a>
</div>

</body>
</html>
