<?php
// Start session
session_start();

// Database connection
$host = 'localhost';
$dbname = 'employee_managements';
$username = 'root'; // Change to your database username
$password = ''; // Change to your database password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $password === $user['password']) { // In production, use password_verify() with hashed passwords
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $user['username'];
        header('Location: admin-dashboard.php');
        exit();
    } else {
        $error_message = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
	body {
  margin: 0;
  padding: 0;
  font-family: Arial, sans-serif;
  background: url("gwegw.jpg") no-repeat center center fixed;
  background-size: cover;
}

.login-wrapper {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-box {
  background-color: white;
  width: 350px;
  padding: 30px;
  border-radius: 15px;
  text-align: center;
  box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
}

.logo {
  width: 180px;
  margin-bottom: 10px;
}

.admin-title {
  color: red;
  font-size: 24px;
  margin: 10px 0 20px;
}

input[type="text"],
input[type="password"] {
  width: 85%;
  padding: 14px 20px;
  margin: 12px auto;
  display: block;
  border: none;
  border-radius: 25px;
  background-color: #e0e0e0;
  font-size: 15px;
}

.password-wrapper {
  position: relative;
  width: 100%;
  margin: 12px auto;
}

.toggle-password {
  position: absolute;
  top: 50%;
  right: 25px;
  transform: translateY(-50%);
  cursor: pointer;
  width: 20px;
  height: 20px;
  user-select: none;
}

button {
  width: 85%;
  padding: 12px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 25px;
  font-size: 15px;
  font-weight: bold;
  cursor: pointer;
  margin-top: 15px;
}

#error-message {
  color: red;
  font-size: 14px;
  margin-top: 10px;
}

/* Modal styles */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 5px;
  width: 300px;
  text-align: center;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.modal-content p {
  font-size: 16px;
  margin-bottom: 20px;
}

.modal-buttons {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.modal-buttons button {
  padding: 8px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  width: auto;
}

.yes-btn {
  background-color: #dc3545;
  color: white;
}

.no-btn {
  background-color: #6c757d;
  color: white;
}

    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <img src="download-removebg-preview (14).png" alt="Yazaki-Torres Logo" class="logo">
            <h2 class="admin-title">ADMIN</h2>
            <form method="POST" action="">
                <input type="text" id="username" name="username" placeholder="Username" required>
                
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <img src="eye.svg" alt="Toggle password" class="toggle-password" id="toggleIcon" onclick="togglePassword()">
                </div>

                <button type="submit">Login</button>
                <?php if ($error_message): ?>
                    <p id="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.getElementById("toggleIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.src = "eye-off.svg"; // eye with slash
            } else {
                passwordField.type = "password";
                toggleIcon.src = "eye.svg"; // normal eye
            }
        }
    </script>
</body>
</html>