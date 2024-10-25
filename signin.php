<?php
session_start(); // Start the session to store user information

// Database connection settings
$servername = "localhost"; // MySQL server, change if not localhost
$name_db = "root"; // MySQL username, default is 'root'
$password_db = ""; // MySQL password, default is empty
$database = "uts_lab"; // Name of the database

// Create a connection
$conn = new mysqli($servername, $name_db, $password_db, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Check if input fields are filled
    if (!empty($email) && !empty($password)) {
        // Query to get the user by email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the user was found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user["password"])) {
                // Password is correct, store user information in the session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                
                // Redirect to the dashboard
                header("Location: dashboard.php");
                exit(); // Stop script execution after the redirect
            } else {
                // Incorrect password
                $error_message = "Incorrect password.";
            }
        } else {
            // Email not found
            $error_message = "Email not found.";
        }
        $stmt->close();
    } else {
        // Missing fields
        $error_message = "Please fill in all fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Sign In</h2>

        <!-- Display error message if there is one -->
        <?php if (!empty($error_message)): ?>
            <p style="color:red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <!-- Sign In Form -->
        <form action="signin.php" method="POST">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br><br>
            
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            
            <input type="submit" value="Sign In">
        </form>
    </div>
</body>
</html>
