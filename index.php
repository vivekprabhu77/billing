<?php
require_once 'config/database.php';
require_once 'config/auth.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Brute force protection
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 5) {
    if (isset($_SESSION['last_attempt_time']) && time() - $_SESSION['last_attempt_time'] < 600) {
        $error = "Too many failed attempts. Please try again after 10 minutes.";
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $conn = getDBConnection();
        
        // CSRF Check
        if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = "Invalid request. Please try again.";
        } else {
            // Prepared statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    // Success: Regenerate session ID for security
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['login_attempts'] = 0; // Reset counter
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password.";
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    logAppError("Failed login attempt for username: $username");
                }
            } else {
                // Initial case: If table is empty, allow creating admin
                $res = $conn->query("SELECT COUNT(*) as count FROM users");
                $user_count = $res->fetch_assoc()['count'];
                if ($user_count == 0 && $username == 'admin') {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                    $stmt->bind_param("ss", $username, $hashed);
                    $stmt->execute();
                    $error = "Admin account created. You can now login.";
                } else {
                    $error = "Invalid username or password.";
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    logAppError("Failed login attempt - User not found: $username");
                }
            }
        }
        $conn->close();
    }
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
        body { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            background: #f4f7f6; 
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            width: 90%; 
            max-width: 400px; 
            border-top: 5px solid var(--accent-color);
        }
        .login-card h2 { 
            text-align: center; 
            margin-bottom: 30px; 
            color: #333;
            font-weight: 700;
        }
        .error { 
            color: #d63031; 
            background: #ff767522; 
            padding: 12px; 
            border-radius: 6px; 
            border-left: 4px solid #d63031; 
            margin-bottom: 25px; 
            font-size: 0.9rem; 
        }
        .bill-input-group { margin-bottom: 20px; }
        .bill-input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.9rem; }
        .bill-input-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .bill-input-group input:focus { border-color: var(--accent-color); outline: none; }
        .btn-login { 
            background: var(--accent-color); 
            color: white; 
            border: none; 
            width: 100%; 
            padding: 14px; 
            border-radius: 6px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 1rem;
            transition: opacity 0.3s;
        }
        .btn-login:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Vignesh Decorators</h2>
        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php" autocomplete="off">
            <!-- Dummy fields to trick browser autofill -->
            <input type="text" style="display:none" aria-hidden="true">
            <input type="password" style="display:none" aria-hidden="true">
            
            <?php echo getCSRFInput(); ?>
            <div class="bill-input-group">
                <label>Username</label>
                <input type="text" name="username" id="username" required 
                       autocomplete="off" placeholder="Enter username"
                       onfocus="this.removeAttribute('readonly');" readonly>
            </div>
            <div class="bill-input-group">
                <label>Password</label>
                <input type="password" name="password" id="password" required 
                       autocomplete="new-password" placeholder="Enter password"
                       onfocus="this.removeAttribute('readonly');" readonly>
            </div>
            <button type="submit" class="btn-login">LOGIN</button>
        </form>
    </div>
</body>
</html>
