<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Ubah Password - Jadwal Perkuliahan Mahasiswa</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>

  <!-- ====== HEADER ====== -->
  <header class="topbar">
    <div class="brand">Jadwal Perkuliahan Mahasiswa</div>

    <nav class="nav">
      <a href="home.php" class="nav-link"><i class="fa-solid fa-house"></i><span>Beranda</span></a>
      <a href="jadwal.php" class="nav-link"><i class="fa-regular fa-calendar-days"></i><span>Jadwal</span></a>
      <a href="aktivitas.php" class="nav-link"><i class="fa-solid fa-list-check"></i><span>Input Aktivitas</span></a>
    </nav>

    <div class="actions">
      <div class="dropdown" id="opts-pass">
        <button class="btn ghost">
          <i class="fa-solid fa-gear"></i>
        </button>
        <div class="dropdown-menu">
          <a href="profil.php"><i class="fa-regular fa-user"></i> Profil</a>
          <a href="password.php"><i class="fa-solid fa-key"></i> Ubah Password</a>
          <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
        </div>
      </div>
    </div>
  </header>

  <!-- ====== MAIN CONTENT ====== -->
  <main class="container">
    <section class="card narrow">
      <h3>Ubah Password</h3>

      <?php if (isset($_GET['success'])): ?>
        <p class="muted" style="color:green;">Password berhasil diubah.</p>
      <?php elseif (isset($_GET['error'])): ?>
        <p class="muted" style="color:red;">Password lama salah atau konfirmasi gagal.</p>
      <?php endif; ?>

      <form method="post" action="password_process.php">
        <label class="label" for="oldpass">Password Lama</label>
        <div class="input-row">
          <i class="fa-solid fa-lock icon-left"></i>
          <input type="password" id="oldpass" name="oldpass" placeholder="Masukkan password lama" required />
        </div>

        <label class="label" for="newpass">Password Baru</label>
        <div class="input-row">
          <i class="fa-solid fa-lock icon-left"></i>
          <input type="password" id="newpass" name="newpass" placeholder="Masukkan password baru" required />
        </div>

        <label class="label" for="confpass">Konfirmasi Password Baru</label>
        <div class="input-row">
          <i class="fa-solid fa-lock icon-left"></i>
          <input type="password" id="confpass" name="confpass" placeholder="Konfirmasi password baru" required />
        </div>

        <button type="submit" class="btn primary full">
          <i class="fa-solid fa-check"></i> Konfirmasi
        </button>
      </form>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>
