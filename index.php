<?php
session_start(); // เริ่มต้น session เพื่อจัดการการเข้าสู่ระบบ
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #7dc1fdff 0%, #2551b9ff 100%); min-height: 100vh;">
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg shadow-sm" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fas fa-shopping-cart me-2"></i>Online Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active fw-medium" href="#"><i class="fas fa-home me-1"></i>หน้าหลัก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#"><i class="fas fa-box me-1"></i>สินค้า</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-medium" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?=htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-edit me-2"></i>แก้ไขโปรไฟล์</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-shopping-bag me-2"></i>ประวัติการสั่งซื้อ</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Welcome Card -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0" style="border-radius: 20px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-shopping-cart display-1 text-primary mb-3"></i>
                        </div>
                        <h1 class="display-4 fw-bold text-dark mb-3">ยินดีต้อนรับสู่ Online Shop</h1>
                        <p class="lead text-secondary mb-4">สวัสดี คุณ <span class="fw-bold text-primary"><?=htmlspecialchars($_SESSION['username']) ?></span> (<?=$_SESSION['role']?>)</p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button class="btn btn-lg text-white fw-semibold px-4" style="background: linear-gradient(135deg, #7dc1fdff 0%, #2551b9ff 100%); border: none; border-radius: 12px;">
                                <i class="fas fa-search me-2"></i>เริ่มช้อปปิ้ง
                            </button>
                            <button class="btn btn-lg btn-outline-primary fw-semibold px-4" style="border-radius: 12px;">
                                <i class="fas fa-tags me-2"></i>ดูโปรโมชั่น
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>