<?php
// SmsManager.php
// SMS Gönderim Simülasyonu (Gerçek API entegrasyonu için altyapı)

function smsGonder($telefon, $mesaj) {
    // Telefon numarası temizleme (Sadece rakamlar)
    $telefon = preg_replace('/[^0-9]/', '', $telefon);
    
    // Eğer numara boşsa gönderme
    if (empty($telefon)) {
        return false;
    }

    // --- SİMÜLASYON MODU ---
    // Gerçek bir SMS API'si (Örn: Netgsm, Twilio) olmadığı için
    // gönderilen SMS'leri bir metin dosyasına logluyoruz.
    
    $log_dosyasi = "gonderilen_smsler.txt";
    $tarih = date("d.m.Y H:i:s");
    
    $log_icerik = "------------------------------------------------------\n";
    $log_icerik .= "Tarih: $tarih\n";
    $log_icerik .= "Kime: $telefon\n";
    $log_icerik .= "Mesaj: $mesaj\n";
    $log_icerik .= "Durum: İletildi (Simülasyon)\n";
    $log_icerik .= "------------------------------------------------------\n\n";
    
    file_put_contents($log_dosyasi, $log_icerik, FILE_APPEND);
    return true;
}
?>