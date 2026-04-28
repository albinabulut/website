<?php
require 'init.php';
require 'header.php';

$cart_items = $_SESSION['cart'] ?? [];
$total_price = 0;
?>
<h2>Alışveriş Sepetiniz</h2>
<hr>
<?php if(empty($cart_items)): ?>
    <div class="alert alert-warning text-center">
        Sepetinizde henüz ürün bulunmamaktadır. <br><br>
        <a href="index.php" class="btn btn-pink">Alışverişe Başla</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ürün Adı</th>
                    <th>Fiyat</th>
                    <th>Adet</th>
                    <th>Toplam</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $product_ids = implode(',', array_keys($cart_items));
                $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($product_ids)");
                $products_in_cart = $stmt->fetchAll();

                foreach($products_in_cart as $item): 
                    $quantity = $cart_items[$item['id']];
                    $subtotal = $item['price'] * $quantity;
                    $total_price += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']); ?></td>
                    <td><?= number_format($item['price'], 2, ',', '.'); ?> TL</td>
                    <td><?= $quantity; ?></td>
                    <td><?= number_format($subtotal, 2, ',', '.'); ?> TL</td>
                    <td>
                        <form action="cart_process.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $item['id']; ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="btn btn-sm btn-danger">Sil</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Genel Toplam:</th>
                    <th colspan="2" class="text-pink fs-5"><?= number_format($total_price, 2, ',', '.'); ?> TL</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="d-flex justify-content-end"><a href="checkout.php" class="btn btn-lg btn-pink">Ödeme Sayfasına Geç ➔</a></div>
<?php endif; ?>
<?php require 'footer.php'; ?>