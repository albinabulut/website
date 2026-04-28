<?php
require 'db.php';
require 'header.php';

// Admine Geri Dönüş İşlemi
if (isset($_GET['geri_don']) && isset($_SESSION['eski_admin_id'])) {
    $admin_id = $_SESSION['eski_admin_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_user = $stmt->fetch();

    if ($admin_user) {
        $_SESSION['user_id'] = $admin_user['id'];
        $_SESSION['ad_soyad'] = $admin_user['ad_soyad'];
        $_SESSION['rol'] = $admin_user['rol'];
        unset($_SESSION['eski_admin_id']);
        
        echo "<script>window.location.href='admin_multisession.php';</script>";
        exit;
    }
}

// Sadece admin rolüne sahip kişiler bu sayfayı görebilir
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center;'>Bu sayfayı görüntüleme yetkiniz yok!</h2>";
    require 'footer.php';
    exit;
}

// Başka bir hesaba geçiş yapma (Multisession / Impersonation)
if (isset($_GET['gecis'])) {
    $hedef_id = intval($_GET['gecis']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$hedef_id]);
    $hedef_user = $stmt->fetch();

    // Başka bir admine geçişi engelliyoruz
    if ($hedef_user && $hedef_user['rol'] !== 'admin') { 
        // Mevcut admin kimliğini hafızaya al (geri dönebilmek için)
        $_SESSION['eski_admin_id'] = $_SESSION['user_id'];
        
        // Hedef kullanıcının bilgilerini mevcut session'a yaz
        $_SESSION['user_id'] = $hedef_user['id'];
        $_SESSION['ad_soyad'] = $hedef_user['ad_soyad'];
        $_SESSION['rol'] = $hedef_user['rol'];
        
        echo "<script>alert('" . $hedef_user['ad_soyad'] . " adlı müşterinin hesabına geçiş yapıldı!'); window.location.href='index.php';</script>";
        exit;
    }
}

// Müşterileri listelemek için veritabanından çekiyoruz (rol = 'user')
$stmt = $pdo->query("SELECT * FROM users WHERE rol = 'user' ORDER BY id DESC");
$uyeler = $stmt->fetchAll();
?>

<h2>Multisession (Hesaplara Geçiş)</h2>
<p>Buradan müşterilerinizin hesaplarına giriş yapıp sitenizi onların gözünden görebilirsiniz.</p>

<?php if (count($uyeler) > 0): ?>
    <table class='tablo'>
        <tr>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>E-Posta</th>
            <th>Kayıt Tarihi</th>
            <th>İşlem</th>
        </tr>
        <?php foreach ($uyeler as $uye): ?>
            <tr>
                <td>#<?= $uye['id'] ?></td>
                <td><strong><?= htmlspecialchars($uye['ad_soyad']) ?></strong></td>
                <td><?= htmlspecialchars($uye['email']) ?></td>
                <td><?= date('d.m.Y', strtotime($uye['olusturulma_tarihi'])) ?></td>
                <td>
                    <a href="?gecis=<?= $uye['id'] ?>" class="btn" style="background-color: #3498db; padding: 5px 10px; font-size: 0.9em;" onclick="return confirm('Bu kullanıcının hesabına geçiş yapmak istediğinize emin misiniz?');">Geçiş Yap</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Sistemde henüz kayıtlı müşteri bulunmuyor. Yeni üyeler kayıt oldukça burada listelenecektir.</p>
<?php endif; ?>

<?php require 'footer.php'; ?>