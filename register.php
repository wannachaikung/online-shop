<?php
 require_once 'config.php';
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        //รับค่าจากฟอร์ม
        $username = trim($_POST['username']);
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['e-mail']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        
        //นำข้อมูลบันทึกลงฐานข้อมูล
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users(username,full_name,email,password,role) VALUES(?, ?, ?, ?, 'admin' )";
        $stmt=$conn->prepare($sql);
        $stmt->execute([$username,$fullname,$email,$hashedPassword]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-4">
        <div class="row w-100" style="max-width: 900px;">
            <div class="col-12">
                <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                    <div class="row g-0">
                        <!-- Left Panel -->
                        <div class="col-lg-5 text-white text-center d-flex flex-column justify-content-center p-5" 
                             style="background: linear-gradient(45deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));">
                            <div class="mb-4">
                                <i class="fas fa-shopping-cart display-1 mb-3"></i>
                            </div>
                            <h2 class="fw-bold mb-3" style="font-size: 2.2rem;">ONLINE SHOP</h2>
                            <p class="lead" style="opacity: 0.95;">   </p>
                        </div>
                        
                        <!-- Right Panel -->
                        <div class="col-lg-7 p-5">
                            <h2 class="text-center mb-4 fw-semibold text-dark">สมัครสมาชิก</h2>
                            <form action="" method="post">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label fw-medium text-secondary">ชื่อผู้ใช้</label>
                                        <input type="text" name="username" id="username" class="form-control form-control-lg" 
                                               placeholder="ชื่อผู้ใช้" required 
                                               style="border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fullname" class="form-label fw-medium text-secondary">ชื่อ-นามสกุล</label>
                                        <input type="text" name="fullname" id="fullname" class="form-control form-control-lg" 
                                               placeholder="ชื่อ-นามสกุล" required 
                                               style="border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="e-mail" class="form-label fw-medium text-secondary">อีเมล</label>
                                        <input type="email" name="e-mail" id="e-mail" class="form-control form-control-lg" 
                                               placeholder="e-mail" required 
                                               style="border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label fw-medium text-secondary">รหัสผ่าน</label>
                                        <input type="password" name="password" id="password" class="form-control form-control-lg" 
                                               placeholder="รหัสผ่าน" required 
                                               style="border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-medium text-secondary">ยืนยันรหัสผ่าน</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-lg" 
                                           placeholder="ยืนยันรหัสผ่าน" required 
                                           style="border-radius: 12px; border: 2px solid #e2e8f0; background: #f8fafc;">
                                </div>
                                
                                <button type="submit" class="btn btn-lg w-100 text-white fw-semibold mb-3" 
                                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px; padding: 15px;">
                                    สมัครสมาชิก
                                </button>
                                
                                <div class="text-center text-secondary">
                                    มีบัญชีอยู่แล้ว? <a href="login.php" class="text-decoration-none fw-medium" style="color: #667eea;">เข้าสู่ระบบ</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>