<?php
session_start();
if (!isset($_SESSION["login"])) { header("Location: login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manajemen Aktivitas - SCHEDU</title>
    <link rel="stylesheet" href="style.css?v=1.6" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container">
        <section class="card">
            <div class="card-head">
                <h3><i class="fa-solid fa-calendar-plus"></i> Kelola Aktivitas</h3>
            </div>
            <form id="formAktivitas" class="form-grid">
                <input type="hidden" id="act_id">
                
                <div class="form-group" style="flex: 2;">
                    <input type="text" id="act_nama" name="nama_aktivitas" placeholder="Nama Aktivitas (Contoh: Rapat Hima)" required>
                </div>
                <div class="form-group">
                    <input type="date" id="act_tanggal" name="tanggal" required>
                </div>
                <div class="form-group">
                    <input type="time" id="act_jam" name="jam">
                </div>
                <button type="button" id="btnSaveAct" class="btn-inline" onclick="saveAktivitas()">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
                <button type="button" id="btnCancelAct" class="btn-inline" style="background:#6b7280; display:none;" onclick="resetActForm()">
                    Batal
                </button>
            </form>
        </section>

        <section class="card mt-2">
            <div class="card-head">
                <h3><i class="fa-solid fa-list-check"></i> Daftar Aktivitas Anda</h3>
            </div>
            <div id="aktivitas-list" class="flow">
                </div>
        </section>
    </main>

    <script src="script.js"></script>
    <script>
        // Trigger render saat halaman dimuat
        document.addEventListener("DOMContentLoaded", () => renderAktivitas());
    </script>
</body>
</html>