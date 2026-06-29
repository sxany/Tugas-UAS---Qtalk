<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../Loginpage/unauth.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'user') {
    header('Location: ../Loginpage/unauth.php');
    exit;
}
?>