<?php
// -----------------------------
// ฟังกช์ นั ดงึรำยกำรสนิ คำ้ในค ำสั่งซอื้
// -----------------------------
function getOrderItems($pdo, $order_id) {
            $stmt = $pdo->prepare("SELECT oi.quantity, oi.price, p.product_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?");
            $stmt->execute([$order_id]);
return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// -----------------------------
// ฟังกช์ นั ดงึขอ้ มลู จัดสง่
// -----------------------------
function getShippingInfo($pdo, $order_id) {
            $stmt = $pdo->prepare("SELECT * FROM shipping WHERE order_id = ?"); // shipping table
            $stmt->execute([$order_id]);
return $stmt->fetch(PDO::FETCH_ASSOC);
}

// -----------------------------
// ฟังก์ชันดึงข้อมูลผู้ใช้ด้วย ID
// -----------------------------
function get_user_by_id($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>