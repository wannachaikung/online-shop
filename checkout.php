<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$isLoggedIn = true;

$stmt = $conn->prepare("SELECT c.quantity, p.product_name, p.price, p.product_id FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    header("Location: cart.php");
    exit;
}

$total = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $items));
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postal_code = trim($_POST['postal_code']);
    $phone = trim($_POST['phone']);

    if (empty($address) || empty($city) || empty($postal_code) || empty($phone)) {
        $errors[] = "กรุณากรอกข้อมูลการจัดส่งให้ครบถ้วน";
    }
    if (empty($errors)) {
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $total]);
            $order_id = $conn->lastInsertId();

            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }

            $stmt = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $address, $city, $postal_code, $phone]);

            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);

            $conn->commit();
            header("Location: orders.php?success=1");
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ชำระเงิน - Online Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bs-primary-rgb: 59, 130, 246; 
            --bs-body-font-family: 'Kanit', sans-serif; 
            --bs-body-bg: #F0F4F8; /* <-- ปรับสีพื้นหลังให้เข้มขึ้นเล็กน้อย */
        }
        .footer a { color: #adb5bd; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #ffffff; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'templates/header.php'; ?>

    <main class="container py-5">
        <div class="text-center mb-4">
            <h2 class="display-6 fw-bold">ชำระเงิน</h2>
            <p class="text-muted">กรุณาตรวจสอบรายการสินค้าและกรอกข้อมูลการจัดส่งให้ครบถ้วน</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                    <p class="mb-0"><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h5 class="mb-0"><i class="bi bi-truck me-2"></i>ข้อมูลการจัดส่ง</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="address" class="form-label">ที่อยู่</label>
                                    <textarea name="address" id="address" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">จังหวัด</label>
                                    <input type="text" name="city" id="city" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                                    <input type="text" name="postal_code" id="postal_code" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card shadow">
                         <div class="card-header py-3">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>สรุปรายการสั่งซื้อ</h5>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($items as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <?= htmlspecialchars($item['product_name']) ?><br>
                                            <small class="text-muted">จำนวน: <?= $item['quantity'] ?></small>
                                        </div>
                                        <span><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="card-footer p-4">
                            <div class="d-flex justify-content-between fs-5 fw-bold">
                                <span>ยอดรวมทั้งหมด</span>
                                <span class="text-primary"><?= number_format($total, 2) ?> บาท</span>
                            </div>
                        </div>
                    </div>
                     <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow"><i class="bi bi-shield-check-fill me-2"></i>ยืนยันการสั่งซื้อ</button>
                        <a href="cart.php" class="btn btn-outline-secondary">กลับไปที่ตะกร้า</a>
                    </div>
                </div>
            </div>
        </form>
    </main>

   <?php include 'templates/footer.php'; ?>
   
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>