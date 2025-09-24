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
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .product-card { border: 1; background:#fff; }
        .product-thumb { height: 180px; object-fit: cover; border-radius:.5rem; }
        .product-meta { font-size:.75rem; letter-spacing:.05em; color:#8a8f98; text-transform:uppercase; }
        .product-title { font-size:1rem; margin:.25rem 0 .5rem; font-weight:600; color:#222; }
        .price { font-weight:700; }
        .rating i { color:#ffc107; } /* ดำวสที อง */
        .wishlist { color:#b9bfc6; }
        .wishlist:hover { color:#ff5b5b; }
        .badge-top-left {
            position:absolute; top:.5rem; left:.5rem; z-index:2;
            border-radius:.375rem;
        }
    </style>

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


         <!-- ===== ส่วนแสดงสินค้า =====  -->
        <div class="row g-4">
        <?php foreach ($products as $p): ?>
        <?php
        // เตรียมรูป
        $img = !empty($p['image'])
            ? 'product_images/' . rawurlencode($p['image'])
            : 'product_images/no-image.jpg';
        // ตกแต่ง badge: NEW ภายใน 7 วัน / HOT ถ้าสต็อกน้อยกว่า 5
        $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7*24*3600);
        $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;
        // ดาวรีวิว (ถ้าไม่มีใน DB จะโชว์ 4.5 จำลอง; ถ้ามี $p['rating'] ให้แทน)
        $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;
        $full = floor($rating); // จำนวนดาวเต็ม
        $half = ($rating - $full) >= 0.5 ? 1 : 0; // มีดาวครึ่งดวงหรือไม่
        ?>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card product-card h-100 position-relative">
                <?php if ($isNew): ?>
                    <span class="badge bg-success badge-top-left">ใหม่</span>
                <?php elseif ($isHot): ?>
                    <span class="badge bg-danger badge-top-left">ฮอต</span>
                <?php endif; ?>
                <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="p-3 d-block">
                    <img src="<?= htmlspecialchars($img) ?>"
                         alt="<?= htmlspecialchars($p['product_name']) ?>"
                         class="img-fluid w-100 product-thumb">
                </a>
                <div class="px-3 pb-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="product-meta">
                            <?= htmlspecialchars($p['category_name'] ?? 'หมวดหมู่') ?>
                        </div>
                        <button class="btn btn-link p-0 wishlist" title="เพิ่มในรายการโปรด" type="button">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                    <a class="text-decoration-none" href="product_detail.php?id=<?= (int)$p['product_id'] ?>">
                        <div class="product-title">
                            <?= htmlspecialchars($p['product_name']) ?>
                        </div>
                    </a>
                    <div class="rating mb-2">
                        <?php for ($i=0; $i<$full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                        <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>
                        <?php for ($i=0; $i<5-$full-$half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                    </div>
                    <div class="price mb-3">
                        <?= number_format((float)$p['price'], 2) ?> บาท
                    </div>
                    <div class="mt-auto d-flex gap-2">
                        <?php if ($isLoggedIn): ?>
                            <form action="cart.php" method="post" class="d-inline-flex gap-2">
                                <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร้า</button>
                            </form>
                        <?php else: ?>
                            <small class="text-muted">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
                        <?php endif; ?>
                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>"
                           class="btn btn-sm btn-outline-primary ms-auto">ดูรายละเอียด</a>
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