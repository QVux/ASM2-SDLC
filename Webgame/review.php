<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "Webgamestore";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy ID game từ URL
if (isset($_GET['id'])) {
    $game_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT title, comment, image, price FROM games WHERE id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $stmt->bind_result($title, $comment, $image, $price);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Không tìm thấy game.";
    exit();
}

// Xử lý nút thanh toán
if (isset($_GET['checkout'])) {
    $item = [
        'name' => $title,
        'price' => $price,
        'quantity' => 1 // Số lượng mặc định là 1
    ];

    // Tạo mảng giỏ hàng nếu chưa tồn tại
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Thêm sản phẩm vào giỏ hàng
    $_SESSION['cart'][] = $item;

    // Chuyển hướng đến trang giỏ hàng
    header("Location: /WEBGAME/DATA/cart.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Chi tiết</title>
    <link rel="stylesheet" type="text/css" href="/Webgame/CSS/review.css">
</head>
<body>
    <div class="game-review">
        <h2><?= $title ?></h2>
        <?php if (!empty($image)): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($image) ?>" alt="Game Image">
        <?php else: ?>
            <p>Không có ảnh</p>
        <?php endif; ?>
        <p><?= $comment ?></p>

        <div class="review-actions">
            <a href="/Webgame/index.php" class="continue-shopping-btn">Tiếp tục mua hàng</a>
            <a href="review.php?id=<?= $game_id ?>&checkout=true" class="checkout-btn">Thanh toán</a>
        </div>
    </div>
</body>
</html>