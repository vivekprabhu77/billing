<?php
require_once 'config/database.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = getDBConnection();
    
    // Simple check if user exists (create admin user if table empty)
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        // If no user exists yet, let's allow creating the first one automatically for this demo
        $user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        if ($user_count == 0 && $username == 'admin') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (username, password) VALUES ('admin', '$hashed')");
            $error = "Admin account created. Please login again with the same credentials.";
        } else {
            $error = "Username not found.";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vignesh Decorators</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f0f2f5; }
        .login-card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h2 { text-align: center; margin-bottom: 30px; color: var(--accent-color); }
        .error { color: #e74c3c; background: #fadbd8; padding: 10px; border-radius: 4px; border-left: 4px solid #e74c3c; margin-bottom: 20px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Vignesh Decorators</h2>
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="bill-input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="bill-input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-add-item" style="background: var(--accent-color); color: white; border: none; margin-top: 20px; height: 50px; font-size: 1.1rem;">LOGIN</button>
        </form>
    </div>
</body>
</html>
