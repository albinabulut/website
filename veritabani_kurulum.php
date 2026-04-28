<?php
require_once 'baglanti.php';

echo "<h2>🛠️ Veritabanı Kurulum Sihirbazı</h2>";

try {
    // 1. Kullanıcı Tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS kullanici (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ad VARCHAR(50) NOT NULL,
        soyad VARCHAR(50) NOT NULL,
        mail VARCHAR(100) NOT NULL UNIQUE,
        telefon VARCHAR(20),
        sifre VARCHAR(255) NOT NULL,
        kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ 'kullanici' tablosu hazır.<br>";

    // 2. Ürünler Tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS urunler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ad VARCHAR(100) NOT NULL,
        fiyat DECIMAL(10, 2) NOT NULL,
        resim VARCHAR(255),
        eklenme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ 'urunler' tablosu hazır.<br>";

    // 3. Sepet Tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS sepet (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        urun_id INT NULL,
        urun_ad VARCHAR(100) NOT NULL,
        fiyat DECIMAL(10, 2) NOT NULL,
        resim VARCHAR(255),
        adet INT DEFAULT 1,
        FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
    )");
    echo "✅ 'sepet' tablosu hazır.<br>";

    // 4. Siparişler Tablosu (YENİ)
    $db->exec("CREATE TABLE IF NOT EXISTS siparisler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        urun_id INT NULL,
        urun_ad VARCHAR(100) NOT NULL,
        fiyat DECIMAL(10, 2) NOT NULL,
        adet INT NOT NULL,
        toplam_tutar DECIMAL(10, 2) NOT NULL,
        tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
        durum VARCHAR(50) DEFAULT 'Hazırlanıyor',
        FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE SET NULL
    )");
    echo "✅ 'siparisler' tablosu hazır.<br>";

    // 5. Session Tablosu (Gelişmiş Oturum)
    $db->exec("CREATE TABLE IF NOT EXISTS user_sessions_v2 (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1
    )");
    echo "✅ 'user_sessions_v2' tablosu hazır.<br>";

    // 6. IP Ban Tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS ip_bans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        reason VARCHAR(255),
        banned_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ 'ip_bans' tablosu hazır.<br>";

    // 7. Mail Ban Tablosu (YENİ - IP Ban yerine)
    $db->exec("CREATE TABLE IF NOT EXISTS mail_bans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mail VARCHAR(100) NOT NULL,
        reason VARCHAR(255),
        banned_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ 'mail_bans' tablosu hazır.<br>";

    // Varsayılan Admin Kullanıcısı (Eğer yoksa)
    // Şifre: 123456
    $admin_mail = "admin@zaun.com";
    $stmt = $db->prepare("SELECT id FROM kullanici WHERE mail = :mail");
    $stmt->execute(['mail' => $admin_mail]);
    
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO kullanici (ad, soyad, mail, telefon, sifre) VALUES ('Admin', 'User', :mail, '5555555555', '123456')";
        $stmt = $db->prepare($sql);
        $stmt->execute(['mail' => $admin_mail]);
        echo "👤 Varsayılan Admin oluşturuldu (Mail: admin@zaun.com, Şifre: 123456)<br>";
    }

    // Özel Admin: UZAY BULUT (ID: 9)
    $uzay_mail = "uzay@gmail.com";
    $stmt = $db->prepare("SELECT id FROM kullanici WHERE mail = :mail");
    $stmt->execute(['mail' => $uzay_mail]);
    
    if (!$stmt->fetch()) {
        // ID'yi 9 olarak zorlayarak ekliyoruz
        $sql = "INSERT INTO kullanici (id, ad, soyad, mail, telefon, sifre) VALUES (9, 'UZAY', 'BULUT', :mail, '451445135153', '451488')";
        $stmt = $db->prepare($sql);
        $stmt->execute(['mail' => $uzay_mail]);
        echo "👤 Özel Admin oluşturuldu (UZAY BULUT - ID: 9)<br>";
    }

    echo "<hr><h3 style='color:green;'>Tüm kurulum işlemleri başarıyla tamamlandı!</h3>";
    echo "<a href='anasayfa.php'>Ana Sayfaya Git</a>";

} catch (PDOException $e) {
    echo "<h3 style='color:red;'>Kurulum Hatası: " . $e->getMessage() . "</h3>";
}
?>