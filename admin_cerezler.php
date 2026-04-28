<?php
require 'db.php';
require 'header.php';

// Sadece admin rolüne sahip kişiler bu sayfayı görebilir
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center;'>Bu sayfayı görüntüleme yetkiniz yok!</h2>";
    require 'footer.php';
    exit;
}

// Çerez Silme İşlemi (Çerezin ömrünü geçmiş bir tarihe -1 saat geriye ayarlayarak sileriz)
if (isset($_GET['sil'])) {
    $cerez_adi = $_GET['sil'];
    setcookie($cerez_adi, '', time() - 3600, "/");
    
    // Tarayıcıdaki çerezin silindiğini anında görmek için sayfayı yeniliyoruz
    echo "<script>window.location.href='admin_cerezler.php';</script>";
    exit;
}

// Çerez Ekleme İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cerez_adi = trim($_POST['cerez_adi']);
    $cerez_degeri = trim($_POST['cerez_degeri']);
    $sure_gun = intval($_POST['sure_gun']);
    
    // Çerezi oluştur (gün sayısını saniyeye: 86400 çevirerek ekliyoruz)
    setcookie($cerez_adi, $cerez_degeri, time() + ($sure_gun * 86400), "/");
    
    echo "<script>window.location.href='admin_cerezler.php';</script>";
    exit;
}
?>

<h2>Çerez (Cookie) Yönetimi (Admin)</h2>

<h3>Yeni Çerez Ekle</h3>
<form method="POST" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 30px;">
    <input type="text" name="cerez_adi" placeholder="Çerez Adı (Boşluk kullanmayın, örn: test_cerezi)" required style="width: 95%; margin-bottom: 10px;"><br>
    <input type="text" name="cerez_degeri" placeholder="Çerez Değeri (Örn: aktif)" required style="width: 95%; margin-bottom: 10px;"><br>
    <input type="number" name="sure_gun" placeholder="Geçerlilik Süresi (Gün)" value="30" required style="width: 95%; margin-bottom: 10px;"><br>
    <button type="submit" class="btn">Çerez Oluştur</button>
</form>

<h3>Sistemdeki Aktif Çerezler</h3>
<?php if (count($_COOKIE) > 0): ?>
    <table class='tablo'>
        <tr><th>Çerez Adı</th><th>Değeri</th><th>İşlem</th></tr>
        <?php foreach ($_COOKIE as $isim => $deger): ?>
            <tr>
                <td><strong><?= htmlspecialchars($isim) ?></strong></td>
                <td><code style="background:#eee; padding:2px 5px; border-radius:3px;"><?= htmlspecialchars($deger) ?></code></td>
                <td><a href="?sil=<?= urlencode($isim) ?>" class="btn" style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.9em;" onclick="return confirm('Bu çerezi silmek istediğinize emin misiniz?');">Sil</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?><p>Sistemde henüz aktif bir çerez bulunmuyor.</p><?php endif; ?>
<?php require 'footer.php'; ?>