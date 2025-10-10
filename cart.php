<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$isLoggedIn = true;

if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    $_SESSION['success'] = "ลบสินค้าออกจากตะกร้าแล้ว";
    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // เพิ่มทีละ 1 ชิ้น
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    $_SESSION['success'] = "เพิ่มสินค้าลงในตะกร้าแล้ว";
    header("Location: cart.php");
    exit;
}

$stmt = $conn->prepare("SELECT c.cart_id, c.quantity, p.product_name, p.price, p.image FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $items));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --bs-primary-rgb: 59, 130, 246; --bs-body-font-family: 'Kanit', sans-serif; --bs-body-bg: #F0F4F8; }
        .footer a { color: #adb5bd; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #ffffff; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'templates/header.php'; ?>

    <main class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="display-6 fw-bold mb-0">ตะกร้าสินค้าของคุณ</h2>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>กลับไปเลือกสินค้า</a>
        </div>
        <?php if (empty($items)): ?>
            <div class="text-center py-5"><i class="bi bi-cart-x" style="font-size: 5rem; color: #6c757d;"></i><h4 class="mt-3">ยังไม่มีสินค้าในตะกร้า</h4></div>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr class="text-center"><th scope="col" class="text-start ps-4">สินค้า</th><th scope="col">ราคา</th><th scope="col">จำนวน</th><th scope="col">รวม</th><th scope="col" class="pe-4"></th></tr></thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr class="text-center">
                                <td class="text-start ps-4"><div class="d-flex align-items-center">
                                    <img src="product_images/<?= htmlspecialchars($item['image'] ?? 'no-image.jpg') ?>" width="60" class="me-3 rounded" alt="">
                                    <span><?= htmlspecialchars($item['product_name']) ?></span>
                                </div></td>
                                <td><?= number_format($item['price'], 2) ?></td><td><?= $item['quantity'] ?></td>
                                <td><strong><?= number_format($item['price'] * $item['quantity'], 2) ?></strong></td>
                                <td class="pe-4"><button class="btn btn-sm btn-outline-danger delete-item" data-cart-id="<?= $item['cart_id'] ?>" data-product-name="<?= htmlspecialchars($item['product_name']) ?>"><i class="bi bi-trash3"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div></div>
                <div class="card-footer bg-white p-4">
                    <div class="d-flex justify-content-end align-items-center">
                        <h4 class="me-4 mb-0">ยอดรวม: <span class="text-primary fw-bold"><?= number_format($total, 2) ?> บาท</span></h4>
                        <a href="checkout.php" class="btn btn-primary btn-lg"><i class="bi bi-box-arrow-right me-2"></i>ชำระเงิน</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script>
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({ title: 'สำเร็จ!', text: '<?= $_SESSION['success'] ?>', icon: 'success', timer: 2000, showConfirmButton: false });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        document.querySelectorAll('.delete-item').forEach(b=>b.addEventListener('click',function(){const c=this.dataset.cartId,p=this.dataset.productName;Swal.fire({title:'ยืนยันการลบ',text:`ต้องการลบ "${p}" ออกจากตะกร้า?`,icon:'warning',showCancelButton:true,confirmButtonColor:'#dc3545',confirmButtonText:'ลบ',cancelButtonText:'ยกเลิก'}).then(r=>r.isConfirmed&&(window.location.href=`cart.php?remove=${c}`))}));
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>