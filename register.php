<?php
 require_once 'config.php';
    $error =[]; //array to hold error messages

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        //รับค่าจากฟอร์ม
        $username = trim($_POST['username']);
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['e-mail']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        //  ตรวจสอบว่ากรอกข้อมูลมาครบหรือไม่ (empty)
        if(empty($username) || empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
            $error[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
            // ตรวจสอบว่าอีเมลถูกต้องหรือไม่ (filter_var)
        }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error[] ="กรุณากรอกอีเมลให้ถูกต้อง";
        //ตรวจสอบว่ารหัสผ่านและยืนยันว่ารหัสผ่านตรงกันหรือไม่
        } elseif ($password !== $confirm_password) {
            $errors[] = "รหัสผ่านไม่ตรงกัน";
        } else {
        //ตรวจสอบว่าชื่อผู้ใชหรืออีเมลถูกใช้ไปแล้วหรือไม่
            $sql ="SELECT * FROM users WHERE username = ? or email = ? ";
            $stmt=$conn->prepare($sql);
            $stmt->execute([$username,$email]);
            if($stmt->rowCount() > 0){
                $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้ไปแล้ว";
            }
        }

        if(empty($error)) { //ถ้าไม่มี error ใดๆ
            //นำข้อมูลบันทึกลงฐานข้อมูล
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users(username,full_name,email,password,role) VALUES(?, ?, ?, ?, 'member' )";
            $stmt=$conn->prepare($sql);
            $stmt->execute([$username,$fullname,$email,$hashedPassword]);
            //ถ้าบันทึกสำเร็จให้เปลี่ยนเส้นทางไปหน้าลอ็อคอิน
            header("Location: login.php?register=success");
            exit();//หยุดการทำงานของสคริปต์หลังจากเปลี่ยนเส้นทาง
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        }
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

<body style="background: linear-gradient(135deg, #7dc1fdff 0%, #2551b9ff 100%); min-height: 100vh;">
    <div class="container-fluid d-flex align-items-center justify-content-center min-vh-100 py-4">
        <div class="row w-100" style="max-width: 900px;">
            <div class="col-12">
                <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                    <div class="row g-0">
                        <!-- Left Panel -->
                        <div class="col-lg-5 text-white text-center d-flex flex-column justify-content-center p-5" 
                             style="background: linear-gradient(45deg, rgba(108, 179, 241, 0.9), rgba(63, 125, 241, 0.9));">
                            <div class="mb-4">
                                <i class="fas fa-shopping-cart display-1 mb-3"></i>
                            </div>
                            <h2 class="fw-bold mb-3" style="font-size: 2.2rem;">ONLINE SHOP</h2>
                            <p class="lead" style="opacity: 0.95;"> </p>
                        </div>
                        
                        <!-- Right Panel -->
                        <div class="col-lg-7 p-5">
                            <h2 class="text-center mb-4 fw-semibold text-dark">สมัครสมาชิก</h2>

                            <?php if (!empty($error)): // ถ้ามีข้อผิดพลาด ให้แสดงข้อความ ?>
                                <div class="alert alert-danger">
                                        <ul>
                                    <?php foreach ($error as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                            <!-- <!—ใช ้ htmlspecialchars เพื่อป้องกัน XSS -->
                                            <!-- < ? = คือ short echo tag ?> -->
                                            <!-- ถ้าเขียนเต็ม จะได ้แบบด้านล่ำง -->
                                            <?php // echo "<li>" . htmlspecialchars($e) . "</li>"; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                </div>
                                <?php endif; ?>

                            <form action="" method="post">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label fw-medium text-secondary">ชื่อผู้ใช้</label>
                                        <input type="text" name="username" id="username" class="form-control" 
                                               placeholder="ชื่อผู้ใช้"  value="<?= isset($_POST['username']) ?
                                               htmlspecialchars($_POST['username']) : '' ?>"  
                                               style="border-radius: 8px; border: 2px solid #e2e8f0; background: #f8fafc; padding: 10px 12px; font-size: 14px;" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fullname" class="form-label fw-medium text-secondary">ชื่อ-นามสกุล</label>
                                        <input type="text" name="fullname" id="fullname" class="form-control" 
                                               placeholder="ชื่อ-นามสกุล" value="<?= isset($_POST['fullname']) ?
                                               htmlspecialchars($_POST['fullname']) : '' ?>"
                                               style="border-radius: 8px; border: 2px solid #e2e8f0; background: #f8fafc; padding: 10px 12px; font-size: 14px;" required>
                                    </div>
                                </div>
                                        
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="e-mail" class="form-label fw-medium text-secondary">อีเมล</label>
                                        <input type="email" name="e-mail" id="e-mail" class="form-control" 
                                               placeholder="e-mail"  value=" <?= isset($_POST['e-mail']) ?
                                               htmlspecialchars($_POST['e-mail']) : '' ?>"
                                               style="border-radius: 8px; border: 2px solid #e2e8f0; background: #f8fafc; padding: 10px 12px; font-size: 14px;" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label fw-medium text-secondary">รหัสผ่าน</label>
                                        <input type="password" name="password" id="password" class="form-control" 
                                               placeholder="รหัสผ่าน" 
                                               style="border-radius: 8px; border: 2px solid #e2e8f0; background: #f8fafc; padding: 10px 12px; font-size: 14px;" required>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-medium text-secondary">ยืนยันรหัสผ่าน</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                                           placeholder="ยืนยันรหัสผ่าน" 
                                           style="border-radius: 8px; border: 2px solid #e2e8f0; background: #f8fafc; padding: 10px 12px; font-size: 14px;" required>
                                </div>
                                
                                <button type="submit" class="btn w-100 text-white fw-semibold mb-3" 
                                        style="background: linear-gradient(135deg, rgba(108, 179, 241, 0.9), rgba(63, 125, 241, 0.9) 100%); border: none; border-radius: 8px; padding: 10px;">
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