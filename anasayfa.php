<?php

// Session'ı başlat
session_start();
date_default_timezone_set('Europe/Istanbul');

// Ziyaret edilen sayfayı kaydet
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Anasayfa',
    'zaman' => date("d.m.Y H:i:s")
];

// Session verisinin aşırı büyümesini önle (Son 50 kayıt)
if (count($_SESSION['izlenen_sayfalar']) > 50) {
    $_SESSION['izlenen_sayfalar'] = array_slice($_SESSION['izlenen_sayfalar'], -50);
}

include 'baglanti.php';

// Oturum Durumu
$oturum_acik = isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true;
$kullanici_adi = htmlspecialchars($_SESSION['kullanici_ad'] ?? 'Ziyaretçi');
$is_admin = isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] == 9;
$is_admin_ghost = isset($_SESSION['is_admin_ghost']) && $_SESSION['is_admin_ghost'] === true;

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcane Portal - <?php echo $kullanici_adi; ?></title>
    <link rel="icon" href="img/logo.jpg" type="image/jpeg">
    <style>
        /* Genel Tema */
        html { scroll-behavior: smooth; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding-top: 60px;
            background-color: #1a1a1a;
            color: #f0f0f0;
        }
        
        /* 1. Navigasyon Çubuğu (Sabit Başlık) */
        .navbar {
            background-color: #0d0d0d;
            color: #f7e7a3;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 9999; /* Navbar üstte kalsın */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5em;
            font-weight: bold;
        }

        /* Sağ Çizgi Menü (Hamburger) */
        .menu-toggle {
            font-size: 2em;
            cursor: pointer;
        }

        .sidebar {
            height: 100%;
            width: 0;
            position: fixed;
            z-index: 10000; /* Sidebar her şeyin en üstünde olsun */
            top: 0;
            right: 0;
            background-color: #111;
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 60px;
        }

        .sidebar a {
            padding: 8px 8px 8px 32px;
            text-decoration: none;
            font-size: 18px;
            color: #818181;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            color: #f7e7a3;
        }

        .sidebar .closebtn {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 36px;
            margin-left: 50px;
        }
        
        .slider-container {
            position: relative;
            width: 100%;
            height: 700px;
            overflow: hidden;
        }

        .slider-wrapper {
            display: flex;
        }

        .slide {
            min-width: 100%;
            height: 700px;
            background-size: cover;
            background-position: center;
        }
        
        /* Noktalar */
        .dots-container {
            text-align: center;
            padding: 10px;
        }

        .dot {
            cursor: pointer;
            height: 15px;
            width: 15px;
            margin: 0 2px;
            background-color: #bbb;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
        }

        .active-dot, .dot:hover {
            background-color: #f7e7a3;
        }

        /* Slider Butonları (İleri/Geri) */
        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 50%;
            width: auto;
            padding: 16px;
            margin-top: -22px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
            background-color: rgba(0,0,0,0.5);
            z-index: 10;
        }
        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }
        .prev:hover, .next:hover {
            background-color: rgba(0,0,0,0.8);
        }
        
        /* İçerik Alanı */
        .content {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            color: #f7e7a3;
            border-bottom: 2px solid #1a4d87;
            padding-bottom: 10px;
            margin-top: 40px;
            scroll-margin-top: 80px; /* Sabit navbar altında kalmaması için */
        }

        /* Grid Yapıları (Video ve Hikaye Kartları) */
        .video-grid, .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 30px; 
            margin-top: 20px; 
        }

        /* GÖRSEL HİZALAMA DÜZENLEMESİ */
        /* Bu kod, hem .video-grid içindeki hem de .grid içindeki görselleri kapsar */
        .video-item img, .grid-item img { 
            width: 100%; 
            height: 180px; /* Görsel Yüksekliğini Sabitle */
            object-fit: cover; /* Görüntüyü kırparak kutuyu tamamen kapla */
            display: block; 
        }

        /* Footer Stili */
        .footer {
            background-color: #0d0d0d;
            padding: 20px 0;
            text-align: center;
            margin-top: 50px;
            font-size: 0.8em;
            border-top: 1px solid #333;
            color: #818181;
        }

        .footer p {
            margin: 5px 0;
        }

        /* Kesintisiz Kayan Yazı (Marquee) */
        .marquee-container {
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
            background-color: #000;
            border-bottom: 1px solid #28a745;
            padding: 10px 0;
        }
        .marquee-track {
            display: inline-block;
            animation: marquee 30s linear infinite;
        }
        .marquee-content {
            display: inline-block;
            padding-right: 50px; /* Tekrar arasındaki boşluk */
            color: #28a745;
            font-family: monospace;
            font-size: 18px;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .marquee-container:hover .marquee-track {
            animation-play-state: paused;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <div class="logo">ARCANE PORTAL</div>
        <div class="menu-toggle" onclick="openNav()">&#9776;</div>
    </div>
    
    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        
        <?php if ($oturum_acik): ?>
            <a href="#">Hoş Geldin, <?php echo $kullanici_adi; ?></a>
            <a href="profil.php">Profilim</a>
            <?php if ($is_admin): ?>
                <a href="urun_ekle.php">➕ Ürün Ekle</a>
                <a href="admin_sessions.php">🛡️ Admin Paneli</a>
                <a href="admin_mesajlar.php">📥 Gelen Mesajlar</a>
                <a href="kullanici_degistir.php" style="color: #ffc107;">🔄 Hesap Değiştir</a>
            <?php elseif ($is_admin_ghost): ?>
                <a href="kullanici_degistir.php" style="color: #ffc107;">🔄 Admin'e Dön / Hesap Değiştir</a>
            <?php endif; ?>
            <a href="sepet.php">🛒 Sepetim</a>
            <a href="mesajlar.php">📨 Mesajlarım</a>
            <a href="session.php">Session Paneli</a>
            <a href="duyurular.php">📢 Duyurular</a>
            <a href="cerezler.php">Çerezler</a>
            <a href="hakkimizda.php">Hakkımda</a>
            <a href="bize_ulasin.php">Bize Ulaşın</a>
            <a href="cikis.php">Çıkış Yap</a>
        <?php else: ?>
            <a href="giris.php">Giriş Yap</a>
            <a href="kayit.php">Kayıt Ol</a>
            <?php if ($is_admin): // Normalde giriş yapmamış admin olmaz ama güvenlik için ?>
                <a href="kullanici_degistir.php" style="color: #ffc107;">🔄 Hesap Değiştir</a>
            <?php endif; ?>
            <a href="cerezler.php">Çerezler</a>
            <a href="session.php">Session</a>
            <a href="duyurular.php">Duyurular</a>
            <a href="hakkimizda.php">Hakkımda</a>
            <a href="bize_ulasin.php">Bize Ulaşın</a>
        <?php endif; ?>
        
        <hr style="border-color: #333;">
        <a href="#tanitim">Arcane Tanıtım</a>
        <a href="#magaza">Ürünler</a>
    </div>

    <!-- DUYURU BANDI -->
    <div class="marquee-container">
        <div class="marquee-track">
            <?php
            $duyuru_dosyasi = "duyurular.txt";
            $duyuru_metni = "Zaun Veri Merkezi: Şu an aktif duyuru bulunmamaktadır.";
            
            if (file_exists($duyuru_dosyasi)) {
                $duyurular = file($duyuru_dosyasi, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if (!empty($duyurular)) {
                    $duyuru_metni = implode(" &nbsp;&nbsp; ★ &nbsp;&nbsp; ", $duyurular);
                }
            }
            // Kesintisiz döngü için içeriği iki kez yazdırıyoruz
            echo '<span class="marquee-content">' . $duyuru_metni . '</span>';
            echo '<span class="marquee-content">' . $duyuru_metni . '</span>';
            ?>
        </div>
    </div>

    <div class="slider-container">
        <div class="slider-wrapper" id="sliderWrapper">
            <div class="slide" style="background-image: url('img/arcane6.jpg');"></div>
            <div class="slide" style="background-image: url('img/arcane3.jpg');"></div>
            <div class="slide" style="background-image: url('img/arcane9.jpg');"></div>
            <div class="slide" style="background-image: url('img/arcane8.jpg');"></div>

            <!-- 1. görselin KLONU (sonsuz döngü için şart) -->
            <div class="slide" style="background-image: url('img/arcane6.jpg');"></div>
        </div>

        <!-- İleri Geri Butonları -->
        <a class="prev" onclick="changeSlide(-1)">&#10094;</a>
        <a class="next" onclick="changeSlide(1)">&#10095;</a>
    </div>

    <div class="dots-container" id="dotsContainer"></div>

    <div class="content">
        <h2 id="tanitim" class="section-title">ARCANE: YAŞAMAK VE ÖLMEK ARASINDA</h2>
        <p>Arcane, Piltover'ın zarif yaldızları ile Zaun'un dumanlı, yeraltı sokakları arasındaki hassas dengeyi konu alır. Dizide, efsanevi League of Legends şampiyonları Jinx ve Vi'ın köken hikayelerine tanık oluyoruz.</p>
        <p> sayın cem hocam bu proje günlerimi aldı umarım düzgünce yapabilmişimdir projede eksik yok yüz kere kontrol ettim eksik olursada çay koyup yeniden başlarım</p>

        
        <h2 id="magaza" class="section-title">ZAUN PAZARI & KOLEKSİYON ÜRÜNLERİ</h2>
        <div class="video-grid">
            
            <?php
            // Ürünleri veritabanından çek
            $urunler = [];
            try {
                $urunler = $db->query("SELECT * FROM urunler ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "<p style='color: #dc3545; text-align: center; width: 100%;'>Ürünler yüklenirken bir hata oluştu.</p>";
            }

            foreach ($urunler as $urun): 
            ?>
                <div class="video-item" style="border: 1px solid #333; background: #0d0d0d; padding-bottom: 15px; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
                    <img src="<?php echo $urun['resim']; ?>" alt="<?php echo $urun['ad']; ?>" style="width: 100%; height: 200px; object-fit: cover;">
                    
                    <div style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                        <h3 style="color: #f7e7a3; margin: 10px 0; font-size: 1.1em;"><?php echo $urun['ad']; ?></h3>
                        <p style="color: #28a745; font-weight: bold; font-size: 1.2em; margin-bottom: 15px;">
                            <?php echo number_format($urun['fiyat'], 2); ?> ₺
                        </p>
                        
                        <?php if ($oturum_acik): ?>
                            <form action="sepet.php" method="POST" style="margin-top: auto;">
                                <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                                <input type="hidden" name="urun_ad" value="<?php echo $urun['ad']; ?>">
                                <input type="hidden" name="fiyat" value="<?php echo $urun['fiyat']; ?>">
                                <input type="hidden" name="resim" value="<?php echo $urun['resim']; ?>">
                                <button type="submit" name="sepete_ekle" style="
                                    width: 100%; 
                                    padding: 10px; 
                                    background-color: #28a745; 
                                    color: white; 
                                    border: none; 
                                    border-radius: 4px; 
                                    cursor: pointer; 
                                    font-weight: bold;
                                    transition: 0.3s;">
                                    🛒 Sepete Ekle
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="giris.php" style="
                                display: block; 
                                text-align: center; 
                                padding: 10px; 
                                background-color: #333; 
                                color: #888; 
                                text-decoration: none; 
                                border-radius: 4px; 
                                font-size: 0.9em;
                                margin-top: auto;">
                                Satın almak için giriş yap
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
        </div>
    </div>

<script>
/* Hamburger Menü */
function openNav() {
    document.getElementById("mySidebar").style.width = "250px";
}

function closeNav() {
    document.getElementById("mySidebar").style.width = "0";
}

/* Slider Kodlarını Sayfa Yüklendikten Sonra Çalıştır (Çakışmayı Önle) */
document.addEventListener("DOMContentLoaded", function() {

/* ===== SLIDER ===== */
const sliderWrapper = document.getElementById("sliderWrapper");

if (sliderWrapper) { // Slider varsa çalıştır
    let slides = document.querySelectorAll(".slide");
    const dotsContainer = document.getElementById("dotsContainer");

    let index = 0;
    let slideCount = slides.length;
    let slideInterval;
    let isTransitioning = false;

    if (slides.length > 0) {
        /* 🔁 İlk slaytı klonla */
        const firstClone = slides[0].cloneNode(true);
        sliderWrapper.appendChild(firstClone);
        slides = document.querySelectorAll(".slide");

        /* DOT OLUŞTUR */
        for (let i = 0; i < slideCount; i++) {
            const dot = document.createElement("span");
            dot.className = "dot";
            dot.addEventListener("click", () => goToSlide(i));
            dotsContainer.appendChild(dot);
        }

        const dots = document.querySelectorAll(".dot");
        if(dots.length > 0) dots[0].classList.add("active-dot");

        /* SLIDE GEÇİŞ (Global Erişim İçin window'a ata) */
        window.moveSlider = function() {
            changeSlide(1);
        }

        /* BUTON İLE GEÇİŞ */
        window.changeSlide = function(direction) {
            if (isTransitioning) return;
            resetTimer();
            isTransitioning = true;

            if (direction === 1) {
                // --- İLERİ ---
                index++;
                sliderWrapper.style.transition = "transform 0.6s ease-in-out";
                sliderWrapper.style.transform = `translateX(-${index * 100}%)`;

                if (index === slideCount) {
                    setTimeout(() => {
                        sliderWrapper.style.transition = "none";
                        sliderWrapper.style.transform = "translateX(0)";
                        index = 0;
                        isTransitioning = false;
                    }, 600);
                } else {
                    setTimeout(() => { isTransitioning = false; }, 600);
                }
            } else {
                // --- GERİ ---
                if (index === 0) {
                    // Baştaysak, önce klona (sona) ışınlan
                    sliderWrapper.style.transition = "none";
                    index = slideCount;
                    sliderWrapper.style.transform = `translateX(-${index * 100}%)`;
                    sliderWrapper.offsetHeight; // Reflow
                }
                
                requestAnimationFrame(() => {
                    index--;
                    sliderWrapper.style.transition = "transform 0.6s ease-in-out";
                    sliderWrapper.style.transform = `translateX(-${index * 100}%)`;
                    setTimeout(() => { isTransitioning = false; }, 600);
                });
            }
            updateDots();
        }

        /* DOT GÜNCELLE */
        function updateDots() {
            dots.forEach(dot => dot.classList.remove("active-dot"));
            if(dots[index % slideCount]) dots[index % slideCount].classList.add("active-dot");
        }

        /* DOT TIKLAMA */
        window.goToSlide = function(n) {
            resetTimer();
            index = n;
            sliderWrapper.style.transition = "transform 0.6s ease-in-out";
            sliderWrapper.style.transform = `translateX(-${index * 100}%)`;
            updateDots();
        }

        /* ZAMANLAYICI SIFIRLAMA */
        function resetTimer() {
            clearInterval(slideInterval);
            slideInterval = setInterval(moveSlider, 10000);
        }

        /* OTOMATİK KAYDIRMA */
        slideInterval = setInterval(moveSlider, 10000);
    }
}
});

</script>

    <footer class="footer">
        <p>&#xa9; 2025 Arcane Fan Portalı | Albina Bulut tarafından yapılmıştır.</p>
        <p>Tüm hakları Riot Games'e aittir.</p>
        <p style="margin-top: 10px; color: #f7e7a3;">📧 İletişim: <a href="mailto:albinabulut@gmail.com" style="color: #28a745; text-decoration: none;">albinabulut@gmail.com</a></p>
        <p style="margin-top: 10px; font-size: 0.8em;"><a href="admin_giris.php" style="color: #333; text-decoration: none;">Yönetici Girişi</a></p>
    </footer>

</body>
</html>