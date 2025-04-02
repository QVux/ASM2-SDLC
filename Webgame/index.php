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

// Đăng xuất
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Kiểm tra đăng nhập & phân quyền
if (isset($_SESSION['name'])) {
    if (strpos($_SESSION['name'], "admin") === 0) {
        $_SESSION['role'] = "admin";
    } else {
        $_SESSION['role'] = "member";
    }
}

// Lấy danh sách thể loại
$categories = $conn->query("SELECT * FROM categories");

// Xử lý lọc game theo danh mục
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Xử lý tìm kiếm
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';
if (!empty($search_term)) {
    $search_query = "WHERE games.title LIKE '%" . $conn->real_escape_string($search_term) . "%'";
}

if ($category_filter > 0) {
    $category_query = ($search_query == '') ? "WHERE games.category_id = ?" : "AND games.category_id = ?";
    $stmt = $conn->prepare("SELECT games.*, categories.name as category_name FROM games 
                                    LEFT JOIN categories ON games.category_id = categories.id 
                                    $search_query $category_query");
    if($search_query == ''){
        $stmt->bind_param("i", $category_filter);
    }else{
        $stmt->bind_param("i", $category_filter);
    }
} else {
    $stmt = $conn->prepare("SELECT games.*, categories.name as category_name FROM games 
                                    LEFT JOIN categories ON games.category_id = categories.id 
                                    $search_query");
}

$stmt->execute();
$games = $stmt->get_result();

// Xử lý mua hàng
if (isset($_POST['add_to_cart'])) {
    $game_id = $_POST['game_id'];

    // Lấy thông tin game
    $stmt = $conn->prepare("SELECT title, price FROM games WHERE id = ?");
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $game = $result->fetch_assoc();
    $stmt->close();

    $item = [
        'name' => $game['title'],
        'price' => $game['price'],
        'quantity' => 1 // Số lượng mặc định là 1
    ];

    // Tạo mảng giỏ hàng nếu chưa tồn tại
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Thêm sản phẩm vào giỏ hàng
    $_SESSION['cart'][] = $item;

    // Chuyển hướng đến trang giỏ hàng
    header("Location: DATA/cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang chủ - Web Game</title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<header>
    <div class="header-left">
        <div class="logo">
            <a href="index.php"><img src="img/logo.png" alt="Web Game"></a>
        </div>
        <div class="search-bar">
            <form method="GET" action="index.php">
                <input type="text" name="search" placeholder="Tìm kiếm game..." value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit">Tìm</button>
            </form>
        </div>
    </div>
    <div class="header-right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Xin chào, <?= $_SESSION['name'] ?? "User" ?> 
            (<?= $_SESSION['role'] ?? "member" ?>)</span>
            <a href="index.php?logout=true"><button>Đăng xuất</button></a>
        <?php else: ?>
            <a href="Login/Login.php"><button>Đăng nhập</button></a>
            <a href="SignUp/Signup.php"><button>Đăng ký</button></a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <div class="sidebar">
        <h3>Thể loại game</h3>
        <ul>
            <li><a href="index.php">Tất cả</a></li>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <li><a href="index.php?category=<?= $cat['id'] ?>"><?= $cat['name'] ?></a></li>
            <?php endwhile; ?>
        </ul>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="admin-panel">
                <h3>Quản trị Admin</h3>
                <a href="DATA/user.php"><button>Quản lý User</button></a>
                <a href="DATA/product.php"><button>Quản lý Sản phẩm</button></a>
                <a href="DATA/cart_manager.php"><button>Quản lý Đơn hàng</button></a>
            </div>
        <?php endif; ?>

    </div>

    <div class="content">
        <h2>Danh sách Game</h2>
        <?php if ($games->num_rows > 0): ?>
            <?php while ($game = $games->fetch_assoc()): ?>
                <div class="game">
                    <?php if (!empty($game['image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($game['image']) ?>" alt="Game Image">
                    <?php else: ?>
                        <p>Không có ảnh</p>
                    <?php endif; ?>
                    <h3><?= $game['title'] ?></h3>
                    <a href="review.php?id=<?= $game['id'] ?>" ><button style = >Xem thêm</button></a>
                    <form method="POST" action="index.php">
                        <input type="hidden" name="add_to_cart" value="true">
                        <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                        <button type="submit">Mua ngay</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Không có game nào trong danh mục này.</p>
        <?php endif; ?>
    </div>
</div>
<footer>
    <p>BTEC</p>
</footer>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>