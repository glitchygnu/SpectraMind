<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Register new user
    public function register($username, $password, $specialCode, $email = null) {
        // Validate inputs
        if(empty($username) || empty($password) || empty($specialCode)) {
            return false;
        }
        
        // Check if username exists
        $this->db->query('SELECT id FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        $this->db->execute();
        
        if($this->db->rowCount() > 0) {
            return false; // Username exists
        }
        
        // Hash password with pepper and special code
        $passwordHash = $this->hashPassword($password, $specialCode);
        $specialCodeHash = password_hash($specialCode, PASSWORD_BCRYPT);
        
        // Insert user
        $this->db->query('INSERT INTO users (username, password_hash, special_code_hash, email) VALUES (:username, :password_hash, :special_code_hash, :email)');
        $this->db->bind(':username', $username);
        $this->db->bind(':password_hash', $passwordHash);
        $this->db->bind(':special_code_hash', $specialCodeHash);
        $this->db->bind(':email', $email);
        
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    // Login user
    public function login($username, $password, $specialCode) {
        // Check login attempts
        if($this->isLoginLocked($username)) {
            return ['status' => false, 'message' => 'Too many failed attempts. Try again later.'];
        }
        
        // Get user
        $this->db->query('SELECT * FROM users WHERE username = :username AND is_active = TRUE');
        $this->db->bind(':username', $username);
        $user = $this->db->single();
        
        if(!$user) {
            $this->logFailedAttempt($username);
            return ['status' => false, 'message' => 'Invalid credentials'];
        }
        
        // Verify special code
        if(!password_verify($specialCode, $user->special_code_hash)) {
            $this->logFailedAttempt($username);
            return ['status' => false, 'message' => 'Invalid special code'];
        }
        
        // Verify password
        $hashedPassword = $this->hashPassword($password, $specialCode);
        if(!hash_equals($user->password_hash, $hashedPassword)) {
            $this->logFailedAttempt($username);
            return ['status' => false, 'message' => 'Invalid password'];
        }
        
        // Create session
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_EXPIRE);
        
        $this->db->query('INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (:user_id, :session_token, :ip_address, :user_agent, :expires_at)');
        $this->db->bind(':user_id', $user->id);
        $this->db->bind(':session_token', $sessionToken);
        $this->db->bind(':ip_address', $_SERVER['REMOTE_ADDR']);
        $this->db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT']);
        $this->db->bind(':expires_at', $expiresAt);
        
        if(!$this->db->execute()) {
            return ['status' => false, 'message' => 'Failed to create session'];
        }
        
        // Update last login
        $this->db->query('UPDATE users SET last_login = NOW() WHERE id = :id');
        $this->db->bind(':id', $user->id);
        $this->db->execute();
        
        // Set session variables
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['role'] = $user->role;
        
        return ['status' => true, 'user' => $user];
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        if(!isset($_SESSION['user_id'], $_SESSION['session_token'])) {
            return false;
        }
        
        $this->db->query('SELECT * FROM user_sessions WHERE user_id = :user_id AND session_token = :session_token AND expires_at > NOW() AND is_active = TRUE');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $this->db->bind(':session_token', $_SESSION['session_token']);
        $session = $this->db->single();
        
        return $session ? true : false;
    }
    
    // Logout user
    public function logout() {
        if($this->isLoggedIn()) {
            // Invalidate session
            $this->db->query('UPDATE user_sessions SET is_active = FALSE WHERE user_id = :user_id AND session_token = :session_token');
            $this->db->bind(':user_id', $_SESSION['user_id']);
            $this->db->bind(':session_token', $_SESSION['session_token']);
            $this->db->execute();
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        return true;
    }
    
    // Password hashing with pepper and special code
    private function hashPassword($password, $specialCode) {
        return hash_hmac('sha256', $password . $specialCode, PEPPER);
    }
    
    // Check if login is locked for user
    private function isLoginLocked($username) {
        $this->db->query('SELECT COUNT(*) as attempts FROM login_attempts WHERE username = :username AND attempt_time > DATE_SUB(NOW(), INTERVAL :lockout_time SECOND)');
        $this->db->bind(':username', $username);
        $this->db->bind(':lockout_time', LOGIN_LOCKOUT_TIME);
        $result = $this->db->single();
        
        return ($result->attempts >= MAX_LOGIN_ATTEMPTS);
    }
    
    // Log failed login attempt
    private function logFailedAttempt($username) {
        $this->db->query('INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (:username, :ip_address, NOW())');
        $this->db->bind(':username', $username);
        $this->db->bind(':ip_address', $_SERVER['REMOTE_ADDR']);
        $this->db->execute();
    }
}
?>
