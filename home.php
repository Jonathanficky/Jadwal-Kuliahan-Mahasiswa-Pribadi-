<?php
session_start();
// Fungsi untuk memberikan class warna berdasarkan nama mata kuliah
function getCourseColor($courseName) {
    $colors = ['blue', 'green', 'red', 'purple', 'orange', 'indigo'];
    $index = abs(crc32($courseName)) % count($colors);
    return $colors[$index];
}

require_once "config/database.php";

if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$nama_user = $_SESSION["nama"];

$stmt = $conn->prepare("SELECT * FROM jadwal_kuliah WHERE user_id = ? ORDER BY jam_mulai ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$jadwal = [];
while ($row = $result->fetch_assoc()) {
    $jadwal[] = $row;
}

$jadwal_json = json_encode($jadwal);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Beranda - SCHEDU</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<header class="topbar">
    <div class="brand">
        <i class="fa-solid fa-graduation-cap"></i> SCHEDU
    </div>

    <nav class="nav">
        <a href="home.php" class="nav-link active"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="jadwal_tampilan.php" class="nav-link"><i class="fa-regular fa-calendar-days"></i> Jadwal</a>
        <a href="aktivitas.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Aktivitas</a>
    </nav>

    <div class="header-right">
        <div class="user-greeting">
            <span>Halo, <strong><?= explode(' ', $_SESSION['nama'])[0]; ?></strong></span>
        </div>
        
        <div class="dropdown-settings" id="settingsDropdown">
            <button class="gear-btn" onclick="toggleSettingsMenu()">
                <i class="fa-solid fa-gear"></i>
            </button>
            
            <div class="dropdown-menu">
                <a href="profil.php">
                    <i class="fa-regular fa-user"></i> Profil
                </a>
                <hr class="menu-divider">
                <a href="#" class="logout-item" onclick="confirmLogout(event)">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>

<main class="container content-grid">
    <section class="card wide">
        <div class="card-head">
            <div class="title-group">
                <h3><i class="fa-regular fa-calendar-check"></i> Jadwal Hari Ini</h3>
                <p class="muted" id="todayLabel"></p>
            </div>
            
            <div id="summaryBadge" class="dashboard-status"></div>
        </div>

        <div class="card-body" id="homeActivities">
            <div class="loading">Memuat jadwal...</div>
        </div>
    </section>

    <aside class="sidebar-group">
        <section class="card">
            <h4><i class="fa-solid fa-circle-info"></i> Info Kampus</h4>
            <p class="small muted">Pastikan Anda memeriksa ruangan kuliah 15 menit sebelum dimulai. Jangan lupa membawa KTM!</p>
        </section>
        
        <section class="card mt-2">
            <h4><i class="fa-solid fa-quote-left"></i> Motivasi</h4>
            <p class="small italic">"Pendidikan adalah senjata paling mematikan di dunia, karena dengan itu Anda bisa mengubah dunia."</p>
        </section>
    </aside>
</main>

<script>
const jadwal = <?= $jadwal_json ?>;
const namaHari = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
const sekarang = new Date();
const hariIni = namaHari[sekarang.getDay()];

// Tampilkan Tanggal Hari Ini
document.getElementById("todayLabel").innerText = 
    hariIni + ", " + sekarang.toLocaleDateString("id-ID", { 
        day: 'numeric', month: 'long', year: 'numeric' 
    });

function renderToday() {
    const container = document.getElementById("homeActivities");
    const jadwalHariIni = jadwal.filter(j => j.hari === hariIni);

    if (jadwalHariIni.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-mug-hot"></i>
                <p>Tidak ada jadwal kuliah untuk hari ini. Waktunya istirahat!</p>
            </div>`;
        return;
    }

    container.innerHTML = jadwalHariIni.map(j => `
        <div class="schedule-item">
            <div class="time-tag">${j.jam_mulai.substring(0,5)} - ${j.jam_selesai.substring(0,5)}</div>
            <div class="schedule-info">
                <h4>${j.mata_kuliah}</h4>
                <p><i class="fa-solid fa-location-dot"></i> ${j.ruangan} | <i class="fa-solid fa-user-tie"></i> ${j.dosen}</p>
            </div>
        </div>
    `).join('');
}

function renderSummary() {
    const badgeContainer = document.getElementById("summaryBadge");
    const jadwalHariIniCount = jadwal.filter(j => j.hari === hariIni).length;

    badgeContainer.innerHTML = `
        <span class="badge b-blue">
            <i class="fa-solid fa-book-open"></i> Total: ${jadwal.length} Matkul
        </span>
        <span class="badge b-green">
            <i class="fa-solid fa-calendar-day"></i> Hari Ini: ${jadwalHariIniCount}
        </span>
    `;
}

// TOGGLE DROPDOWN GEAR
function toggleSettingsMenu() {
    const dropdown = document.getElementById('settingsDropdown');
    dropdown.classList.toggle('active');
}

// FUNGSI KONFIRMASI LOGOUT
function confirmLogout(event) {
    event.preventDefault(); 
    
    Swal.fire({
        title: 'Keluar dari SCHEDU?',
        text: "Anda akan mengakhiri sesi aktif ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6', 
        cancelButtonColor: '#6b7280', 
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    })
}

// Tutup dropdown jika klik di luar
window.onclick = function(event) {
    if (!event.target.closest('.dropdown-settings')) {
        const dropdown = document.getElementById('settingsDropdown');
        if (dropdown && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
        }
    }
}

renderToday();
renderSummary();
</script>

</body>
</html>