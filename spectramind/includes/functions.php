<?php
// CSRF protection
function generateCsrfToken() {
    if(empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Password strength checker
function checkPasswordStrength($password) {
    $strength = 0;
    
    // Length
    if(strlen($password) >= 8) $strength++;
    if(strlen($password) >= 12) $strength++;
    
    // Mixed case
    if(preg_match('/([a-z].*[A-Z])|([A-Z].*[a-z])/', $password)) $strength++;
    
    // Numbers
    if(preg_match('/[0-9]/', $password)) $strength++;
    
    // Special chars
    if(preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
    
    return $strength;
}

// Special code validation
function validateSpecialCode($code) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $code);
}

// Rate limiting
function checkRateLimit($key, $limit = 5, $timeout = 60) {
    $now = time();
    
    if(!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [
            'count' => 1,
            'time' => $now
        ];
        return true;
    }
    
    $rate = $_SESSION['rate_limits'][$key];
    
    if(($now - $rate['time']) > $timeout) {
        $_SESSION['rate_limits'][$key] = [
            'count' => 1,
            'time' => $now
        ];
        return true;
    }
    
    if($rate['count'] >= $limit) {
        return false;
    }
    
    $_SESSION['rate_limits'][$key]['count']++;
    return true;
}
?>
