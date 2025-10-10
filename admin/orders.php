<?php
session_start();
require '../config.php';
require '../function.php';   // ดึงฟังก์ชันที่เก็บไว้

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// ดึงคำสั่งซื้อทั้งหมด
$stmt = $conn->query("
    SELECT o.*, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// อัปเดตสถานะคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        header("Location: orders.php");
        exit;
    }
    if (isset($_POST['update_shipping'])) {
        $stmt = $conn->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        header("Location: orders.php");
        exit;
    }
}

// ฟังก์ชันสำหรับกำหนดสีของสถานะ
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'status-pending';
        case 'processing':
            return 'status-processing';
        case 'shipped':
            return 'status-shipped';
        case 'completed':
            return 'status-completed';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'bg-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการคำสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* General Styles */
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #F0F4F8; /* Light blue-gray background */
        }

        /* Page Header */
        .page-header {
            color: #1E40AF; /* Darker blue for title */
            border-bottom: 3px solid #60A5FA; /* Lighter blue accent */
            padding-bottom: 0.75rem;
            margin-bottom: 2rem;
            font-weight: 700;
        }

        /* Accordion Styles */
        .accordion-item {
            border: none;
            border-radius: 0.75rem !important;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .accordion-header .accordion-button {
            background-color: #ffffff;
            color: #1F2937;
            font-weight: 600;
            font-size: 1.05rem;
            transition: background-color 0.2s;
        }

        .accordion-header .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);
            color: white;
            box-shadow: inset 0 -2px 4px rgba(0,0,0,0.1);
        }
        
        .accordion-button:focus {
            box-shadow: none;
        }

        /* Customizing the arrow icon color */
        .accordion-button::after {
            filter: grayscale(1) brightness(1.2);
        }
        .accordion-button:not(.collapsed)::after {
            filter: brightness(0) invert(1);
        }

        .accordion-body {
            background-color: #FAFCFF;
            padding: 1.5rem;
        }

        /* Status Badge Colors */
        .status-badge {
            color: #fff !important;
            padding: 0.4em 0.8em;
            font-size: 0.8rem;
            font-weight: 500;
            border-radius: 0.5rem;
            vertical-align: middle;
        }
        .status-pending { background-color: #F59E0B; } /* Amber */
        .status-processing { background-color: #3B82F6; } /* Blue */
        .status-shipped { background-color: #10B981; } /* Emerald */
        .status-completed { background-color: #16A34A; } /* Green */
        .status-cancelled { background-color: #EF4444; } /* Red */

        /* Section titles inside accordion */
        .accordion-body h5 {
            color: #1E40AF;
            margin-top: 1rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            border-left: 3px solid #60A5FA;
            padding-left: 0.75rem;
        }
        .accordion-body h5:first-child {
            margin-top: 0;
        }
        
        /* Button styles */
         .btn {
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .btn-secondary {
             background-color: #6B7281;
             border-color: #6B7281;
        }
        .btn-secondary:hover {
             background-color: #4B5563;
             border-color: #4B5563;
        }
    </style>
</head>
<body class="container py-4">

<h2 class="page-header">จัดการคำสั่งซื้อ</h2>
<a href="index.php" class="btn btn-secondary mb-4">← กลับหน้าผู้ดูแล</a>

<div class="accordion" id="ordersAccordion">

<?php foreach ($orders as $index => $order): ?>
    <?php 
        $shipping = getShippingInfo($conn, $order['order_id']); 
        $status_class = getStatusBadgeClass($order['status']);
    ?>
    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?= $index ?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                <div class="d-flex justify-content-between w-100 align-items-center pe-3">
                    <span><strong>คำสั่งซื้อ #<?= $order['order_id'] ?></strong> | โดย: <?= htmlspecialchars($order['username']) ?></span>
                    <span class="<?= $status_class ?> status-badge"><?= ucfirst($order['status']) ?></span>
                </div>
            </button>
        </h2>
        <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>" data-bs-parent="#ordersAccordion">
            <div class="accordion-body">
               
                <h5><i class="bi bi-box-seam"></i> รายการสินค้า (<?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>)</h5>
                <ul class="list-group mb-4">
                    <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 bg-transparent ps-0">
                            <?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?>
                            <span><?= number_format($item['quantity'] * $item['price'], 2) ?> บาท</span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 bg-transparent ps-0 fw-bold mt-2">
                        ยอดรวม
                        <span><?= number_format($order['total_amount'], 2) ?> บาท</span>
                    </li>
                </ul>
                
                <?php if ($shipping): ?>
                    <h5><i class="bi bi-truck"></i> ข้อมูลจัดส่ง</h5>
                    <p class="mb-1"><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                    <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                <?php endif; ?>

                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-6">
                        <h6>อัปเดตสถานะคำสั่งซื้อ</h6>
                         <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <select name="status" class="form-select form-select-sm" style="width: auto; flex-grow: 1;">
                                <?php
                                $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                                foreach ($statuses as $status) {
                                    $selected = ($order['status'] === $status) ? 'selected' : '';
                                    echo "<option value=\"$status\" $selected>" . ucfirst($status) . "</option>";
                                }
                                ?>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary btn-sm">บันทึก</button>
                        </form>
                    </div>
                    <?php if ($shipping): ?>
                        <div class="col-md-6">
                            <h6>อัปเดตสถานะการจัดส่ง</h6>
                            <form method="post" class="d-flex gap-2">
                                <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                                <select name="shipping_status" class="form-select form-select-sm" style="width: auto; flex-grow: 1;">
                                    <?php
                                    $s_statuses = ['not_shipped', 'shipped', 'delivered'];
                                    foreach ($s_statuses as $s) {
                                        $selected = ($shipping['shipping_status'] === $s) ? 'selected' : '';
                                        echo "<option value=\"$s\" $selected>" . ucfirst($s) . "</option>";
                                    }
                                    ?>
                                </select>
                                <button type="submit" name="update_shipping" class="btn btn-success btn-sm">บันทึก</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>