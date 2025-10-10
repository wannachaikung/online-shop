<?php
session_start();
require 'config.php';
require 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$isLoggedIn = true;

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประวัติการสั่งซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --bs-primary-rgb: 59, 130, 246; --bs-body-font-family: 'Kanit', sans-serif; --bs-body-bg: #F9FAFB; }
        .accordion-button:not(.collapsed) { background-color: rgba(var(--bs-primary-rgb), 0.1); color: var(--bs-body-color); }
        .accordion-button:focus { box-shadow: none; }
        .footer a { color: #adb5bd; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #ffffff; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'templates/header.php'; ?>
    
    <main class="container py-5">
        <h2 class="display-6 fw-bold mb-4">ประวัติการสั่งซื้อ</h2>
        <?php if (isset($_GET['success'])): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>ทำรายการสั่งซื้อเรียบร้อยแล้ว</div><?php endif; ?>
        <?php if (empty($orders)): ?>
            <div class="text-center py-5"><i class="bi bi-receipt" style="font-size: 5rem; color: #6c757d;"></i><h4 class="mt-3">คุณยังไม่มีประวัติการสั่งซื้อ</h4><a href="index.php" class="btn btn-primary mt-3">ไปเลือกซื้อสินค้ากันเลย!</a></div>
        <?php else: ?>
            <div class="accordion" id="ordersAccordion">
                <?php foreach ($orders as $index => $order): ?>
                <div class="accordion-item shadow-sm mb-3 border-0 rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                            <div class="d-flex justify-content-between w-100 pe-3 flex-wrap">
                                <span><strong>#<?= $order['order_id'] ?></strong></span>
                                <span><i class="bi bi-calendar3 me-2"></i><?= date('d/m/Y', strtotime($order['order_date'])) ?></span>
                                <span><span class="badge bg-primary rounded-pill"><?= ucfirst($order['status']) ?></span></span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#ordersAccordion">
                        <div class="accordion-body">
                            <h6><i class="bi bi-list-ul me-2"></i>รายการสินค้า</h6>
                            <ul class="list-group mb-3">
                                <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                                <li class="list-group-item d-flex justify-content-between"><span><?= htmlspecialchars($item['product_name']) ?> (×<?= $item['quantity'] ?>)</span><span><?= number_format($item['price'] * $item['quantity'], 2) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="text-end fs-5"><strong>ยอดรวม: <span class="text-primary"><?= number_format($order['total_amount'], 2) ?> บาท</span></strong></p>
                            <hr>
                            <h6><i class="bi bi-truck me-2"></i>ข้อมูลการจัดส่ง</h6>
                            <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                            <?php if ($shipping): ?>
                                <p class="mb-1"><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                                <p><strong>สถานะ:</strong> <?= ucfirst($shipping['shipping_status']) ?></p>
                            <?php else: ?><p class="text-muted">ไม่มีข้อมูลการจัดส่ง</p><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>