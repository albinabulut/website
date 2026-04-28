CREATE DATABASE IF NOT EXISTS gk_taki_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gk_taki_db;

-- Kullanıcılar Tablosu (5 Kayıt Alanı: Ad_Soyad, Email, Telefon, Adres, Sifre)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefon VARCHAR(20),
    adres TEXT,
    sifre VARCHAR(255) NOT NULL,
    rol ENUM('user', 'admin') DEFAULT 'user', -- Multisession/Yetkilendirme için
    olusturulma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ürünler Tablosu
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_adi VARCHAR(150) NOT NULL,
    aciklama TEXT,
    fiyat DECIMAL(10,2) NOT NULL,
    stok INT DEFAULT 0,
    resim_url VARCHAR(255)
);

-- Alışveriş Sepeti (İlişkilendirilmiş Tablo - Foreign Key Kullanımı)
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    miktar INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Duyurular Tablosu
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(200) NOT NULL,
    icerik TEXT,
    yayin_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bize Ulaşın (Mesajlar) Tablosu
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isim VARCHAR(100),
    email VARCHAR(100),
    konu VARCHAR(255),
    mesaj TEXT,
    tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);