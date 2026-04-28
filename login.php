<?php
require 'init.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = date('d.m.Y H:i:s'); // Giriş zamanını kaydet

        header("Location: " . ($user['role'] === 'admin' ? "admin.php" : "index.php"));
        exit();
    } else {
        $error = "Hatalı e-posta veya şifre!";
    }
}
require 'header.php';
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-pink text-white"><h4>Giriş Yap</h4></div>
            <div class="card-body">
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="mb-3"><label>E-posta Adresi</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label>Şifre</label><input type="password" name="password" class="form-control" required></div>
                    <button type="submit" class="btn btn-pink w-100">Giriş Yap</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>