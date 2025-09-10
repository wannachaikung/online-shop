<?php
session_start();
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
    </style>
</head>

<body>
    <div class="container-fluid py-5">
        <!-- Header -->
        <div class="text-center mb-5">
            <div class="bg-white rounded-4 p-4 d-inline-block shadow-lg mb-3">
                <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
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
                        <p class="card-text text-muted">ดูและจัดการข้อมูลสมาชิกทั้งหมดในระบบ</p>
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
                        <a href="categories.php" class="btn btn-dark btn-lg w-100 rounded-pill shadow-sm">
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
                        <p class="card-text text-muted">ตรวจสอบและจัดการออเดอร์ทั้งหมด</p>
                        <a href="orders.php" class="btn btn-success btn-lg w-100 rounded-pill shadow-sm">
                            <i class="bi bi-cart-check me-2"></i>เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Section -->
        <div class="text-center mt-5">
            <div class="card d-inline-block shadow">
                <div class="card-body p-4">
                    <h6 class="card-title text-muted mb-3">ต้องการออกจากระบบ?</h6>
                    <a href="../logout.php" class="btn btn-outline-secondary btn-lg rounded-pill px-5">
                        <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>