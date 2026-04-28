<?php
require 'db.php';
require 'header.php';

// Kullanıcı giriş yapmamışsa ödeme sayfasına giremez
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

// Form gönderildiyse (Ödeme Yapıldıysa)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kredi kartı işlemleri burada banka API'sine gönderilir.
    // Biz simülasyon olarak başarılı varsayıp kullanıcının sepetini temizliyoruz.
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    echo "<div style='text-align:center; padding: 40px;'>";
    echo "<h2 style='color:green;'>Ödemeniz Başarıyla Alındı!</h2>";
    echo "<p>Siparişiniz hazırlanıyor. Bizi tercih ettiğiniz için teşekkür ederiz.</p>";
    echo "<br><a href='index.php' class='btn'>Alışverişe Dön</a>";
    echo "</div>";
    require 'footer.php';
    exit;
}
?>

<h2>Güvenli Ödeme Sayfası</h2>
<form method="POST" style="max-width: 500px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
    <label>Kart Üzerindeki İsim</label><br>
    <input type="text" name="kart_isim" placeholder="Ad Soyad" required style="width: 95%; margin-bottom: 15px;"><br>
    
    <label>Kart Numarası</label><br>
    <input type="text" id="kart_numarasi" name="kart_no" placeholder="0000 0000 0000 0000" maxlength="19" required style="width: 95%; margin-bottom: 15px;"><br>
    
    <div style="display: flex; gap: 15px; margin-bottom: 15px; width: 99%;">
        <div style="flex: 1;">
            <label>Ay</label><br>
            <select name="ay" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="">Ay Seç</option>
                <?php for($i=1; $i<=12; $i++): $val = str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                    <option value="<?= $val ?>"><?= $val ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div style="flex: 1;">
            <label>Yıl</label><br>
            <select name="yil" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
                <option value="">Yıl Seç</option>
                <?php $yil = date("Y"); for($i=0; $i<=10; $i++): ?>
                    <option value="<?= $yil+$i ?>"><?= $yil+$i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div style="flex: 1;">
            <label>CVV</label><br>
            <input type="text" name="cvv" placeholder="123" maxlength="3" pattern="[0-9]{3}" required style="width: 80%; padding: 10px;">
        </div>
    </div>
    
    <button type="submit" class="btn" style="width: 100%;">Ödemeyi Tamamla</button>
</form>

<script>
// Kart Numarasını otomatik 4'erli boşluklu yazma scripti
document.getElementById('kart_numarasi').addEventListener('input', function (e) {
    let val = this.value.replace(/\D/g, ''); // Harfleri ve boşlukları sil, sadece rakamları al
    val = val.substring(0, 16); // Maksimum 16 hane
    
    // Rakamları 4'erli gruplara böl ve aralarına boşluk ekle
    let formatted = val !== '' ? val.match(/.{1,4}/g).join(' ') : '';
    this.value = formatted;
});
</script>

<?php require 'footer.php'; ?>