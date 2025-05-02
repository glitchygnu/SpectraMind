<?php
require_once 'includes/config.php';

$auth = new Auth();

// Redirect if not logged in
if(!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user
$db = new Database();
$db->query('SELECT * FROM users WHERE id = :id');
$db->bind(':id', $_SESSION['user_id']);
$user = $db->single();

// Get channels
$db->query('SELECT * FROM channels WHERE is_private = FALSE OR created_by = :user_id ORDER BY name');
$db->bind(':user_id', $_SESSION['user_id']);
$channels = $db->resultSet();

// Get recent messages
$db->query('SELECT m.*, u.username, u.avatar FROM messages m JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC LIMIT 20');
$recentMessages = $db->resultSet();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            display: grid;
            grid-template-columns: 240px auto;
            grid-template-rows: 48px auto;
            grid-template-areas: 
                "sidebar header"
                "sidebar main";
        }
        
        /* Header */
        .header {
            grid-area: header;
            background-color: var(--background-secondary);
            border-bottom: 1px solid rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            padding: 0 20px;
            z-index: 10;
        }
        
        .channel-info {
            display: flex;
            align-items: center;
        }
        
        .channel-icon {
            margin-right: 10px;
            color: var(--text-muted);
        }
        
        .channel-name {
            font-weight: 500;
        }
        
        /* Sidebar */
        .sidebar {
            grid-area: sidebar;
            background-color: var(--background-tertiary);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }
        
        .server-header {
            padding: 16px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.2);
            box-shadow: var(--elevation-low);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .server-name {
            font-weight: 700;
            font-size: 16px;
            color: white;
        }
        
        .channels {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;
        }
        
        .channel-category {
            padding: 8px 16px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .channel-list {
            list-style: none;
        }
        
        .channel-item {
            padding: 6px 16px;
            margin: 2px 0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .channel-item:hover {
            background-color: rgba(79, 84, 92, 0.32);
        }
        
        .channel-item.active {
            background-color: rgba(79, 84, 92, 0.6);
        }
        
        .channel-item i {
            margin-right: 8px;
            color: var(--text-muted);
            font-size: 14px;
        }
        
        .channel-item-name {
            font-size: 14px;
        }
        
        /* Main Content */
        .main-content {
            grid-area: main;
            background-color: var(--background);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 48px);
            overflow: hidden;
        }
        
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .message {
            display: flex;
            margin-bottom: 20px;
            padding: 8px 0;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 16px;
            background-color: var(--background-tertiary);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .message-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .message-content {
            flex: 1;
        }
        
        .message-header {
            display: flex;
            align-items: baseline;
            margin-bottom: 6px;
        }
        
        .message-username {
            font-weight: 500;
            margin-right: 8px;
            color: white;
        }
        
        .message-time {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .message-text {
            font-size: 15px;
            line-height: 1.4;
        }
        
        /* Message Input */
        .message-input-container {
            padding: 20px;
            background-color: var(--background-secondary);
        }
        
        .message-input {
            width: 100%;
            padding: 12px 16px;
            background-color: var(--background-tertiary);
            border: none;
            border-radius: 8px;
            color: var(--text-normal);
            font-size: 15px;
            resize: none;
            max-height: 200px;
        }
        
        .message-input:focus {
            outline: none;
        }
        
        /* User Panel */
        .user-panel {
            padding: 12px;
            background-color: var(--background-tertiary);
            border-top: 1px solid rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 8px;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-weight: 500;
            font-size: 14px;
        }
        
        .user-status {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .user-controls {
            display: flex;
        }
        
        .user-controls button {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
        }
        
        .user-controls button:hover {
            color: var(--interactive-active);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="channel-info">
            <i class="fas fa-hashtag channel-icon"></i>
            <span class="channel-name">welcome</span>
        </div>
    </header>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="server-header">
            <span class="server-name"><?php echo SITE_NAME; ?></span>
            <i class="fas fa-chevron-down"></i>
        </div>
        
        <div class="channels">
            <div class="channel-category">
                <span>Text Channels</span>
                <i class="fas fa-plus"></i>
            </div>
            
            <ul class="channel-list">
                <?php foreach($channels as $channel): ?>
                    <li class="channel-item <?php echo $channel->id == 1 ? 'active' : ''; ?>">
                        <i class="fas fa-hashtag"></i>
                        <span class="channel-item-name"><?php echo htmlspecialchars($channel->name); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="user-panel">
            <div class="user-avatar">
                <?php if($user->avatar && file_exists('assets/images/avatars/' . $user->avatar)): ?>
                    <img src="assets/images/avatars/<?php echo htmlspecialchars($user->avatar); ?>" alt="<?php echo htmlspecialchars($user->username); ?>">
                <?php else: ?>
                    <span><?php echo strtoupper(substr($user->username, 0, 1)); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user->username); ?></div>
                <div class="user-status">Online</div>
            </div>
            
            <div class="user-controls">
                <button><i class="fas fa-microphone"></i></button>
                <button><i class="fas fa-headphones"></i></button>
                <button><i class="fas fa-cog"></i></button>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="messages-container" id="messages-container">
            <?php foreach(array_reverse($recentMessages) as $message): ?>
                <div class="message">
                    <div class="message-avatar">
                        <?php if($message->avatar && file_exists('assets/images/avatars/' . $message->avatar)): ?>
                            <img src="assets/images/avatars/<?php echo htmlspecialchars($message->avatar); ?>" alt="<?php echo htmlspecialchars($message->username); ?>">
                        <?php else: ?>
                            <span><?php echo strtoupper(substr($message->username, 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-username"><?php echo htmlspecialchars($message->username); ?></span>
                            <span class="message-time"><?php echo date('M j, Y g:i A', strtotime($message->created_at)); ?></span>
                        </div>
                        
                        <div class="message-text">
                            <?php echo nl2br(htmlspecialchars($message->content)); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="message-input-container">
            <form id="message-form" action="send_message.php" method="POST">
                <input type="hidden" name="channel_id" value="1">
                <textarea 
                    class="message-input" 
                    name="message" 
                    placeholder="Message #welcome" 
                    rows="1"
                    oninput="autoResize(this)"
                ></textarea>
            </form>
        </div>
    </main>
    
    <script>
        // Auto-resize textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        }
        
        // Scroll to bottom of messages
        const messagesContainer = document.getElementById('messages-container');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Handle message submission
        const messageForm = document.getElementById('message-form');
        const messageInput = messageForm.querySelector('textarea');
        
        messageInput.addEventListener('keydown', function(e) {
            if(e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if(messageInput.value.trim() !== '') {
                    // In a real app, you would use AJAX here
                    messageForm.submit();
                }
            }
        });
    </script>
</body>
</html>
