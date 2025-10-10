<?php

require '../config.php';
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ผู้ดูแลระบบ

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Kanit', sans-serif; }
        body { background: linear-gradient(135deg, rgba(108, 179, 241, 0.9), rgba(63, 125, 241, 0.9) 100%); min-height: 100vh; }
        .admin-card { transition: all 0.3s ease; border: none; }
        .admin-card:hover { transform: translateY(-8px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .text-primary-blue { color: #4285f4 !important; }
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/68s_onlineshop/index.php">
            <i class="bi bi-cart3 fs-4 text-primary-blue"></i>
            <span style="color: #4285f4 !important;">Online Shop</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="/68s_onlineshop/index.php">
                        <i class="bi bi-house"></i>หน้าหลัก user
                    </a>
                </li>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/68s_onlineshop/admin/index.php">
                            <i class="bi bi-shield-check"></i>แผงควบคุม
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/68s_onlineshop/profile.php">ข้อมูลส่วนตัว</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="/68s_onlineshop/logout.php">
                                    <i class="bi bi-box-arrow-right"></i>ออกจากระบบ
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/68s_onlineshop/login.php">
                            <i class="bi bi-box-arrow-in-right"></i>เข้าสู่ระบบ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary rounded-pill px-3 d-flex align-items-center gap-2" href="/68s_onlineshop/register.php">
                            <i class="bi bi-person-plus"></i>สมัครสมาชิก
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
   
    <div class="container-fluid py-5">
        <!-- Header -->
        <div class="text-center mb-5">
            <div class="bg-white rounded-4 p-4 d-inline-block shadow-lg mb-3">
                <i class="bi bi-shield-check text-primary-blue" style="font-size: 3rem;"></i>
            </div>
            <h1 class="display-4 fw-bold text-white mb-2">ระบบผู้ดูแลระบบ</h1>
            <p class="lead text-white-50">ยินดีต้อนรับ, <strong class="text-white"><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        </div>

        <!-- Admin Menu Cards -->
        <div class="row g-4 justify-content-center" style="max-width: 1200px; margin: 0 auto;">
            <div class="col-lg-3 col-md-6">
                <div class="card admin-card h-100 shadow-lg">
                    <div class="card-body text-center p-4">
                        <div class="bg-warning bg-gradient rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-people-fill text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title fw-bold">จัดการสมาชิก</h5>
                        <p class="card-text text-muted">ดูและจัดการข้อมูลสมาชิก</p>
                        <a href="users.php" class="btn btn-warning btn-lg w-100 rounded-pill shadow-sm">
                            <i class="bi bi-people me-2"></i>เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card admin-card h-100 shadow-lg">
                    <div class="card-body text-center p-4">
                        <div class="bg-dark bg-gradient rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-tags-fill text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title fw-bold">จัดการหมวดหมู่</h5>
                        <p class="card-text text-muted">สร้างและจัดการหมวดหมู่สินค้า</p>
                        <a href="category.php" class="btn btn-dark btn-lg w-100 rounded-pill shadow-sm">
                            <i class="bi bi-tags me-2"></i>เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card admin-card h-100 shadow-lg">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary bg-gradient rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-box-seam-fill text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title fw-bold">จัดการสินค้า</h5>
                        <p class="card-text text-muted">เพิ่ม แก้ไข และลบสินค้าในระบบ</p>
                        <a href="products.php" class="btn btn-primary btn-lg w-100 rounded-pill shadow-sm">
                            <i class="bi bi-box-seam me-2"></i>เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card admin-card h-100 shadow-lg">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-gradient rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="bi bi-cart-check-fill text-white" style="font-size: 2rem;"></i>
                        </div>
                        <h5 class="card-title fw-bold">จัดการคำสั่งซื้อ</h5>
                        <p class="card-text text-muted">ตรวจสอบและจัดการออเดอร์</p>
                        <a href="orders.php" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm">
                            <i class="bi bi-cart-check me-2"></i>เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>