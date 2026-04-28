<?php
// .env dosyasını okuyup $_ENV ve $_SERVER dizilerine yükleyen yardımcı dosya

if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Yorum satırlarını atla
            if (strpos(trim($line), '#') === 0) continue;
            
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv(__DIR__ . '/.env');
?>