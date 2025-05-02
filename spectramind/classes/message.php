<?php
require_once 'includes/config.php';

$auth = new Auth();

// Only allow POST requests
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Check if user is logged in
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Validate input
$channelId = filter_input(INPUT_POST, 'channel_id', FILTER_VALIDATE_INT);
$messageContent = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

if(!$channelId || empty($messageContent)) {
    $_SESSION['error'] = 'Invalid message data';
    header('Location: dashboard.php');
    exit;
}

// Check if channel exists and user has access
$db = new Database();
$db->query('SELECT id FROM channels WHERE id = :id AND (is_private = FALSE OR created_by = :user_id)');
$db->bind(':id', $channelId);
$db->bind(':user_id', $_SESSION['user_id']);
$channel = $db->single();

if(!$channel) {
    $_SESSION['error'] = 'Invalid channel';
    header('Location: dashboard.php');
    exit;
}

// Insert message
$db->query('INSERT INTO messages (user_id, content, channel_id) VALUES (:user_id, :content, :channel_id)');
$db->bind(':user_id', $_SESSION['user_id']);
$db->bind(':content', $messageContent);
$db->bind(':channel_id', $channelId);

if($db->execute()) {
    $_SESSION['success'] = 'Message sent';
} else {
    $_SESSION['error'] = 'Failed to send message';
}

header('Location: dashboard.php');
exit;
?>
