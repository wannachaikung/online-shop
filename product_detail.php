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
    echo "<h3>ไม่พบสินค้าที่คุณต้องการ</h3>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name'])?> - Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Kanit', sans-serif; }
        body { background: linear-gradient(135deg, rgba(108, 179, 241, 0.9), rgba(63, 125, 241, 0.9) 100%); min-height: 100vh; }
        .navbar-brand { color: #4285f4 !important; font-weight: 600; }
        .nav-link { color: #5f6368 !important; font-weight: 500; }
        .nav-link:hover { color: #4285f4 !important; }
        .hero-gradient { background: linear-gradient(135deg, #e8f0fe 0%, white 100%); }
        .text-primary-blue { color: #4285f4 !important; }
        .bg-primary-blue { background-color: #4285f4 !important; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        .float-animation { animation: float 3s ease-in-out infinite; }
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
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="index.php">
                            <i class="bi bi-house"></i>หน้าหลัก
                        </a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2" href="cart.php">
                                <i class="bi bi-bag"></i>ตะกร้าสินค้า
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2" href="profile.php">
                                <i class="bi bi-person-circle"></i>
                                <?= htmlspecialchars($_SESSION['username']) ?>
                                <span class="badge bg-light text-primary"><?= $_SESSION['role'] ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary rounded-pill px-3 d-flex align-items-center gap-2" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>ออกจากระบบ
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i>เข้าสู่ระบบ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary rounded-pill px-3 d-flex align-items-center gap-2" href="register.php">
                                <i class="bi bi-person-plus"></i>สมัครสมาชิก
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4" style="max-width: 1000px;">
        <!-- Back Button -->
        <a href="index.php" class="btn btn-outline-light rounded-pill mb-3">
            <i class="bi bi-arrow-left me-2"></i>กลับหน้ารายการสินค้า
        </a>

        <!-- Breadcrumb -->
        <div class="card mb-4">
            <div class="card-body">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php" class="text-decoration-none">
                                <i class="bi bi-house me-1"></i>หน้าหลัก
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php" class="text-decoration-none">สินค้าทั้งหมด</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= htmlspecialchars($product['product_name'])?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Product Detail Card -->
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <!-- Product Header -->
            <div class="hero-gradient p-5 text-center position-relative">
                <div class="float-animation mb-3">
                    <i class="bi bi-box-seam text-primary-blue" style="font-size: 5rem;"></i>
                </div>
                <h1 class="display-5 fw-bold text-dark mb-3"><?= htmlspecialchars($product['product_name'])?></h1>
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill fs-6 shadow-sm">
                    <i class="bi bi-tag-fill me-2"></i><?= htmlspecialchars($product['category_name'])?>
                </span>
            </div>

            <!-- Product Body -->
            <div class="card-body p-5">
                <!-- Info Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-currency-dollar display-4 mb-3"></i>
                                <p class="card-text opacity-75">ราคาสินค้า</p>
                                <h3 class="card-title">฿<?= number_format($product['price'], 2)?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-box-seam-fill display-4 mb-3"></i>
                                <p class="card-text opacity-75">จำนวนคงเหลือ</p>
                                <h3 class="card-title"><?= htmlspecialchars($product['stock'])?> ชิ้น</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <?php if (!empty($product['description'])): ?>
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center gap-2">
                            <i class="bi bi-file-text"></i>รายละเอียดสินค้า
                        </h5>
                        <p class="card-text lead"><?= nl2br(htmlspecialchars($product['description']))?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Purchase Section -->
                <div class="card border-2">
                    <div class="card-body p-4">
                        <?php if ($isLoggedIn): ?>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                
                                <div class="row align-items-center mb-3">
                                    <div class="col-md-6">
                                        <label for="quantity" class="form-label fw-semibold">
                                            <i class="bi bi-123 me-2"></i>จำนวนที่ต้องการ:
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" 
                                               name="quantity" 
                                               id="quantity" 
                                               value="1" 
                                               min="1" 
                                               max="<?= $product['stock'] ?>" 
                                               class="form-control form-control-lg text-center fw-bold"
                                               required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-cart-plus me-2"></i>เพิ่มลงในตะกร้าสินค้า
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i>
                                <span>กรุณา <a href="login.php" class="alert-link fw-bold">เข้าสู่ระบบ</a> เพื่อซื้อสินค้า</span>
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