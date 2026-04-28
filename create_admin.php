<?php
require 'init.php';

$email = 'cicek@gmail.com';
$password = '12345';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // Eğer tabloya daha önce eklenmediyse admin kullanıcısını oluşturur
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES ('Sistem Yöneticisi', ?, ?, '05550000000', 'Merkez Ofis', 'admin')");
    
    if ($stmt->execute([$email, $hashedPassword])) {
        echo "<h1>Tebrikler!</h1><p>Admin kullanıcısı başarıyla eklendi.</p><p>E-posta: <b>$email</b></p><p>Şifre: <b>$password</b></p><p><a href='login.php'>Buraya tıklayarak giriş yapabilirsiniz.</a></p>";
    }
} catch (PDOException $e) {
    echo "Kullanıcı zaten mevcut veya bir hata oluştu. Hata detayı: " . $e->getMessage();
}
?>