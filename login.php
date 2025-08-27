<?php
    session_start(); //start the session to use session variables
    require_once 'config.php';
    $error = '';
     
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        //รับค่าจากฟอร์ม
        $username_or_email = trim($_POST['username_or_email']);
        $password = $_POST['password'];

        //ตรวจสอบต่าที่รับจากฟอร์ม ไปตรวจสอบว่ามีข้อมูลตรงกับใน db หรือไม่
        $sql ="SELECT * FROM users WHERE (username = ? OR email = ?)";
        $stmt=$conn->prepare($sql);
        $stmt->execute([$username_or_email,$username_or_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); 

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    if($user['role'] === 'admin'){  
        header("Location: admin/index.php");
    }else{
        header("Location: index.php");
    }
    exit();
}else{
    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
}
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body  style="background: linear-gradient(135deg, #7dc1fdff 0%, #2551b9ff 100%); min-height: 100vh;">


<?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
<div class="alert alert-success">สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>



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

                        <!-- right panel -->
                <div class="col-lg-7 p-5">
                            <h2 class="text-center mb-4 fw-semibold text-dark">ล็อคอิน</h2>

                            

    <form method="post" class="row g-3">
        <div class="col-md-12">
            <label for="username_or_email" class="form-label">ชื่อผู้ใช้ หรือ อีเมล</label>
            <input type="text" name="username_or_email" id="username_or_email" class="form-control" placeholder="ชื่อผู้ใช้ หรือ อีเมล" required>
        </div>
        
        
        <div class="col-md-12">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="รหัสผ่าน" required>
        </div>


        <div class="col-6">
             <button type="submit" class="btn w-100 text-white fw-semibold mb-3" 
                    style="background: linear-gradient(135deg, rgba(108, 179, 241, 0.9), rgba(63, 125, 241, 0.9) 100%); border: none; border-radius: 8px; padding: 10px;">
                    เข้าสู่ระบบ</button>
            <a href="register.php" class="btn btn-link">สมัครสมาชิก</a>
        </div>
    </form>
     </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>