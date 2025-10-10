<?php
// session_start();
require '../config.php'; // เชื่อมต่อฐานข้อมูลด้วย PDO (แก้ไข path ตามจริง)
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ admin

// Admin Guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// เพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    
    if ($name && $price > 0 && $category_id > 0) {
        
        $imageName = null;

        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png'];

            if (in_array($file['type'], $allowed)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                $path = __DIR__ . '/../product_images/' . $imageName;
                move_uploaded_file($file['tmp_name'], $path);
            }
        }
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image)
        VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
        $_SESSION['success'] = "เพิ่มสินค้าเรียบร้อยแล้ว";
        header("Location: products.php");
        exit;
    } else {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง";
    }
}


// ลบสนิ คำ้ (ลบไฟลร์ปู ดว้ย)
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete']; // แคสต์เป็น int
    // 1) ดงึชอื่ ไฟลร์ปู จำก DB ก่อน
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $imageName = $stmt->fetchColumn(); // null ถ ้ำไม่มีรูป
    // 2) ลบใน DB ด ้วย Transaction
    try {
        $conn->beginTransaction();
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        // ใส่ flash message หรือ log ได ้ตำมต ้องกำร
        header("Location: products.php");
        exit;
    }
    // 3) ลบไฟล์รูปหลัง DB ลบส ำเร็จ
    if ($imageName) {
        $baseDir = realpath(__DIR__ . '/../product_images'); // โฟลเดอร์เก็บรูป
        $filePath = realpath($baseDir . '/' . $imageName);
        // กัน path traversal: ต ้องอยู่ใต้ $baseDir จริง ๆ
        if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath); // ใช ้@ กัน warning ถำ้ลบไมส่ ำเร็จ
        }
    }
    header("Location: products.php");
    exit;
}
    
    // ดึงรายการสินค้า
    $stmt = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า - ระบบผู้ดูแล</title>
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
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            background: linear-gradient(45deg, #427effff, #7db1edff);
            border: none;
        }
        .btn-gradient {
            background: linear-gradient(45deg, #427effff, #7db1edff);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.1);
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
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
            <div class="col-lg-11">
                <div class="card">
                    <div class="card-header text-white py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box-seam fs-3 me-3"></i>
                                <h2 class="mb-0 fw-bold">จัดการสินค้า</h2>
                            </div>
                            <a href="index.php" class="btn btn-light btn-lg rounded-pill">
                                <i class="bi bi-arrow-left me-2"></i>กลับหน้าผู้ดูแล
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Product Form -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-11">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>เพิ่มสินค้าใหม่</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data"  id="addProductForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ชื่อสินค้า <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-box"></i></span>
                                        <input type="text" name="product_name" class="form-control" placeholder="กรอกชื่อสินค้า" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">หมวดหมู่ <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                        <select name="category_id" class="form-select" required>
                                            <option value="">เลือกหมวดหมู่</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">ราคา <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                                        <span class="input-group-text">บาท</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">จำนวนคงเหลือ</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                        <input type="number" name="stock" class="form-control" placeholder="0" min="0" required>
                                        <span class="input-group-text">ชิ้น</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">รายละเอียดสินค้า</label>
                                    <textarea name="description" class="form-control" placeholder="กรอกรายละเอียดสินค้า..." rows="3"></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">รปูสินค้า (jpg, png)</label>
                                    <input type="file" name="product_image" class="form-control">
                                </div>


                                <div class="col-12">
                                    <button type="submit" name="add_product" class="btn btn-gradient btn-lg px-4">
                                        <i class="bi bi-plus-circle me-2"></i>เพิ่มสินค้า
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products List -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-11">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>รายการสินค้าทั้งหมด</h5>
                            <span class="badge bg-light text-primary fs-6"><?= count($products) ?> รายการ</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($products) === 0): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                                <h4 class="text-muted mt-3">ยังไม่มีสินค้าในระบบ</h4>
                                <p class="text-muted">เริ่มต้นโดยการเพิ่มสินค้าใหม่</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0 align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="py-3">#</th>
                                            <th class="py-3"><i class="bi bi-box me-2"></i>ชื่อสินค้า</th>
                                            <th class="py-3"><i class="bi bi-tags me-2"></i>หมวดหมู่</th>
                                            <th class="py-3"><i class="bi bi-currency-exchange me-2"></i>ราคา</th>
                                            <th class="py-3"><i class="bi bi-box-seam me-2"></i>คงเหลือ</th>
                                            <th class="py-3"><i class="bi bi-calendar me-2"></i>วันที่เพิ่ม</th>
                                            <th class="py-3 text-center"><i class="bi bi-gear me-2"></i>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $index => $p): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="bg-primary rounded d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px;">
                                                            <i class="bi bi-box"></i>
                                                        </div>
                                                        <div>
                                                            <strong><?= htmlspecialchars($p['product_name']) ?></strong>
                                                            <?php if ($p['description']): ?>
                                                                <br><small class="text-muted"><?= htmlspecialchars(substr($p['description'], 0, 50)) ?>...</small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($p['category_name']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success"><?= number_format($p['price'], 2) ?> ฿</span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $p['stock'] <= 5 ? 'bg-danger' : ($p['stock'] <= 20 ? 'bg-warning' : 'bg-success') ?>">
                                                        <?= $p['stock'] ?> ชิ้น
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_products.php?id=<?= $p['product_id'] ?>" 
                                                           class="btn btn-outline-warning btn-sm" 
                                                           data-bs-toggle="tooltip" title="แก้ไข">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger btn-sm delete-product" 
                                                                data-product-id="<?= $p['product_id'] ?>"
                                                                data-product-name="<?= htmlspecialchars($p['product_name']) ?>"
                                                                data-bs-toggle="tooltip" title="ลบ">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

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

        // Delete confirmation
        document.querySelectorAll('.delete-product').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: `ต้องการลบสินค้า "${productName}" หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ลบ',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `products.php?delete=${productId}`;
                    }
                });
            });
        });

        // Form validation
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            const productName = this.querySelector('[name="product_name"]').value.trim();
            const price = parseFloat(this.querySelector('[name="price"]').value);
            const categoryId = this.querySelector('[name="category_id"]').value;
            
            if (!productName || price <= 0 || !categoryId) {
                e.preventDefault();
                Swal.fire({
                    title: 'ข้อมูลไม่ครบถ้วน!',
                    text: 'กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    </script>
</body>
</html>