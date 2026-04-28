<?php
session_start();

// Sadece Admin (ID: 9) bu sayfayı görüntüleyebilir
if (!isset($_SESSION['kullanici_id']) || $_SESSION['kullanici_id'] != 9) {
    header("Location: anasayfa.php");
    exit;
}

// Session şişmesini burada da kontrol et ve temizle
if (isset($_SESSION['izlenen_sayfalar']) && count($_SESSION['izlenen_sayfalar']) > 50) {
    $_SESSION['izlenen_sayfalar'] = array_slice($_SESSION['izlenen_sayfalar'], -50);
}

require_once 'baglanti.php';
require_once 'AdminManager.php';

// AdminManager örneği
$adminManager = new AdminManager($db);

// İşlemler
if (isset($_GET['kill_user'])) {
    $adminManager->killUserSessions($_GET['kill_user']);
    header("Location: admin_sessions.php");
    exit;
}
if (isset($_POST['ban_mail'])) {
    $adminManager->banMail($_POST['mail'], $_POST['reason']);
    header("Location: admin_sessions.php");
    exit;
}
if (isset($_POST['unban_mail'])) {
    $adminManager->unbanMail($_POST['mail']);
    header("Location: admin_sessions.php");
    exit;
}

// --- KULLANICI YÖNETİMİ İŞLEMLERİ ---
$edit_mode = false;
$edit_data = ['ad' => '', 'soyad' => '', 'mail' => '', 'telefon' => '', 'sifre' => ''];

// Düzenleme Modu
if (isset($_GET['edit_user'])) {
    $user = $adminManager->getUser($_GET['edit_user']);
    if ($user) {
        $edit_mode = true;
        $edit_data = $user;
    }
}

// Ekleme ve Güncelleme
if (isset($_POST['save_user'])) {
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        // Update
        $adminManager->updateUser($_POST['user_id'], $_POST['ad'], $_POST['soyad'], $_POST['mail'], $_POST['telefon'], $_POST['sifre']);
    } else {
        // Insert
        $adminManager->addUser($_POST['ad'], $_POST['soyad'], $_POST['mail'], $_POST['telefon'], $_POST['sifre']);
    }
    header("Location: admin_sessions.php");
    exit;
}

// Tekli Silme
if (isset($_GET['delete_user'])) {
    $adminManager->deleteUser($_GET['delete_user']);
    header("Location: admin_sessions.php");
    exit;
}

// Çoklu Silme
if (isset($_POST['bulk_delete']) && isset($_POST['selected_users'])) {
    foreach ($_POST['selected_users'] as $id) {
        // Admini (ID: 9) yanlışlıkla silmeyi engelle
        if ($id != 9) {
            $adminManager->deleteUser($id);
        }
    }
    header("Location: admin_sessions.php");
    exit;
}

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

$sessions = $adminManager->getAllSessions($search_query);
$users = $adminManager->getUsers($search_query);
$stats = $adminManager->getStats();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Admin Oturum Yönetimi</title>
<link rel="icon" href="img/logo.jpg?v=2" type="image/jpeg">
<style>
    body { font-family: Arial, sans-serif; background-color: #1a1a1a; color: #f0f0f0; padding: 20px; }
    h2 { color: #f7e7a3; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #0d0d0d; border: 1px solid #333; }
    th, td { border: 1px solid #333; padding: 12px; text-align: left; }
    th { background-color: #28a745; color: #000; }
    tr:nth-child(even) { background-color: #111; }
    tr:hover { background-color: #1f1f1f; }
    a { color: #f7e7a3; text-decoration: none; }
    .btn-ban { background: #dc3545; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px; font-weight: bold; }
    .btn-ban:hover { background-color: #c82333; }
    .btn-unban { background: #28a745; color: white; border: none; padding: 6px 12px; cursor: pointer; border-radius: 4px; font-weight: bold; }
    .btn-unban:hover { background-color: #218838; }
    .status-active { color: #28a745; font-weight: bold; }
    .status-passive { color: #6c757d; }
    .back-link { display: inline-block; margin-bottom: 20px; color: #818181; font-size: 1em; transition: 0.3s; }
    .back-link:hover { color: #f7e7a3; }
    .kill-link { color: #ffc107; font-weight: bold; }
    .kill-link:hover { color: #ffca2c; }
    .stats-container { display: flex; gap: 20px; margin-bottom: 20px; }
    .stat-box { background: #0d0d0d; border: 1px solid #28a745; padding: 15px; border-radius: 8px; flex: 1; text-align: center; }
    .stat-box h3 { margin: 0 0 10px 0; font-size: 1.1em; color: #28a745; border: none; }
    .stat-box p { font-size: 2em; margin: 0; font-weight: bold; }
    
    .search-box { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .search-box input { padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; width: 300px; }
    .search-box button { background: #ffc107; color: #000; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; }
    
    /* Form Stilleri */
    .user-form { background: #111; padding: 20px; border: 1px solid #333; margin-bottom: 30px; border-radius: 8px; }
    .form-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .form-row input { flex: 1; padding: 10px; background: #222; border: 1px solid #444; color: #fff; border-radius: 4px; }
    .btn-save { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; font-weight: bold; width: 100%; }
    .btn-save:hover { background: #218838; }
    .btn-edit { background: #ffc107; color: black; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; }
    .btn-bulk-del { background: #dc3545; color: white; border: none; padding: 10px; cursor: pointer; border-radius: 4px; font-weight: bold; margin-top: 10px; }
</style>
</head>
<body>
    <a href="anasayfa.php" class="back-link">← Ana Sayfaya Dön</a>
    <h2>🛡️ Admin Oturum Yönetimi</h2>
    
    <form method="GET" class="search-box">
        <input type="text" name="q" placeholder="Ad Soyad ile kullanıcı ara..." value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit">🔍 Ara</button>
        <?php if(!empty($search_query)): ?>
            <a href="admin_sessions.php" style="color: #dc3545; text-decoration: none; font-weight: bold;">Temizle</a>
        <?php endif; ?>
    </form>

    <div class="stats-container">
        <div class="stat-box">
            <h3>🟢 Aktif Oturumlar</h3>
            <p><?= $stats['active_sessions'] ?></p>
        </div>
        <div class="stat-box" style="border-color: #dc3545;">
            <h3 style="color: #dc3545;">🚫 Banlı Kullanıcılar</h3>
            <p><?= $stats['banned_users'] ?></p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Kullanıcı</th>
                <th>E-posta</th>
                <th>IP Adresi</th>
                <th>Tarayıcı</th>
                <th>Son Aktivite</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sessions as $s): ?>
            <tr>
                <td><?= $s['user_id'] ?></td>
                <td><?= htmlspecialchars(($s['ad'] ?? '') . ' ' . ($s['soyad'] ?? '')) ?></td>
                <td><?= htmlspecialchars($s['mail'] ?? '-') ?></td>
                <td><?= $s['ip_address'] ?? '<span style="color:#555;">-</span>' ?></td>
                <td><?= !empty($s['user_agent']) ? (substr($s['user_agent'], 0, 40) . (strlen($s['user_agent'])>40 ? '...' : '')) : '-' ?></td>
                <td><?= $s['last_activity'] ?? '<span style="color:#555;">Hiç giriş yapmadı</span>' ?></td>
                <td>
                    <?php echo !empty($s['is_active']) ? '<span class="status-active">Çevrimiçi</span>' : '<span class="status-passive">Çevrimdışı</span>'; ?>
                </td>
                <td>
                    <?php if(!empty($s['is_active'])): ?>
                        <a href="?kill_user=<?= $s['user_id'] ?>" class="kill-link" onclick="return confirm('Kullanıcının tüm oturumlarını kapatmak istediğinize emin misiniz?')">Oturumu Kapat</a>
                        <span style="margin: 0 5px; color: #555;">|</span>
                    <?php endif; ?>

                    <?php if ($s['is_banned'] > 0): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="mail" value="<?= htmlspecialchars($s['mail'] ?? '') ?>">
                            <button type="submit" name="unban_mail" class="btn-unban">Banı Kaldır</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="mail" value="<?= htmlspecialchars($s['mail'] ?? '') ?>">
                            <input type="hidden" name="reason" value="Admin Ban">
                            <button type="submit" name="ban_mail" class="btn-ban">Mail Ban</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br><hr style="border-color: #333; margin: 40px 0;"><br>

    <!-- KULLANICI YÖNETİMİ BÖLÜMÜ -->
    <h2>👥 Kullanıcı Yönetimi (CRUD)</h2>

    <!-- Ekleme / Güncelleme Formu -->
    <div class="user-form">
        <h3 style="margin-top:0; color:#818181;"><?= $edit_mode ? '✏️ Kullanıcıyı Düzenle' : '➕ Yeni Kullanıcı Ekle' ?></h3>
        <form method="POST">
            <input type="hidden" name="user_id" value="<?= $edit_mode ? $edit_data['id'] : '' ?>">
            <div class="form-row">
                <input type="text" name="ad" placeholder="Ad" value="<?= htmlspecialchars($edit_data['ad']) ?>" required>
                <input type="text" name="soyad" placeholder="Soyad" value="<?= htmlspecialchars($edit_data['soyad']) ?>" required>
            </div>
            <div class="form-row">
                <input type="email" name="mail" placeholder="E-posta" value="<?= htmlspecialchars($edit_data['mail']) ?>" required>
                <input type="text" name="telefon" placeholder="Telefon" value="<?= htmlspecialchars($edit_data['telefon']) ?>">
                <input type="text" name="sifre" placeholder="Şifre" value="<?= htmlspecialchars($edit_data['sifre']) ?>" required>
            </div>
            <button type="submit" name="save_user" class="btn-save"><?= $edit_mode ? 'Güncelle (Update)' : 'Kaydet (Insert)' ?></button>
            <?php if($edit_mode): ?>
                <a href="admin_sessions.php" style="display:block; text-align:center; margin-top:10px; color:#888;">İptal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Kullanıcı Listesi ve Çoklu Silme -->
    <form method="POST">
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;"><input type="checkbox" onclick="toggle(this)"></th>
                    <th>ID</th>
                    <th>Ad Soyad</th>
                    <th>Mail</th>
                    <th>Telefon</th>
                    <th>Şifre</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><input type="checkbox" name="selected_users[]" value="<?= $u['id'] ?>"></td>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['ad'] . ' ' . $u['soyad']) ?></td>
                    <td><?= htmlspecialchars($u['mail']) ?></td>
                    <td><?= htmlspecialchars($u['telefon']) ?></td>
                    <td><?= htmlspecialchars($u['sifre']) ?></td>
                    <td>
                        <a href="?edit_user=<?= $u['id'] ?>" class="btn-edit">Düzenle</a>
                        <a href="?delete_user=<?= $u['id'] ?>" style="color:#dc3545; margin-left:10px;" onclick="return confirm('Silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="bulk_delete" class="btn-bulk-del" onclick="return confirm('Seçili kullanıcıları silmek istediğinize emin misiniz?')">🗑️ Seçilenleri Sil</button>
    </form>

    <script>
    function toggle(source) {
        checkboxes = document.getElementsByName('selected_users[]');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }
    </script>
</body>
</html>