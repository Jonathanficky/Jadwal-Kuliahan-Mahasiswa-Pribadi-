<?php
session_start();

/* ================== CEK LOGIN ================== */
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: login.php");
  exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Beranda - Jadwal Perkuliahan Mahasiswa</title>

    <link rel="stylesheet" href="style.css" />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
      rel="stylesheet"
    />

    <style>
      .content-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 16px;
        padding: 18px;
      }

      .calendar-week {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 12px;
      }

      .calendar-day {
        padding: 8px;
        border-radius: 6px;
        background: #f5f5f5;
        cursor: pointer;
      }

      .calendar-day.has-event {
        background: #e8f7ff;
        font-weight: 600;
      }

      .day-name {
        font-size: 0.95rem;
      }

      .day-content {
        font-size: 0.85rem;
        color: #444;
      }

      .list-item {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
      }
    </style>
  </head>

  <body>
    <!-- NAVBAR -->
    <header class="topbar">
      <div class="brand">Jadwal Perkuliahan Mahasiswaa</div>

      <nav class="nav">
        <a href="home.php" class="nav-link">
          <i class="fa-solid fa-house"></i> Beranda
        </a>
        <a href="jadwal.php" class="nav-link">
          <i class="fa-regular fa-calendar-days"></i> Jadwal
        </a>
        <a href="aktivitas.php" class="nav-link">
          <i class="fa-solid fa-list-check"></i> Aktivitas
        </a>
      </nav>

      <div class="actions">
        <div class="dropdown" id="opts-home">
          <button class="btn ghost">
            <i class="fa-solid fa-gear"></i>
          </button>
          <div class="dropdown-menu">
            <a href="profil.php">
              <i class="fa-regular fa-user"></i> Profil
            </a>
            <a href="password.php">
              <i class="fa-solid fa-key"></i> Ubah Password
            </a>
            <a href="logout.php">
              <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
          </div>
        </div>
      </div>
    </header>

    <main class="container content-grid">
      <section class="card wide">
        <div class="card-head">
          <h3><i class="fa-regular fa-calendar"></i> Jadwal Hari Ini</h3>
          <p class="muted" id="todayLabel">—</p>
        </div>

        <div class="card-body flow" id="homeActivities">
          <p class="muted">Tidak ada jadwal untuk hari ini.</p>
        </div>
      </section>

      <aside class="card narrow">
        <h4><i class="fa-regular fa-calendar-days"></i> Kalender Minggu Ini</h4>
        <div id="calendarWeek" class="calendar-week"></div>

        <h4><i class="fa-solid fa-chart-simple"></i> Ringkasan Aktivitas</h4>
        <div id="summaryList" class="flow"></div>
      </aside>
    </main>

    <!-- ================= JS ASLI TIDAK DIUBAH ================= -->
    <script>
      const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
      const today = new Date();

      const todayLabel = document.getElementById("todayLabel");
      const homeActivities = document.getElementById("homeActivities");
      const calendarWeek = document.getElementById("calendarWeek");
      const summaryList = document.getElementById("summaryList");

      todayLabel.textContent =
        hari[today.getDay()] + ", " + today.toLocaleDateString("id-ID");

      let jadwal = [];
      try {
        const raw = localStorage.getItem("jadwalMahasiswa");
        if (raw) jadwal = JSON.parse(raw);
      } catch {}

      function renderToday() {
        homeActivities.innerHTML = "";
        const dayName = hari[today.getDay()];
        const data = jadwal.filter(j => j.hari === dayName);

        if (!data.length) {
          homeActivities.innerHTML =
            '<p class="muted">Tidak ada jadwal untuk hari ini.</p>';
          return;
        }

        data.forEach(item => {
          const el = document.createElement("div");
          el.className = "list-item";
          el.innerHTML = `
            <strong>${item.mataKuliah}</strong><br>
            ${item.jamMulai} - ${item.jamSelesai} | ${item.ruangan}<br>
            <small>${item.dosen}</small>
          `;
          homeActivities.appendChild(el);
        });
      }

      function renderWeek() {
        calendarWeek.innerHTML = "";
        const start = new Date(today);
        start.setDate(today.getDate() - today.getDay() + 1);

        for (let i = 0; i < 6; i++) {
          const d = new Date(start);
          d.setDate(start.getDate() + i);
          const dayName = hari[d.getDay()];
          const count = jadwal.filter(j => j.hari === dayName).length;

          const el = document.createElement("div");
          el.className = "calendar-day" + (count ? " has-event" : "");
          el.innerHTML = `
            <div class="day-name">${dayName}</div>
            <div class="day-content">${count ? count + " Mata Kuliah" : "-"}</div>
          `;
          calendarWeek.appendChild(el);
        }
      }

      function renderSummary() {
        summaryList.innerHTML = jadwal.length
          ? `<p>Total mata kuliah tersimpan: <strong>${jadwal.length}</strong></p>`
          : `<p class="muted">Belum ada jadwal tersimpan.</p>`;
      }

      renderToday();
      renderWeek();
      renderSummary();
    </script>

    <script src="script.js"></script>
  </body>
</html>
