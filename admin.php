<?php
require 'init.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image_url) VALUES (NULL, ?, ?, ?, 100, ?)");
        if ($stmt->execute([$name, $description, $price, $image_url])) {
            $message = "Çiçek başarıyla eklendi!";
        }
    } elseif (isset($_POST['delete_product'])) {
        $id = $_POST['product_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Çiçek başarıyla silindi!";
        } catch(PDOException $e) {
            $message = "Hata: Bu ürün bir siparişte yer aldığı için silinemez!";
        }
    } elseif (isset($_POST['edit_product'])) {
        $id = $_POST['product_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $image_url = $_POST['image_url'];
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $image_url, $id]);
        $message = "Çiçek başarıyla güncellendi!";
        header("Location: admin.php?action=products");
        exit();
    } elseif (isset($_POST['add_announcement'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
        $stmt->execute([$title, $content]);
        $message = "Duyuru eklendi!";
    } elseif (isset($_POST['delete_announcement'])) {
        $id = $_POST['announcement_id'];
        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Duyuru silindi!";
    } elseif (isset($_POST['add_cookie'])) {
        $name = trim($_POST['cookie_name']);
        $value = trim($_POST['cookie_value']);
        setcookie($name, $value, time() + (86400 * 30), "/"); // 30 günlük çerez oluşturur
        $_COOKIE[$name] = $value; // Listede anında görünmesi için
        $message = "Çerez başarıyla eklendi!";
    } elseif (isset($_POST['delete_cookie'])) {
        $name = trim($_POST['cookie_name']);
        setcookie($name, "", time() - 3600, "/"); // Çerezi geçmiş tarihe ayarlayarak siler
        unset($_COOKIE[$name]); // Listeden anında silinmesi için
        $message = "Çerez başarıyla silindi!";
    } elseif (isset($_POST['add_session'])) {
        $key = trim($_POST['session_key']);
        $value = trim($_POST['session_value']);
        $_SESSION[$key] = $value;
        $message = "Session başarıyla eklendi!";
    } elseif (isset($_POST['delete_session'])) {
        $key = trim($_POST['session_key']);
        unset($_SESSION[$key]);
        $message = "Session başarıyla silindi!";
    } elseif (isset($_POST['impersonate_user'])) {
        $target_id = $_POST['target_user_id'];
        $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = ?");
        $stmt->execute([$target_id]);
        $target_user = $stmt->fetch();

        if ($target_user && $target_user['role'] !== 'admin') {
            // Mevcut admini yedeğe al
            $_SESSION['original_admin_id'] = $_SESSION['user_id'];
            $_SESSION['original_admin_name'] = $_SESSION['user_name'];
            
            // Seçilen müşterinin hesabına geçiş yap
            $_SESSION['user_id'] = $target_user['id'];
            $_SESSION['user_name'] = $target_user['name'];
            $_SESSION['role'] = $target_user['role'];
            
            header("Location: index.php");
            exit();
        } else {
            $message = "Bu kullanıcıya geçiş yapılamaz!";
        }
    } elseif (isset($_POST['add_user'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$name, $email, $password, $phone, $address, $role]);
            $message = "Kullanıcı başarıyla eklendi!";
        } catch (PDOException $e) {
            $message = "Hata: Bu e-posta adresi zaten kullanımda!";
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = $_POST['user_id'];
        if ($id == $_SESSION['user_id']) {
            $message = "Hata: Kendi yönetici hesabınızı silemezsiniz!";
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Kullanıcı başarıyla silindi!";
            } catch(PDOException $e) {
                $message = "Hata: Bu kullanıcıya ait geçmiş siparişler olduğu için silinemez!";
            }
        }
    } elseif (isset($_POST['edit_user_submit'])) {
        $id = $_POST['user_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password=?, phone=?, address=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $password, $phone, $address, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $phone, $address, $role, $id]);
        }
        $message = "Kullanıcı başarıyla güncellendi!";
        header("Location: admin.php?action=users");
        exit();
    }
}

$action = $_GET['action'] ?? 'products';
require 'header.php';
?>
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group shadow-sm">
            <a href="admin.php?action=products" class="list-group-item list-group-item-action <?= $action==='products' || $action==='edit_product' ? 'active' : '' ?>">Ürün Yönetimi</a>
            <a href="admin.php?action=announcements" class="list-group-item list-group-item-action <?= $action==='announcements' ? 'active' : '' ?>">Duyuru Yönetimi</a>
            <a href="admin.php?action=cookies" class="list-group-item list-group-item-action <?= $action==='cookies' ? 'active' : '' ?>">Çerez Yönetimi</a>
            <a href="admin.php?action=sessions" class="list-group-item list-group-item-action <?= $action==='sessions' ? 'active' : '' ?>">Session Yönetimi</a>
            <a href="admin.php?action=users" class="list-group-item list-group-item-action <?= $action==='users' || $action==='edit_user' ? 'active' : '' ?>">Kullanıcı Yönetimi</a>
        </div>
    </div>
    <div class="col-md-9">
        <?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        
        <?php if ($action === 'products'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-pink text-white">Yeni Çiçek Ekle</div>
                <div class="card-body">
                    <form action="admin.php?action=products" method="POST">
                        <input type="hidden" name="add_product" value="1">
                        <div class="mb-3"><label>Çiçek Adı</label><input type="text" name="name" class="form-control" required></div>
                        <div class="mb-3"><label>Açıklama</label><textarea name="description" class="form-control" rows="2" required></textarea></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Fiyat (TL)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>Resim URL'si</label><input type="text" name="image_url" class="form-control" required></div>
                        </div>
                        <button type="submit" class="btn btn-pink">Kaydet</button>
                    </form>
                </div>
            </div>

            <h4>Mevcut Çiçekler</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light"><tr><th>Resim</th><th>Adı</th><th>Fiyat</th><th>İşlemler</th></tr></thead>
                    <tbody>
                        <?php
                        $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
                        foreach($products as $p):
                        ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($p['image_url']) ?>" width="50" height="50" style="object-fit:cover; border-radius:5px;"></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= number_format($p['price'], 2, ',', '.') ?> TL</td>
                            <td>
                                <a href="admin.php?action=edit_product&id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
                                <form action="admin.php?action=products" method="POST" class="d-inline">
                                    <input type="hidden" name="delete_product" value="1">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($action === 'edit_product' && isset($_GET['id'])): 
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $p = $stmt->fetch();
            if ($p):
        ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Çiçek Güncelle: <?= htmlspecialchars($p['name']) ?></div>
                <div class="card-body">
                    <form action="admin.php?action=products" method="POST">
                        <input type="hidden" name="edit_product" value="1">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <div class="mb-3"><label>Çiçek Adı</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required></div>
                        <div class="mb-3"><label>Açıklama</label><textarea name="description" class="form-control" rows="2" required><?= htmlspecialchars($p['description']) ?></textarea></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Fiyat (TL)</label><input type="number" step="0.01" name="price" class="form-control" value="<?= $p['price'] ?>" required></div>
                            <div class="col-md-6 mb-3"><label>Resim URL'si</label><input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($p['image_url']) ?>" required></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                        <a href="admin.php?action=products" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif ($action === 'announcements'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-pink text-white">Yeni Duyuru Ekle</div>
                <div class="card-body">
                    <form action="admin.php?action=announcements" method="POST">
                        <input type="hidden" name="add_announcement" value="1">
                        <div class="mb-3"><label>Duyuru Başlığı</label><input type="text" name="title" class="form-control" required></div>
                        <div class="mb-3"><label>İçerik</label><textarea name="content" class="form-control" rows="2" required></textarea></div>
                        <button type="submit" class="btn btn-pink">Ekle</button>
                    </form>
                </div>
            </div>

            <h4>Mevcut Duyurular</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light"><tr><th>Başlık</th><th>İçerik</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php
                        $announcements = $pdo->query("SELECT * FROM announcements ORDER BY id DESC")->fetchAll();
                        foreach($announcements as $a):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['content']) ?></td>
                            <td>
                                <form action="admin.php?action=announcements" method="POST" class="d-inline">
                                    <input type="hidden" name="delete_announcement" value="1">
                                    <input type="hidden" name="announcement_id" value="<?= $a['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Silmek istediğinize emin misiniz?');">Sil</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($action === 'cookies'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-pink text-white">Yeni Çerez Ekle</div>
                <div class="card-body">
                    <form action="admin.php?action=cookies" method="POST">
                        <input type="hidden" name="add_cookie" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Çerez Adı (Key)</label><input type="text" name="cookie_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>Değeri (Value)</label><input type="text" name="cookie_value" class="form-control" required></div>
                        </div>
                        <button type="submit" class="btn btn-pink">Ekle</button>
                    </form>
                </div>
            </div>

            <h4>Mevcut Çerezler</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle bg-white">
                    <thead class="table-light"><tr><th>Çerez Adı</th><th>Değeri</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php if(empty($_COOKIE)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Aktif çerez bulunmamaktadır.</td></tr>
                        <?php else: ?>
                            <?php foreach($_COOKIE as $key => $val): ?>
                            <tr>
                                <td><span class="fw-bold"><?= htmlspecialchars($key) ?></span></td>
                                <td><span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></span></td>
                                <td>
                                    <?php if($key !== 'PHPSESSID'): ?>
                                    <form action="admin.php?action=cookies" method="POST" class="d-inline">
                                        <input type="hidden" name="delete_cookie" value="1">
                                        <input type="hidden" name="cookie_name" value="<?= htmlspecialchars($key) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu çerezi silmek istediğinize emin misiniz?');">Sil</button>
                                    </form>
                                    <?php else: ?>
                                    <span class="badge bg-secondary" title="Sistemin çalışması için gereklidir">Oturum Çerezi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($action === 'sessions'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-pink text-white">Yeni Session Ekle</div>
                <div class="card-body">
                    <form action="admin.php?action=sessions" method="POST">
                        <input type="hidden" name="add_session" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Session Adı (Key)</label><input type="text" name="session_key" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>Değeri (Value)</label><input type="text" name="session_value" class="form-control" required></div>
                        </div>
                        <button type="submit" class="btn btn-pink">Ekle</button>
                    </form>
                </div>
            </div>

            <h4>Mevcut Sessionlar</h4>
            <div class="table-responsive">
                <table class="table table-bordered align-middle bg-white">
                    <thead class="table-light"><tr><th>Session Adı</th><th>Değeri</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php if(empty($_SESSION)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Aktif session bulunmamaktadır.</td></tr>
                        <?php else: ?>
                            <?php foreach($_SESSION as $key => $val): ?>
                            <tr>
                                <td><span class="fw-bold"><?= htmlspecialchars($key) ?></span></td>
                                <td>
                                    <?php if ($key === 'page_history' && is_array($val)): ?>
                                        <div style="max-height: 120px; overflow-y: auto; font-size: 0.85rem;" class="border p-2 bg-light">
                                            <?php foreach(array_reverse($val) as $v): ?>
                                                <div><strong class="text-pink"><?= $v['time'] ?></strong> : <?= htmlspecialchars($v['page']) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 250px;" title="<?= htmlspecialchars(is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val) ?>">
                                            <?= htmlspecialchars(is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(in_array($key, ['user_id', 'user_name', 'role', 'login_time', 'page_history'])): ?>
                                    <span class="badge bg-secondary" title="Sistemin çalışması için gereklidir">Oturum Bilgisi</span>
                                    <?php else: ?>
                                    <form action="admin.php?action=sessions" method="POST" class="d-inline">
                                        <input type="hidden" name="delete_session" value="1">
                                        <input type="hidden" name="session_key" value="<?= htmlspecialchars($key) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bu session bilgisini silmek istediğinize emin misiniz?');">Sil</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($action === 'users'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-pink text-white">Yeni Kullanıcı Ekle</div>
                <div class="card-body">
                    <form action="admin.php?action=users" method="POST">
                        <input type="hidden" name="add_user" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Ad Soyad</label><input type="text" name="name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>E-posta</label><input type="email" name="email" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>Şifre</label><input type="password" name="password" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label>Telefon</label><input type="tel" name="phone" class="form-control" required maxlength="10" minlength="10" pattern="[0-9]{10}" placeholder="5555555555"></div>
                            <div class="col-md-8 mb-3"><label>Adres</label><textarea name="address" class="form-control" rows="1" required></textarea></div>
                            <div class="col-md-4 mb-3"><label>Rol</label><select name="role" class="form-control"><option value="user">Müşteri</option><option value="admin">Admin</option></select></div>
                        </div>
                        <button type="submit" class="btn btn-pink">Ekle</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">Mevcut Kullanıcılar ve Geçiş İşlemleri</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle bg-white">
                            <thead class="table-light"><tr><th>ID</th><th>Ad Soyad</th><th>E-posta</th><th>Rol</th><th>İşlem</th></tr></thead>
                            <tbody>
                                <?php
                                $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
                                foreach($users as $u):
                                ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= $u['role'] === 'admin' ? '<span class="badge bg-danger">Admin</span>' : '<span class="badge bg-secondary">Müşteri</span>' ?></td>
                                    <td>
                                        <a href="admin.php?action=edit_user&id=<?= $u['id'] ?>" class="btn btn-sm btn-primary mb-1">Düzenle</a>
                                        
                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                        <form action="admin.php?action=users" method="POST" class="d-inline">
                                            <input type="hidden" name="delete_user" value="1">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?');">Sil</button>
                                        </form>
                                        <?php endif; ?>

                                        <?php if ($u['role'] !== 'admin'): ?>
                                        <form action="admin.php?action=users" method="POST" class="d-inline">
                                            <input type="hidden" name="impersonate_user" value="1">
                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-dark mb-1" onclick="return confirm('Bu müşterinin hesabına geçiş yapmak istediğinize emin misiniz?');">Hesaba Geç</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($action === 'edit_user' && isset($_GET['id'])): 
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $u = $stmt->fetch();
            if ($u):
        ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Kullanıcı Güncelle: <?= htmlspecialchars($u['name']) ?></div>
                <div class="card-body">
                    <form action="admin.php?action=users" method="POST">
                        <input type="hidden" name="edit_user_submit" value="1">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label>Ad Soyad</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($u['name']) ?>" required></div>
                            <div class="col-md-6 mb-3"><label>E-posta</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required></div>
                            <div class="col-md-6 mb-3"><label>Yeni Şifre <small class="text-muted">(Değişmeyecekse boş bırakın)</small></label><input type="password" name="password" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label>Telefon</label><input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($u['phone'] ?? '') ?>" required maxlength="10" minlength="10" pattern="[0-9]{10}"></div>
                            <div class="col-md-8 mb-3"><label>Adres</label><textarea name="address" class="form-control" rows="1" required><?= htmlspecialchars($u['address'] ?? '') ?></textarea></div>
                            <div class="col-md-4 mb-3">
                                <label>Rol</label>
                                <select name="role" class="form-control">
                                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>Müşteri</option>
                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                        <a href="admin.php?action=users" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php require 'footer.php'; ?>