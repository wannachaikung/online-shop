<?php
session_start();
require 'config.php';
require 'function.php';
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) { // TODO: ใส่ session ของ user
header("Location: login.php"); // TODO: หน้ำ login
exit;
}
$user_id = $_SESSION['user_id']; // TODO: ก ำหนด user_id

// -----------------------------
// ดงึค ำสั่งซอื้ ของผใู้ช ้
// -----------------------------
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC"); // orderstable และ order_date
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>


<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ประวัติการสั่งซื้อ</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-4">
        <h2>ประวัติการสั่งซื้อ</h2>
        <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าหลัก</a>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">ทำรายการสั่งซื้อเรียบร้อยแล้ว</div>
            <?php endif; ?>

            <?php if (count($orders) === 0): ?>
                <div class="alert alert-warning">คณุยังไม่ได้สั่งซื้อสินค้า</div>
                <?php else: ?>

                    <?php foreach ($orders as $order): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <strong>รหัสคำสั่งซื้อ :</strong> #<?= $order['order_id'] ?> |
                                <strong>วันที่:</strong> <?= $order['order_date'] ?> |
                                <strong>สถานะ:</strong> <?= ucfirst($order['status']) ?>
                            </div>
                            <div class="card-body">
                                <ul class="list-group mb-3">
                                    <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                                        <li class="list-group-item">
                                            <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?> = <?=
                                                number_format($item['price'] * $item['quantity'], 2) ?> บำท
                                            </li>
                                            <?php endforeach; ?>
                                            </ul>
                                            <p><strong>รวมทัง้สนิ้ :</strong> <?= number_format($order['total_amount'], 2) ?> บำท</p>
                                        <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                                        <?php if ($shipping): ?>
                                    <p><strong>ทอี่ ยจู่ ัดสง่ :</strong> <?= htmlspecialchars($shipping['address']) ?>, <?=
                                        htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                                        <p><strong>สถำนะกำรจัดสง่ :</strong> <?= ucfirst($shipping['shipping_status']) ?></p>
                                        <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                                        <?php endif; ?>
    </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    </body>
    </html>