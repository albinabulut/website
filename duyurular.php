<?php
session_start();
date_default_timezone_set('Europe/Istanbul');

// Sayfa izleme (Session altına kayıt)
if (!isset($_SESSION['izlenen_sayfalar'])) {
    $_SESSION['izlenen_sayfalar'] = [];
}
$_SESSION['izlenen_sayfalar'][] = [
    'sayfa' => 'Duyurular',
    'zaman' => date("d.m.Y H:i:s")
];

// Oturum durumunu kontrol et
$oturum_acik = isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true;
// Admin kontrolü (Sadece ID: 9)
$is_admin = isset($_SESSION['kullanici_id']) && $_SESSION['kullanici_id'] == 9;

$dosya_yolu = "duyurular.txt";

$uyari = "";
$uyari_tur = "";

// --- AJAX İLE SIRALAMA KAYDETME ---
if (isset($_POST['ajax_reorder']) && $is_admin) {
    $json = $_POST['liste'];
    $yeni_siralama = json_decode($json, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($yeni_siralama)) {
        // Ekranda Üstten Alta görünen listeyi, dosyaya kaydederken ters çeviriyoruz
        // (Çünkü okurken array_reverse ile okuyoruz)
        $kaydedilecek = array_reverse($yeni_siralama);
        
        $icerik = "";
        foreach ($kaydedilecek as $satir) {
            $icerik .= trim($satir) . PHP_EOL;
        }
        file_put_contents($dosya_yolu, $icerik);
        echo "OK";
        exit; // İşlem tamam, sayfayı durdur
    }
}

// --- DUYURU SİLME İŞLEMİ ---
if (isset($_GET['sil']) && $is_admin) {
    $sil_id = (int)$_GET['sil'];
    if (file_exists($dosya_yolu)) {
        $icerik = file($dosya_yolu, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (isset($icerik[$sil_id])) {
            unset($icerik[$sil_id]);
            // Dosyayı güncel haliyle kaydet
            file_put_contents($dosya_yolu, implode(PHP_EOL, $icerik) . PHP_EOL);
            $uyari = "Duyuru silindi.";
            $uyari_tur = "basari";
        }
    }
}

// --- DUYURU EKLEME İŞLEMİ ---
if (isset($_POST['duyuru_ekle']) && $is_admin) {
    $metin = trim($_POST['duyuru_metni']);
    if (!empty($metin)) {
        
        // Duyuruyu dosyaya ekle (Her duyuru yeni bir satır)
        file_put_contents($dosya_yolu, $metin . PHP_EOL, FILE_APPEND);
        
        $uyari = "Duyuru sisteme başarıyla işlendi.";
        $uyari_tur = "basari";
    } else {
        $uyari = "Duyuru metni boş bırakılamaz.";
        $uyari_tur = "hata";
    }
}

// --- DUYURULARI OKUMA ---
$duyurular = [];
if (file_exists($dosya_yolu)) {
    $duyurular = file($dosya_yolu, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Zaun İletişim Ağı | Duyurular</title>
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
    .main-box {
        background-color: #0d0d0d;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 0 30px rgba(40, 167, 69, 0.4);
        width: 450px;
        text-align: center;
        border: 1px solid #28a745;
        position: relative;
    }
    h2 { color: #f7e7a3; margin-top: 0; text-transform: uppercase; letter-spacing: 1px; }
    
    /* Form Elemanları */
    .input-area {
        width: 100%;
        padding: 12px;
        margin-bottom: 10px;
        background-color: #111;
        border: 1px solid #333;
        color: #f0f0f0;
        border-radius: 4px;
        box-sizing: border-box;
        resize: vertical;
    }
    .input-area:focus { border-color: #28a745; outline: none; }
    
    button {
        width: 100%;
        padding: 12px;
        background-color: #28a745;
        color: #0d0d0d;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        text-transform: uppercase;
        transition: 0.3s;
    }
    button:hover { background-color: #32d65b; }

    /* Liste Alanı */
    .liste {
        background-color: #111;
        border: 1px solid #222;
        padding: 15px;
        margin-top: 20px;
        border-radius: 4px;
        text-align: left;
        max-height: 200px;
        overflow-y: auto;
    }
    .liste-item { 
        padding: 10px; 
        border-bottom: 1px solid #333; 
        color: #ccc; 
        font-size: 0.9em; 
        cursor: grab; /* Tutma imleci */
    }
    .liste-item:active { cursor: grabbing; }
    .liste-item.dragging { opacity: 0.5; background-color: #222; border: 1px dashed #28a745; }
    
    /* Hamburger Menü */
    .hamburger-menu { position: absolute; top: 15px; right: 15px; }
    .menu-btn { font-size: 24px; cursor: pointer; color: #28a745; user-select: none; }
    .menu-content {
        display: none;
        position: absolute;
        right: 0;
        top: 30px;
        background-color: #0d0d0d;
        border: 1px solid #28a745;
        min-width: 160px;
        z-index: 100;
        border-radius: 4px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    }
    .hamburger-menu:hover .menu-content { display: block; }
    .menu-content a {
        color: #f0f0f0;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        text-align: left;
    }
    .menu-content a:hover { background-color: #1f6f3f; }

    .uyari { padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 0.9em; }
    .uyari.basari { background-color: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
    .uyari.hata { background-color: rgba(220, 53, 69, 0.2); color: #ff6b6b; border: 1px solid #dc3545; }
</style>
</head>
<body>

<div class="main-box">
    <!-- Hamburger Menü -->
    <div class="hamburger-menu">
        <div class="menu-btn">☰</div>
        <div class="menu-content">
            <a href="anasayfa.php">🏠 Ana Sayfa</a>
            <?php if ($oturum_acik): ?>
                <a href="profil.php">👤 Profilim</a>
                <a href="sepet.php">🛒 Sepetim</a>
                <a href="mesajlar.php">📨 Mesajlarım</a>
                <a href="kullanici_degistir.php">🔄 Hesap Değiştir</a>
            <?php else: ?>
                <a href="giris.php">🔑 Giriş Yap</a>
                <a href="kayit.php">📝 Kayıt Ol</a>
                <a href="kullanici_degistir.php">🔄 Hesap Değiştir</a>
            <?php endif; ?>
            <?php if ($is_admin): ?>
                <a href="admin_mesajlar.php">📥 Gelen Mesajlar</a>
            <?php endif; ?>
            <a href="cerezler.php">🍪 Çerezler</a>
            <a href="duyurular.php">📢 Duyurular</a>
            <a href="hakkimizda.php">ℹ️ Hakkımda</a>
            <a href="bize_ulasin.php">📬 Bize Ulaşın</a>
            <?php if ($oturum_acik): ?>
                <a href="cikis.php" style="border-top:1px solid #333; color:#dc3545;">🚪 Çıkış Yap</a>
            <?php endif; ?>
        </div>
    </div>

    <h2>📢 Duyuru Paneli</h2>
    <?php if ($uyari != "") echo "<div class='uyari $uyari_tur'>$uyari</div>"; ?>

    <?php if ($is_admin): ?>
    <form method="post">
        <textarea name="duyuru_metni" rows="3" class="input-area" placeholder="Yeni duyuru metnini buraya giriniz..."></textarea>
        <button name="duyuru_ekle">Yayınla (Ekle)</button>
    </form>
    <?php endif; ?>

    <div class="liste" id="duyuruListesi">
        <h3 style="margin-top:0; color:#818181; font-size:0.9em; border-bottom:1px solid #333; padding-bottom:5px;">YAYINDAKİ DUYURULAR</h3>
        <?php
        if (!empty($duyurular)) {
            // array_reverse($duyurular, true) kullanarak orijinal satır numaralarını (key) koruyoruz
            foreach (array_reverse($duyurular, true) as $id => $d) {
                // Sadece admin ise sürüklenebilir (draggable) olsun
                $draggable = $is_admin ? "draggable='true'" : "";
                echo "<div class='liste-item' $draggable style='display: flex; justify-content: space-between; align-items: center;'>";
                echo "<span class='duyuru-metin'>" . htmlspecialchars($d) . "</span>";
                echo "<div>";
                if ($is_admin) {
                    echo "<a href='?sil=$id' style='color: #dc3545; text-decoration: none; font-size: 0.8em; border: 1px solid #dc3545; padding: 2px 6px; border-radius: 3px;' onclick=\"return confirm('Silmek istediğinize emin misiniz?')\">Sil</a>";
                }
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='liste-item' style='color:#666;'>Henüz duyuru eklenmemiş.</div>";
        }
        ?>
    </div>
    
    <a href="anasayfa.php" style="display:block; margin-top:20px; color:#818181; text-decoration:none;">← Ana Portala Dön</a>
</div>

<script>
<?php if ($is_admin): ?>
const list = document.getElementById('duyuruListesi');
let draggedItem = null;

list.addEventListener('dragstart', (e) => {
    draggedItem = e.target;
    e.target.classList.add('dragging');
});

list.addEventListener('dragend', (e) => {
    e.target.classList.remove('dragging');
    saveOrder(); // Sıralama bitince kaydet
});

list.addEventListener('dragover', (e) => {
    e.preventDefault();
    const afterElement = getDragAfterElement(list, e.clientY);
    const draggable = document.querySelector('.dragging');
    if (afterElement == null) {
        list.appendChild(draggable);
    } else {
        list.insertBefore(draggable, afterElement);
    }
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.liste-item:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function saveOrder() {
    const items = [...list.querySelectorAll('.duyuru-metin')];
    const texts = items.map(item => item.innerText);

    const formData = new FormData();
    formData.append('ajax_reorder', '1');
    formData.append('liste', JSON.stringify(texts));

    fetch('duyurular.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(data => { if(data.trim() === "OK") location.reload(); }); // Sayfayı yenile ki silme linkleri (ID'ler) güncellensin
}
<?php endif; ?>
</script>
</body>
</html>