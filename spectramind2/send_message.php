<?php
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user']['username'], $data['message']]);
}
?>
