<?php
session_start();
require "config/database.php";

/* Proteksi halaman: wajib login */
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $oldpass  = trim($_POST["oldpass"]);
    $newpass  = trim($_POST["newpass"]);
    $confpass = trim($_POST["confpass"]);

    if (empty($oldpass) || empty($newpass) || empty($confpass)) {
        $error = "Semua field wajib diisi.";
    } elseif (strlen($newpass) < 6) {
        $error = "Password baru harus memiliki minimal 6 karakter.";
    } elseif ($newpass !== $confpass) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $user_id = $_SESSION["user_id"];

        /* Ambil password lama dari database */
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (!password_verify($oldpass, $user["password"])) {
                $error = "Password lama salah.";
            } else {
                $hash = password_hash($newpass, PASSWORD_DEFAULT);

                /* Update password */
                $update = mysqli_prepare(
                    $conn,
                    "UPDATE users SET password = ? WHERE id = ?"
                );
                mysqli_stmt_bind_param($update, "si", $hash, $user_id);

                if (mysqli_stmt_execute($update)) {
                    /* Logout otomatis setelah berhasil */
                    session_unset();
                    session_destroy();

                    header("Location: login.php?password=updated");
                    exit;
                } else {
                    $error = "Gagal mengubah password. Silakan coba lagi.";
                }
            }
        }
    }
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
        <button class="btn ghost" onclick="toggleDropdown('opts-pass')">
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

      <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
      <?php endif; ?>

      <form method="POST">
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
<?php
session_start();
require "config/database.php";

/* Proteksi halaman: wajib login */
if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

$error = "";
$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $oldpass  = trim($_POST["oldpass"]);
    $newpass  = trim($_POST["newpass"]);
    $confpass = trim($_POST["confpass"]);

    if (empty($oldpass) || empty($newpass) || empty($confpass)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (strlen($newpass) < 6) {
        $error = "Password baru minimal harus 6 karakter.";
    } elseif ($newpass !== $confpass) {
        $error = "Konfirmasi password baru tidak cocok.";
    } else {
        /* Ambil password lama dari database */
        $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (!password_verify($oldpass, $user["password"])) {
                $error = "Password lama yang Anda masukkan salah.";
            } else {
                $hash = password_hash($newpass, PASSWORD_DEFAULT);

                /* Update password */
                $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($update, "si", $hash, $user_id);

                if (mysqli_stmt_execute($update)) {
                    /* Logout otomatis demi keamanan */
                    session_unset();
                    session_destroy();
                    header("Location: login.php?status=password_updated");
                    exit;
                } else {
                    $error = "Gagal memperbarui database. Silakan coba lagi.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Ubah Password - Jadwal Kuliah</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <header class="topbar">
        <div class="brand"><i class="fa-solid fa-graduation-cap"></i> Jadwal Kuliah</div>

        <nav class="nav">
            <a href="home.php" class="nav-link"><i class="fa-solid fa-house"></i><span> Beranda</span></a>
            <a href="jadwal.php" class="nav-link"><i class="fa-regular fa-calendar-days"></i><span> Jadwal</span></a>
            <a href="aktivitas.php" class="nav-link"><i class="fa-solid fa-list-check"></i><span> Aktivitas</span></a>
        </nav>

        <div class="actions">
            <div class="dropdown" id="opts-pass">
                <button class="btn ghost" onclick="toggleDropdown('opts-pass')">
                    <i class="fa-solid fa-gear"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="profil.php"><i class="fa-regular fa-user"></i> Profil</a>
                    <a href="logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <section class="card narrow centered-card">
            <div class="card-head">
                <h3><i class="fa-solid fa-key"></i> Ubah Password</h3>
                <p class="muted">Keamanan akun adalah prioritas utama.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="form-flow">
                <div class="form-group">
                    <label class="label" for="oldpass">Password Saat Ini</label>
                    <div class="input-row">
                        <i class="fa-solid fa-lock-open icon-left"></i>
                        <input type="password" id="oldpass" name="oldpass" placeholder="Masukkan password lama" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="label" for="newpass">Password Baru</label>
                    <div class="input-row">
                        <i class="fa-solid fa-lock icon-left"></i>
                        <input type="password" id="newpass" name="newpass" placeholder="Minimal 6 karakter" minlength="6" required />
                    </div>
                </div>

                <div class="form-group">
                    <label class="label" for="confpass">Ulangi Password Baru</label>
                    <div class="input-row">
                        <i class="fa-solid fa-shield-halved icon-left"></i>
                        <input type="password" id="confpass" name="confpass" placeholder="Konfirmasi password baru" required />
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn primary full">
                        <i class="fa-solid fa-save"></i> Perbarui Password
                    </button>
                    <a href="profil.php" class="btn ghost full text-center">Batal</a>
                </div>
            </form>
        </section>
    </main>

    

    <script src="script.js"></script>
</body>
</html>
  <script src="script.js"></script>
</body>
</html>
