<?php
require 'db.php';
require 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center;'>Bu sayfayı görüntüleme yetkiniz yok!</h2>";
    require 'footer.php';
    exit;
}

// Ürün Silme İşlemi
if (isset($_GET['sil'])) {
    $sil_id = intval($_GET['sil']);
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$sil_id])) {
        echo "<p style='color:green;'>Ürün başarıyla silindi!</p>";
    }
}

// Ürün Ekleme İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urun_adi = $_POST['urun_adi'];
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $stok = $_POST['stok'];
    $resim_url = $_POST['resim_url'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO products (urun_adi, aciklama, fiyat, stok, resim_url) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$urun_adi, $aciklama, $fiyat, $stok, $resim_url])) {
        echo "<p style='color:green;'>Ürün başarıyla eklendi!</p>";
    } else {
        echo "<p style='color:red;'>Ürün eklenirken hata oluştu.</p>";
    }
}

// Ürünleri Listelemek İçin Veritabanından Çek
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$urunler = $stmt->fetchAll();
?>

<h2>Ürün Yönetimi (Admin)</h2>

<h3>Yeni Ürün Ekle</h3>
<form method="POST" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 30px;">
    <input type="text" name="urun_adi" placeholder="Ürün Adı" required style="width: 95%; margin-bottom: 10px;"><br>
    <textarea name="aciklama" placeholder="Ürün Açıklaması" rows="3" style="width: 95%; margin-bottom: 10px;"></textarea><br>
    <input type="number" step="0.01" name="fiyat" placeholder="Fiyat (TL)" required style="width: 95%; margin-bottom: 10px;"><br>
    <input type="number" name="stok" placeholder="Stok Adedi" required style="width: 95%; margin-bottom: 10px;"><br>
    <input type="url" name="resim_url" placeholder="Ürün Resmi URL'si (Örn: https://...)" style="width: 95%; margin-bottom: 10px;"><br>
    <button type="submit" class="btn">Kaydet</button>
</form>

<h3>Mevcut Ürünler</h3>
<?php if (count($urunler) > 0): ?>
    <table class='tablo'>
        <tr>
            <th>Resim</th>
            <th>Ürün Adı</th>
            <th>Fiyat</th>
            <th>Stok</th>
            <th>İşlem</th>
        </tr>
        <?php foreach ($urunler as $urun): ?>
            <tr>
                <td><img src="<?= htmlspecialchars($urun['resim_url'] ?: 'default.jpg') ?>" width="50" height="50" style="object-fit:cover; border-radius:4px;"></td>
                <td><?= htmlspecialchars($urun['urun_adi']) ?></td>
                <td><?= number_format($urun['fiyat'], 2, ',', '.') ?> TL</td>
                <td><?= $urun['stok'] ?></td>
                <td><a href="?sil=<?= $urun['id'] ?>" class="btn" style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.9em;" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?');">Sil</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Henüz ürün eklenmemiş.</p>
<?php endif; ?>

<?php require 'footer.php'; ?>