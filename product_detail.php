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

$isLoggedIn = isset($_SESSION['user_id']);

if (!$product) {
    // You can create a proper "Not Found" page later
    header("HTTP/1.0 404 Not Found");
    echo "<h3>ไม่พบสินค้าที่คุณต้องการ</h3>";
    exit;
}

// Prepare image path
$img_path = !empty($product['image'])
    ? 'product_images/' . rawurlencode($product['image'])
    : 'product_images/no-image.jpg';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name']) ?> - Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Kanit', sans-serif; }
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh; }
        .navbar-brand { color: #4285f4 !important; font-weight: 600; }
        .nav-link { color: #5f6368 !important; font-weight: 500; }
        .nav-link:hover { color: #4285f4 !important; }
        
        .product-image-main {
            width: 100%;
            height: 450px;
            object-fit: cover;
            border-radius: .75rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .product-image-main:hover {
            transform: scale(1.03);
        }
        .product-details-card {
            background: #ffffff;
            border-radius: .75rem;
            padding: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .product-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: #333;
        }
        .product-category {
            font-size: 1rem;
            font-weight: 500;
            color: #666;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .product-price {
            font-size: 2.5rem;
            font-weight: 600;
            color: #4285f4;
        }
        .stock-status {
            font-weight: 500;
        }
        .stock-status .in-stock { color: #198754; }
        .stock-status .out-of-stock { color: #dc3545; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="bi bi-cart3 fs-4"></i>
                <span>Online Shop</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>หน้าหลัก</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-bag me-1"></i>ตะกร้า</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['username']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="profile.php">ข้อมูลส่วนตัว</a></li>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/index.php">หน้าแอดมิน</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ</a></li>
                        <li class="nav-item"><a class="btn btn-primary rounded-pill px-3" href="register.php"><i class="bi bi-person-plus me-1"></i>สมัครสมาชิก</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">สินค้า</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['product_name']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <!-- Product Image Column -->
            <div class="col-lg-6">
                <img src="<?= htmlspecialchars($img_path) ?>" 
                     alt="<?= htmlspecialchars($product['product_name']) ?>" 
                     class="product-image-main">
            </div>

            <!-- Product Details Column -->
            <div class="col-lg-6">
                <div class="product-details-card h-100 d-flex flex-column">
                    <div class="mb-3">
                        <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่') ?></span>
                        <h1 class="product-title mt-1"><?= htmlspecialchars($product['product_name']) ?></h1>
                    </div>
                    
                    <p class="text-muted mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="product-price">
                            <?= number_format($product['price'], 2) ?> บาท
                        </div>
                        <div class="stock-status">
                            <?php if ($product['stock'] > 0): ?>
                                <span class="in-stock"><i class="bi bi-check-circle-fill me-1"></i>มีสินค้า (<?= $product['stock'] ?> ชิ้น)</span>
                            <?php else: ?>
                                <span class="out-of-stock"><i class="bi bi-x-circle-fill me-1"></i>สินค้าหมด</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <?php if ($isLoggedIn): ?>
                            <?php if ($product['stock'] > 0): ?>
                                <form action="cart.php" method="post">
                                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                    <div class="input-group input-group-lg mb-3">
                                        <label class="input-group-text" for="quantity">จำนวน</label>
                                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="form-control text-center" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="bi bi-cart-plus-fill me-2"></i>เพิ่มลงตะกร้า
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">สินค้าหมดชั่วคราว</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <div>กรุณา <a href="login.php" class="alert-link fw-bold">เข้าสู่ระบบ</a> เพื่อทำการสั่งซื้อ</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
