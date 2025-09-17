<?php
// session_start();
require '../config.php'; // เชื่อมต่อฐานข้อมูลด้วย PDO
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ admin

// ตรวจสอบสิทธิ์ (Admin Guard)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        // ตรวจสอบว่าหมวดหมู่ซ้ำหรือไม่
        $check = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ?");
        $check->execute([$category_name]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['error'] = "หมวดหมู่นี้มีอยู่แล้วในระบบ";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->execute([$category_name]);
            $_SESSION['success'] = "เพิ่มหมวดหมู่สำเร็จ";
        }
        header("Location: category.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกชื่อหมวดหมู่";
    }
}

// ลบหมวดหมู่
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    // ตรวจสอบว่ามีสินค้าที่ใช้หมวดหมู่นี้หรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $productCount = $stmt->fetchColumn();
    if ($productCount > 0) {
        $_SESSION['error'] = "ไม่สามารถลบหมวดหมู่นี้ได้ เนื่องจากยังมีสินค้าในหมวดหมู่นี้อยู่ $productCount รายการ";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
    }
    header("Location: category.php");
    exit;
}

// แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        // ตรวจสอบว่าชื่อใหม่ซ้ำกับหมวดหมู่อื่นหรือไม่
        $check = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ? AND category_id != ?");
        $check->execute([$category_name, $category_id]);
        if ($check->fetchColumn() > 0) {
            $_SESSION['error'] = "ชื่อหมวดหมู่นี้มีอยู่แล้วในระบบ";
        } else {
            $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
            $stmt->execute([$category_name, $category_id]);
            $_SESSION['success'] = "แก้ไขชื่อหมวดหมู่สำเร็จ";
        }
        header("Location: category.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกชื่อใหม่";
    }
}

// ดึงหมวดหมู่ทั้งหมดพร้อมจำนวนสินค้า
$stmt = $conn->query("
    SELECT c.*, 
           COALESCE(COUNT(p.product_id), 0) as product_count
    FROM categories c 
    LEFT JOIN products p ON c.category_id = p.category_id 
    GROUP BY c.category_id 
    ORDER BY c.category_id ASC
");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหมวดหมู่ - ระบบผู้ดูแล</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            font-family: 'Kanit', sans-serif;
        }
        body {
            background: linear-gradient(135deg, rgba(108, 179, 241, 0.9), rgba(63, 125, 241, 0.9) 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(45deg, #3b3838ff, #414141ff);
            border: none;
        }
        .btn-gradient {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            transition: all 0.3s;
        }
        .btn-gradient:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
            color: white;
            transform: translateY(-2px);
        }
        .category-card {
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .category-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.2);
        }
        .edit-form {
            display: none;
        }
        .product-count-badge {
            position: absolute;
            top: -5px;
            right: -5px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/68s_onlineshop/index.php">
            <i class="bi bi-cart3 fs-4"></i>
            <span>Online Shop</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="/68s_onlineshop/index.php">
                        <i class="bi bi-house"></i>หน้าหลัก
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
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="/68s_onlineshop/cart.php">
                            <i class="bi bi-bag"></i>ตะกร้าสินค้า
                        </a>
                    </li>
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
    <div class="container-fluid pt-4">
        <!-- Header -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header text-white py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-tags fs-3 me-3"></i>
                                <h2 class="mb-0 fw-bold ">จัดการหมวดหมู่สินค้า</h2>
                            </div>
                            <a href="index.php" class="btn btn-light btn-lg rounded-pill">
                                <i class="bi bi-arrow-left me-2"></i>กลับหน้าผู้ดูแล
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Category Form -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>เพิ่มหมวดหมู่ใหม่</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" id="addCategoryForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                        <input type="text" name="category_name" class="form-control" placeholder="กรอกชื่อหมวดหมู่ใหม่..." required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" name="add_category" class="btn btn-gradient btn-lg w-100">
                                        <i class="bi bi-plus-circle me-2"></i>เพิ่มหมวดหมู่
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>รายการหมวดหมู่ทั้งหมด</h5>
                            <span class="badge bg-light text-primary fs-6"><?= count($categories) ?> หมวดหมู่</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($categories) === 0): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-tags text-muted" style="font-size: 4rem;"></i>
                                <h4 class="text-muted mt-3">ยังไม่มีหมวดหมู่ในระบบ</h4>
                                <p class="text-muted">เริ่มต้นโดยการเพิ่มหมวดหมู่ใหม่</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($categories as $cat): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card category-card h-100 position-relative">
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white position-relative" style="width: 60px; height: 60px;">
                                                        <i class="bi bi-tag fs-3"></i>
                                                        <?php if ($cat['product_count'] > 0): ?>
                                                            <span class="badge bg-danger product-count-badge"><?= $cat['product_count'] ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <!-- Display Mode -->
                                                <div class="display-mode-<?= $cat['category_id'] ?>">
                                                    <h5 class="card-title mb-2"><?= htmlspecialchars($cat['category_name']) ?></h5>
                                                    <p class="text-muted small mb-3">
                                                        <i class="bi bi-box me-1"></i>
                                                        <?= $cat['product_count'] ?> สินค้า
                                                    </p>
                                                    
                                                    <div class="btn-group w-100" role="group">
                                                        <button type="button" class="btn btn-outline-warning btn-sm edit-btn" data-category-id="<?= $cat['category_id'] ?>">
                                                            <i class="bi bi-pencil me-1"></i>แก้ไข
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm delete-category" 
                                                                data-category-id="<?= $cat['category_id'] ?>"
                                                                data-category-name="<?= htmlspecialchars($cat['category_name']) ?>"
                                                                data-product-count="<?= $cat['product_count'] ?>">
                                                            <i class="bi bi-trash me-1"></i>ลบ
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Edit Mode -->
                                                <div class="edit-mode-<?= $cat['category_id'] ?>" style="display: none;">
                                                    <form method="post" class="edit-form-<?= $cat['category_id'] ?>">
                                                        <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                        <div class="mb-3">
                                                            <input type="text" name="new_name" class="form-control" 
                                                                   value="<?= htmlspecialchars($cat['category_name']) ?>" required>
                                                        </div>
                                                        <div class="btn-group w-100" role="group">
                                                            <button type="submit" name="update_category" class="btn btn-success btn-sm">
                                                                <i class="bi bi-check me-1"></i>บันทึก
                                                            </button>
                                                            <button type="button" class="btn btn-secondary btn-sm cancel-edit" data-category-id="<?= $cat['category_id'] ?>">
                                                                <i class="bi bi-x me-1"></i>ยกเลิก
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Summary Cards -->
                            <div class="row g-3 mt-4">
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-tags-fill fs-2 mb-2"></i>
                                            <h5>หมวดหมู่ทั้งหมด</h5>
                                            <h3><?= count($categories) ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-box-fill fs-2 mb-2"></i>
                                            <h5>สินค้าทั้งหมด</h5>
                                            <h3><?= array_sum(array_column($categories, 'product_count')) ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-exclamation-triangle-fill fs-2 mb-2"></i>
                                            <h5>หมวดหมู่ว่าง</h5>
                                            <h3><?= count(array_filter($categories, function($cat) { return $cat['product_count'] == 0; })) ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // SweetAlert2 for success/error messages
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                title: 'สำเร็จ!',
                text: '<?= $_SESSION['success'] ?>',
                icon: 'success',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#667eea'
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                title: 'เกิดข้อผิดพลาด!',
                text: '<?= $_SESSION['error'] ?>',
                icon: 'error',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#dc3545'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // Edit category functionality
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                document.querySelector(`.display-mode-${categoryId}`).style.display = 'none';
                document.querySelector(`.edit-mode-${categoryId}`).style.display = 'block';
            });
        });

        document.querySelectorAll('.cancel-edit').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                document.querySelector(`.display-mode-${categoryId}`).style.display = 'block';
                document.querySelector(`.edit-mode-${categoryId}`).style.display = 'none';
            });
        });

        // Delete confirmation
        document.querySelectorAll('.delete-category').forEach(button => {
            button.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-category-id');
                const categoryName = this.getAttribute('data-category-name');
                const productCount = parseInt(this.getAttribute('data-product-count'));
                
                if (productCount > 0) {
                    Swal.fire({
                        title: 'ไม่สามารถลบได้!',
                        text: `หมวดหมู่ "${categoryName}" ยังมีสินค้าอยู่ ${productCount} รายการ`,
                        icon: 'warning',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#ffc107'
                    });
                } else {
                    Swal.fire({
                        title: 'คุณแน่ใจหรือไม่?',
                        text: `ต้องการลบหมวดหมู่ "${categoryName}" หรือไม่?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'ลบ',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `category.php?delete=${categoryId}`;
                        }
                    });
                }
            });
        });

        // Form validation
        document.getElementById('addCategoryForm').addEventListener('submit', function(e) {
            const categoryName = this.querySelector('[name="category_name"]').value.trim();
            
            if (!categoryName) {
                e.preventDefault();
                Swal.fire({
                    title: 'ข้อมูลไม่ครบถ้วน!',
                    text: 'กรุณากรอกชื่อหมวดหมู่',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                });
            }
        });

        // Edit form validation
        document.querySelectorAll('[class^="edit-form-"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                const newName = this.querySelector('[name="new_name"]').value.trim();
                
                if (!newName) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'ข้อมูลไม่ครบถ้วน!',
                        text: 'กรุณากรอกชื่อหมวดหมู่ใหม่',
                        icon: 'warning',
                        confirmButtonText: 'ตกลง'
                    });
                }
            });
        });
    </script>
</body>
</html>