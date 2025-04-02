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

// Lấy dữ liệu giỏ hàng từ session
$cart_items = $_SESSION['cart'] ?? [];

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_index = $_GET['remove'];
    if (isset($cart_items[$remove_index])) {
        unset($cart_items[$remove_index]);
        // Re-index the array to avoid gaps
        $cart_items = array_values($cart_items);
        $_SESSION['cart'] = $cart_items;
    }
}

// Xử lý thanh toán
if (isset($_POST['checkout'])) {
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Lưu thông tin đơn hàng vào cơ sở dữ liệu
    $user_id = $_SESSION['user_id'] ?? null;
    $order_details = json_encode($cart_items);

    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_date, order_details, total_price) VALUES (?, NOW(), ?, ?)");
    $stmt->bind_param("isd", $user_id, $order_details, $total);
    $stmt->execute();
    $stmt->close();

    // Xóa giỏ hàng sau khi thanh toán thành công
    unset($_SESSION['cart']);

    // Hiển thị thông báo thanh toán thành công
    $payment_success = true;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn</title>
    <link rel="stylesheet" type="text/css" href="/Webgame/CSS/cart.css">
</head>
<body>
    <div class="cart">
        <h2>Hóa đơn của bạn</h2>
        <?php if (isset($payment_success) && $payment_success === true): ?>
            <p style="color: red;">Thanh toán thành công!</p>
        <?php elseif (!empty($cart_items)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cart_items as $index => $item): ?>
                        <tr>
                            <td><?= $item['name'] ?></td>
                            <td><?= $item['price'] ?> VNĐ</td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= $item['price'] * $item['quantity'] ?> VNĐ</td>
                            <td><a href="cart.php?remove=<?= $index ?>">Xóa</a></td>
                        </tr>
                        <?php $total += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Tổng tiền:</td>
                        <td><?= $total ?> VNĐ</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <form method="POST" action="cart.php">
                <input type="hidden" name="checkout" value="true">
                <button type="submit" class="checkout-btn">Thanh toán</button>
            </form>
        <?php else: ?>
            <p>Không có sản phẩm nào trong giỏ hàng.</p>
        <?php endif; ?>

        <a href="/Webgame/index.php" class="continue-shopping-btn">Tiếp tục mua hàng</a>
    </div>
</body>
</html>