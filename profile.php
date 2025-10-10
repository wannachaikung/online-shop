<?php
session_start();
require 'config.php';
require 'function.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// --- ตรรกะการอัปเดตข้อมูลโปรไฟล์ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $user = get_user_by_id($conn, $user_id); // ดึงข้อมูลเก่ามาเปรียบเทียบ
    
    // รับข้อมูลจากฟอร์ม
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($username) || empty($full_name) || empty($email)) {
        $errors[] = "กรุณากรอกข้อมูล ชื่อผู้ใช้, ชื่อ-นามสกุล และอีเมลให้ครบถ้วน";
    }

    // ตรวจสอบ Username ซ้ำ (ถ้ามีการเปลี่ยนแปลง)
    if ($username !== $user['username']) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "ชื่อผู้ใช้นี้ถูกใช้งานแล้ว";
        }
    }
    
    // ตรวจสอบอีเมลซ้ำ (ถ้ามีการเปลี่ยนแปลง)
    if ($email !== $user['email']) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = "อีเมลนี้ถูกใช้งานแล้ว";
        }
    }

    // ตรวจสอบการเปลี่ยนรหัสผ่าน
    $new_hashed_password = null;
    if (!empty($new_password)) {
        if (empty($current_password) || empty($confirm_password)) {
             $errors[] = "กรุณากรอกรหัสผ่านทุกช่องเพื่อทำการเปลี่ยนแปลง";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "รหัสผ่านเดิมไม่ถูกต้อง";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "รหัสผ่านใหม่และการยืนยันไม่ตรงกัน";
        } else {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    // --- อัปเดตข้อมูลลง DB ---
    if (empty($errors)) {
        if ($new_hashed_password !== null) {
            // อัปเดตข้อมูลทั้งหมดรวมถึงรหัสผ่าน
            $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$username, $full_name, $email, $new_hashed_password, $user_id]);
        } else {
            // อัปเดตเฉพาะข้อมูลทั่วไป
            $stmt = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$username, $full_name, $email, $user_id]);
        }
        
        // อัปเดต Session เพื่อให้ชื่อที่ Navbar เปลี่ยนตาม
        $_SESSION['username'] = $username;
        
        $success = "บันทึกข้อมูลส่วนตัวเรียบร้อยแล้ว!";
    }
}

// ดึงข้อมูลผู้ใช้ล่าสุดเสมอเพื่อแสดงผล
$user = get_user_by_id($conn, $user_id);

// --- ดึงข้อมูล Wishlist ของผู้ใช้ ---
$stmt = $conn->prepare("
    SELECT w.wishlist_id, w.product_id, p.product_name, p.price, p.image
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bs-primary-rgb: 59, 130, 246;
            --bs-body-font-family: 'Kanit', sans-serif;
            --bs-body-bg: #F0F4F8;
        }
        .profile-avatar {
            width: 120px; height: 120px; border-radius: 50%; object-fit: cover;
            border: 4px solid var(--bs-primary); padding: 4px; background-color: white; margin-top: -60px;
        }
        .card-header-bg {
            height: 150px;
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 1) 0%, rgba(var(--bs-primary-rgb), 0.7) 100%);
        }
        .section-title-underline {
            font-weight: 600; border-bottom: 2px solid var(--bs-primary);
            padding-bottom: 0.5rem; display: inline-block;
        }
        .footer a { color: #adb5bd; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #ffffff; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'templates/header.php'; ?>

    <main class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header-bg rounded-top"></div>
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-circle" style="font-size: 100px; color: #6c757d;"></i>
                            <h2 class="h4 mt-3 mb-1 fw-bold"><?= htmlspecialchars($user['full_name']) ?></h2>
                            <p class="text-muted">@<?= htmlspecialchars($user['username']) ?></p>
                        </div>

                        <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button">
                                    <i class="bi bi-person-fill me-2"></i>ข้อมูลส่วนตัว
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="wishlist-tab" data-bs-toggle="tab" data-bs-target="#wishlist-tab-pane" type="button">
                                    <i class="bi bi-heart-fill me-2"></i>สินค้าที่ถูกใจ <span class="badge bg-danger rounded-pill"><?= count($wishlist_items) ?></span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-4" id="myTabContent">
                            <div class="tab-pane fade show active" id="profile-tab-pane" role="tabpanel">
                                <?php if (!empty($errors)): ?><div class="alert alert-danger"><?php foreach ($errors as $e) echo "<p class='mb-0'>".htmlspecialchars($e)."</p>"; ?></div><?php endif; ?>
                                <?php if (!empty($success)): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                                <form method="post" action="profile.php">
                                    <input type="hidden" name="update_profile" value="1">
                                    <h5 class="mb-3 section-title-underline">ข้อมูลส่วนตัว</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4"><label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label><input type="text" name="username" id="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>"></div>
                                        <div class="col-md-4"><label for="full_name" class="form-label">ชื่อ-นามสกุล</label><input type="text" name="full_name" id="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>"></div>
                                        <div class="col-md-4"><label for="email" class="form-label">อีเมล</label><input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>"></div>
                                    </div>
                                    <hr class="my-4">
                                    <h5 class="mb-3 section-title-underline">เปลี่ยนรหัสผ่าน (ไม่จำเป็น)</h5>
                                    <div class="row g-3">
                                        <div class="col-md-12"><label for="current_password" class="form-label">รหัสผ่านเดิม</label><input type="password" name="current_password" id="current_password" class="form-control"></div>
                                        <div class="col-md-6"><label for="new_password" class="form-label">รหัสผ่านใหม่</label><input type="password" name="new_password" id="new_password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร"></div>
                                        <div class="col-md-6"><label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label><input type="password" name="confirm_password" id="confirm_password" class="form-control"></div>
                                    </div>
                                    <div class="d-grid mt-4"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-floppy-fill me-2"></i>บันทึกการเปลี่ยนแปลง</button></div>
                                </form>
                            </div>

                            <div class="tab-pane fade" id="wishlist-tab-pane" role="tabpanel">
                                <?php if (empty($wishlist_items)): ?>
                                    <div class="alert alert-info">คุณยังไม่มีสินค้าที่ถูกใจ</div>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($wishlist_items as $item): 
                                            $wimg = !empty($item['image']) ? 'product_images/' . rawurlencode($item['image']) : 'product_images/no-image.jpg';
                                        ?>
                                        <div class="list-group-item d-flex gap-3 py-3" id="wishlist-item-<?= (int)$item['product_id'] ?>">
                                            <img src="<?= htmlspecialchars($wimg) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                                            <div class="flex-fill">
                                                <a href="product_detail.php?id=<?= (int)$item['product_id'] ?>" class="text-decoration-none">
                                                    <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                </a>
                                                <div class="text-muted">ราคา: ฿<?= number_format((float)$item['price'], 2) ?></div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <button type="button" class="btn btn-outline-danger wishlist-btn" data-product-id="<?= (int)$item['product_id'] ?>">
                                                    <i class="bi bi-trash"></i> ลบ
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Attach handler to wishlist buttons: remove item with spinner and fade-out
        document.querySelectorAll('.wishlist-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const productId = this.dataset.productId;
                const itemElement = document.getElementById('wishlist-item-' + productId);

                // disable button and show spinner
                this.disabled = true;
                const originalHtml = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังลบ...';

                const formData = new FormData();
                formData.append('product_id', productId);

                fetch('wishlist_action.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.action === 'removed') {
                            if (itemElement) {
                                itemElement.style.transition = 'opacity 250ms ease, max-height 300ms ease, margin 300ms ease';
                                itemElement.style.opacity = '0';
                                itemElement.style.maxHeight = '0';
                                itemElement.style.overflow = 'hidden';
                                setTimeout(() => itemElement.remove(), 320);
                            }
                        } else if (data.action === 'added') {
                            // If toggled on (unlikely from profile delete), you can add UI feedback here
                            alert('สินค้าถูกเพิ่มในรายการที่ถูกใจ');
                        }
                    } else {
                        alert(data.message || 'เกิดข้อผิดพลาด กรุณาลองอีกครั้ง');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                })
                .finally(() => {
                    // restore button state if the element still exists
                    if (document.body.contains(this)) {
                        this.disabled = false;
                        this.innerHTML = originalHtml;
                    }
                });
            });
        });
    });
    </script>
</body>
</html>