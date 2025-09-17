<?php
// session_start();

// TODO-1: เชื่อมต่อฐานข้อมูลด้วย PDO
require '../config.php'; // สมมติไฟล์เชื่อมต่อชื่อ db.php อยู่ในโฟลเดอร์ admin
require 'auth_admin.php'; // ตรวจสอบสิทธิ์ admin

// TODO-3: ตรวจว่ามี parameter id มาจริงไหม (ผ่าน GET)
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

// TODO-4: ดึงค่า id และ "แคสต์เป็น int" เพื่อความปลอดภัย
$user_id = (int)$_GET['id'];

// TODO-5: เตรียม/รัน SELECT (เฉพาะ role = 'member')
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// TODO-6: ถ้าไม่พบข้อมูล -> แสดงข้อความและ exit;
if (!$user) {
    echo "<h3>ไม่พบสมาชิก</h3>";
    exit;
}

// ========== เมื่อผู้ใช้กด Submit ฟอร์ม ==========
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO-7: รับค่า POST + trim
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['comfirm_password'];

    // TODO-8: ตรวจความครบถ้วน และตรวจรูปแบบ email
    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }

    // TODO-9: ตรวจสอบซ้ำ (username/email ชนกับคนอื่นที่ไม่ใช่ตัวเองหรือไม่)
    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้วในระบบ";
        }
    }

    // TODO-10: ถ้าไม่ซ้ำ -> ทำ UPDATE
    if (!$error) {
        $upd = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?");
        $upd->execute([$username, $full_name, $email, $user_id]);
        // TODO-11: redirect กลับหน้า users.php หลังอัปเดตสำเร็จ
        header("Location: users.php");
        exit;
    }
    // ตรวจรหัสผ่าน (กรณีต้องการเปลี่ยน)
    // เงื่อนไข: อนุญาตให้ปล่อยว่างได้ (คือไม่เปลี่ยนรหัสผ่าน)
    $updatePassword = false;
    $hashed = null;
    if (!$error && ($password !== '' || $confirm !== '')) {
        // TODO: ตรวจเงื่อนไข เช่น ยาว >= 6 และรหัสผ่านตรงกัน
        if (strlen($password) < 6) {
            $error = "รหัสผ่านต้องยาวอย่างน้อย 6 อักขระ";
        } elseif ($password !== $confirm) {
            $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน";
        } else {
            // แฮชรหัสผ่าน
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = true;
        }
    }
    // สร้าง SQL UPDATE แบบยืดหยุ่น (ถ้าไม่เปลี่ยนรหัสผ่านจะไม่แตะ field password)
    if (!$error) {
        if ($updatePassword) {
            // อัปเดตรวมรหัสผ่าน
            $sql = "UPDATE users
                SET username = ?, full_name = ?, email = ?, password = ?
                WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            // อัปเดตเฉพาะข้อมูลทั่วไป
            $sql = "UPDATE users
                SET username = ?, full_name = ?, email = ?
                WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }
        $upd = $conn->prepare($sql);
        $upd->execute($args);
        header("Location: users.php");
        exit;
    }
    // เขียน update แบบปกติ: ถ้าไม่ซ้ำ -> ทำ UPDATE
    // if (!$error) {
    // $upd = $conn->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?");
    // $upd->execute([$username, $full_name, $email, $user_id]);
    // // TODO-11: redirect กลับหน้า users.php หลังอัปเดตสำเร็จ
    // header("Location: users.php");
    // exit;
    // }


    // OPTIONAL: อัปเดตค่า $user เพื่อสะท้อนค่าที่ช่องฟอร์ม (หากมี error)
    $user['username'] = $username;
    $user['full_name'] = $full_name;
    $user['email'] = $email;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Kanit', sans-serif;
        }
        body {
            background: linear-gradient(135deg, rgba(108, 179, 241, 0.9), rgba(63, 125, 241, 0.9) 100%);
            min-height: 100vh;
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
        <div class="row justify-content-center">
            <div class="col-lg-8 mb-5">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i> แก้ไขข้อมูลสมาชิก</h2>
                        <p class="mb-0">จัดการข้อมูลของ <?= htmlspecialchars($user['username']) ?></p>
                    </div>
                    <div class="card-body p-5">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="post" class="row g-4">
                            <div class="col-12 text-center">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <i class="bi bi-person-fill text-primary" style="font-size: 4rem;"></i>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">ชื่อผู้ใช้</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ชื่อ-นามสกุล</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">อีเมล</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="col-12">
                                <h5 class="text-muted fw-bold">เปลี่ยนรหัสผ่าน <small class="text-muted">(ถ้าไม่ต้องการเปลี่ยน ให้เว้นว่าง)</small></h5>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">รหัสผ่านใหม่</label>
                                <div class="input-group">
                                     <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <div class="input-group">
                                     <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="confirm_password" class="form-control">
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between mt-5 ">
                                <a href="users.php" class="btn btn-secondary btn-lg rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i>กลับ</a>
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5"><i class="bi bi-check-circle me-2"></i>บันทึกการแก้ไข</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
