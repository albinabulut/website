<?php
// SessionManager.php
// Gelişmiş ve Güvenli Oturum Yönetimi Sınıfı

class SessionManager {
    private $pdo;
    private $timeout_duration = 900; // 15 Dakika (Saniye cinsinden)

    public function __construct($pdo) {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Kullanıcı Girişi ve Oturum Oluşturma
     */
    public function login($user_id, $single_session_mode = false) {
        $ip = $_SERVER['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        
        // 1. Kullanıcı bilgilerini ve ban durumunu kontrol et
        if ($this->isMailBanned($user_id)) {
            throw new Exception("Erişim engellendi: Hesabınız (E-posta) yasaklanmış.");
        }

        $stmtUser = $this->pdo->prepare("SELECT ad FROM kullanici WHERE id = :id");
        $stmtUser->execute(['id' => $user_id]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            throw new Exception("Oturum oluşturulacak kullanıcı bulunamadı.");
        }

        // Eski oturum verilerini temizle (Clean Switch)
        $old_session_id = $_SESSION['db_session_id'] ?? null;
        session_unset();

        // Eğer halihazırda bir oturum varsa (başka hesaptan geçiş yapılıyorsa) eskisini kapat
        if ($old_session_id) {
            $this->deactivateSession($old_session_id);
        }

        // 2. Session Fixation Koruması
        session_regenerate_id(true); 
        $session_id = session_id();

        try {
            $this->pdo->beginTransaction();

            // 3. Tek Oturum Modu (Opsiyonel)
            if ($single_session_mode) {
                $stmt = $this->pdo->prepare("UPDATE user_sessions_v2 SET is_active = 0 WHERE user_id = :uid");
                $stmt->execute(['uid' => $user_id]);
            }

            // 4. Yeni Oturumu Kaydet
            $sql = "INSERT INTO user_sessions_v2 (user_id, session_id, ip_address, user_agent, login_time, last_activity, is_active) 
                    VALUES (:uid, :sid, :ip, :ua, NOW(), NOW(), 1)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'uid' => $user_id,
                'sid' => $session_id,
                'ip'  => $ip,
                'ua'  => $agent
            ]);

            // DB'deki ID'yi session'a al
            $_SESSION['db_session_id'] = $this->pdo->lastInsertId();
            $_SESSION['giris_yapildi'] = true;
            $_SESSION['kullanici_id'] = $user_id;
            $_SESSION['kullanici_ad'] = $user['ad'];
            $_SESSION['login_ip'] = $ip;
            $_SESSION['user_agent'] = $agent;
            $_SESSION['giris_zamani'] = date("d.m.Y H:i:s");

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sayfa Yüklenirken Oturum Doğrulama
     */
    public function validateSession() {
        if (!isset($_SESSION['db_session_id']) || !isset($_SESSION['kullanici_id'])) {
            return false;
        }

        $current_ip = $_SERVER['REMOTE_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['REMOTE_ADDR'];
        $current_ua = $_SERVER['HTTP_USER_AGENT'];

        if ($_SESSION['login_ip'] !== $current_ip || $_SESSION['user_agent'] !== $current_ua) {
            $this->destroySession();
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT last_activity, is_active FROM user_sessions_v2 WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $_SESSION['db_session_id']]);
        $session_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session_data || $session_data['is_active'] == 0) {
            $this->destroySession();
            return false;
        }

        if (time() - strtotime($session_data['last_activity']) > $this->timeout_duration) {
            $this->deactivateSession($_SESSION['db_session_id']);
            $this->destroySession();
            return false;
        }

        $update = $this->pdo->prepare("UPDATE user_sessions_v2 SET last_activity = NOW() WHERE id = :id");
        $update->execute(['id' => $_SESSION['db_session_id']]);

        return true;
    }

    public function logout() {
        if (isset($_SESSION['db_session_id'])) $this->deactivateSession($_SESSION['db_session_id']);
        $this->destroySession();
    }

    private function deactivateSession($db_id) {
        $stmt = $this->pdo->prepare("UPDATE user_sessions_v2 SET is_active = 0 WHERE id = :id");
        $stmt->execute(['id' => $db_id]);
    }

    private function destroySession() {
        session_unset();
        session_destroy();
    }

    private function isIpBanned($ip) {
        $stmt = $this->pdo->prepare("SELECT id FROM ip_bans WHERE ip_address = :ip LIMIT 1");
        $stmt->execute(['ip' => $ip]);
        return $stmt->fetch() !== false;
    }

    private function isMailBanned($user_id) {
        // Kullanıcının mailini bul
        $stmt = $this->pdo->prepare("SELECT mail FROM kullanici WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $stmtBan = $this->pdo->prepare("SELECT id FROM mail_bans WHERE mail = :mail LIMIT 1");
            $stmtBan->execute(['mail' => $user['mail']]);
            return $stmtBan->fetch() !== false;
        }
        return false;
    }
}
?>