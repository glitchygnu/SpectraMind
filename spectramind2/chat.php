<?php
require 'includes/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Chat</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2>Group Chat</h2>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <div id="chat-box"></div>
        <div class="message-input">
            <input type="text" id="message" placeholder="Type your message...">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>
    <script src="assets/script.js"></script>
</body>
</html>
