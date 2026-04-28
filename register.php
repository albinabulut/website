<?php
require 'db.php';
require 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad_soyad = $_POST['ad_soyad'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];
    $adres = $_POST['adres'];
    // Şifreyi güvenlik için Hash'liyoruz
    $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT); 

    $stmt = $pdo->prepare("INSERT INTO users (ad_soyad, email, telefon, adres, sifre) VALUES (?, ?, ?, ?, ?)");
    
    if($stmt->execute([$ad_soyad, $email, $telefon, $adres, $sifre])) {
        echo "<p style='color:green;'>Kayıt başarılı! <a href='login.php'>Giriş yapabilirsiniz</a>.</p>";
    } else {
        echo "<p style='color:red;'>Kayıt sırasında hata oluştu. Bu e-posta zaten kullanımda olabilir.</p>";
    }
}
?>

<h2>Yeni Üye Kaydı</h2>
<form method="POST" id="kayitFormu" novalidate>
    <input type="text" name="ad_soyad" placeholder="Adınız Soyadınız" required><br><br>
    <input type="email" name="email" placeholder="E-posta Adresiniz" required><br><br>
    <input type="text" name="telefon" placeholder="Telefon Numaranız" maxlength="11" minlength="11" pattern="[0-9]{11}" title="Lütfen 11 haneli telefon numaranızı giriniz (Örn: 05xxxxxxxxx)" required><br><br>
    <textarea name="adres" placeholder="Adresiniz" rows="3" required></textarea><br><br>
    <input type="password" name="sifre" placeholder="Şifreniz" required><br><br>
    <button type="submit">Kayıt Ol</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('kayitFormu');
    const inputs = form.querySelectorAll('input[required], textarea[required]');

    // Kullanıcı giriş yaparken veya kutudan çıkarken anlık (canlı) kontrol et
    inputs.forEach(input => {
        input.addEventListener('input', kontrolEt);
        input.addEventListener('blur', kontrolEt);
    });

    function kontrolEt() {
        if (this.value.trim() === '') {
            this.style.border = '2px solid red'; // Boşsa kutuyu anlık kırmızı yap
        } else {
            this.style.border = '1px solid #ccc'; // Doluysa normal görünüme çevir
        }
    }

    // Kayıt ol butonuna basıldığında boş olanları topluca uyar
    form.addEventListener('submit', function(e) {
        let bosAlanlar = [];
        inputs.forEach(input => {
            if (input.value.trim() === '') {
                input.style.border = '2px solid red';
                bosAlanlar.push(input.getAttribute('placeholder'));
            }
        });
        
        if (bosAlanlar.length > 0) {
            e.preventDefault(); // Sayfanın yenilenmesini ve formun gönderilmesini durdur
            alert("Lütfen şu zorunlu alanları doldurun:\n- " + bosAlanlar.join("\n- "));
        }
    });
});
</script>

<?php require 'footer.php'; ?>