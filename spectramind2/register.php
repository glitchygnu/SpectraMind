<?php
require 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $code = trim($_POST['code']);
    
    if (strlen($username) > 4 && registerUser($username, $password, $code)) {
        header('Location: login.php');
        exit();
    } else {
        $error = "Registration failed! Check username or code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Register</h2>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username (min 5 chars)" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="code" placeholder="Use: addmetothecrew069/" required>
            <button type="submit">Register</button>
        </form>
        <p>Already registered? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
