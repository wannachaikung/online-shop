<?php
session_start();
require_once 'config.php';
$isLoggedIn = isset($_SESSION['user_id']);

$stmt = $conn->query("SELECT p.*, c.category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop - ร้านค้าออนไลน์</title>
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
        .product-image { background: linear-gradient(135deg, #e8f0fe 0%, white 100%); height: 200px; }
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

    <!-- Hero Section -->
    <div class="container my-5">
        <div class="hero-gradient rounded-4 p-5 text-center shadow">
            <div class="float-animation">
                <i class="bi bi-cart3 text-primary-blue" style="font-size: 4rem;"></i>
            </div>
            <h1 class="display-5 fw-bold text-dark mb-3">ยินดีต้อนรับสู่ Online Shop</h1>
            <p class="lead text-muted mb-4">
                <?php if ($isLoggedIn): ?>
                    สวัสดี คุณ <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= $_SESSION['role'] ?>)
                <?php else: ?>
                    พบกับสินค้าคุณภาพดี ราคาสมเหตุสมผล
                <?php endif; ?>
            </p>
            
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <?php if ($isLoggedIn): ?>
                    <a href="cart.php" class="btn btn-primary btn-lg rounded-pill px-4">
                        <i class="bi bi-bag-check me-2"></i>ดูตะกร้าสินค้า
                    </a>
                    <a href="profile.php" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                        <i class="bi bi-person me-2"></i>ข้อมูลส่วนตัว
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary btn-lg rounded-pill px-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                    </a>
                    <a href="register.php" class="btn btn-outline-primary btn-lg rounded-pill px-4">
                        <i class="bi bi-person-plus me-2"></i>สมัครสมาชิก
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div class="container pb-5">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold text-white d-inline-flex align-items-center gap-3">
                <i class="bi bi-stars"></i>สินค้าแนะนำ
            </h2>
            <p class="text-white-50 fs-5">คัดสรรสินค้าคุณภาพดีมาให้คุณโดยเฉพาะ</p>
        </div>

        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="product-image d-flex align-items-center justify-content-center position-relative">
                            <i class="bi bi-box-seam text-primary-blue opacity-50" style="font-size: 3rem;"></i>
                            <span class="position-absolute top-0 end-0 badge bg-primary m-3 rounded-pill">NEW</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-semibold"><?= htmlspecialchars($product['product_name']) ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($product['category_name']) ?>
                            </p>
                            <p class="card-text text-muted small"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                            <h4 class="text-primary-blue fw-bold mb-3">฿<?= number_format($product['price'], 2) ?></h4>
                            
                            <div class="d-flex gap-2">
                                <?php if ($isLoggedIn): ?>
                                    <form action="cart.php" method="post" class="flex-fill">
                                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-cart-plus me-1"></i>เพิ่มลงตะกร้า
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="bg-light text-muted p-2 rounded text-center flex-fill small">
                                        <i class="bi bi-lock me-1"></i>เข้าสู่ระบบเพื่อสั่งซื้อ
                                    </div>
                                <?php endif; ?>
                                
                                <a href="product_detail.php?id=<?= $product['product_id'] ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>