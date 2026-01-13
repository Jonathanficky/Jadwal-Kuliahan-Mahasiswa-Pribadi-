<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$nama_user = $_SESSION["nama"];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manajemen Jadwal - SCHEDU</title>
    <link rel="stylesheet" href="style.css?v=1.6" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main class="container">
        <section class="card">
            <div class="card-head">
                <h3><i class="fa-solid fa-plus-circle"></i> Tambah Jadwal Manual</h3>
            </div>
            <form id="formJadwal" class="form-grid">
                <div class="form-group" style="flex: 2; min-width: 200px;">
                    <input type="text" id="matkul" placeholder="Nama Mata Kuliah" required>
                </div>
                <div class="form-group" style="flex: 1.5;">
                    <input type="text" id="dosen" placeholder="Nama Dosen">
                </div>
                <div class="form-group" style="flex: 1;">
                    <input type="text" id="ruang" placeholder="Ruangan (Mis: L-201)">
                </div>
                
                <div class="form-group" style="flex: 1;">
                    <select id="hari" required style="cursor: pointer;">
                        <option value="" disabled selected>Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 0.8;">
                    <input type="time" id="jamMulai" required title="Jam Mulai">
                </div>
                <div class="form-group" style="flex: 0.8;">
                    <input type="time" id="jamSelesai" required title="Jam Selesai">
                </div>
                
                <button type="button" class="btn-inline" onclick="addSchedule()" style="height: 45px; margin-top: 1px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                </button>
            </form>
        </section>

        <section class="card mt-2">
            <div class="card-head">
                <h3><i class="fa-solid fa-file-excel"></i> Import dari Excel</h3>
            </div>
            <div class="form-grid">
                <div class="form-group" style="flex: 2;">
                    <input type="file" id="fileInput" accept=".xlsx, .xls">
                </div>
                <button type="button" class="btn-inline" onclick="importExcelFile(document.getElementById('fileInput'))">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload
                </button>
            </div>
        </section>

        <section class="card mt-2">
            <div class="card-head">
                <div class="title-group">
                    <h3><i class="fa-solid fa-list-ul"></i> Daftar Jadwal Anda</h3>
                </div>
                
                <div class="input-group" style="max-width: 250px; margin-bottom: 0;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchBox" placeholder="Cari Matkul / Dosen..." onkeyup="searchHandler(this.value)">
                </div>
            </div>

            <div id="jadwal-list" class="card-body">
                </div>
        </section>
    </main>

    <script src="script.js"></script>
    
    <script>
        async function addSchedule() {
            // Ambil data dari form
            const data = {
                mata_kuliah: document.getElementById('matkul').value,
                dosen: document.getElementById('dosen').value,
                ruangan: document.getElementById('ruang').value,
                hari: document.getElementById('hari').value,
                jam_mulai: document.getElementById('jamMulai').value,
                jam_selesai: document.getElementById('jamSelesai').value,
                catatan: ""
            };

            // Validasi sederhana
            if(!data.mata_kuliah || !data.jam_mulai || !data.jam_selesai) {
                Swal.fire('Peringatan', 'Mata kuliah dan jam wajib diisi!', 'warning');
                return;
            }

            try {
                // Kirim ke jadwal.php
                const response = await fetch('jadwal.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const res = await response.json();
                
                if (res.status === 'success') {
                    Swal.fire('Berhasil!', 'Jadwal ditambahkan', 'success');
                    // Reset form manual
                    document.getElementById('formJadwal').reset();
                    // Refresh daftar jadwal
                    renderSchedule(); 
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }
    </script>
</body>
</html>