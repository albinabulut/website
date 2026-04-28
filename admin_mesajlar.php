<?php
session_start();
date_default_timezone_set('Europe/Istanbul');
require_once 'baglanti.php';
require_once 'MailManager.php'; // Mail gönderme işlemleri için dahil ediyoruz

// Sadece Admin (ID: 9) bu sayfayı görüntüleyebilir
if (!isset($_SESSION['kullanici_id']) || $_SESSION['kullanici_id'] != 9) {
    header("Location: anasayfa.php");
    exit;
}

$uyari = "";

// Durum Bildirim (Evet/Hayır) İşlemi
if (isset($_GET['islem']) && isset($_GET['id'])) {
    $islem = $_GET['islem'];
    $id = (int)$_GET['id'];

    // Tabloya 'durum' sütunu ekleme (Eğer eski tabloda yoksa otomatik ekler)
    try {
        $db->exec("ALTER TABLE mesajlar ADD COLUMN durum VARCHAR(50) DEFAULT 'Bekliyor'");
    } catch (PDOException $e) { }

    // Mesajın e-posta adresini al
    $stmt = $db->prepare("SELECT email FROM mesajlar WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $mesaj = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mesaj) {
        $alici = $mesaj['email'];
        $konu = "Bize Ulaşın - İletişim Talebiniz Hakkında";
        
        if ($islem == 'evet') {
            $icerik = "Merhaba,<br><br>Bize iletmiş olduğunuz talebiniz <b>işleme alınmış ve olumlu (Yapıldı)</b> olarak sonuçlandırılmıştır.<br><br>İlginiz için teşekkür ederiz.";
            if (mailGonder($alici, "Kullanıcı", $konu, $icerik)) {
                $db->prepare("UPDATE mesajlar SET durum = 'Yapıldı' WHERE id = :id")->execute(['id' => $id]);
                $uyari = "Kullanıcıya 'Yapıldı' e-postası başarıyla gönderildi.";
            } else {
                $uyari = "E-posta gönderilirken bir hata oluştu.";
            }
        } elseif ($islem == 'hayir') {
            $icerik = "Merhaba,<br><br>Bize iletmiş olduğunuz talebiniz incelenmiş olup, <b>olumsuz (Yapılmadı)</b> olarak sonuçlandırılmıştır.<br><br>İlginiz için teşekkür ederiz.";
            if (mailGonder($alici, "Kullanıcı", $konu, $icerik)) {
                $db->prepare("UPDATE mesajlar SET durum = 'Yapılmadı' WHERE id = :id")->execute(['id' => $id]);
                $uyari = "Kullanıcıya 'Yapılmadı' e-postası başarıyla gönderildi.";
            } else {
                $uyari = "E-posta gönderilirken bir hata oluştu.";
            }
        }
    }
}

// Mesaj Silme İşlemi
if (isset($_GET['sil'])) {
    $sil_id = (int)$_GET['sil'];
    $stmt = $db->prepare("DELETE FROM mesajlar WHERE id = :id");
    if ($stmt->execute(['id' => $sil_id])) {
        $uyari = "İlgili mesaj başarıyla silindi.";
    }
}

// Mesajları Çek (Tarihe göre en yeniden eskiye)
$mesajlar = [];
try {
    $mesajlar = $db->query("SELECT * FROM mesajlar ORDER BY tarih DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tablo henüz oluşmamış olabilir
    $uyari = "Mesajlar tablosu henüz oluşturulmamış. Bize Ulaşın sayfasından ilk mesaj gönderildiğinde sistem otomatik olarak tabloyu oluşturacaktır.";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Gelen Mesajlar | Admin Paneli</title>
<link rel="icon" href="img/logo.jpg" type="image/jpeg">
<style>
    body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #f0f0f0; padding: 20px; max-width: 900px; margin: 0 auto; }
    h2 { color: #f7e7a3; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
    .back-link { display: inline-block; margin-bottom: 20px; color: #818181; font-size: 1em; text-decoration: none; transition: 0.3s; }
    .back-link:hover { color: #f7e7a3; }
    .uyari { background: rgba(40,167,69,0.2); color: #28a745; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #28a745; }
    
    .mesaj-kutu { background: #0d0d0d; border: 1px solid #333; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 0 15px rgba(0,0,0,0.5); }
    .mesaj-baslik { border-bottom: 1px solid #222; padding-bottom: 15px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
    .mesaj-bilgi { font-size: 1.1em; color: #f7e7a3; }
    .mesaj-email { color: #818181; font-size: 0.9em; margin-left: 10px; }
    .mesaj-tarih { color: #28a745; font-size: 0.9em; font-weight: bold; }
    .mesaj-konu { color: #fff; font-weight: bold; margin-bottom: 10px; display: block; }
    .mesaj-icerik { color: #ccc; line-height: 1.6; white-space: pre-wrap; font-size: 1.05em; }
    
    .sil-btn { background: transparent; color: #dc3545; border: 1px solid #dc3545; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; transition: 0.3s; }
    .sil-btn:hover { background: #dc3545; color: white; }
    .btn-evet { background: transparent; color: #28a745; border: 1px solid #28a745; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; transition: 0.3s; margin-left: 15px; }
    .btn-evet:hover { background: #28a745; color: white; }
    .btn-hayir { background: transparent; color: #ffc107; border: 1px solid #ffc107; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; transition: 0.3s; margin-left: 5px; }
    .btn-hayir:hover { background: #ffc107; color: black; }
    
    .empty-msg { text-align: center; color: #666; padding: 40px; background: #0d0d0d; border-radius: 8px; border: 1px dashed #333; }
</style>
</head>
<body>

<a href="anasayfa.php" class="back-link">← Ana Sayfaya Dön</a>
<a href="admin_sessions.php" class="back-link" style="margin-left: 15px; color:#28a745;">🛡️ Admin Paneli</a>

<h2>📥 Bize Ulaşın - Gelen Mesajlar</h2>

<?php if ($uyari): ?>
    <div class="uyari"><?= $uyari ?></div>
<?php endif; ?>

<?php if (empty($mesajlar)): ?>
    <div class="empty-msg">Sisteme ulaşan yeni bir mesaj bulunmuyor.</div>
<?php else: ?>
    <?php foreach ($mesajlar as $m): ?>
        <div class="mesaj-kutu">
            <div class="mesaj-baslik">
                <div>
                    <span class="mesaj-bilgi"> <?= htmlspecialchars($m['email']) ?></span>
                </div>
                <div>
                    <span class="mesaj-tarih">🕒 <?= date("d.m.Y H:i:s", strtotime($m['tarih'])) ?></span>
                    <?php
                    $durum = isset($m['durum']) ? $m['durum'] : 'Bekliyor';
                    if ($durum == 'Yapıldı') {
                        echo "<span style='color:#28a745; margin-left:10px; font-weight:bold;'>[✅ Yapıldı]</span>";
                    } elseif ($durum == 'Yapılmadı') {
                        echo "<span style='color:#ffc107; margin-left:10px; font-weight:bold;'>[❌ Yapılmadı]</span>";
                    }
                    ?>
                    <a href="?islem=evet&id=<?= $m['id'] ?>" class="btn-evet" onclick="return confirm('Kullanıcıya talebinin (Yapıldığı) ile ilgili bir e-posta gönderilecek. Onaylıyor musunuz?')">✅ Yapıldı</a>
                    <a href="?islem=hayir&id=<?= $m['id'] ?>" class="btn-hayir" onclick="return confirm('Kullanıcıya talebinin (Yapılmadığı) ile ilgili bir e-posta gönderilecek. Onaylıyor musunuz?')">❌ Yapılmadı</a>
                    <a href="?sil=<?= $m['id'] ?>" class="sil-btn" style="margin-left: 15px;" onclick="return confirm('Bu mesajı kalıcı olarak silmek istediğinize emin misiniz?')">🗑️ Sil</a>
                </div>
            </div>
            
            <div class="mesaj-icerik"><?= nl2br(htmlspecialchars($m['mesaj'])) ?></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>