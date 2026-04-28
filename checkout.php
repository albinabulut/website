<?php
require 'init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_amount = 0;
    $product_ids = implode(',', array_keys($_SESSION['cart']));
    $stmt = $pdo->query("SELECT id, price FROM products WHERE id IN ($product_ids)");
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $total_amount += $p['price'] * $_SESSION['cart'][$p['id']];
    }

    try {
        $pdo->beginTransaction();
        $stmtOrder = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'Bekliyor')");
        $stmtOrder->execute([$_SESSION['user_id'], $total_amount]);
        $order_id = $pdo->lastInsertId();

        $stmtItems = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($products as $p) {
            $qty = $_SESSION['cart'][$p['id']];
            $stmtItems->execute([$order_id, $p['id'], $qty, $p['price']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];
        $success = "Siparişiniz başarıyla alındı! Sipariş Numaranız: #" . $order_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Sipariş oluşturulurken bir hata oluştu.");
    }
}
require 'header.php';
?>
<?php if($success): ?>
    <div class="alert alert-success text-center mt-5">
        <h2>🎉 Teşekkürler!</h2>
        <p><?= $success ?></p>
        <div class="mt-4">
            <a href="index.php" class="btn btn-pink btn-lg">Alışverişe Dön</a>
        </div>
    </div>
<?php else: ?>
    <h2 class="text-pink">Ödeme ve Sipariş Onayı</h2>
    <hr>
    <div class="row mt-4 justify-content-center">
        <div class="col-md-8">
            <form action="checkout.php" method="POST">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-pink text-white fw-bold">📍 Teslimat Bilgileri</div>
                    <div class="card-body bg-white">
                        <div class="mb-3">
                            <label class="form-label text-muted">Alıcı Adı Soyadı</label>
                            <input type="text" class="form-control" required value="<?= htmlspecialchars($_SESSION['user_name']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Teslimat Adresi</label>
                            <textarea name="address" class="form-control" rows="3" required placeholder="Sokak, mahalle, ilçe, il vb. açık adres giriniz..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-pink text-white fw-bold">💳 Kredi/Banka Kartı Bilgileri</div>
                    <div class="card-body bg-white">
                        <div class="mb-3">
                            <label class="form-label text-muted">Kart Üzerindeki İsim</label>
                            <input type="text" class="form-control form-control-lg" required placeholder="AD SOYAD">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Kart Numarası</label>
                            <input type="text" class="form-control form-control-lg" required placeholder="0000 0000 0000 0000" maxlength="19">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label text-muted">Son Kullanma (AA/YY)</label>
                                <input type="text" id="expiry" class="form-control form-control-lg" required placeholder="AA/YY" maxlength="5">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label text-muted">CVV</label>
                                <input type="text" class="form-control form-control-lg" required placeholder="123" maxlength="3">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-pink btn-lg w-100 mb-5 shadow-sm">Siparişi Tamamla ve Öde</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
// Sadece rakam girilmesini sağlar ve araya otomatik '/' ekler
document.getElementById('expiry')?.addEventListener('input', function (e) {
    let input = e.target.value.replace(/\D/g, ''); // Sadece rakamları tut
    if (input.length > 2) {
        e.target.value = input.substring(0, 2) + '/' + input.substring(2, 4);
    } else {
        e.target.value = input;
    }
});
</script>
<?php require 'footer.php'; ?>