<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/koneksi.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($email)) {
    die('Nama dan email wajib diisi.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Format email tidak valid.');
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)'
    );
    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => password_hash($password, PASSWORD_BCRYPT),
    ]);    
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        die('<div style="display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; font-family:sans-serif;">
        <div style="text-align:center;">
            <p>Email sudah terdaftar.</p>
            <a href="src/index.html">Kembali</a>
        </div>
    </div>');
    }
    die('<div style="display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; font-family:sans-serif;">
        <div style="text-align:center;">
            <p>Terjadi kesalahan..</p>
            <a href="src/index.html">Kembali</a>
        </div>
    </div>
    ');
}
$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'putuprawira425@gmail.com';   // ← ganti email Gmail kamu
    $mail->Password   = 'iaba oktl ilel ckxi';   // ← App Password Gmail (bukan password biasa)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    
    $mail->setFrom('putuprawira425@gmail.com', 'Sistem Pendaftaran');
    $mail->addAddress($email, $username);

    
    $mail->isHTML(true);
    $mail->Subject = 'Konfirmasi Pendaftaran Akun';
    $mail->Body    = "
        <h2>Halo, {$username}!</h2>
        <p>Terima kasih telah mendaftar, akun kamu berhasil dibuat</p>
        <p><strong>Detail akun:</strong></p>
        <ul>
            <li>Nama  : {$username}</li>
            <li>Password : {$password}</li>
        </ul>
    ";
    $mail->AltBody = "Halo {$username}, akun kamu berhasil didaftarkan dengan email {$email}.";

    $mail->send();

echo "
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Pendaftaran Berhasil</title>
    <style>
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; font-family:sans-serif; }
        div  { text-align:center; }
    </style>
</head>
<body>
    <div>
        <h2>Pendaftaran Berhasil!</h2>
        <p>Halo <strong>{$username}</strong>, email konfirmasi telah dikirim ke <strong>{$email}</strong></p>
        <a href='src/index.html'>Kembali</a>
    </div>
</body>
</html>
";

} catch (Exception $e) {
echo "
<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Gagal</title>
    <style>
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; font-family:sans-serif; }
        div  { text-align:center; }
    </style>
</head>
<body>
    <div>
        <h2>Email Gagal Dikirim</h2>
        <p>Terjadi kesalahan saat mengirim email konfirmasi.</p>
        <code>{$mail->ErrorInfo}</code>
        <a href='src/index.html'>Coba Lagi</a>
    </div>
</body>
</html>
";
}