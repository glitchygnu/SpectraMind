<?php
require 'config.php';

function registerUser($username, $password, $code) {
    global $pdo;
    if ($code !== 'addmetothecrew069/') return false;
    
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) return false;

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    return $stmt->execute([$username, $hash]);
}

function loginUser($username, $password, $code) {
    global $pdo;
    if ($code !== 'r3pL-8kWd') return false;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    return ($user && password_verify($password, $user['password'])) ? $user : false;
}
?>
