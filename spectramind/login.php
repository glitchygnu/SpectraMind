<?php
require_once 'includes/config.php';

$auth = new Auth();
$error = '';

// Redirect if already logged in
if($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Process login form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $specialCode = $_POST['special_code'];
    
    $login = $auth->login($username, $password, $specialCode);
    
    if($login['status']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $login['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5865F2;
            --primary-dark: #4752C4;
            --primary-darker: #3C45A5;
            --background: #36393F;
            --background-secondary: #2F3136;
            --background-tertiary: #202225;
            --text-normal: #DCDDDE;
            --text-muted: #72767D;
            --text-link: #00AFF4;
            --interactive: #B9BBBE;
            --interactive-active: #FFFFFF;
            --elevation-low: 0 1px 0 rgba(0, 0, 0, 0.2), 0 1.5px 0 rgba(0, 0, 0, 0.05), 0 2px 0 rgba(0, 0, 0, 0.05);
            --elevation-high: 0 8px 16px rgba(0, 0, 0, 0.24);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background);
            color: var(--text-normal);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('assets/images/bg-pattern.png');
            background-size: cover;
            background-position: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 480px;
            background-color: var(--background-secondary);
            border-radius: 8px;
            box-shadow: var(--elevation-high);
            overflow: hidden;
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            background-color: var(--primary);
            padding: 20px;
            text-align: center;
            color: white;
        }
        
        .login-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: 1px;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background-color: var(--background-tertiary);
            border: 1px solid rgba(0, 0, 0, 0.3);
            border-radius: 4px;
            color: var(--text-normal);
            font-size: 16px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(88, 101, 242, 0.3);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            text-align: center;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn:active {
            background-color: var(--primary-darker);
            transform: translateY(0);
        }
        
        .error-message {
            color: #F04747;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .login-footer a {
            color: var(--text-link);
            text-decoration: none;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle input {
            padding-right: 40px;
        }
        
        .toggle-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
        </div>
        
        <form class="login-form" action="login.php" method="POST">
            <?php if($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            
            <div class="form-group password-toggle">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <span class="toggle-icon" onclick="togglePassword('password')">👁️</span>
            </div>
            
            <div class="form-group password-toggle">
                <label for="special_code">Special Code</label>
                <input type="password" id="special_code" name="special_code" class="form-control" required>
                <span class="toggle-icon" onclick="togglePassword('special_code')">👁️</span>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Login</button>
            </div>
        </form>
        
        <div class="login-footer">
            Don't have an account? <a href="register.php">Register</a>
        </div>
    </div>
    
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
