<?php
session_start();
require_once "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nim = trim($_POST["nim"] ?? "");
  $password = trim($_POST["password"] ?? "");

  if ($nim === "" || $password === "") {
    $error = "Harap isi NIM dan Password terlebih dahulu.";
  } else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user["password"])) {
      $error = "NIM atau Password salah.";
    } else {
      $_SESSION["login"] = true;
      $_SESSION["user"] = $user;
      header("Location: home.php");
      exit;
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Jadwal Perkuliahan Mahasiswa</title>

    <link rel="stylesheet" href="style.css" />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
      rel="stylesheet"
    />
  </head>

  <body class="page-center">
    <main class="card auth-card">
      <div class="auth-top">
        <h2>Jadwal Perkuliahan Mahasiswa</h2>
        <p class="muted">Masuk untuk melihat jadwal dan mengatur aktivitas</p>
      </div>

      <?php if ($error): ?>
        <p class="muted" style="color:red;"><?= $error ?></p>
      <?php endif; ?>

      <form class="form" method="POST">
        <label class="label" for="nim">NIM</label>
        <div class="input-row">
          <i class="fa-regular fa-id-badge icon-left"></i>
          <input id="nim" name="nim" type="text" placeholder="Masukkan NIM" required />
        </div>

        <label class="label" for="password">Password</label>
        <div class="input-row">
          <i class="fa-solid fa-lock icon-left"></i>
          <input id="password" name="password" type="password" placeholder="Password" required />
          <button type="button" class="icon-btn eye" onclick="togglePasswordField('password', this)">
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>

        <button class="btn primary full" type="submit">
          <i class="fa-solid fa-right-to-bracket"></i> Sign in
        </button>

        <p class="muted small">
          Belum punya akun? <a href="register.php">Daftar di sini</a>
        </p>
      </form>
    </main>

    <script src="script.js"></script>
  </body>
</html>
