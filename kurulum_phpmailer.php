<?php
set_time_limit(300);

$dir = __DIR__ . '/PHPMailer/src';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

$files = [
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php'      => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php'
];

echo "<h3>PHPMailer dosyaları GitHub'dan doğrudan indiriliyor... Lütfen bekleyin.</h3>";

$basarili = true;

foreach ($files as $name => $url) {
    echo "İndiriliyor: <b>$name</b>... ";
    
    $ch = curl_init($url);
    $fp = fopen($dir . '/' . $name, 'w+');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // XAMPP'ta SSL hatası almamak için
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_exec($ch);
    
    if (curl_errno($ch)) {
        $basarili = false;
        echo "<span style='color:red;'>HATA!</span><br>";
    } else {
        echo "<span style='color:green;'>Tamamlandı.</span><br>";
    }
    
    curl_close($ch);
    fclose($fp);
}

if ($basarili) {
    echo "<h2 style='color:green;'>✅ Harika! PHPMailer sisteme otomatik olarak eklendi.</h2>";
    echo "<p>Artık <b>iletisim.php</b> sayfanız çalışmaya hazırdır. (iletisim.php dosyasının içindeki kendi e-posta ve uygulama şifrenizi girmeyi unutmayın!)</p>";
    echo "<p style='color:gray;'>Not: Güvenliğiniz için işleminiz bittikten sonra bu <b>kurulum_phpmailer.php</b> dosyasını silebilirsiniz.</p>";
} else {
    echo "<h2 style='color:red;'>❌ İndirme işlemi sırasında bir hata oluştu.</h2>";
}
?>