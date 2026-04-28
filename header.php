<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çiçek Sepetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff0f5; } /* Açık pembe arka plan */
        .product-card img { height: 250px; object-fit: cover; }
        .bg-pink { background-color: #e83e8c !important; }
        .text-pink { color: #e83e8c !important; }
        .btn-pink { background-color: #e83e8c; color: white; border-color: #e83e8c; }
        .btn-pink:hover { background-color: #d81b60; color: white; border-color: #d81b60; }
        .badge-pink { background-color: white; color: #e83e8c; }
        .list-group-item.active { background-color: #e83e8c !important; border-color: #e83e8c !important; }
        .navbar-brand, .nav-link { font-weight: 500; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-pink mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">🌸 Çiçek Sepetim</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Anasayfa</a></li>
                <li class="nav-item"><a class="nav-link" href="iletisim.php">İletişim</a></li>
                <li class="nav-item"><a class="nav-link" href="bize_ulasin.php">Bize Ulaşın</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        🛒 Sepet 
                        <span class="badge badge-pink">
                            <?= isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                </li>
                <?php if(isset($_SESSION['original_admin_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-warning btn-sm text-dark ms-2 fw-bold px-3" href="?switch_back=1">🔙 Admin'e Dön</a>
                    </li>
                <?php endif; ?>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">👤 <?= htmlspecialchars($_SESSION['user_name']); ?></a>
                        <ul class="dropdown-menu">
                            <?php if($_SESSION['role'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="admin.php">⚙️ Admin Panel</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Üye Ol</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">