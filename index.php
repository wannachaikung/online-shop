<?php
session_start();
require_once 'config.php';
$isLoggedIn = isset($_SESSION['user_id']);

// === ดึงข้อมูล Wishlist ของผู้ใช้ปัจจุบัน (ถ้าล็อกอินอยู่) ===
$wishlisted_products = [];
if ($isLoggedIn) {
    $stmt_wishlist = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt_wishlist->execute([$_SESSION['user_id']]);
    $wishlisted_products = $stmt_wishlist->fetchAll(PDO::FETCH_COLUMN, 0);
}

// ดึงข้อมูลสินค้าทั้งหมด
$stmt = $conn->query("SELECT p.*, c.category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop - ร้านค้าออนไลน์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bs-primary-rgb: 59, 130, 246;
            --bs-body-font-family: 'Kanit', sans-serif;
            --bs-body-bg: #F0F4F8;
        }

        /* --- Carousel Styling --- */
        .carousel-item img {
            height: 450px; /* กำหนดความสูงของรูปสไลด์ */
            object-fit: cover; /* ทำให้รูปภาพเต็มพื้นที่โดยไม่เสียสัดส่วน */
            filter: brightness(0.8); /* ทำให้รูปมืดลงเล็กน้อยเพื่อให้ข้อความเด่นขึ้น */
        }
        .carousel-caption {
            background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0));
            bottom: 0;
            left: 0;
            right: 0;
            padding-bottom: 2rem;
            padding-top: 4rem;
        }

        /* --- Product Card Styling (เหมือนเดิม) --- */
        .product-card {
            border: none; border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #ffffff;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .product-thumb-wrapper { overflow: hidden; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; }
        .product-thumb { height: 200px; object-fit: cover; transition: transform 0.3s ease; }
        .product-card:hover .product-thumb { transform: scale(1.1); }
        .product-meta { font-size: .75rem; letter-spacing: .05em; color: #6c757d; text-transform: uppercase; }
        .product-title { font-size: 1rem; font-weight: 600; color: #212529; }
        .price { font-size: 1.5rem; font-weight: 700; }
        .badge-top-left { position: absolute; top: 1rem; left: 1rem; z-index: 2; }
        .rating i { color: #fabb05; }
        .wishlist-btn i { color: #adb5bd; transition: color 0.2s, transform 0.2s; }
        .wishlist-btn:hover i { color: #e53935; }
        .wishlist-btn i.bi-heart-fill { color: #e53935; }
        .footer a { color: #adb5bd; text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #ffffff; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <?php include 'templates/header.php'; ?>

    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="product_images/Online Shopping Concept.jpg" class="d-block w-100" alt="Online Shopping">
                <div class="carousel-caption d-none d-md-block text-start">
                    <h1 class="display-4 fw-bold p-3">ยินดีต้อนรับสู่ร้านค้าออนไลน์</h1>
                    <p class="fs-4">ช็อปสินค้าคุณภาพ ส่งฟรีทั่วประเทศ</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="product_images/Shopping_online07.jpg" class="d-block w-100" alt="Shopping Online">
                <div class="carousel-caption d-none d-md-block text-start">
                    <h1 class="display-4 fw-bold">ช็อปง่าย จ่ายสะดวก</h1>
                    <div class="px-3 px-md-5">
                        <p class="fs-4">รองรับทุกช่องทางการชำระเงิน</p>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?q=80&w=2069" class="d-block w-100" alt="Promotion">
                <div class="carousel-caption d-none d-md-block text-start">
                    <h1 class="display-4 fw-bold">Gaming Gear</h1>
                    <p class="fs-4">อุปกรณ์สำหรับเกมเมอร์ชั้นนำในราคาสุดพิเศษ</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>

    <main class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold section-title"><i class="bi bi-stars text-primary me-2"></i>สินค้าแนะนำ</h2>
        </div>
        <div class="row g-4">
        <?php foreach ($products as $p): ?>
        <?php
        $img = !empty($p['image']) ? 'product_images/' . rawurlencode($p['image']) : 'product_images/no-image.jpg';
        $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7*24*3600);
        $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 10;
        $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;
        $full = floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        ?>
        <div class="col-12 col-md-6 col-lg-3 d-flex align-items-stretch">
            <div class="card product-card w-100 h-100 position-relative">
                <?php if ($isNew): ?><span class="badge bg-success badge-top-left">ใหม่</span><?php endif; ?>
                <?php if ($isHot): ?><span class="badge bg-danger badge-top-left">ขายดี</span><?php endif; ?>
                <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="product-thumb-wrapper"><img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="card-img-top product-thumb"></a>
                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="product-meta"><?= htmlspecialchars($p['category_name'] ?? 'หมวดหมู่') ?></span>
                        <button class="btn btn-link p-0 wishlist-btn" title="เพิ่มในรายการโปรด" type="button" data-product-id="<?= (int)$p['product_id'] ?>">
                            <?php if (in_array($p['product_id'], $wishlisted_products)): ?>
                                <i class="bi bi-heart-fill"></i>
                            <?php else: ?>
                                <i class="bi bi-heart"></i>
                            <?php endif; ?>
                        </button>
                    </div>
                    <a class="text-decoration-none" href="product_detail.php?id=<?= (int)$p['product_id'] ?>"><h5 class="product-title card-title"><?= htmlspecialchars($p['product_name']) ?></h5></a>
                    <div class="rating mb-2">
                        <?php for ($i=0; $i<$full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                        <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>
                        <?php for ($i=0; $i<5-$full-$half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                    </div>
                    <div class="mt-auto">
                        <div class="d-flex align-items-center mb-3 pt-3">
                            <span class="price text-primary"><?= number_format((float)$p['price'], 2) ?> บาท</span>
                        </div>
                        <form action="cart.php" method="post" class="d-grid">
                            <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                            <?php if ((int)$p['stock'] > 0): ?>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-cart-plus me-2"></i>เพิ่มลงตะกร้า</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary" disabled><i class="bi bi-x-circle me-2"></i>สินค้าหมด</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </main>

    <?php include 'templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.wishlist-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const productId = this.dataset.productId;
                const icon = this.querySelector('i');
                const formData = new FormData();
                formData.append('product_id', productId);
                fetch('wishlist_action.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.action === 'added') {
                            icon.classList.remove('bi-heart');
                            icon.classList.add('bi-heart-fill');
                        } else {
                            icon.classList.remove('bi-heart-fill');
                            icon.classList.add('bi-heart');
                        }
                    } else {
                        if(data.message.includes('เข้าสู่ระบบ')){
                           window.location.href = 'login.php';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
    </script>
</body>
</html>