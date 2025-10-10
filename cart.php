<?php
session_start();
require 'config.php';
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) { // TODO: ใส่ session ของ user
    header("Location: login.php"); // TODO: หน้ำ login
    exit;
}
$user_id = $_SESSION['user_id']; // TODO: ก ำหนด user_id

// -----------------------------
// ดงึรำยกำรสนิ คำ้ในตะกรำ้
// -----------------------------
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, products.product_name, products.price, products.product_id
                    FROM cart
                    JOIN products ON cart.product_id = products.product_id
                    WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------
// ลบสนิ คำ้ออกจำกตะกรำ้
// -----------------------------
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    
    $_SESSION['success'] = "ลบสินค้าออกจากตะกร้าเรียบร้อยแล้ว";
    header("Location: cart.php");
    exit;
}

// -----------------------------
// เพมิ่ สนิ คำ้เขำ้ตะกรำ้
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) { // TODO: product_id
    $product_id = $_POST['product_id']; // TODO: product_id
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    // ตรวจสอบวำ่ สนิ คำ้อยใู่ นตะกรำ้แลว้หรอื ยัง
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    // TODO: ใสช่ อื่ ตำรำงตะกรำ้
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // ถ ้ำมีแล้ว ให้เพิ่มจ ำนวน
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        // TODO: ชอื่ ตำรำง, primary key ของตะกร ้ำ
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        // ถ ้ำยังไม่มี ให้เพิ่มใหม่
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    $_SESSION['success'] = "เพิ่มสินค้าลงในตะกร้าเรียบร้อยแล้ว";
    header("Location: cart.php"); // TODO: กลับมำที่ cart
    exit;
}

// -----------------------------
// ค ำนวณรำคำรวม
// -----------------------------
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price']; // TODO: quantity * price
}


?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="container mt-4">
    <h2>ตะกร้าสินค้า</h2>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับไปเลือกสินค้า</a> <!-- TODO: หน้ำ index -->
    <?php if (count($items) === 0) : ?>
        <div class="alert alert-warning">ยังไม่มีสินค้าในตะกร้า</div> <!-- TODO: ข ้อควำมกรณีตะกร ้ำว่ำง -->
    <?php else : ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ชื่อสินค้า</th>
                    <th>จำนวน</th>
                    <th>ราคาต่อหน่วย</th>
                    <th>ราคารวม</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item) : ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td> <!-- TODO: product_name -->
                        <td><?= $item['quantity'] ?></td> <!-- TODO: quantity -->
                        <td><?= number_format($item['price'], 2) ?></td> <!-- TODO: price -->
                        <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td> <!-- TODO: price *
                                quantity -->
                        <td>
                            <button class="btn btn-sm btn-danger delete-item" 
                                    data-cart-id="<?= $item['cart_id'] ?>" 
                                    data-product-name="<?= htmlspecialchars($item['product_name']) ?>">
                                ลบ
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>รวมทั้งหมด:</strong></td>
                    <td colspan="2"><strong><?= number_format($total, 2) ?> บาท</strong></td>
                </tr>
            </tbody>
        </table>
        <a href="checkout.php" class="btn btn-success">สั่งซื้อสินค้า</a> <!-- TODO: checkout -->
    <?php endif; ?>

    <script>
        // SweetAlert2 for success/error messages
        <?php if (isset($_SESSION['success'])) : ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: '<?= $_SESSION['success'] ?>',
                icon: 'success',
                confirmButtonText: 'ตกลง'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        //Sweetalert2 for Delete confirmation
        document.querySelectorAll('.delete-item').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.getAttribute('data-cart-id');
                const productName = this.getAttribute('data-product-name');

                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: `ต้องการลบสินค้า "${productName}" ออกจากตะกร้าหรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `cart.php?remove=${cartId}`;
                    }
                });
            });
        });
    </script>
</body>

</html>