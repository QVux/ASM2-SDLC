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

// Lấy ID người dùng từ session
$user_id = $_SESSION['user_id'] ?? null;

// Xóa đơn hàng đã thanh toán
if (isset($_GET['delete_order'])) {
    $order_id = $_GET['delete_order'];

    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND total_price IS NOT NULL");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: cart_manager.php");
    exit();
}

// Lấy danh sách đơn hàng đã thanh toán
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND total_price IS NOT NULL");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" type="text/css" href="/Webgame/CSS/cartmana.css">
</head>
<body>
    <div class="cart-manager">
        <h2>Quản lý đơn hàng đã thanh toán</h2>

        <?php if (!empty($orders)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID đơn hàng</th>
                        <th>Ngày đặt hàng</th>
                        <th>Tổng tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= $order['order_date'] ?></td>
                            <td><?= $order['total_price'] ?> VNĐ</td>
                            <td>
                                <a href="cart_manager.php?delete_order=<?= $order['id'] ?>">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Không có đơn hàng nào đã thanh toán.</p>
        <?php endif; ?>

        <a href="/Webgame/index.php" class="back-home-btn">Quay lại trang chủ</a>
    </div>
</body>
</html>