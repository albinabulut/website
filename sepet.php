<?php
require 'db.php';
require 'header.php';

echo "<h2>Alışveriş Sepetim</h2>";
$toplam = 0;

echo "<table class='tablo'><tr><th>Ürün</th><th>Fiyat</th><th>Miktar</th><th>Toplam</th><th>İşlem</th></tr>";

if (isset($_SESSION['user_id'])) {
    // Giriş yapılmış - İlişkili Tablo Kullanımı (JOIN)
    $stmt = $pdo->prepare("SELECT c.product_id, c.miktar, p.urun_adi, p.fiyat, (c.miktar * p.fiyat) as ara_toplam 
                           FROM cart_items c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $sepet = $stmt->fetchAll();
    
    foreach ($sepet as $urun) {
        echo "<tr><td>{$urun['urun_adi']}</td><td>{$urun['fiyat']} TL</td><td>{$urun['miktar']}</td><td>{$urun['ara_toplam']} TL</td><td><a href='sepetten_cikar.php?id={$urun['product_id']}' class='btn' style='background-color:#e74c3c; padding: 5px 10px;'>Kaldır</a></td></tr>";
        $toplam += $urun['ara_toplam'];
    }
} else {
    // Giriş yapılmamış - Session Kullanımı
    if (isset($_SESSION['sepet']) && !empty($_SESSION['sepet'])) {
        foreach ($_SESSION['sepet'] as $id => $miktar) {
            $stmt = $pdo->prepare("SELECT urun_adi, fiyat FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $urun = $stmt->fetch();
            
            if ($urun) {
                $ara = $urun['fiyat'] * $miktar;
                $toplam += $ara;
                echo "<tr><td>{$urun['urun_adi']}</td><td>{$urun['fiyat']} TL</td><td>$miktar</td><td>$ara TL</td><td><a href='sepetten_cikar.php?id={$id}' class='btn' style='background-color:#e74c3c; padding: 5px 10px;'>Kaldır</a></td></tr>";
            }
        }
    }
}

echo "</table>";

if ($toplam > 0) {
    echo "<h3>Genel Toplam: " . number_format($toplam, 2, ',', '.') . " TL</h3>";
    if(isset($_SESSION['user_id'])){
        echo "<a href='odeme.php' class='btn'>Siparişi Tamamla</a>";
    } else {
        echo "<p><a href='login.php'>Siparişi tamamlamak için lütfen giriş yapın.</a></p>";
    }
} else {
    echo "<p>Sepetiniz boş.</p>";
}

require 'footer.php';
?>