<?php

session_start();

if (isset($_SESSION['user'])) {
    header('Location: src/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: src/login.html');
    exit;
}

require_once __DIR__ . '/koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');


if (empty($username) || empty($password)) {
    header('Location: src/login.html?error=empty');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: src/login.html?error=invalid');
    exit;
}

$_SESSION['user'] = [
    'id'       => $user['id'],
    'username' => $user['username'],
    'email'    => $user['email'],
];

header('Location: src/dashboard.php');
exit;