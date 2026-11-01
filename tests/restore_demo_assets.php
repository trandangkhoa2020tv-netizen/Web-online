<?php
require_once __DIR__ . '/../config/database.php';

$uploadsReviewDir = __DIR__ . '/../uploads/reviews';
if (!is_dir($uploadsReviewDir) && !mkdir($uploadsReviewDir, 0775, true) && !is_dir($uploadsReviewDir)) {
    throw new RuntimeException('Không thể tạo thư mục uploads/reviews.');
}

$sourceImage = __DIR__ . '/../image/Frame21472288341.webp';
$demoReviewImage = $uploadsReviewDir . '/demo_review_sample.webp';
if (is_file($sourceImage) && !is_file($demoReviewImage)) {
    copy($sourceImage, $demoReviewImage);
}

$conn->exec("UPDATE site_side_banners SET title = 'Banner quảng cáo dọc bên trái', image = 'image/Vivo-S5-Promo-Poster-2.jpg', link_url = '/Web/products.php', is_active = 1 WHERE placement = 'left'");
$conn->exec("UPDATE site_side_banners SET title = 'Banner quảng cáo dọc bên phải', image = 'image/oppo-hop-tac-son-tung-mtp-13-1759759661178-17597596619081804933467.webp', link_url = '/Web/products.php', is_active = 1 WHERE placement = 'right'");

$insertSlides = [
    ['title' => 'Khuyến mãi Samsung', 'image' => 'image/Frame21472288341.webp', 'link_url' => '/Web/products.php?q=samsung'],
    ['title' => 'Ưu đãi iPhone', 'image' => 'image/690x300_open_iPhone_17e.webp', 'link_url' => '/Web/products.php?q=iphone'],
    ['title' => 'Laptop gaming', 'image' => 'image/Redmi-K40-poster-face.jpg', 'link_url' => '/Web/products.php?q=laptop'],
    ['title' => 'Tai nghe không dây', 'image' => 'image/dai-su-thuong-hieu-find-x-series-1.jpg', 'link_url' => '/Web/products.php?q=phu-kien'],
];

$maxSort = (int)$conn->query('SELECT COALESCE(MAX(sort_order), 0) FROM site_banner_slides')->fetchColumn();
foreach ($insertSlides as $index => $slide) {
    $sql = 'INSERT INTO site_banner_slides (title, image, link_url, is_active, sort_order) VALUES (?, ?, ?, 1, ?)';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$slide['title'], $slide['image'], $slide['link_url'], $maxSort + $index + 1]);
}

$reviewExists = $conn->prepare('SELECT 1 FROM product_review_images WHERE review_id = 1 AND image = ? LIMIT 1');
$reviewExists->execute(['uploads/reviews/demo_review_sample.webp']);
if (!$reviewExists->fetchColumn()) {
    $insertReviewImage = $conn->prepare('INSERT INTO product_review_images (review_id, image) VALUES (?, ?)');
    $insertReviewImage->execute([1, 'uploads/reviews/demo_review_sample.webp']);
}

echo "Demo banner and review assets restored.\n";
