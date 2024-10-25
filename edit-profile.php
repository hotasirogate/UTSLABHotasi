<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uts_lab";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $userId) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $profileImage = $_FILES['profile_image']['name'];

    if ($profileImage) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["profile_image"]["name"]);
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFile);
    } else {
        $profileImage = $_POST['current_image'];
    }

    $sql = "UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $email, $profileImage, $userId);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
    exit();
}

if ($userId) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="edit.css">
</head>
<body>
    <form action="edit-profile.php" method="post" enctype="multipart/form-data">
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
        <input type="file" name="profile_image"><br>
        <button type="submit">Update Profile</button>
    </form>
</body>
</html>
