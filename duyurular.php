<?php
require 'db.php';
require 'header.php';

$stmt = $pdo->query("SELECT * FROM announcements ORDER BY yayin_tarihi DESC");
$duyurular = $stmt->fetchAll();
?>

<h2>Duyurular</h2>
<div class="duyurular-listesi">
    <?php if (count($duyurular) > 0): ?>
        <?php foreach ($duyurular as $duyuru): ?>
            <div class="duyuru" style="border-bottom: 1px solid #ddd; padding: 10px 0; margin-bottom: 10px;">
                <h3><?= htmlspecialchars($duyuru['baslik']) ?></h3>
                <p><small>Yayın Tarihi: <?= date('d.m.Y H:i', strtotime($duyuru['yayin_tarihi'])) ?></small></p>
                <p><?= nl2br(htmlspecialchars($duyuru['icerik'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Henüz bir duyuru yayınlanmamış.</p>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>