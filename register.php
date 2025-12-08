<?php
require_once "koneksi.php";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $nama     = trim($_POST["nama"]);
  $nim      = trim($_POST["nim"]);
  $email    = trim($_POST["email"]);
  $telpon   = trim($_POST["telpon"]);
  $fakultas = trim($_POST["fakultas"]);
  $prodi    = trim($_POST["prodi"]);
  $password = trim($_POST["password"]);

  if (!$nama || !$nim || !$email || !$telpon || !$fakultas || !$prodi || !$password) {
    $error = "Harap isi semua data.";
  } else {
    $cek = $conn->prepare("SELECT nim FROM users WHERE nim = ?");
    $cek->bind_param("s", $nim);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
      $error = "NIM sudah terdaftar.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $stmt = $conn->prepare(
        "INSERT INTO users (nama, nim, email, telpon, fakultas, prodi, password)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
      );
      $stmt->bind_param(
        "sssssss",
        $nama,
        $nim,
        $email,
        $telpon,
        $fakultas,
        $prodi,
        $hash
      );

      if ($stmt->execute()) {
        $success = "Pendaftaran berhasil. Silakan login.";
      } else {
        $error = "Gagal menyimpan data.";
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
  <title>Daftar - Jadwal Perkuliahan Mahasiswa</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="page-center">

  <main class="card auth-card wide">
    <div class="auth-top">
      <h2>Daftar Akun</h2>
      <p class="muted">Buat akun untuk mengelola jadwalmu</p>
    </div>

    <?php if ($error): ?>
      <p class="muted" style="color:red;"><?= $error ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
      <p class="muted" style="color:green;">
        <?= $success ?> <a href="login.php">Login</a>
      </p>
    <?php endif; ?>

    <form class="form" method="POST">
      <div class="row">
        <div class="col">
          <label class="label">Nama Lengkap</label>
          <div class="input-row">
            <i class="fa-regular fa-user icon-left"></i>
            <input name="nama" type="text" required />
          </div>
        </div>
        <div class="col">
          <label class="label">NIM</label>
          <div class="input-row">
            <i class="fa-regular fa-id-badge icon-left"></i>
            <input name="nim" type="text" required />
          </div>
        </div>
      </div>

      <label class="label">Email</label>
      <div class="input-row">
        <i class="fa-regular fa-envelope icon-left"></i>
        <input name="email" type="email" required />
      </div>

      <label class="label">No Telepon</label>
      <div class="input-row">
        <i class="fa-solid fa-phone icon-left"></i>
        <input name="telpon" type="tel" required />
      </div>

      <div class="row">
        <div class="col">
          <label class="label">Fakultas</label>
          <div class="input-row">
            <i class="fa-solid fa-building icon-left"></i>
            <select id="fakultas" name="fakultas" required onchange="updateProdi()">
              <option value="">Pilih Fakultas</option>
              <option value="Manajemen dan Bisnis">Manajemen dan Bisnis</option>
              <option value="Teknik Mesin">Teknik Mesin</option>
              <option value="Teknik Elektro">Teknik Elektro</option>
              <option value="Teknik Informatika">Teknik Informatika</option>
            </select>
          </div>
        </div>

        <div class="col">
          <label class="label">Program Studi</label>
          <div class="input-row">
            <i class="fa-solid fa-graduation-cap icon-left"></i>
            <select id="prodi" name="prodi" required>
              <option value="">Pilih Program Studi</option>
            </select>
          </div>
        </div>
      </div>

      <label class="label">Password</label>
      <div class="input-row">
        <i class="fa-solid fa-lock icon-left"></i>
        <input name="password" type="password" required />
      </div>

      <button class="btn primary full" type="submit">
        <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
      </button>

      <p class="muted small">
        Sudah punya akun? <a href="login.php">Masuk di sini</a>
      </p>
    </form>
  </main>

<script>
const dataProdi = {
  "Manajemen dan Bisnis": [
    "Akuntansi",
    "Sarjana Terapan Akuntansi Manajerial",
    "Sarjana Terapan Administrasi Bisnis Terapan",
    "Sarjana Terapan Logistik Perdagangan Internasional",
    "Distribusi Barang"
  ],
  "Teknik Mesin": [
    "Teknik Mesin",
    "Teknik Perawatan Pesawat Udara",
    "Teknologi Rekayasa Konstruksi Perkapalan",
    "Teknologi Rekayasa Pengelasan Dan Fabrikasi",
    "Teknologi Rekayasa Metalurgi"
  ],
  "Teknik Elektro": [
    "Teknik Elektronika Manufaktur",
    "Teknologi Rekayasa Elektronika",
    "Teknik Instrumentasi",
    "Teknik Mekatronika",
    "Teknologi Rekayasa Pembangkit Energi",
    "Teknologi Rekayasa Robotika"
  ],
  "Teknik Informatika": [
    "Teknik Informatika",
    "Teknologi Geomatika",
    "Animasi",
    "Teknologi Rekayasa Multimedia",
    "Rekayasa Keamanan Siber",
    "Rekayasa Perangkat Lunak",
    "Teknik Komputer",
    "Teknologi Permainan"
  ]
};

function updateProdi() {
  const fakultas = document.getElementById("fakultas").value;
  const prodi = document.getElementById("prodi");
  prodi.innerHTML = '<option value="">Pilih Program Studi</option>';

  if (dataProdi[fakultas]) {
    dataProdi[fakultas].forEach(p => {
      const opt = document.createElement("option");
      opt.value = p;
      opt.textContent = p;
      prodi.appendChild(opt);
    });
  }
}
</script>

</body>
</html>
