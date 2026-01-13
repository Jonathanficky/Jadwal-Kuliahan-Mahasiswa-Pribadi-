<?php
session_start();
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // READ: Ambil semua aktivitas
        $stmt = $conn->prepare("SELECT * FROM aktivitas WHERE user_id = ? ORDER BY tanggal DESC, jam ASC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST':
        // CREATE: Tambah aktivitas baru
        // Support FormData (biasa) dan JSON Raw
        $nama = $_POST['nama_aktivitas'] ?? '';
        $tgl  = $_POST['tanggal'] ?? '';
        $jam  = $_POST['jam'] ?? '';

        // Jika data dikirim via JSON (bukan FormData)
        if (empty($nama)) {
            $data = json_decode(file_get_contents("php://input"), true);
            $nama = $data['nama_aktivitas'] ?? '';
            $tgl  = $data['tanggal'] ?? '';
            $jam  = $data['jam'] ?? '';
        }

        if (empty($nama) || empty($tgl)) {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan Tanggal wajib diisi']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO aktivitas (user_id, nama_aktivitas, tanggal, jam) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $nama, $tgl, $jam);
        
        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan']);
        break;

    case 'PUT':
        // UPDATE: Edit aktivitas
        $data = json_decode(file_get_contents("php://input"), true);
        $id   = $data['id'] ?? null;
        $nama = $data['nama_aktivitas'] ?? '';
        $tgl  = $data['tanggal'] ?? '';
        $jam  = $data['jam'] ?? '';

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE aktivitas SET nama_aktivitas=?, tanggal=?, jam=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssii", $nama, $tgl, $jam, $id, $user_id);

        if ($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'message' => 'Gagal update']);
        break;

    case 'DELETE':
        // DELETE: Hapus aktivitas
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM aktivitas WHERE id=? AND user_id=?");
            $stmt->bind_param("ii", $id, $user_id);
            if ($stmt->execute()) echo json_encode(['status' => 'success']);
            else echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus']);
        }
        break;
}