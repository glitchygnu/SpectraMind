<?php
require_once 'includes/config.php';

$auth = new Auth();
$error = '';
$success = '';

// Redirect if already logged in
if($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Process registration form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $specialCode = $_POST['special_code'];
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    // Validate inputs
    if(empty($username) || empty($password) || empty($specialCode)) {
        $error = 'All fields are required';
    } elseif($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif(strlen($specialCode) < 8) {
        $error = 'Special code must be at least 8 characters';
    } else {
        // Register user
        $registration = $auth->register($username, $password, $specialCode, $email);
        
        if($registration) {
            $success = 'Registration successful! You can now login.';
        } else {
            $error = 'Registration failed. Username may already exist.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | <?php echo SITE_NAME; ?></title>
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
        
        .register-container {
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
        
        .register-header {
            background-color: var(--primary);
            padding: 20px;
            text-align: center;
            color: white;
        }
        
        .register-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 24px;
            letter-spacing: 1px;
        }
        
        .register-form {
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
        
        .success-message {
            color: #43B581;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .register-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .register-footer a {
            color: var(--text-link);
            text-decoration: none;
        }
        
        .register-footer a:hover {
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
        
        .password-strength {
            height: 4px;
            background-color: var(--background-tertiary);
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0%;
            background-color: #F04747;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Create an Account</h1>
        </div>
        
        <form class="register-form" action="register.php" method="POST">
            <?php if($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="email">Email (Optional)</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
            
            <div class="form-group password-toggle">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required oninput="checkPasswordStrength(this.value)">
                <span class="toggle-icon" onclick="togglePassword('password')">👁️</span>
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
            </div>
            
            <div class="form-group password-toggle">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <span class="toggle-icon" onclick="togglePassword('confirm_password')">👁️</span>
            </div>
            
            <div class="form-group password-toggle">
                <label for="special_code">Special Code</label>
                <input type="password" id="special_code" name="special_code" class="form-control" required>
                <span class="toggle-icon" onclick="togglePassword('special_code')">👁️</span>
                <small style="display: block; margin-top: 8px; color: var(--text-muted);">Must be at least 8 characters with letters, numbers, and symbols</small>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Register</button>
            </div>
        </form>
        
        <div class="register-footer">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </div>
    
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        
        function checkPasswordStrength(password) {
            const meter = document.getElementById('strength-meter');
            let strength = 0;
            
            // Check length
            if(password.length >= 8) strength += 1;
            if(password.length >= 12) strength += 1;
            
            // Check for mixed case
            if(password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
            
            // Check for numbers
            if(password.match(/[0-9]/)) strength += 1;
            
            // Check for special chars
            if(password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            // Update meter
            let width = strength * 25;
            let color = '#F04747'; // red
            
            if(strength >= 3) color = '#FAA61A'; // orange
            if(strength >= 4) color = '#43B581'; // green
            
            meter.style.width = width + '%';
            meter.style.backgroundColor = color;
        }
    </script>
</body>
</html>
