<?php
require '../config.php';
require 'auth_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    
    $oldImage = $_POST['old_image'] ?? null;
    $removeImage = !empty($_POST['remove_image']);
    $newImageName = $oldImage;

    if ($removeImage) {
        $newImageName = null;
    } elseif (!empty($_FILES['product_image']['name'])) {
        $file = $_FILES['product_image'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_mime_type = mime_content_type($file['tmp_name']);

        if (in_array($file_mime_type, $allowed_mime_types, true) && $file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newImageName = 'product_' . time() . '.' . $ext;
            $uploadDir = realpath(__DIR__ . '/../product_images');
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                // If move fails, revert to old image
                $newImageName = $oldImage;
            }
        }
    }

    // Update database
    $sql = "UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $description, $price, $stock, $category_id, $newImageName, $product_id]);

    // Delete old file from disk if the image was changed
    if (!empty($oldImage) && $oldImage !== $newImageName) {
        $baseDir = realpath(__DIR__ . '/../product_images');
        $filePath = $baseDir . DIRECTORY_SEPARATOR . $oldImage;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    $_SESSION['success'] = "อัปเดตข้อมูลสินค้าเรียบร้อยแล้ว";
    header("Location: products.php");
    exit;
}

// Fetch existing product data
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<h3>ไม่พบข้อมูลสินค้า</h3>";
    exit;
}

// Fetch all categories for the dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Prepare current image path
$current_img_path = !empty($product['image'])
    ? '../product_images/' . rawurlencode($product['image'])
    : '../product_images/no-image.jpg';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า - <?= htmlspecialchars($product['product_name']) ?> - Online Shop</title>
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
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
        }
        .form-control, .form-select {
            border-radius: .5rem;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4285f4;
            box-shadow: 0 0 0 0.2rem rgba(66, 133, 244, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4285f4, #34a853);
            border: none;
            border-radius: .5rem;
            font-weight: 500;
            padding: 0.75rem 2rem;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #3367d6, #2e7d32);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-light {
            background: rgba(255,255,255,0.9);
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            font-weight: 500;
        }
        .btn-light:hover {
            background: #ffffff;
            transform: translateY(-1px);
        }
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .form-check-input:checked {
            background-color: #4285f4;
            border-color: #4285f4;
        }
        .image-preview-container {
            position: relative;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .no-image-placeholder {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px dashed #dee2e6;
            border-radius: .75rem;
            height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .upload-section {
            background: #f8f9fa;
            border-radius: .75rem;
            padding: 1.5rem;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }
        .upload-section:hover {
            border-color: #4285f4;
            background: rgba(66, 133, 244, 0.05);
        }
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            font-weight: 600;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="../index.php">
                <i class="bi bi-cart3 fs-4"></i>
                <span>Online Shop</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-speedometer2 me-1"></i>แดชบอร์ด</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php"><i class="bi bi-box-seam me-1"></i>จัดการสินค้า</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php"><i class="bi bi-tags me-1"></i>หมวดหมู่</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right me-1"></i>ออกจากระบบ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">แดชบอร์ด</a></li>
                <li class="breadcrumb-item"><a href="products.php" class="text-decoration-none">จัดการสินค้า</a></li>
                <li class="breadcrumb-item active" aria-current="page">แก้ไขสินค้า</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="text-center mb-5">
            <h1 class="page-title">แก้ไขข้อมูลสินค้า</h1>
            <p class="page-subtitle"><?= htmlspecialchars($product['product_name']) ?></p>
        </div>

        <form method="post" enctype="multipart/form-data">
            <div class="row g-5">
                <!-- Product Image Column -->
                <div class="col-lg-6">
                    <div class="image-preview-container">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= htmlspecialchars($current_img_path) ?>" 
                                 alt="รูปปัจจุบัน" 
                                 class="product-image-main">
                        <?php else: ?>
                            <div class="no-image-placeholder">
                                <div class="text-center">
                                    <i class="bi bi-image fs-1 mb-3 d-block"></i>
                                    ไม่มีรูปภาพ
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Upload Section -->
                    <div class="upload-section mt-4">
                        <div class="text-center mb-3">
                            <i class="bi bi-cloud-upload fs-2 text-primary"></i>
                            <h5 class="mt-2">อัปโหลดรูปใหม่</h5>
                            <p class="text-muted">เลือกไฟล์รูปภาพ (JPG, PNG, GIF)</p>
                        </div>
                        <input type="file" name="product_image" class="form-control mb-3" accept="image/*">
                        
                        <?php if (!empty($product['image'])): ?>
                        <div class="form-check form-switch text-center">
                            <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                            <label class="form-check-label fw-semibold text-danger" for="remove_image">
                                <i class="bi bi-trash me-1"></i>ลบรูปภาพปัจจุบัน
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">
                </div>

                <!-- Product Form Column -->
                <div class="col-lg-6">
                    <div class="product-details-card h-100">
                        <div class="row g-4">
                            <div class="col-12">
                                <label for="product_name" class="form-label">
                                    <i class="bi bi-tag me-1"></i>ชื่อสินค้า
                                </label>
                                <input type="text" id="product_name" name="product_name" 
                                       class="form-control form-control-lg" 
                                       value="<?= htmlspecialchars($product['product_name']) ?>" 
                                       placeholder="กรอกชื่อสินค้า" required>
                            </div>

                            <div class="col-md-6">
                                <label for="price" class="form-label">
                                    <i class="bi bi-currency-dollar me-1"></i>ราคา
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="number" id="price" step="0.01" name="price" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($product['price']) ?>" 
                                           placeholder="0.00" required>
                                    <span class="input-group-text">บาท</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="stock" class="form-label">
                                    <i class="bi bi-boxes me-1"></i>จำนวนในคลัง
                                </label>
                                <input type="number" id="stock" name="stock" 
                                       class="form-control form-control-lg" 
                                       value="<?= htmlspecialchars($product['stock']) ?>" 
                                       placeholder="0" required>
                            </div>

                            <div class="col-12">
                                <label for="category_id" class="form-label">
                                    <i class="bi bi-grid-3x3-gap me-1"></i>หมวดหมู่
                                </label>
                                <select id="category_id" name="category_id" class="form-select form-select-lg" required>
                                    <option value="">-- เลือกหมวดหมู่ --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $product['category_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['category_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">
                                    <i class="bi bi-card-text me-1"></i>รายละเอียดสินค้า
                                </label>
                                <textarea id="description" name="description" 
                                          class="form-control" rows="6" 
                                          placeholder="กรอกรายละเอียดสินค้า..."><?= htmlspecialchars($product['description']) ?></textarea>
                            </div>

                            <div class="col-12 mt-5">
                                <div class="d-grid gap-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-save-fill me-2"></i>บันทึกการเปลี่ยนแปลง
                                    </button>
                                    <a href="products.php" class="btn btn-light btn-lg">
                                        <i class="bi bi-arrow-left me-2"></i>ยกเลิก
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview uploaded image
        document.querySelector('input[name="product_image"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageContainer = document.querySelector('.image-preview-container');
                    imageContainer.innerHTML = '<img src="' + e.target.result + '" alt="ตัวอย่างรูปใหม่" class="product-image-main">';
                }
                reader.readAsDataURL(file);
            }
        });

        // Toggle remove image checkbox behavior
        document.getElementById('remove_image')?.addEventListener('change', function() {
            const imageContainer = document.querySelector('.image-preview-container');
            const fileInput = document.querySelector('input[name="product_image"]');
            
            if (this.checked) {
                imageContainer.innerHTML = `
                    <div class="no-image-placeholder">
                        <div class="text-center">
                            <i class="bi bi-image-fill fs-1 mb-3 d-block text-danger"></i>
                            รูปภาพจะถูกลบ
                        </div>
                    </div>
                `;
                fileInput.value = '';
            } else {
                // Restore original image if unchecked and no new file selected
                if (!fileInput.files.length) {
                    location.reload(); // Simple way to restore original image
                }
            }
        });
    </script>
</body>
</html>