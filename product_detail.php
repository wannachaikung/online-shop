<?php
session_start();
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$product_id = $_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่
$isLoggedIn = isset($_SESSION['user_id']);

if (!$product) {
echo "<h3>ไมพ่ บสนิ คำ้ทคี่ ณุ ตอ้ งกำร</h3>";
exit;
}
?>
<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายละเอยีดสิน ค้า</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-4">
        <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้ารายการสินค้า</a>
        <div class="card">
            <div class="card-body">
                <h3 class="card-title"><?= htmlspecialchars($product['product_name'])?></h3>
                <h6 class="text-muted">หมวดหมู่: <?= htmlspecialchars($product['category_name'])?></h6>
                <p class="card-text mt-3"></p>
                <p><strong>ราคา:</strong> <?= htmlspecialchars($product['price'])?>บาท</p>
                <p><strong>คงเหลือ:</strong>  <?= htmlspecialchars($product['stock'])?>ชิ้น </p>
                <?php if ($isLoggedIn): ?>
                    <form action="cart.php" method="post" class="mt-3">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <label for="quantity">จำนวน:</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?=
$product['stock'] ?>" required>
<button type="submit" class="btn btn-success">เพิ่มในตะกร้า</button>
</form>
<?php else: ?>
    <div class="alert alert-info mt-3">กรุณาเข้าสู่ระบบเพื่อเข้าซื้อสินค้า</div>
    <?php endif; ?>
</div>
</div>
</body>
</html>