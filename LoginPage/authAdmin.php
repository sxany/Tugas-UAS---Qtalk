<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ../Loginpage/unauth.php');
    exit;
}

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../Loginpage/unauth.php');
    exit;
}
?>