<?php
session_start();
require 'config.php';

header('Content-Type: application/json'); // กำหนดให้ไฟล์นี้ตอบกลับเป็น JSON

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

// ตรวจสอบว่ามีการส่ง product_id มาหรือไม่
if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// ตรวจสอบว่าสินค้านี้อยู่ใน Wishlist แล้วหรือยัง
$stmt = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$exists = $stmt->fetch();

if ($exists) {
    // ถ้ามีอยู่แล้ว -> ให้ลบออก
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    echo json_encode(['status' => 'success', 'action' => 'removed']);
} else {
    // ถ้ายังไม่มี -> ให้เพิ่มเข้าไป
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
    echo json_encode(['status' => 'success', 'action' => 'added']);
}
?>