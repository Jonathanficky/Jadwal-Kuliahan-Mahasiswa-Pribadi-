<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("Location: login.php");
  exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Profil - Jadwal Perkuliahan Mahasiswa</title>

  <link rel="stylesheet" href="style.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
</head>

<body>

<header class="topbar">
  <div class="brand">Jadwal Perkuliahan Mahasiswa</div>

  <nav class="nav">
    <a href="home.php" class="nav-link"><i class="fa-solid fa-house"></i><span>Beranda</span></a>
    <a href="jadwal.php" class="nav-link"><i class="fa-regular fa-calendar-days"></i><span>Jadwal</span></a>
    <a href="aktivitas.php" class="nav-link"><i class="fa-solid fa-list-check"></i><span>Aktivitas</span></a>
  </nav>

  <div class="actions">
    <div class="dropdown">
      <button class="btn ghost" onclick="toggleDropdown('opts-profil')">
        <i class="fa-solid fa-gear"></i>
      </button>
      <div class="dropdown-menu" id="opts-profil">
        <a href="profil.php"><i class="fa-regular fa-user"></i> Profil</a>
        <a href="password.php"><i class="fa-solid fa-key"></i> Ubah Password</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
      </div>
    </div>
  </div>
</header>

<main class="container">
<section class="card profile-card">

  <div class="profile-left">
    <div class="avatar-large"><i class="fa-solid fa-user"></i></div>
    <h3><?= htmlspecialchars($user['nama']) ?></h3>
    <p class="muted">Mahasiswa - <?= htmlspecialchars($user['prodi']) ?></p>
    <button class="btn primary" onclick="toggleEditProfil()">
      <i class="fa-solid fa-pen-to-square"></i> Edit Profil
    </button>
  </div>

  <div class="profile-right" id="profilDisplay">
    <div class="info-row"><strong>NIM:</strong> <?= $user['nim'] ?></div>
    <div class="info-row"><strong>Email:</strong> <?= $user['email'] ?></div>
    <div class="info-row"><strong>Telepon:</strong> <?= $user['telpon'] ?></div>
    <div class="info-row"><strong>Fakultas:</strong> <?= $user['fakultas'] ?></div>
    <div class="info-row"><strong>Program Studi:</strong> <?= $user['prodi'] ?></div>
  </div>

  <form class="profile-right" id="profilEdit" style="display:none;" method="post" action="profil_update.php">
    <input type="text" name="nama" value="<?= $user['nama'] ?>" required>
    <input type="text" name="nim" value="<?= $user['nim'] ?>" readonly>
    <input type="email" name="email" value="<?= $user['email'] ?>" required>
    <input type="text" name="telpon" value="<?= $user['telpon'] ?>" required>
    <input type="text" name="fakultas" value="<?= $user['fakultas'] ?>" required>
    <input type="text" name="prodi" value="<?= $user['prodi'] ?>" required>

    <button class="btn primary full" type="submit">Simpan</button>
    <button type="button" class="btn full" onclick="toggleEditProfil()">Batal</button>
  </form>

</section>
</main>

<script>
function toggleEditProfil(){
  document.getElementById("profilDisplay").style.display =
    document.getElementById("profilDisplay").style.display === "none" ? "block" : "none";
  document.getElementById("profilEdit").style.display =
    document.getElementById("profilEdit").style.display === "block" ? "none" : "block";
}
</script>

<script src="script.js"></script>
</body>
</html>
