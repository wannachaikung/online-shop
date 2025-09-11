<?php

require '../config.php';
require 'auth_admin.php';

// ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // ป้องกันลบตัวเอง
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
    }
    header("Location: users.php");
    exit;
}
// ดึงข้อมูลสมาชิกทั้งหมด
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CDN sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    <div class="container-fluid py-5">
        <!-- Header -->
        <div class="row justify-content-center mb-4">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-warning text-white py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-people-fill fs-3"></i>
                                <h2 class="mb-0 fw-bold">จัดการสมาชิก</h2>
                            </div>
                            <a href="index.php" class="btn btn-light btn-lg rounded-pill">
                                <i class="bi bi-arrow-left me-2"></i>กลับหน้าผู้ดูแล
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-4">
                        <?php if (count($users) === 0): ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                                </div>
                                <h4 class="text-muted mb-3">ยังไม่มีสมาชิกในระบบ</h4>
                                <p class="text-muted">เมื่อมีผู้ใช้สมัครสมาชิก รายการจะแสดงที่นี่</p>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-lines-fill me-2 text-warning"></i>
                                    รายการสมาชิก (<span class="badge bg-warning"><?= count($users) ?></span> คน)
                                </h5>
                                <div class="text-muted small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    คลิกเพื่อจัดการข้อมูลสมาชิก
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="py-3">
                                                <i class="bi bi-person me-2"></i>ชื่อผู้ใช้
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-person-badge me-2"></i>ชื่อ-นามสกุล
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-envelope me-2"></i>อีเมล
                                            </th>
                                            <th class="py-3">
                                                <i class="bi bi-calendar me-2"></i>วันที่สมัคร
                                            </th>
                                            <th class="py-3 text-center">
                                                <i class="bi bi-gear me-2"></i>จัดการ
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                            style="width: 40px; height: 40px;">
                                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                        </div>
                                                        <span class="fw-semibold"><?= htmlspecialchars($user['username']) ?></span>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?= htmlspecialchars($user['email']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_user.php?id=<?= $user['user_id'] ?>"
                                                            class="btn btn-outline-warning btn-sm rounded-pill me-2"
                                                            data-bs-toggle="tooltip" title="แก้ไขข้อมูล">
                                                            <i class="bi bi-pencil-square"> edit</i>
                                                        </a>

                                                        <!-- <a href="users.php?delete=<?= $user['user_id'] ?>"
                                                            class="btn btn-outline-danger btn-sm rounded-pill"
                                                            onclick="return confirm('คุณต้องการลบสมาชิกนี้หรือไม่?')"
                                                            data-bs-toggle="tooltip" title="ลบสมาชิก"> -->

                                                        <form action="del_sweet.php" method="POST" style="display:inline;">
                                                            <input type="hidden" name="u_id" value="<?php echo $user['user_id']; ?>">
                                                            <button type="button" class="delete-button btn btn-danger btn-sm " data-user-id="<?php echo
                                                            $user['user_id']; ?>">Delete</button>
                                                        </form>
                                                        <!-- <i class="bi bi-trash"></i> -->
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary Card -->
                            <div class="row g-3 mt-4">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-people-fill fs-2 mb-2"></i>
                                            <h5>สมาชิกทั้งหมด</h5>
                                            <h3><?= count($users) ?> คน</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-person-check-fill fs-2 mb-2"></i>
                                            <h5>สมาชิกใหม่วันนี้</h5>
                                            <h3><?= count(array_filter($users, function ($user) {
                                                    return date('Y-m-d', strtotime($user['created_at'])) == date('Y-m-d');
                                                })) ?> คน</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <i class="bi bi-calendar-plus-fill fs-2 mb-2"></i>
                                            <h5>สมาชิกใหม่สัปดาห์นี้</h5>
                                            <h3><?= count(array_filter($users, function ($user) {
                                                    return strtotime($user['created_at']) >= strtotime('-7 days');
                                                })) ?> คน</h3>
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
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
    <script>
        // ฟังกช์ นั ส ำหรับแสดงกลอ่ งยนื ยัน SweetAlert2
        function showDeleteConfirmation(userId) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: 'คุณจะไม่สำมำรถเรียกคืนข ้อมูลกลับได ้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
            }).then((result) => {
                if (result.isConfirmed) {
                    // หำกผใู้ชย้นื ยัน ใหส้ ง่ คำ่ ฟอรม์ ไปยัง delete.php เพื่อลบข ้อมูล
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'del_sweet.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'u_id';
                    input.value = userId;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        // แนบตัวตรวจจับเหตุกำรณ์คลิกกับองค์ปุ ่่มลบทั ่ ้งหมดที่มีคลำส delete-button
        const deleteButtons = document.querySelectorAll('.delete-button');
        deleteButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                showDeleteConfirmation(userId);
            });
        });
    </script>

</body>

</html>