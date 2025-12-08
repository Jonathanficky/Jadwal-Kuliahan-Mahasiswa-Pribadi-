/* ============================================================
script.js (FINAL FIXED + CARD STYLE)
Compatible with all 7 HTML pages
Fix: Dropdown gear click (home, jadwal, aktivitas)
============================================================ */

/* ---------- Helpers ---------- */
function getData(key) {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch (e) {
    console.warn("getData err", e);
    return [];
  }
}

function saveData(key, arr) {
  try {
    localStorage.setItem(key, JSON.stringify(arr || []));
  } catch (e) {
    console.warn("saveData err", e);
  }
}

/* ---------- Escape HTML (fixed & secure) ---------- */
function escapeHtml(str) {
  if (typeof str !== "string") return str;
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

/* ---------- Dropdown toggle (global base) ---------- */
(function () {
  document.addEventListener("click", (e) => {
    document.querySelectorAll(".dropdown").forEach((d) => {
      if (!d.contains(e.target)) d.classList.remove("show");
    });
  });
})();

function toggleDropdown(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const container = el.closest(".dropdown") || el;
  container.classList.toggle("show");
}

/* ---------- Nav highlight ---------- */
function highlightActiveNav() {
  try {
    const page = location.pathname.split("/").pop() || "home.html";
    document.querySelectorAll(".nav-link").forEach((a) => {
      const href = a.getAttribute("href") || "";
      a.classList.toggle(
        "active",
        href === page ||
          (href === "home.html" && (page === "" || page === "index.html"))
      );
    });
  } catch (e) {
    /* ignore */
  }
}

/* ---------- Toggle password visibility ---------- */
function togglePasswordField(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === "password") {
    input.type = "text";
    btn?.querySelector("i")?.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    input.type = "password";
    btn?.querySelector("i")?.classList.replace("fa-eye-slash", "fa-eye");
  }
}

/* ============================================================
ACTIVITIES (aktivitas.html)
============================================================ */
function submitActivity() {
  const name = document.getElementById("act-name")?.value?.trim();
  const type = document.getElementById("act-type")?.value;
  const date = document.getElementById("act-date")?.value;
  const start = document.getElementById("act-start")?.value;
  const end = document.getElementById("act-end")?.value;
  const note = document.getElementById("act-note")?.value?.trim() || "";

  if (!name || !type || !date || !start || !end) {
    alert("Harap isi semua kolom penting (Nama, Jenis, Tanggal, Waktu).");
    return;
  }

  const arr = getData("activities");
  arr.push({ id: Date.now(), name, type, date, start, end, note });
  saveData("activities", arr);
  renderActivities();
  clearActivityForm();
  renderWeek();
  renderToday();
  renderSummary();
}

function clearActivityForm() {
  ["act-name", "act-type", "act-date", "act-start", "act-end", "act-note"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });
}

function renderActivities() {
  const container = document.getElementById("activities");
  if (!container) return;
  const data = getData("activities");
  if (!data.length) {
    container.innerHTML = '<p class="muted">Belum ada aktivitas ditambahkan.</p>';
    return;
  }

  container.innerHTML = data
    .map(
      (a) => `
      <div class="activity-item">
        <div class="info">
          <strong>${escapeHtml(a.name)}</strong>
          <span class="muted">${escapeHtml(a.type)} • ${escapeHtml(a.date)} • ${escapeHtml(a.start)} - ${escapeHtml(a.end)}</span>
          ${a.note ? `<span class="muted">${escapeHtml(a.note)}</span>` : ""}
        </div>
        <div class="actions">
          <button title="Hapus" onclick="deleteActivity(${a.id})">
            <i class="fa-solid fa-trash"></i>
          </button>
          <button title="Edit" onclick="editActivity(${a.id})">
            <i class="fa-regular fa-pen-to-square"></i>
          </button>
        </div>
      </div>`
    )
    .join("");
}

function deleteActivity(id) {
  if (!confirm("Hapus aktivitas ini?")) return;
  let arr = getData("activities").filter((i) => i.id !== id);
  saveData("activities", arr);
  renderActivities();
  renderWeek();
  renderToday();
  renderSummary();
}

function editActivity(id) {
  const arr = getData("activities");
  const it = arr.find((x) => x.id === id);
  if (!it) return;
  document.getElementById("act-name").value = it.name || "";
  document.getElementById("act-type").value = it.type || "";
  document.getElementById("act-date").value = it.date || "";
  document.getElementById("act-start").value = it.start || "";
  document.getElementById("act-end").value = it.end || "";
  document.getElementById("act-note").value = it.note || "";
  deleteActivity(id);
}

/* ============================================================
SCHEDULE (jadwal.html & home.html)
============================================================ */
function addSchedule() {
  const matkul = document.getElementById("matkul")?.value?.trim();
  const dosen = document.getElementById("dosen")?.value?.trim();
  const ruang = document.getElementById("ruang")?.value?.trim();
  const hari = document.getElementById("hari")?.value;
  const jamMulai = document.getElementById("jamMulai")?.value;
  const jamSelesai = document.getElementById("jamSelesai")?.value;
  const catatan = document.getElementById("catatan")?.value?.trim() || "";

  if (!matkul || !hari || !jamMulai || !jamSelesai) {
    alert("Lengkapi nama mata kuliah, hari, dan jam.");
    return;
  }

  const jadwal = getData("jadwalMahasiswa");
  jadwal.push({
    id: Date.now(),
    mataKuliah: matkul,
    dosen,
    ruangan: ruang,
    hari,
    jamMulai,
    jamSelesai,
    catatan,
  });

  saveData("jadwalMahasiswa", jadwal);
  renderWeek();
  renderToday();
  renderSummary();

  ["matkul", "dosen", "ruang", "hari", "jamMulai", "jamSelesai", "catatan"].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });
}

/* ---------- NEW CARD STYLE RENDER ---------- */
function renderSchedule() {
  const container = document.getElementById("jadwal-list");
  if (!container) return;

  const data = getData("jadwalMahasiswa");
  if (!data.length) {
    container.innerHTML = '<p class="muted">Belum ada jadwal ditambahkan.</p>';
    return;
  }

  container.innerHTML = data
    .map(
      (j) => `
      <div class="jadwal-card">
        <div class="jadwal-info">
          <h4>${escapeHtml(j.mataKuliah)}</h4>
          <p class="muted">${escapeHtml(j.hari)} • ${escapeHtml(j.jamMulai)} - ${escapeHtml(
        j.jamSelesai
      )}</p>
          <p class="muted">Dosen: ${escapeHtml(j.dosen || "-")}</p>
          <p class="muted">Ruang: ${escapeHtml(j.ruangan || "-")}</p>
          ${j.catatan ? `<p class="muted">${escapeHtml(j.catatan)}</p>` : ""}
        </div>
        <div class="jadwal-actions">
          <button title="Edit" onclick="openEditSchedule(${j.id})">
            <i class="fa-regular fa-pen-to-square"></i>
          </button>
          <button title="Hapus" onclick="deleteSchedule(${j.id})">
            <i class="fa-solid fa-trash"></i>
          </button>
        </div>
      </div>`
    )
    .join("");
}

function deleteSchedule(id) {
  if (!confirm("Hapus jadwal ini?")) return;
  const arr = getData("jadwalMahasiswa").filter((x) => x.id !== id);
  saveData("jadwalMahasiswa", arr);
  renderSchedule();
  renderWeek();
  renderToday();
  renderSummary();
}

/* ============================================================
IMPORT / EXPORT
============================================================ */
function importSchedule(e) {
  const input = e?.target || document.getElementById("fileInput");
  if (!input || !input.files?.length) {
    alert("Pilih file terlebih dahulu.");
    return;
  }

  const file = input.files[0];
  const name = file.name.toLowerCase();

  if (name.endsWith(".json")) return importJSON(file);
  if (name.endsWith(".csv")) return importCSV(file);
  if (name.endsWith(".xlsx") || name.endsWith(".xls"))
    return importExcelFile(file);

  alert("Format file tidak didukung. Gunakan CSV, JSON, atau XLSX.");
  input.value = "";
}

/* JSON Import */
function importJSON(file) {
  const reader = new FileReader();
  reader.onload = (ev) => {
    try {
      const parsed = JSON.parse(ev.target.result);
      if (!Array.isArray(parsed)) throw new Error("JSON harus array");

      const existing = getData("jadwalMahasiswa").concat(
        parsed.map((p) => ({ id: Date.now() + Math.random(), ...p }))
      );

      saveData("jadwalMahasiswa", existing);
      alert("Impor JSON sukses.");
      renderWeek();
      renderToday();
      renderSummary();
    } catch (err) {
      alert("Gagal membaca JSON: " + err.message);
    }
  };
  reader.readAsText(file, "UTF-8");
}

/* ============================================================
AUTH & SESSION
============================================================ */
function logoutUser() {
  if (confirm("Apakah Anda yakin ingin logout?")) {
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("loggedInUser");
    window.location.href = "login.html";
  }
}

function checkLoginStatus() {
  const loggedIn = localStorage.getItem("isLoggedIn");
  if (loggedIn !== "true") {
    alert("Silakan login terlebih dahulu.");
    window.location.href = "login.html";
  }
}

/* ============================================================
AUTO INIT
============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  highlightActiveNav();
  renderActivities();
});

/* ============================================================
DROPDOWN GEAR FIX (FINAL UNIVERSAL)
============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".dropdown > .btn.ghost .fa-gear").forEach((icon) => {
    const btn = icon.closest("button");
    const dropdown = btn.closest(".dropdown");
    const menu = dropdown.querySelector(".dropdown-menu");

    if (!btn || !menu) return;

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      document.querySelectorAll(".dropdown.show").forEach((d) => {
        if (d !== dropdown) d.classList.remove("show");
      });
      dropdown.classList.toggle("show");
    });
  });

  document.addEventListener("click", () => {
    document.querySelectorAll(".dropdown.show").forEach((d) => d.classList.remove("show"));
  });
});

/* ============================================================
EDIT JADWAL MODAL (jadwal.html)
============================================================ */
function openEditSchedule(id) {
  const arr = getData("jadwalMahasiswa");
  const it = arr.find((x) => x.id === id);
  if (!it) return;

  const modal = document.createElement("div");
  modal.className = "modal-backdrop";
  modal.innerHTML = `
    <div class="modal">
      <h3>Edit Jadwal</h3>
      <label>Mata Kuliah</label>
      <input id="editMatkul" value="${escapeHtml(it.mataKuliah)}">
      <label>Dosen</label>
      <input id="editDosen" value="${escapeHtml(it.dosen)}">
      <label>Ruang</label>
      <input id="editRuang" value="${escapeHtml(it.ruangan)}">
      <label>Hari</label>
      <select id="editHari">
        ${["Senin","Selasa","Rabu","Kamis","Jumat","Sabtu","Minggu"]
          .map(h=>`<option ${h===it.hari?"selected":""}>${h}</option>`).join("")}
      </select>
      <label>Jam Mulai</label>
      <input type="time" id="editMulai" value="${it.jamMulai}">
      <label>Jam Selesai</label>
      <input type="time" id="editSelesai" value="${it.jamSelesai}">
      <label>Catatan</label>
      <input id="editCatatan" value="${escapeHtml(it.catatan||"")}">
      <div class="modal-actions">
        <button class="btn primary" onclick="saveEditedSchedule(${id})">Simpan</button>
        <button class="btn ghost" onclick="this.closest('.modal-backdrop').remove()">Batal</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
}

function saveEditedSchedule(id) {
  const arr = getData("jadwalMahasiswa");
  const idx = arr.findIndex(x => x.id === id);
  if (idx === -1) return;

  arr[idx] = {
    ...arr[idx],
    mataKuliah: document.getElementById("editMatkul").value.trim(),
    dosen: document.getElementById("editDosen").value.trim(),
    ruangan: document.getElementById("editRuang").value.trim(),
    hari: document.getElementById("editHari").value,
    jamMulai: document.getElementById("editMulai").value,
    jamSelesai: document.getElementById("editSelesai").value,
    catatan: document.getElementById("editCatatan").value.trim(),
  };

  saveData("jadwalMahasiswa", arr);
  renderSchedule();
  renderWeek();
  renderToday();
  renderSummary();

  document.querySelector(".modal-backdrop")?.remove();
}

/* ============================================================
SAFE FALLBACKS (prevent ReferenceError)
============================================================ */
function renderWeek() {}
function renderToday() {}
function renderSummary() {}
