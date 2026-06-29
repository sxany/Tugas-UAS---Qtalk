<?php
$error = $_GET['error'] ?? 'unauth';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <style>
        body { margin: 0; background: #f0f2f5; }
    </style>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const error = "<?= htmlspecialchars($error) ?>";
const loginUrl = "src/login.html"; // 

if (error === "empty") {
    Swal.fire({
        icon: "warning",
        title: "Form Kosong",
        text: "Username dan password wajib diisi"
    }).then(() => {
        window.location.href = loginUrl;
    });
}
else if (error === "invalid") {
    Swal.fire({
        icon: "error",
        title: "Login Gagal",
        text: "Username atau password salah"
    }).then(() => {
        window.location.href = loginUrl;
    });
}
else {
    Swal.fire({
        icon: "warning",
        title: "Unauthorized",
        text: "Silakan login terlebih dahulu"
    }).then(() => {
        window.location.href = loginUrl;
    });
}
</script>
</body>
</html>