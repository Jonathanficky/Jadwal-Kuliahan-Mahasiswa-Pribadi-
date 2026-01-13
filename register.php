<?php
require "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitasi input
    $nama     = mysqli_real_escape_string($conn, trim($_POST["nama"]));
    $nim      = mysqli_real_escape_string($conn, trim($_POST["nim"]));
    $email    = mysqli_real_escape_string($conn, trim($_POST["email"]));
    $telpon   = mysqli_real_escape_string($conn, trim($_POST["telpon"]));
    $fakultas = mysqli_real_escape_string($conn, trim($_POST["fakultas"]));
    $prodi    = mysqli_real_escape_string($conn, trim($_POST["prodi"]));
    $password = trim($_POST["password"]);

    if (empty($nama) || empty($nim) || empty($email) || empty($password)) {
        $error = "Harap isi semua data yang wajib.";
    } 
    elseif (strlen($password) < 6) {
        $error = "Password terlalu pendek! Minimal harus 6 karakter.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // CEK APAKAH NIM SUDAH TERDAFTAR
        $cek_query = "SELECT nim FROM users WHERE nim = ?";
        $stmt_cek = mysqli_prepare($conn, $cek_query);
        mysqli_stmt_bind_param($stmt_cek, "s", $nim);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);

        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $error = "NIM sudah terdaftar. Silakan login.";
        } else {
            // PROSES INSERT KE DATABASE
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $query_insert = "INSERT INTO users (nama, nim, email, telpon, fakultas, prodi, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $query_insert);
            mysqli_stmt_bind_param($stmt_insert, "sssssss", $nama, $nim, $email, $telpon, $fakultas, $prodi, $hash);

            if (mysqli_stmt_execute($stmt_insert)) {
                // Berhasil daftar, arahkan ke login
                header("Location: login.php?status=success");
                exit;
            } else {
                $error = "Terjadi kesalahan sistem saat menyimpan data.";
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
    <title>Daftar Akun - SCHEDU</title>
    <link rel="stylesheet" href="style.css?v=1.5" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .auth-card.wide { max-width: 750px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px 25px; }
        @media (max-width: 650px) { .form-row { grid-template-columns: 1fr; gap: 0; } }
    </style>
</head>
<body class="page-center">
    <main class="auth-card wide">
        <div class="auth-top">
            <div class="brand"><i class="fa-solid fa-graduation-cap"></i> SCHEDU</div>
            <h2>Buat Akun Baru</h2>
            <p>Lengkapi data diri untuk memulai pengelolaan jadwal</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error-alert"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label class="label">Nama Lengkap</label>
                    <div class="input-group">
                        <i class="fa-regular fa-user"></i>
                        <input name="nama" type="text" placeholder="Nama Lengkap" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="label">NIM</label>
                    <div class="input-group">
                        <i class="fa-regular fa-id-badge"></i>
                        <input name="nim" type="text" placeholder="Nomor Induk Mahasiswa" value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : '' ?>" required />
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="label">Email Kampus</label>
                    <div class="input-group">
                        <i class="fa-regular fa-envelope"></i>
                        <input name="email" type="email" placeholder="contoh@mahasiswa.ac.id" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required />
                    </div>
                </div>
                <div class="form-group">
                    <label class="label">No. Telepon</label>
                    <div class="input-group">
                        <i class="fa-solid fa-phone"></i>
                        <input name="telpon" type="tel" placeholder="0812xxxx" value="<?= isset($_POST['telpon']) ? htmlspecialchars($_POST['telpon']) : '' ?>" required />
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="label">Jurusan</label>
                    <div class="input-group">
                        <i class="fa-solid fa-building"></i>
                        <select name="fakultas" id="jurusan" required onchange="updateProdi()">
                            <option value="">Pilih Jurusan</option>
                            <option value="Manajemen dan Bisnis">Jurusan Manajemen dan Bisnis</option>
                            <option value="Teknik Elektro">Jurusan Teknik Elektro</option>
                            <option value="Teknik Informatika">Jurusan Teknik Informatika</option>
                            <option value="Teknik Mesin">Jurusan Teknik Mesin</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="label">Program Studi</label>
                    <div class="input-group">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <select name="prodi" id="prodi" required>
                            <option value="">Pilih Program Studi</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="label">Password Akun</label>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password" name="password" type="password" placeholder="Minimal 6 karakter" minlength="6" required />
                    <button type="button" class="eye-toggle" onclick="togglePassword()">
                        <i id="eye-icon" class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <button class="btn-auth" type="submit">Buat Akun Sekarang</button>

            <div class="auth-footer">
                Sudah memiliki akun? <a href="login.php" class="link-auth">Masuk di sini</a>
            </div>
        </form>
    </main>

    <script>
        const dataKampus = {
            "Manajemen dan Bisnis": ["D3 Akuntansi", "D4 Akuntansi Manajerial", "D4 Administrasi Bisnis Terapan", "D4 Administrasi Bisnis Terapan (International Class)", "D4 Logistik Perdagangan Internasional", "D2 Distribusi Barang"],
            "Teknik Elektro": ["D3 Teknik Elektronika Manufaktur", "D3 Teknik Instrumentasi", "D4 Teknologi Rekayasa Elektronika", "D4 Teknik Mekatronika", "D4 Teknologi Rekayasa Pembangkit Energi", "D4 Teknologi Rekayasa Robotika"],
            "Teknik Informatika": ["D3 Teknik Informatika", "D3 Teknologi Geomatika", "D4 Animasi", "D4 Teknologi Rekayasa Multimedia", "D4 Rekayasa Keamanan Siber", "D4 Rekayasa Perangkat Lunak", "D4 Teknologi Permainan"],
            "Teknik Mesin": ["D3 Teknik Mesin", "D3 Teknik Perawatan Pesawat Udara", "D4 Teknologi Rekayasa Konstruksi Perkapalan", "D4 Teknologi Rekayasa Pengelasan & Fabrikasi", "D4 Teknologi Rekayasa Metalurgi"]
        };

        function updateProdi() {
            const jurusanSelect = document.getElementById("jurusan");
            const prodiSelect = document.getElementById("prodi");
            const selectedJurusan = jurusanSelect.value;
            prodiSelect.innerHTML = '<option value="">Pilih Program Studi</option>';
            if (selectedJurusan && dataKampus[selectedJurusan]) {
                dataKampus[selectedJurusan].forEach(prodi => {
                    const option = document.createElement("option");
                    option.value = prodi;
                    option.textContent = prodi;
                    prodiSelect.appendChild(option);
                });
            }
        }

        function togglePassword() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eye-icon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>