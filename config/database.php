<?php
// Pengaturan Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "jadwal_kuliah_db"; // Pastikan nama DB ini sesuai dengan yang ada di phpMyAdmin

// Membuat koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Periksa Koneksi
if (!$conn) {
    // Menggunakan format JSON agar jika dipanggil oleh AJAX/Fetch JS tidak merusak format data
    die(json_encode([
        "status" => "error", 
        "message" => "Koneksi database gagal: " . mysqli_connect_error()
    ]));
}

// Set Charset ke utf8mb4 agar mendukung karakter khusus/emoji (jika ada catatan)
mysqli_set_charset($conn, "utf8mb4");

// Set Timezone (Sangat penting untuk fitur jadwal agar waktu server sinkron)
date_default_timezone_set('Asia/Jakarta');
?>