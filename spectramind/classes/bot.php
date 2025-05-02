<?php
require_once 'includes/config.php';

class ChatBot {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Process incoming message
    public function processMessage($channelId, $userId, $message) {
        // Check if message is a command
        if(strpos($message, '!') === 0) {
            $command = strtolower(trim(substr($message, 1)));
            
            switch($command) {
                case 'help':
                    return $this->sendHelpMessage($channelId);
                case 'time':
                    return $this->sendCurrentTime($channelId);
                case 'users':
                    return $this->sendOnlineUsers($channelId);
                case 'joke':
                    return $this->sendRandomJoke($channelId);
                case 'quote':
                    return $this->sendRandomQuote($channelId);
                default:
                    return $this->sendUnknownCommand($channelId, $command);
            }
        }
        
        return false;
    }
    
    // Send help message
    private function sendHelpMessage($channelId) {
        $helpText = "Available commands:\n" .
                    "!help - Show this help message\n" .
                    "!time - Show current time\n" .
                    "!users - List online users\n" .
                    "!joke - Tell a random joke\n" .
                    "!quote - Share an inspirational quote";
        
        return $this->sendBotMessage($channelId, $helpText);
    }
    
    // Send current time
    private function sendCurrentTime($channelId) {
        $time = date('Y-m-d H:i:s');
        return $this->sendBotMessage($channelId, "The current time is: {$time}");
    }
    
    // Send online users
    private function sendOnlineUsers($channelId) {
        $this->db->query('SELECT username FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY username');
        $users = $this->db->resultSet();
        
        if(empty($users)) {
            return $this->sendBotMessage($channelId, "No users are currently online.");
        }
        
        $userList = array_map(function($user) { return $user->username; }, $users);
        $message = "Online users (" . count($userList) . "):\n" . implode(", ", $userList);
        
        return $this->sendBotMessage($channelId, $message);
    }
    
    // Send random joke
    private function sendRandomJoke($channelId) {
        $jokes = [
            "Why don't scientists trust atoms? Because they make up everything!",
            "Did you hear about the mathematician who's afraid of negative numbers? He'll stop at nothing to avoid them.",
            "Why don't skeletons fight each other? They don't have the guts.",
            "I'm reading a book about anti-gravity. It's impossible to put down!",
            "Did you hear about the claustrophobic astronaut? He just needed a little space."
        ];
        
        $joke = $jokes[array_rand($jokes)];
        return $this->sendBotMessage($channelId, $joke);
    }
    
    // Send random quote
    private function sendRandomQuote($channelId) {
        $quotes = [
            "The only way to do great work is to love what you do. - Steve Jobs",
            "Innovation distinguishes between a leader and a follower. - Steve Jobs",
            "Your time is limited, don't waste it living someone else's life. - Steve Jobs",
            "Stay hungry, stay foolish. - Steve Jobs",
            "The greatest glory in living lies not in never falling, but in rising every time we fall. - Nelson Mandela"
        ];
        
        $quote = $quotes[array_rand($quotes)];
        return $this->sendBotMessage($channelId, $quote);
    }
    
    // Send unknown command message
    private function sendUnknownCommand($channelId, $command) {
        return $this->sendBotMessage($channelId, "Unknown command: {$command}. Type !help for available commands.");
    }
    
    // Send message as bot
    private function sendBotMessage($channelId, $message) {
        // Get bot user (create if doesn't exist)
        $this->db->query('SELECT id FROM users WHERE username = :username LIMIT 1');
        $this->db->bind(':username', 'ChatBot');
        $botUser = $this->db->single();
        
        if(!$botUser) {
            // Create bot user
            $this->db->query('INSERT INTO users (username, password_hash, special_code_hash, role) VALUES (:username, :password, :code, :role)');
            $this->db->bind(':username', 'ChatBot');
            $this->db->bind(':password', 'bot_password_hash');
            $this->db->bind(':code', 'bot_special_code_hash');
            $this->db->bind(':role', 'admin');
            
            if(!$this->db->execute()) {
                error_log("Failed to create bot user");
                return false;
            }
            
            $botUserId = $this->db->lastInsertId();
        } else {
            $botUserId = $botUser->id;
        }
        
        // Insert bot message
        $this->db->query('INSERT INTO messages (user_id, content, channel_id) VALUES (:user_id, :content, :channel_id)');
        $this->db->bind(':user_id', $botUserId);
        $this->db->bind(':content', $message);
        $this->db->bind(':channel_id', $channelId);
        
        return $this->db->execute();
    }
}
?>
