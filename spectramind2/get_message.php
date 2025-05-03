<?php
require 'includes/config.php';

header('Content-Type: application/json');

$lastId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id > ? ORDER BY timestamp ASC");
$stmt->execute([$lastId]);
$messages = $stmt->fetchAll();

echo json_encode($messages);
?>
