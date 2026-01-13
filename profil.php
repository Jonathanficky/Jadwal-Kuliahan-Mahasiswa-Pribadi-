<?php
session_start();
require_once "config/database.php";

/* Cek login */
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";

/* Ambil data user */
$stmt = $conn->prepare("SELECT nama, nim, email, telpon, fakultas, prodi FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* Proses update profil */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profil'])) {
    $nama     = htmlspecialchars(trim($_POST['nama']));
    $email    = htmlspecialchars(trim($_POST['email']));
    $telpon   = htmlspecialchars(trim($_POST['telpon']));
    $fakultas = htmlspecialchars(trim($_POST['fakultas']));
    $prodi    = htmlspecialchars(trim($_POST['prodi']));

    $update = $conn->prepare("UPDATE users SET nama=?, email=?, telpon=?, fakultas=?, prodi=? WHERE id=?");
    $update->bind_param("sssssi", $nama, $email, $telpon, $fakultas, $prodi, $user_id);
    
    if($update->execute()) {
        $_SESSION['nama'] = $nama;
        header("Location: profil.php?updated=1");
        exit;
    }
}

if (isset($_GET['updated'])) {
    $success_msg = "Profil berhasil diperbarui!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profil Saya - SCHEDU</title>
    <link rel="stylesheet" href="style.css?v=1.2" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>

<body>

<?php include 'navbar.php'; ?>

<main class="container">
    <?php if($success_msg): ?>
        <div class="alert success-alert" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <i class="fa-solid fa-circle-check"></i> <?= $success_msg ?>
        </div>
    <?php endif; ?>

    <section class="profile-container">
        <div class="card profile-main-card">
            <div class="profile-header-info">
                <div class="avatar-wrapper">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <div class="user-meta">
                    <h2><?= htmlspecialchars($user['nama']) ?></h2>
                    <p><?= htmlspecialchars($user['nim']) ?> • <?= htmlspecialchars($user['prodi']) ?></p>
                    <button class="btn-edit-toggle" id="btnToggle" onclick="toggleEditProfil()">
                        <i class="fa-solid fa-user-pen"></i> Edit Profil
                    </button>
                </div>
            </div>

            <hr class="divider">

            <div id="profilDisplay" class="info-grid">
                <div class="info-item">
                    <label><i class="fa-solid fa-envelope"></i> Email</label>
                    <p Fitz><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-phone"></i> Nomor Telepon</label>
                    <p><?= htmlspecialchars($user['telpon']) ?></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-building-columns"></i> Fakultas</label>
                    <p><?= htmlspecialchars($user['fakultas']) ?></p>
                </div>
                <div class="info-item">
                    <label><i class="fa-solid fa-graduation-cap"></i> Program Studi</label>
                    <p><?= htmlspecialchars($user['prodi']) ?></p>
                </div>
            </div>

            <form id="profilEdit" method="POST" style="display:none;" class="edit-form-layout">
    <div class="form-grid">
        <div class="form-group">
            <label class="label">Nama Lengkap</label>
            <div class="input-group">
                <i class="fa-regular fa-user"></i>
                <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="label">NIM (Terkunci)</label>
            <div class="input-group">
                <i class="fa-regular fa-id-badge"></i>
                <input type="text" value="<?= htmlspecialchars($user['nim']) ?>" disabled style="background: #f1f3f5;">
            </div>
        </div>

        <div class="form-group">
            <label class="label">Email</label>
            <div class="input-group">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="label">Telepon</label>
            <div class="input-group">
                <i class="fa-solid fa-phone"></i>
                <input type="text" name="telpon" value="<?= htmlspecialchars($user['telpon']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="label">Fakultas</label>
            <div class="input-group">
                <i class="fa-solid fa-building"></i>
                <input type="text" name="fakultas" value="<?= htmlspecialchars($user['fakultas']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="label">Program Studi</label>
            <div class="input-group">
                <i class="fa-solid fa-graduation-cap"></i>
                <input type="text" name="prodi" value="<?= htmlspecialchars($user['prodi']) ?>" required>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" name="update_profil" class="btn-save">
            <i class="fa-solid fa-check"></i> Simpan Perubahan
        </button>
        <button type="button" class="btn-cancel" onclick="toggleEditProfil()">
            Batal
        </button>
    </div>
</form>
        </div>
    </section>
</main>

<script>
/** * Fungsi Gear dan Logout sudah ada di navbar.php, 
 * jadi tidak perlu ditulis ulang di sini.
 */

// Fungsi khusus Toggle Edit Profil
function toggleEditProfil() {
    const display = document.getElementById('profilDisplay');
    const editForm = document.getElementById('profilEdit');
    const btnToggle = document.getElementById('btnToggle');

    if (editForm.style.display === 'none') {
        display.style.display = 'none';
        editForm.style.display = 'block';
        btnToggle.style.visibility = 'hidden';
    } else {
        display.style.display = 'grid';
        editForm.style.display = 'none';
        btnToggle.style.visibility = 'visible';
    }
}
</script>

</body>
</html>