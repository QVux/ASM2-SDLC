<?php
session_start();

$host = "localhost";
$username = "root"; // Thay bằng username của MySQL
$password = ""; // Thay bằng password của MySQL
$database = "Webgamestore"; // Tên database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Kiểm tra tên đăng nhập chỉ chứa chữ và số
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            echo "<script>alert('Tên đăng nhập chỉ được chứa chữ và số!');</script>";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);

            if ($stmt->execute()) {
                echo "<script>alert('Đăng ký thành công!'); window.location.href = '/Webgame/Login/Login.php';</script>";
            } else {
                echo "<script>alert('Lỗi: " . $stmt->error . "');</script>";
            }

            $stmt->close();
        }
    } else {
        echo "<script>alert('Vui lòng điền đầy đủ thông tin!');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Webgame/CSS/signup.css">
    <title>Đăng ký tài khoản</title>
</head>
<body>
    <div class="signup-form">
        <h2>Đăng ký tài khoản</h2>
        <form method="POST" action="SignUp.php">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" name="email" required><br>

            <label for="password">Mật khẩu:</label>
            <input type="password" name="password" required><br>

            <button type="submit">Đăng ký</button>
            <button class="back-button" onclick="window.location.href='/Webgame/Login/Login.php'">Quay về Đăng nhập</button>
        </form>
    </div>
</body>
</html>