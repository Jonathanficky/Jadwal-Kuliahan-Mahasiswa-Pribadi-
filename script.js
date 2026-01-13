/* ============================================================
   script.js (FINAL VERSION: CRUD + SEARCH + NEW UI)
   Menghubungkan Frontend JavaScript ke Backend PHP
   ============================================================ */

/* ---------- Escape HTML (XSS Protection) ---------- */
function escapeHtml(str) {
    if (!str) return "";
    const p = document.createElement('p');
    p.textContent = str;
    return p.innerHTML;
}

/* ---------- Dropdown Global Handler ---------- */
document.addEventListener("DOMContentLoaded", () => {
    // Nav highlight: Menandai menu aktif berdasarkan URL
    const page = location.pathname.split("/").pop();
    document.querySelectorAll(".nav-link").forEach((a) => {
        if (a.getAttribute("href") === page) a.classList.add("active");
    });

    // Menutup semua dropdown jika user mengklik di luar area dropdown
    document.addEventListener("click", (e) => {
        if (!e.target.closest('.dropdown-settings')) {
            const gearDropdown = document.getElementById('settingsDropdown');
            if (gearDropdown) gearDropdown.classList.remove('active');
        }

        const dropdown = e.target.closest('.dropdown');
        if (!dropdown) {
            document.querySelectorAll(".dropdown.active").forEach(d => d.classList.remove("active"));
        }
    });

    // Inisialisasi Render Halaman
    if (document.getElementById("jadwal-list")) renderSchedule();
    if (document.getElementById("aktivitas-list")) renderAktivitas();
});

/* ---------- Toggle Settings Menu (Gear Dropdown) ---------- */
function toggleSettingsMenu() {
    const dropdown = document.getElementById('settingsDropdown');
    if (dropdown) {
        dropdown.classList.toggle('active');
    }
}

/* ---------- Toggle Password Visibility ---------- */
function togglePasswordField(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector("i") || document.getElementById("eye-icon");
    if (!input) return;
    
    if (input.type === "password") {
        input.type = "text";
        if (icon) icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        if (icon) icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

/* ============================================================
   JADWAL KULIAH (CRUD + SEARCH + NEW UI)
   ============================================================ */

// 1. Fetch Data (Mendukung Keyword Pencarian)
async function fetchJadwal(keyword = '') {
    try {
        const url = keyword ? `jadwal.php?q=${encodeURIComponent(keyword)}` : 'jadwal.php';
        const response = await fetch(url);
        return await response.json();
    } catch (err) {
        console.error("Gagal mengambil data jadwal:", err);
        return [];
    }
}

// 2. Render Jadwal ke HTML (Updated UI)
async function renderSchedule(keyword = '') {
    const container = document.getElementById("jadwal-list");
    if (!container) return;

    // Loading indicator
    if (keyword) container.innerHTML = '<p class="muted small" style="padding:1rem;">Mencari...</p>';

    const data = await fetchJadwal(keyword);

    if (!data || data.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-magnifying-glass-minus"></i>
                <p class="muted">
                    ${keyword ? `Tidak ditemukan jadwal dengan kata kunci "<strong>${escapeHtml(keyword)}</strong>"` : 'Belum ada jadwal. Silakan tambah manual atau import Excel.'}
                </p>
            </div>`;
        return;
    }

    container.innerHTML = data.map(j => `
        <div class="schedule-item ${getCourseColorJS(j.mata_kuliah)}">
            <div class="schedule-info">
                <span class="time-tag">
                    <i class="fa-regular fa-clock"></i> 
                    ${j.jam_mulai.substring(0,5)} - ${j.jam_selesai.substring(0,5)}
                </span>
                <h4>${escapeHtml(j.mata_kuliah)}</h4>
                <p class="muted">
                    <i class="fa-solid fa-calendar-day"></i> <strong>${j.hari}</strong> 
                    <span style="margin: 0 5px;">•</span> 
                    <i class="fa-solid fa-door-open"></i> ${escapeHtml(j.ruangan)}
                </p>
                <p class="small muted" style="margin-top:4px;">
                    <i class="fa-solid fa-user-tie"></i> ${escapeHtml(j.dosen)}
                </p>
                ${j.catatan ? `<p class="note small"><em>${escapeHtml(j.catatan)}</em></p>` : ""}
            </div>
            
            <div class="jadwal-actions">
                <button title="Edit Jadwal" class="action-btn btn-edit" onclick="editSchedule(${j.id})">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button title="Hapus Jadwal" class="action-btn btn-delete" onclick="deleteSchedule(${j.id})">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>
    `).join("");
}

// 3. Handler Pencarian Real-time
function searchHandler(keyword) {
    renderSchedule(keyword);
}

// 4. Helper Warna Mata Kuliah
function getCourseColorJS(name) {
    const colors = ['blue', 'green', 'red', 'purple', 'orange', 'indigo'];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % colors.length;
    return colors[index];
}

// 5. Delete Jadwal
async function deleteSchedule(id) {
    Swal.fire({
        title: 'Hapus Jadwal?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch(`jadwal.php?id=${id}`, { method: 'DELETE' });
                const res = await response.json();
                if (res.status === 'success') {
                    renderSchedule();
                    Swal.fire('Terhapus!', 'Jadwal berhasil dihapus.', 'success');
                }
            } catch (err) {
                Swal.fire('Error', 'Gagal menghapus data.', 'error');
            }
        }
    });
}

// 6. Edit Jadwal (Siapkan Form)
async function editSchedule(id) {
    const response = await fetch('jadwal.php');
    const allData = await response.json();
    const item = allData.find(j => j.id == id);

    if (item) {
        document.getElementById('matkul').value = item.mata_kuliah;
        document.getElementById('dosen').value = item.dosen;
        document.getElementById('ruang').value = item.ruangan;
        document.getElementById('hari').value = item.hari;
        document.getElementById('jamMulai').value = item.jam_mulai;
        document.getElementById('jamSelesai').value = item.jam_selesai;
        
        const btn = document.querySelector("#formJadwal button");
        if(btn) {
            btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Update Jadwal';
            btn.onclick = () => updateSchedule(id);
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// 7. Update Jadwal (Kirim ke Database)
async function updateSchedule(id) {
    const data = {
        id: id,
        mata_kuliah: document.getElementById('matkul').value,
        dosen: document.getElementById('dosen').value,
        ruangan: document.getElementById('ruang').value,
        hari: document.getElementById('hari').value,
        jam_mulai: document.getElementById('jamMulai').value,
        jam_selesai: document.getElementById('jamSelesai').value,
        catatan: ""
    };

    try {
        const response = await fetch('jadwal.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const res = await response.json();
        if (res.status === 'success') {
            Swal.fire('Terupdate!', 'Jadwal berhasil diubah.', 'success');
            resetForm();
            renderSchedule();
        } else {
            Swal.fire('Gagal!', res.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error!', 'Gagal menghubungi server.', 'error');
    }
}

function resetForm() {
    document.getElementById('formJadwal').reset();
    const btn = document.querySelector("#formJadwal button");
    if(btn) {
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan';
        btn.onclick = () => addSchedule(); 
    }
}

/* ============================================================
   AKTIVITAS CRUD (Connect to aktivitas_api.php)
   ============================================================ */

async function renderAktivitas() {
    const container = document.getElementById("aktivitas-list");
    if (!container) return;

    try {
        const response = await fetch('aktivitas_api.php');
        const data = await response.json();

        if (!data || data.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <p class="muted">Belum ada aktivitas. Yuk, tambah kegiatanmu!</p>
                </div>`;
            return;
        }

        container.innerHTML = data.map(item => `
            <div class="schedule-item" style="border-left: 4px solid #10b981;">
                <div class="schedule-info">
                    <h4>${escapeHtml(item.nama_aktivitas)}</h4>
                    <p class="muted">
                        <i class="fa-regular fa-calendar"></i> ${formatDateIndo(item.tanggal)} 
                        ${item.jam ? ` | <i class="fa-regular fa-clock"></i> ${item.jam.substring(0,5)}` : ''}
                    </p>
                </div>
                
                <div class="jadwal-actions">
                    <button onclick="editAktivitas(${item.id})" class="action-btn btn-edit" title="Edit">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button onclick="deleteAktivitas(${item.id})" class="action-btn btn-delete" title="Hapus">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        `).join("");

    } catch (err) {
        console.error("Gagal load aktivitas:", err);
    }
}

async function saveAktivitas() {
    const id = document.getElementById('act_id').value;
    const nama = document.getElementById('act_nama').value;
    const tanggal = document.getElementById('act_tanggal').value;
    const jam = document.getElementById('act_jam').value;

    if (!nama || !tanggal) {
        Swal.fire('Error', 'Nama dan Tanggal wajib diisi!', 'warning');
        return;
    }

    const data = { id, nama_aktivitas: nama, tanggal, jam };
    const method = id ? 'PUT' : 'POST'; 

    try {
        const response = await fetch('aktivitas_api.php', {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const res = await response.json();
        if (res.status === 'success') {
            Swal.fire('Berhasil', id ? 'Aktivitas diperbarui' : 'Aktivitas ditambahkan', 'success');
            resetActForm();
            renderAktivitas();
        } else {
            Swal.fire('Gagal', res.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    }
}

async function editAktivitas(id) {
    const response = await fetch('aktivitas_api.php');
    const allData = await response.json();
    const item = allData.find(a => a.id == id);

    if (item) {
        document.getElementById('act_id').value = item.id;
        document.getElementById('act_nama').value = item.nama_aktivitas;
        document.getElementById('act_tanggal').value = item.tanggal;
        document.getElementById('act_jam').value = item.jam;

        const btn = document.getElementById('btnSaveAct');
        if(btn) btn.innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Update';
        
        const btnCancel = document.getElementById('btnCancelAct');
        if(btnCancel) btnCancel.style.display = 'inline-block';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

async function deleteAktivitas(id) {
    if (!confirm("Hapus aktivitas ini?")) return; 

    try {
        const response = await fetch(`aktivitas_api.php?id=${id}`, { method: 'DELETE' });
        const res = await response.json();
        if (res.status === 'success') renderAktivitas();
    } catch (err) {
        alert("Gagal menghapus.");
    }
}

function resetActForm() {
    const form = document.getElementById('formAktivitas');
    if(form) form.reset();
    
    document.getElementById('act_id').value = '';
    
    const btn = document.getElementById('btnSaveAct');
    if(btn) btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan';
    
    const btnCancel = document.getElementById('btnCancelAct');
    if(btnCancel) btnCancel.style.display = 'none';
}

function formatDateIndo(dateString) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
}

/* ============================================================
   IMPORT EXCEL
   ============================================================ */
async function importExcelFile(input) {
    if (!input.files.length) return;
    const formData = new FormData();
    formData.append('file', input.files[0]);
    formData.append('import', 'excel');

    try {
        const response = await fetch('jadwal.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();
        if (res.status === 'success') {
            Swal.fire('Berhasil!', res.message, 'success');
            if(document.getElementById("jadwal-list")) renderSchedule();
            else location.reload();
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    } catch (err) {
        Swal.fire('Gagal', 'Gagal mengunggah file.', 'error');
    }
}