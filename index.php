<?php
require 'init.php';
require 'header.php';

// Duyuruları Çekelim
$stmt_announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
$announcements = $stmt_announcements->fetchAll();

// Ürünleri Çekelim
$stmt_products = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt_products->fetchAll();
?>

<!-- Duyurular Bölümü -->
<?php if($announcements): ?>
    <div class="alert alert-secondary shadow-sm" style="background-color: #fdf5f8; border-color: #f8bbd0;">
        <h5 class="alert-heading text-pink">📢 Duyurular</h5>
        <ul class="mb-0 text-dark">
            <?php foreach($announcements as $ann): ?>
                <li><strong><?= htmlspecialchars($ann['title']); ?>:</strong> <?= htmlspecialchars($ann['content']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Ürün Vitrini -->
<div class="row mt-4">
    <h2 class="text-pink">En Güzel Çiçekler</h2>
    <hr>
    <?php if($products): ?>
        <?php foreach($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 product-card shadow-sm border-0">
                    <img src="<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top rounded-top" alt="<?= htmlspecialchars($product['name']); ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-dark"><?= htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars(mb_substr($product['description'], 0, 50)) . '...'; ?></p>
                        <h4 class="text-pink mt-auto fw-bold"><?= number_format($product['price'], 2, ',', '.'); ?> TL</h4>
                        
                        <!-- Sepete Ekle Formu -->
                        <form action="cart_process.php" method="POST" class="mt-3">
                            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="btn btn-pink w-100">🛒 Sepete Ekle</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-muted">Henüz ürün eklenmemiş. Admin panelinden yeni çiçekler ekleyebilirsiniz.</p>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>