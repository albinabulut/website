<?php
// AdminManager.php
// Admin Paneli Oturum Yönetim İşlemleri

class AdminManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        // Tablo eksikse otomatik oluştur (Hata önleyici)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS mail_bans (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mail VARCHAR(100) NOT NULL,
            reason VARCHAR(255),
            banned_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function getAllSessions($search = '') {
        // Kullanıcı bazlı gruplandırma: Her kullanıcının sadece son oturum bilgisini getir
        $sql = "SELECT k.id as user_id, k.ad, k.soyad, k.mail,
                (SELECT ip_address FROM user_sessions_v2 WHERE user_id = k.id ORDER BY last_activity DESC LIMIT 1) as ip_address,
                (SELECT user_agent FROM user_sessions_v2 WHERE user_id = k.id ORDER BY last_activity DESC LIMIT 1) as user_agent,
                (SELECT last_activity FROM user_sessions_v2 WHERE user_id = k.id ORDER BY last_activity DESC LIMIT 1) as last_activity,
                (SELECT is_active FROM user_sessions_v2 WHERE user_id = k.id ORDER BY last_activity DESC LIMIT 1) as is_active,
                (SELECT COUNT(*) FROM mail_bans mb WHERE mb.mail = k.mail) as is_banned 
                FROM kullanici k";
                
        if (!empty($search)) {
            $sql .= " WHERE CONCAT(k.ad, ' ', k.soyad) LIKE :search OR k.ad LIKE :search OR k.soyad LIKE :search";
        }
        $sql .= " ORDER BY last_activity DESC";

        $stmt = $this->pdo->prepare($sql);
        if (!empty($search)) {
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function killSession($session_db_id) {
        $stmt = $this->pdo->prepare("UPDATE user_sessions_v2 SET is_active = 0 WHERE id = :id");
        return $stmt->execute(['id' => $session_db_id]);
    }

    public function killUserSessions($user_id) {
        $stmt = $this->pdo->prepare("UPDATE user_sessions_v2 SET is_active = 0 WHERE user_id = :uid");
        return $stmt->execute(['uid' => $user_id]);
    }

    public function banIp($ip, $reason) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO ip_bans (ip_address, reason) VALUES (:ip, :reason)");
            $stmt->execute(['ip' => $ip, 'reason' => $reason]);
            $stmt2 = $this->pdo->prepare("UPDATE user_sessions_v2 SET is_active = 0 WHERE ip_address = :ip");
            $stmt2->execute(['ip' => $ip]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function banMail($mail, $reason) {
        try {
            $this->pdo->beginTransaction();
            // 1. Yasaklı listesine ekle
            $stmt = $this->pdo->prepare("INSERT INTO mail_bans (mail, reason) VALUES (:mail, :reason)");
            $stmt->execute(['mail' => $mail, 'reason' => $reason]);
            
            // 2. Bu maile sahip kullanıcının ID'sini bul
            $stmtUser = $this->pdo->prepare("SELECT id FROM kullanici WHERE mail = :mail");
            $stmtUser->execute(['mail' => $mail]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // 3. Kullanıcının aktif oturumlarını kapat
                $this->killUserSessions($user['id']);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function unbanMail($mail) {
        try {
            $this->pdo->beginTransaction();
            
            // 1. Banı kaldır
            $stmt = $this->pdo->prepare("DELETE FROM mail_bans WHERE mail = :mail");
            $stmt->execute(['mail' => $mail]);

            // 2. Kullanıcının oturumlarını tekrar aktif et
            $stmtUser = $this->pdo->prepare("SELECT id FROM kullanici WHERE mail = :mail");
            $stmtUser->execute(['mail' => $mail]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $stmtSession = $this->pdo->prepare("UPDATE user_sessions_v2 SET is_active = 1 WHERE user_id = :uid");
                $stmtSession->execute(['uid' => $user['id']]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getStats() {
        $stats = [];
        
        // Aktif Oturum Sayısı
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM user_sessions_v2 WHERE is_active = 1");
        $stats['active_sessions'] = $stmt->fetchColumn();

        // Banlı Kullanıcı Sayısı
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM mail_bans");
        $stats['banned_users'] = $stmt->fetchColumn();

        return $stats;
    }

    // --- KULLANICI YÖNETİMİ (CRUD) ---
    
    public function getUsers($search = '') {
        $sql = "SELECT * FROM kullanici";
        if (!empty($search)) {
            $sql .= " WHERE CONCAT(ad, ' ', soyad) LIKE :search OR ad LIKE :search OR soyad LIKE :search";
        }
        $sql .= " ORDER BY id DESC";
        
        $stmt = $this->pdo->prepare($sql);
        if (!empty($search)) {
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM kullanici WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addUser($ad, $soyad, $mail, $telefon, $sifre) {
        $stmt = $this->pdo->prepare("INSERT INTO kullanici (ad, soyad, mail, telefon, sifre) VALUES (:ad, :soyad, :mail, :telefon, :sifre)");
        return $stmt->execute(['ad' => $ad, 'soyad' => $soyad, 'mail' => $mail, 'telefon' => $telefon, 'sifre' => $sifre]);
    }

    public function updateUser($id, $ad, $soyad, $mail, $telefon, $sifre) {
        $stmt = $this->pdo->prepare("UPDATE kullanici SET ad = :ad, soyad = :soyad, mail = :mail, telefon = :telefon, sifre = :sifre WHERE id = :id");
        return $stmt->execute(['id' => $id, 'ad' => $ad, 'soyad' => $soyad, 'mail' => $mail, 'telefon' => $telefon, 'sifre' => $sifre]);
    }

    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM kullanici WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>