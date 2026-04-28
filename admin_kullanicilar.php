<?php
require 'db.php';
require 'header.php';

// Sadece admin rolüne sahip kişiler bu sayfayı görebilir
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center;'>Bu sayfayı görüntüleme yetkiniz yok!</h2>";
    require 'footer.php';
    exit;
}

$duzenlenecek_kullanici = null;

// Kullanıcı Silme İşlemi
if (isset($_GET['sil'])) {
    $sil_id = intval($_GET['sil']);
    // Admin kendi kendini silemesin
    if ($sil_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$sil_id]);
    }
    header("Location: admin_kullanicilar.php?islem=silindi");
    exit;
}

// Kullanıcı Ekleme/Güncelleme İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? null;
    $ad_soyad = trim($_POST['ad_soyad']);
    $email = trim($_POST['email']);
    $telefon = trim($_POST['telefon']);
    $adres = trim($_POST['adres']);
    $rol = $_POST['rol'];
    $sifre = $_POST['sifre'];

    try {
        if ($id) { // Güncelleme
            if (!empty($sifre)) {
                $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET ad_soyad = ?, email = ?, telefon = ?, adres = ?, rol = ?, sifre = ? WHERE id = ?");
                $stmt->execute([$ad_soyad, $email, $telefon, $adres, $rol, $sifre_hash, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET ad_soyad = ?, email = ?, telefon = ?, adres = ?, rol = ? WHERE id = ?");
                $stmt->execute([$ad_soyad, $email, $telefon, $adres, $rol, $id]);
            }
            header("Location: admin_kullanicilar.php?islem=guncellendi");
        } else { // Ekleme
            if (empty($sifre)) {
                header("Location: admin_kullanicilar.php?hata=sifre_gerekli#form-baslik");
                exit;
            }
            $sifre_hash = password_hash($sifre, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (ad_soyad, email, telefon, adres, sifre, rol) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$ad_soyad, $email, $telefon, $adres, $sifre_hash, $rol]);
            header("Location: admin_kullanicilar.php?islem=eklendi");
        }
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Duplicate entry for email
            header("Location: admin_kullanicilar.php?hata=email_kullanimda" . ($id ? "&duzenle=$id" : "") . "#form-baslik");
            exit;
        } else {
            die("Bir veritabanı hatası oluştu: " . $e->getMessage());
        }
    }
    exit;
}

// Düzenlenecek kullanıcıyı çek
if (isset($_GET['duzenle'])) {
    $duzenle_id = intval($_GET['duzenle']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$duzenle_id]);
    $duzenlenecek_kullanici = $stmt->fetch();
}

// Kullanıcıları Listelemek İçin Veritabanından Çek
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$kullanicilar = $stmt->fetchAll();
?>

<h2>Kullanıcı Yönetimi (Admin)</h2>

<?php
if (isset($_GET['islem'])) echo "<p style='color:green;'>İşlem başarıyla tamamlandı!</p>";
if (isset($_GET['hata'])) {
    $hata_mesaji = '';
    if ($_GET['hata'] == 'email_kullanimda') $hata_mesaji = 'Bu e-posta adresi zaten kullanılıyor!';
    if ($_GET['hata'] == 'sifre_gerekli') $hata_mesaji = 'Yeni kullanıcı için şifre belirlemek zorunludur!';
    if ($hata_mesaji) echo "<p style='color:red;'>$hata_mesaji</p>";
}
?>

<h3 id="form-baslik"><?= $duzenlenecek_kullanici ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı Ekle' ?></h3>
<form method="POST" action="admin_kullanicilar.php" style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 30px;">
    <input type="hidden" name="id" value="<?= $duzenlenecek_kullanici['id'] ?? '' ?>">
    <input type="text" name="ad_soyad" placeholder="Ad Soyad" value="<?= htmlspecialchars($duzenlenecek_kullanici['ad_soyad'] ?? '') ?>" required style="width: 95%; margin-bottom: 10px;"><br>
    <input type="email" name="email" placeholder="E-posta Adresi" value="<?= htmlspecialchars($duzenlenecek_kullanici['email'] ?? '') ?>" required style="width: 95%; margin-bottom: 10px;"><br>
    <input type="text" name="telefon" placeholder="Telefon" value="<?= htmlspecialchars($duzenlenecek_kullanici['telefon'] ?? '') ?>" style="width: 95%; margin-bottom: 10px;"><br>
    <textarea name="adres" placeholder="Adres" rows="3" style="width: 95%; margin-bottom: 10px;"><?= htmlspecialchars($duzenlenecek_kullanici['adres'] ?? '') ?></textarea><br>
    <input type="password" name="sifre" placeholder="Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın)" <?= !$duzenlenecek_kullanici ? 'required' : '' ?> style="width: 95%; margin-bottom: 10px;"><br>
    <select name="rol" required style="width: 98%; margin-bottom: 10px; padding: 10px;">
        <option value="user" <?= (isset($duzenlenecek_kullanici['rol']) && $duzenlenecek_kullanici['rol'] == 'user') ? 'selected' : '' ?>>Kullanıcı (User)</option>
        <option value="admin" <?= (isset($duzenlenecek_kullanici['rol']) && $duzenlenecek_kullanici['rol'] == 'admin') ? 'selected' : '' ?>>Yönetici (Admin)</option>
    </select><br>
    <button type="submit" class="btn"><?= $duzenlenecek_kullanici ? 'Güncelle' : 'Kaydet' ?></button>
    <?php if ($duzenlenecek_kullanici): ?><a href="admin_kullanicilar.php" class="btn" style="background-color: #7f8c8d;">İptal</a><?php endif; ?>
</form>

<h3>Mevcut Kullanıcılar</h3>
<table class='tablo'>
    <tr><th>ID</th><th>Ad Soyad</th><th>E-Posta</th><th>Rol</th><th>Kayıt Tarihi</th><th>İşlemler</th></tr>
    <?php foreach ($kullanicilar as $kullanici): ?>
        <tr>
            <td>#<?= $kullanici['id'] ?></td>
            <td><strong><?= htmlspecialchars($kullanici['ad_soyad']) ?></strong></td>
            <td><?= htmlspecialchars($kullanici['email']) ?></td>
            <td><span style="background: <?= $kullanici['rol'] == 'admin' ? '#2980b9' : '#7f8c8d' ?>; color: white; padding: 2px 6px; border-radius: 3px;"><?= htmlspecialchars(ucfirst($kullanici['rol'])) ?></span></td>
            <td><?= date('d.m.Y', strtotime($kullanici['olusturulma_tarihi'])) ?></td>
            <td>
                <a href="?duzenle=<?= $kullanici['id'] ?>#form-baslik" class="btn" style="background-color: #f39c12; padding: 5px 10px; font-size: 0.9em;">Düzenle</a>
                <?php if ($kullanici['id'] !== $_SESSION['user_id']): ?>
                    <a href="?sil=<?= $kullanici['id'] ?>" class="btn" style="background-color: #e74c3c; padding: 5px 10px; font-size: 0.9em;" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">Sil</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php require 'footer.php'; ?>