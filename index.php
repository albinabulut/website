<?php
require 'db.php';
require 'header.php';

// Ürünleri veritabanından çek
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$urunler = $stmt->fetchAll();

// Eğer veritabanında hiç ürün yoksa, test için rastgele takı ürünleri ekleyelim
if (count($urunler) == 0) {
    $rastgele_urunler = [
        ['Altın Kaplama Zirkon Kolye', 'Günlük kullanıma uygun, kararmaz altın kaplama zarif zirkon taşlı kolye.', 250.00, 20, 'https://images.unsplash.com/photo-1599643478514-4a820c56a820?w=400'],
        ['925 Ayar Gümüş Yüzük', 'Özel tasarım tektaş detaylı parlak gümüş kadın yüzüğü.', 399.90, 15, 'https://images.unsplash.com/photo-1605100804763-247f6612d540?w=400'],
        ['İnci Detaylı Küpe', 'Gerçek tatlı su incisi kullanılarak tasarlanmış sallantılı şık küpe.', 180.50, 30, 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=400'],
        ['Doğal Ametist Bileklik', 'Negatif enerjiyi uzaklaştıran, özel kesim doğal ametist taşı bileklik.', 120.00, 50, 'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=400'],
        ['Pırlanta Görünümlü Takı Seti', 'Kolye, küpe ve yüzükten oluşan, özel günler için parıltılı set.', 850.00, 10, 'https://images.unsplash.com/photo-1515562141207-7a8efbc88b71?w=400']
    ];
    
    $ekleStmt = $pdo->prepare("INSERT INTO products (urun_adi, aciklama, fiyat, stok, resim_url) VALUES (?, ?, ?, ?, ?)");
    foreach ($rastgele_urunler as $urun) {
        $ekleStmt->execute($urun);
    }
    
    // Ürünleri ekledikten sonra ana sayfada göstermek için listeyi güncelliyoruz
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $urunler = $stmt->fetchAll();
}
?>

<h2>Vitrinimiz</h2>
<div class="urun-grid">
    <?php if (count($urunler) > 0): ?>
        <?php foreach ($urunler as $urun): ?>
            <div class="urun-karti">
                <img src="<?= htmlspecialchars($urun['resim_url'] ?: 'default.jpg') ?>" alt="Ürün Resmi" style="width:100%; height:200px; object-fit:cover;">
                <h3><?= htmlspecialchars($urun['urun_adi']) ?></h3>
                <p style="color: #e74c3c; font-weight: bold;"><?= number_format($urun['fiyat'], 2, ',', '.') ?> TL</p>
                <p><?= htmlspecialchars(substr($urun['aciklama'], 0, 50)) ?>...</p>
                <a href="sepete_ekle.php?product_id=<?= $urun['id'] ?>" class="btn">Sepete Ekle</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Henüz ürün eklenmemiş.</p>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>