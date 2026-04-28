<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Hakkımızda',
    'zaman' => date("d.m.Y H:i:s")
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Hakkımda</title>
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
    .content-box {
        background-color: #0d0d0d;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 30px rgba(40, 167, 69, 0.4);
        width: 600px;
        border: 1px solid #28a745;
        line-height: 1.6;
    }
    h2 {
        color: #f7e7a3;
        margin-top: 0;
        border-bottom: 2px solid #28a745;
        padding-bottom: 10px;
        margin-bottom: 20px;
        text-transform: uppercase;
    }
    p {
        margin-bottom: 15px;
        color: #ccc;
    }
    .highlight {
        color: #f7e7a3;
        font-weight: bold;
        margin-top: 20px;
        display: block;
    }
    ul {
        list-style-type: none;
        padding: 0;
        margin-top: 10px;
    }
    li {
        margin-bottom: 8px;
        padding-left: 25px;
        position: relative;
        color: #e0e0e0;
    }
    li::before {
        content: "★";
        color: #28a745;
        position: absolute;
        left: 0;
        font-size: 1.2em;
    }
    .back-link {
        display: block;
        margin-top: 30px;
        color: #818181;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }
    .back-link:hover {
        color: #f7e7a3;
    }
</style>
</head>
<body>

<div class="content-box">
    <h2>Hakkımda</h2>
    
    <p>Ben Albina BULUT, projelerini baştan sona kendi geliştiren bir yazılım geliştiricisiyim. Bir işi sadece “çalışıyor” noktasında bırakmam; anlaşılır, düzenli ve uzun süre sorunsuz kullanılabilecek şekilde yapmaya özen gösteririm.</p>

    <p>Her projeye gerçekten ihtiyaç neyse onu anlamaya çalışarak başlarım. Gereksiz detaylarla işi karmaşıklaştırmak yerine, sade ve işini yapan çözümler üretmeyi tercih ederim.</p>

    <p>Teknoloji sürekli değişiyor, ben de kendimi sürekli güncel tutuyorum. Öğrenmeyi, denemeyi ve geliştirmeyi işin doğal bir parçası olarak görüyorum.</p>

    <span class="highlight">Kısaca;</span>
    <ul>
        <li>İşini sahiplenen,</li>
        <li>Detaylara dikkat eden,</li>
        <li>Yaptığı işin arkasında duran biriyim.</li>
    </ul>

    <a href="anasayfa.php" class="back-link">← Ana Sayfaya Dön</a>
</div>

</body>
</html>