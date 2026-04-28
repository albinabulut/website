<?php
session_start();

// Kayan yazı için son 5 duyuruyu çekiyoruz (Tüm sayfalarda menünün altında görünmesi için)
$kayan_duyurular = [];
if (isset($pdo)) {
    $stmtDuyuru = $pdo->query("SELECT * FROM announcements ORDER BY yayin_tarihi DESC LIMIT 5");
    $kayan_duyurular = $stmtDuyuru->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>GK Takı</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>GK Takı - Şıklığın Adresi</h1>
        <nav>
            <a href="index.php">Ana Sayfa</a>
            <a href="bize_ulasin.php">Bize Ulaşın</a>
            <a href="iletisim.php">İletişim</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="sepet.php">Sepetim</a>
                <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
                    <!-- Multisession Mantığı (Sadece Admin Görebilir) -->
                    <a href="admin_kullanicilar.php">Kullanıcılar(Admin)</a>
                    <a href="urun_ekle.php">Ürün Ekle(Admin)</a>
                    <a href="duyuru_ekle.php">Duyurular(Admin)</a>
                    <a href="admin_mesajlar.php">Mesajlar(Admin)</a>
                    <a href="admin_cerezler.php">Çerezler(Admin)</a>
                    <a href="admin_multisession.php">Multisession(Admin)</a>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['eski_admin_id'])): ?>
                    <a href="admin_multisession.php?geri_don=1" style="background-color: #e74c3c; padding: 2px 10px; border-radius: 4px;">Admine Dön</a>
                <?php endif; ?>
                <a href="logout.php">Çıkış Yap</a>
            <?php else: ?>
                <a href="login.php">Giriş Yap</a>
                <a href="register.php">Kayıt Ol</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <!-- Kayan Duyuru Bandı (Menünün hemen altında tam ekran) -->
    <?php if (count($kayan_duyurular) > 0): ?>
    <div class="duyuru-bandi">
        <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
            <?php foreach ($kayan_duyurular as $kd): ?>
                <span style="margin-right: 50px;"><?= htmlspecialchars($kd['icerik']) ?></span>
            <?php endforeach; ?>
        </marquee>
    </div>
    <?php endif; ?>

    <main>