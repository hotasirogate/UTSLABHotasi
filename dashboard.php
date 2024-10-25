<?php
session_start();

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uts_lab";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Ambil detail pengguna dari session
$userId = $_SESSION['user_id'];
$userName = $_SESSION['name'];
$userEmail = $_SESSION['email'];

// Hapus task
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM todos WHERE id = '$delete_id' AND user_id = '$userId'";
    $conn->query($sql);
    header("Location: dashboard.php");
    exit();
}

// Tambah task baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['task'], $_POST['category'])) {
    $task = $conn->real_escape_string($_POST['task']);
    $category = $conn->real_escape_string($_POST['category']);

    $sql = "INSERT INTO todos (category, task, status, user_id) VALUES ('$category', '$task', 'Incomplete', '$userId')";
    $conn->query($sql);
}

// Update status task
if (isset($_POST['update_status_id']) && isset($_POST['status'])) {
    $update_status_id = $conn->real_escape_string($_POST['update_status_id']);
    $new_status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE todos SET status = '$new_status' WHERE id = '$update_status_id' AND user_id = '$userId'";
    $conn->query($sql);
}

// Filter task
$search = "";
$status_filter = "";
$where_clauses = [];

// Tampilkan task hanya untuk pengguna yang sedang login
$where_clauses[] = "user_id = '$userId'";

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_clauses[] = "(task LIKE '%$search%' OR category LIKE '%$search%')";
}

if (isset($_GET['status_filter'])) {
    $status_filter = $conn->real_escape_string($_GET['status_filter']);
    if ($status_filter === "Completed") {
        $where_clauses[] = "status = 'Completed'";
    } elseif ($status_filter === "Incomplete") {
        $where_clauses[] = "status = 'Incomplete'";
    }
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
$sql = "SELECT * FROM todos $where_sql ORDER BY created_at DESC";

$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - To-Do List</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Dashboard - To-Do List</h2>

        <!-- Info Pengguna -->
        <button id="userInfoBtn" class="user-info-button">
            Profile
        </button>

        <!-- User Info Modal -->
        <div id="userInfoModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <img src="profile-image.jpg" alt="Profile Image" class="profile-image">
                <h3><?php echo htmlspecialchars($userName); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                <div class="modal-actions">
                    <button onclick="window.location.href='edit-profile.php'">Edit Profile</button>
                    <button onclick="window.location.href='logout.php'">Logout</button>
                </div>
            </div>
        </div>

        <!-- Form Pencarian dan Filter -->
        <form action="dashboard.php" method="get" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Cari task..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="status_filter">
                <option value="" <?php echo $status_filter === "" ? "selected" : ""; ?>>Semua Task</option>
                <option value="Completed" <?php echo $status_filter === "Completed" ? "selected" : ""; ?>>Completed</option>
                <option value="Incomplete" <?php echo $status_filter === "Incomplete" ? "selected" : ""; ?>>Incomplete</option>
            </select>
            <button type="submit" class="search-button">Cari</button>
        </form>

        <!-- Form Tambah Task Baru -->
        <form action="dashboard.php" method="post">
            <div class="form-group">
                <label for="task">Task:</label>
                <input type="text" name="task" id="task" required>
            </div>
            <div class="form-group">
                <label for="category">Kategori:</label>
                <select name="category" id="category" required>
                    <option value="Pekerjaan">Pekerjaan</option>
                    <option value="Pribadi">Pribadi</option>
                    <option value="Belanja">Belanja</option>
                </select>
            </div>
            <button type="submit" class="add-button">Tambah Task</button>
        </form>
        
        <!-- Tampilkan Task -->
        <table class="todo-table">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Dibuat Pada</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['category']; ?></td>
                            <td><?php echo $row['task']; ?></td>
                            <td>
                                <form action="dashboard.php" method="post" style="display: inline;">
                                    <input type="hidden" name="update_status_id" value="<?php echo $row['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="Incomplete" <?php echo $row['status'] === 'Incomplete' ? 'selected' : ''; ?>>Incomplete</option>
                                        <option value="Completed" <?php echo $row['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <a href="dashboard.php?delete_id=<?php echo $row['id']; ?>" class="delete-button">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada task ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // JavaScript untuk modal
        const modal = document.getElementById("userInfoModal");
        const btn = document.getElementById("userInfoBtn");
        const span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
