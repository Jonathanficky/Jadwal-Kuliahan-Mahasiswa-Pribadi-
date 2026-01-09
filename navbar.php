<header class="topbar">
    <div class="brand">
        <i class="fa-solid fa-graduation-cap"></i> SCHEDU
    </div>

    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>

    <nav class="nav">
        <a href="home.php" class="nav-link <?= ($current_page == 'home.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> Beranda
        </a>
        <a href="jadwal_tampilan.php" class="nav-link <?= ($current_page == 'jadwal_tampilan.php') ? 'active' : '' ?>">
            <i class="fa-regular fa-calendar-days"></i> Jadwal
        </a>
        <a href="aktivitas.php" class="nav-link <?= ($current_page == 'aktivitas.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-list-check"></i> Aktivitas
        </a>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Fungsi untuk memunculkan/menutup menu gear
function toggleSettingsMenu() {
    const dropdown = document.getElementById('settingsDropdown');
    dropdown.classList.toggle('active');
}

// Fungsi Konfirmasi Logout dengan SweetAlert2
function confirmLogout(event) {
    event.preventDefault(); // Menghentikan link langsung terbuka
    
    Swal.fire({
        title: 'Keluar dari SCHEDU?',
        text: "Anda akan mengakhiri sesi aktif ini.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6', // Biru SCHEDU
        cancelButtonColor: '#6b7280', 
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php'; // Arahkan ke file logout
        }
    });
}

// Tutup dropdown jika klik di luar area gear
window.onclick = function(event) {
    if (!event.target.closest('.dropdown-settings')) {
        const dropdown = document.getElementById('settingsDropdown');
        if (dropdown && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
        }
    }
}
</script>