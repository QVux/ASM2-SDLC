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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $hashed_password);
                $stmt->fetch();
                
                if (password_verify($password, $hashed_password)) {
                    // Lưu thông tin vào SESSION
                    $_SESSION["user_id"] = $user_id;
                    $_SESSION["username"] = $username;

                    // Xác định role (admin hoặc member)
                    if (stripos($username, "admin") === 0) {
                        $_SESSION["role"] = "admin";
                    } else {
                        $_SESSION["role"] = "member";
                    }

                    header("Location: ../index.php"); // Chuyển hướng sau khi đăng nhập thành công
                    exit();
                } else {
                    echo "<p style='color: red;'>Sai mật khẩu!</p>";
                }
            } else {
                echo "<p style='color: red;'>Tài khoản không tồn tại!</p>";
            }
            
            $stmt->close();
        } else {
            echo "<p style='color: red;'>Lỗi truy vấn!</p>";
        }
    } else {
        echo "<p style='color: red;'>Vui lòng điền đầy đủ thông tin!</p>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Webgame/CSS/Login.css">
    <title>Đăng nhập</title>
</head>
<body>
<div class="Login">
    <h2>Đăng nhập</h2>
    <form method="POST" action="Login.php">
        <label for="username">Tên đăng nhập:</label>
        <input type="text" name="username" required><br>

        <label for="password">Mật khẩu:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Đăng nhập</button>
    </form>
</div>

<div class="register-container">
    <p class="register-link">Chưa có tài khoản? <a href="/Webgame/SignUp/Signup.php">Đăng ký ngay</a></p>
</div>
</body>
</html>
