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

// Xóa người dùng
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: user.php");
    exit();
}

// Thêm hoặc cập nhật người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if ($id) {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password_hash =? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $password, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: user.php");
    exit();
}

// Lấy danh sách người dùng
$result = $conn->query("SELECT id, username, email FROM users");

// Lấy thông tin người dùng để sửa
$edit_user = [];
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_result = $stmt->get_result();
    $edit_user = $edit_result->fetch_assoc();
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <link rel="stylesheet" href="/Webgame/CSS/user.css">
</head>
<body>
    <div class="user-management">
        <h2>Quản lý Người Dùng</h2>
        <form method="POST" action="user.php">
            <input type="hidden" name="id" value="<?= $edit_user['id'] ?? '' ?>">
            <label>Tên đăng nhập:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($edit_user['username'] ?? '') ?>" required>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" required>
            <label>Mật khẩu:</label>
            <input type="password" name="password" required>
            <button type="submit">Lưu</button>
        </form>
    </div>

    <div class="user-list">
        <h3>Danh sách Người Dùng</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Email</th>
                <th>Hành động</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td>
                    <a href="user.php?edit=<?= $row['id'] ?>">Sửa</a> |
                    <a href="user.php?delete=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">Xóa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <br>
    <button class="back-btn" onclick="location.href='../index.php'">Quay lại Dashboard</button>

</body>
</html>

<?php $conn->close(); ?>