<?php
session_start();
require_once "koneksi.php";

/* Pastikan user sudah login */
if (!isset($_SESSION["login"]) || !isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$nim = $_SESSION["user"]["nim"];

$old  = $_POST["oldpass"];
$new  = $_POST["newpass"];
$conf = $_POST["confpass"];

/* VALIDASI */
if ($new !== $conf || strlen($new) < 6) {
    header("Location: password.php?error=1");
    exit;
}

/* Ambil password lama dari database */
$stmt = $conn->prepare("SELECT password FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

/* Jika user tidak ditemukan */
if (!$user) {
    header("Location: password.php?error=1");
    exit;
}

/* CEK PASSWORD LAMA */
if (!password_verify($old, $user["password"])) {
    header("Location: password.php?error=1");
    exit;
}

/* Hash password baru */
$newHashed = password_hash($new, PASSWORD_DEFAULT);

/* UPDATE PASSWORD */
$update = $conn->prepare("UPDATE users SET password = ? WHERE nim = ?");
$update->bind_param("ss", $newHashed, $nim);
$update->execute();

/* Update session agar password baru tersimpan di $_SESSION */
$_SESSION["user"]["password"] = $newHashed;

/* Redirect ke home */
header("Location: home.php");
exit;
?>
