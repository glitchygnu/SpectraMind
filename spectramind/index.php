<?php
require_once 'includes/config.php';

$auth = new Auth();

if($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>
