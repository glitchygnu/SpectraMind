<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

$auth = new Auth();

// Check if user is logged in
if(!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get channel ID from request (default to channel 1)
$channelId = isset($_GET['channel_id']) ? (int)$_GET['channel_id'] : 1;

// Verify user has access to channel
$db = new Database();
$db->query('SELECT id FROM channels WHERE id = :id AND (is_private = FALSE OR created_by = :user_id)');
$db->bind(':id', $channelId);
$db->bind(':user_id', $_SESSION['user_id']);
$channel = $db->single();

if(!$channel) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Get messages
$db->query('SELECT m.*, u.username, u.avatar FROM messages m JOIN users u ON m.user_id = u.id WHERE m.channel_id = :channel_id AND m.is_deleted = FALSE ORDER BY m.created_at DESC LIMIT 50');
$db->bind(':channel_id', $channelId);
$messages = $db->resultSet();

// Reverse to show oldest first
echo json_encode(array_reverse($messages));
?>
