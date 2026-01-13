<?php
session_start();
if (isset($_SESSION["login"]) && $_SESSION["login"] === true) {
    header("Location: home.php");
    exit;
}

require "config/database.php";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nim = mysqli_real_escape_string($conn, trim($_POST["nim"]));
    $password = trim($_POST["password"]);

    if (empty($nim) || empty($password)) {
        $error = "NIM dan Password wajib diisi.";
    } 
    elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter. Periksa kembali.";
    } else {
        // PROSES VERIFIKASI LOGIN
        $query = "SELECT * FROM users WHERE nim = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $nim);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user["password"])) {
                $_SESSION["login"] = true;
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["nim"] = $user["nim"];
                $_SESSION["nama"] = $user["nama"];
                header("Location: home.php");
                exit;
            } else {
                $error = "Password yang Anda masukkan salah.";
            }
        } else {
            $error = "NIM tidak terdaftar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - SCHEDU</title>
    <link rel="stylesheet" href="style.css?v=1.3" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="page-center">
    <main class="auth-card">
        <div class="auth-top">
            <div class="brand"><i class="fa-solid fa-graduation-cap"></i> SCHEDU</div>
            <h2>Selamat Datang</h2>
            <p>Masuk untuk mengatur jadwal kuliahmu!</p>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert success-alert" style="background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                <i class="fa-solid fa-circle-check"></i> Registrasi berhasil! Silakan Login.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error-alert"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="label">NIM</label>
                <div class="input-group">
                    <i class="fa-regular fa-id-badge"></i>
                    <input name="nim" type="text" placeholder="Masukkan NIM kamu" value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : '' ?>" required />
                </div>
            </div>

            <div class="form-group">
                <label class="label">Password</label>
                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password" name="password" type="password" placeholder="Minimal 6 karakter" minlength="6" required />
                    <button type="button" class="eye-toggle" onclick="togglePassword()">
                        <i id="eye-icon" class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <button class="btn-auth" type="submit">Masuk Sekarang</button>

            <div class="auth-footer">
                Belum punya akun? <a href="register.php" class="link-auth">Daftar di sini</a>
            </div>
        </form>
    </main>

    <script>
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