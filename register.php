<?php
require 'init.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        $error = "Lütfen tüm alanları doldurun.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'user')");
        
        try {
            $stmt->execute([$name, $email, $hashedPassword, $phone, $address]);
            $success = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
        } catch (PDOException $e) {
            $error = "Bu e-posta adresi zaten kullanımda.";
        }
    }
}
require 'header.php';
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-pink text-white"><h4>Üye Ol</h4></div>
            <div class="card-body">
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
                <form action="register.php" method="POST">
                    <div class="mb-3"><label>Ad Soyad</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label>E-posta Adresi</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label>Şifre</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3"><label>Telefon Numarası</label><input type="tel" name="phone" class="form-control" required maxlength="10" minlength="10" pattern="[0-9]{10}" placeholder="Örn: 5555555555" title="Lütfen başında sıfır olmadan 10 haneli numaranızı girin"></div>
                    <div class="mb-3"><label>Adres</label><textarea name="address" class="form-control" rows="2" required></textarea></div>
                    <button type="submit" class="btn btn-pink w-100">Kayıt Ol</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>