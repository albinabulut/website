<?php
require 'db.php';
require 'header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center;'>Bu sayfayı görüntüleme yetkiniz yok!</h2>";
    require 'footer.php';
    exit;
}

// Duyuru Silme İşlemi
if (isset($_GET['sil'])) {
    $sil_id = intval($_GET['sil']);
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    if ($stmt->execute([$sil_id])) {
        echo "<p style='color:green;'>Duyuru başarıyla silindi!</p>";
    }
}

// Duyuru Ekleme İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['icerik'])) {
    $icerik = $_POST['icerik'];

    $stmt = $pdo->prepare("INSERT INTO announcements (baslik, icerik) VALUES ('Duyuru', ?)");
    if ($stmt->execute([$icerik])) {
        echo "<p style='color:green;'>Duyuru başarıyla yayınlandı!</p>";
    } else {
        echo "<p style='color:red;'>Hata oluştu.</p>";
    }
}

// Duyuruları Listelemek İçin Veritabanından Çek
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY yayin_tarihi DESC");
$duyurular = $stmt->fetchAll();
?>

<h2>Duyuru Yönetimi (Admin)</h2>

<h3>Yeni Duyuru Ekle</h3>
<form method="POST" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 30px;">
    <textarea name="icerik" placeholder="Duyuru İçeriği" rows="4" required style="width: 95%; margin-bottom: 10px;"></textarea><br>
    <button type="submit" class="btn">Yayınla</button>
</form>

<h3>Mevcut Duyurular</h3>
<?php if (count($duyurular) > 0): ?>
    <table class='tablo'>
        <tr>
            <th>Duyuru İçeriği</th>
            <th>Tarih</th>
            <th>İşlem</th>
        </tr>
        <?php foreach ($duyurular as $duyuru): ?>
            <tr>
                <td><?= htmlspecialchars($duyuru['icerik']) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($duyuru['yayin_tarihi'])) ?></td>
                <td><a href="?sil=<?= $duyuru['id'] ?>" class="btn" style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.9em;" onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?');">Sil</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Henüz bir duyuru eklenmemiş.</p>
<?php endif; ?>

<?php require 'footer.php'; ?>