<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Çerezler',
    'zaman' => date("d.m.Y H:i:s")
];

$uyari = "";
$uyari_tur = ""; // basari veya hata
$goster = false;

/* -----------------------------
   ÇEREZ EKLE
------------------------------*/
if (isset($_POST['cerez_ekle'])) {
    $sure = time() + 86400;

    if (!empty($_POST['cerez_adi']) && !empty($_POST['cerez_degeri'])) {
        $ad = $_POST['cerez_adi'];
        $deger = $_POST['cerez_degeri'];
        setcookie($ad, $deger, $sure, "/");
        $_COOKIE[$ad] = $deger;
        $uyari = "Özel veri ($ad) sisteme enjekte edildi.";
        $uyari_tur = "basari";
    } else {
        $uyari = "Lütfen çerez adı ve değerini giriniz.";
        $uyari_tur = "hata";
    }

    $goster = true;
}

/* -----------------------------
   ÇEREZLERİ GÖSTER
------------------------------*/
if (isset($_POST['cerez_goster'])) {
    $goster = true;
}

/* -----------------------------
   TÜM ÇEREZLERİ SİL
------------------------------*/
if (isset($_POST['cerez_sil'])) {
    foreach ($_COOKIE as $k => $v) {
        setcookie($k, "", time() - 3600, "/");
    }
    $_COOKIE = []; // Anlık görünümü temizle
    $uyari = "Tüm çerez verileri imha edildi.";
    $uyari_tur = "hata";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Zaun Veri Merkezi | Çerezler</title>
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

.cerez-box {
    background-color: #0d0d0d;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(40, 167, 69, 0.4);
    width: 450px;
    text-align: center;
    border: 1px solid #28a745;
    position: relative;
}

h2 {
    color: #f7e7a3;
    margin-top: 0;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    background-color: #28a745;
    color: #0d0d0d;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1.05em;
    font-weight: bold;
    text-transform: uppercase;
    transition: all 0.3s ease;
}

button:hover {
    background-color: #32d65b;
    box-shadow: 0 0 15px rgba(40, 167, 69, 0.6);
}

.btn-goster {
    background-color: #1f6f3f;
}

.btn-sil {
    background-color: transparent;
    border: 2px solid #dc3545;
    color: #ff6b6b;
}

.btn-sil:hover {
    background-color: #dc3545;
    color: white;
}

.uyari {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    display: block;
    font-size: 0.9em;
}
.uyari.basari {
    background-color: rgba(40, 167, 69, 0.2);
    color: #28a745;
    border: 1px solid #28a745;
}
.uyari.hata {
    background-color: rgba(220, 53, 69, 0.2);
    color: #ff6b6b;
    border: 1px solid #dc3545;
}

.liste {
    background-color: #111;
    border: 1px solid #222;
    padding: 15px;
    margin-top: 20px;
    border-radius: 4px;
    text-align: left;
    font-size: 0.95em;
    max-height: 200px;
    overflow-y: auto;
}

.liste h3 {
    margin-top: 0;
    color: #818181;
    font-size: 0.9em;
    border-bottom: 1px solid #333;
    padding-bottom: 5px;
}

.liste b {
    color: #28a745;
    display: inline-block;
    width: 120px;
}

.back-link {
    display: block;
    margin-top: 25px;
    color: #818181;
    text-decoration: none;
    font-size: 0.9em;
    transition: 0.3s;
}
.back-link:hover {
    color: #f7e7a3;
}
.input-cerez {
    width: 100%;
    padding: 12px;
    margin-bottom: 10px;
    background-color: #111;
    border: 1px solid #333;
    color: #f0f0f0;
    border-radius: 4px;
    box-sizing: border-box;
}
.input-cerez:focus {
    border-color: #28a745;
    outline: none;
}
</style>
</head>

<body>

<div class="cerez-box">
    <h2>🧪 Zaun Veri Merkezi</h2>

    <?php if ($uyari != "") echo "<div class='uyari $uyari_tur'>$uyari</div>"; ?>

    <form method="post">
        <input type="text" name="cerez_adi" placeholder="Çerez Adı (Örn: kullanici_id)" class="input-cerez">
        <input type="text" name="cerez_degeri" placeholder="Çerez Değeri (Örn: 12345)" class="input-cerez">
        <button name="cerez_ekle">📥 Veri Enjekte Et (Ekle)</button>
        <button name="cerez_goster" class="btn-goster">👁️ Verileri İncele (Göster)</button>
        <button name="cerez_sil" class="btn-sil">🗑️ Sistemi Temizle (Sil)</button>
    </form>

    <?php if ($goster): ?>
    <div class="liste">
        <h3>📦 AKTİF ÇEREZLER</h3>
        <?php
        if (!empty($_COOKIE)) {
            foreach ($_COOKIE as $anahtar => $deger) {
                echo "<div style='padding: 4px 0; border-bottom: 1px solid #222;'><b>$anahtar</b> <span style='color:#ccc;'>$deger</span></div>";
            }
        } else {
            echo "<span style='color:#666;'>Sistemde veri bulunamadı.</span>";
        }
        ?>
    </div>
    <?php endif; ?>

    <a href="anasayfa.php" class="back-link">← Ana Portala Dön</a>
</div>

</body>
</html>
