<?php
require 'db.php';
require 'header.php';

// Sadece admin rolüne sahip kişiler bu sayfayı görebilir
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo "<h2 style='color:red; text-align:center; padding: 50px;'>Bu sayfayı görüntüleme yetkiniz yok!</h2>";
    require 'footer.php';
    exit;
}

// Mesajları veritabanından en yeniden eskiye doğru çekiyoruz
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY tarih DESC");
$mesajlar = $stmt->fetchAll();
?>

<h2>Gelen İletişim Mesajları</h2>

<?php if (count($mesajlar) > 0): ?>
    <table class='tablo'>
        <tr>
            <th>Tarih</th>
            <th>İsim</th>
            <th>E-Posta</th>
            <th>Konu</th>
            <th>Mesaj İçeriği</th>
        </tr>
        <?php foreach ($mesajlar as $msj): ?>
            <tr>
                <td><?= date('d.m.Y H:i', strtotime($msj['tarih'])) ?></td>
                <td><strong><?= htmlspecialchars($msj['isim']) ?></strong></td>
                <td><a href="mailto:<?= htmlspecialchars($msj['email']) ?>"><?= htmlspecialchars($msj['email']) ?></a></td>
                <td><?= htmlspecialchars($msj['konu']) ?></td>
                <td><?= nl2br(htmlspecialchars($msj['mesaj'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p style="text-align: center; padding: 30px; font-size: 1.2em;">Henüz hiç iletişim mesajı almadınız.</p>
<?php endif; ?>

<?php require 'footer.php'; ?>