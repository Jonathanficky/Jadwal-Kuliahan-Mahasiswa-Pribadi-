<?php
session_start();
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['nim'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir']);
    exit;
}

$nim = $_SESSION['nim'];
$user_id = $_SESSION['user_id'];

// Fungsi cek bentrok yang mendukung pengecualian ID (untuk keperluan UPDATE)
function cekBentrok($conn, $nim, $hari, $mulai, $selesai, $excludeId = null) {
    $sql = "SELECT id FROM jadwal_kuliah WHERE nim = ? AND hari = ? AND NOT (jam_selesai <= ? OR jam_mulai >= ?)";
    if ($excludeId) $sql .= " AND id != ?";
    $stmt = $conn->prepare($sql);
    if ($excludeId) { 
        $stmt->bind_param("ssssi", $nim, $hari, $mulai, $selesai, $excludeId); 
    } else { 
        $stmt->bind_param("ssss", $nim, $hari, $mulai, $selesai); 
    }
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Ambil parameter pencarian jika ada
        $search = isset($_GET['q']) ? $_GET['q'] : '';

        if (!empty($search)) {
            // QUERY PENCARIAN (Filter Mata Kuliah ATAU Dosen)
            $sql = "SELECT * FROM jadwal_kuliah 
                    WHERE nim = ? 
                    AND (mata_kuliah LIKE ? OR dosen LIKE ?) 
                    ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai";
            
            $stmt = $conn->prepare($sql);
            $param = "%" . $search . "%";
            $stmt->bind_param("sss", $nim, $param, $param);
        } else {
            // QUERY STANDAR (Tampilkan Semua)
            $sql = "SELECT * FROM jadwal_kuliah 
                    WHERE nim = ? 
                    ORDER BY FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $nim);
        }

        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        break;

    case 'POST': // CREATE: Tambah Manual atau Import Excel
        if (isset($_POST['import']) && $_POST['import'] === 'excel') {
            try {
                require_once __DIR__ . '/vendor/autoload.php';
                $spreadsheet = IOFactory::load($_FILES['file']['tmp_name']);
                $rows = $spreadsheet->getActiveSheet()->toArray();
                unset($rows[0]); // Lewati header
                $count = 0;
                foreach ($rows as $r) {
                    if (empty($r[0])) continue;
                    [$matkul, $dosen, $ruang, $hari, $mulai, $selesai, $catatan] = $r;
                    if (cekBentrok($conn, $nim, $hari, $mulai, $selesai)) continue;
                    $stmt = $conn->prepare("INSERT INTO jadwal_kuliah (nim, mata_kuliah, dosen, ruangan, hari, jam_mulai, jam_selesai, catatan, user_id) VALUES (?,?,?,?,?,?,?,?,?)");
                    $stmt->bind_param("ssssssssi", $nim, $matkul, $dosen, $ruang, $hari, $mulai, $selesai, $catatan, $user_id);
                    $stmt->execute();
                    $count++;
                }
                echo json_encode(['status' => 'success', 'message' => "$count data berhasil diimport"]);
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            // Input Manual via Fetch JSON
            $data = json_decode(file_get_contents("php://input"), true);
            $matkul = $data['mata_kuliah'] ?? "";
            $mulai = $data['jam_mulai'] ?? "";
            $selesai = $data['jam_selesai'] ?? "";
            $hari = $data['hari'] ?? "";
            $dosen = $data['dosen'] ?? "";
            $ruangan = $data['ruangan'] ?? "";
            $catatan = $data['catatan'] ?? "";

            if (cekBentrok($conn, $nim, $hari, $mulai, $selesai)) {
                echo json_encode(['status' => 'error', 'message' => 'Jadwal bentrok dengan jam lain!']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO jadwal_kuliah (nim, mata_kuliah, dosen, ruangan, hari, jam_mulai, jam_selesai, catatan, user_id) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssi", $nim, $matkul, $dosen, $ruangan, $hari, $mulai, $selesai, $catatan, $user_id);
            if ($stmt->execute()) echo json_encode(['status' => 'success']);
            else echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data']);
        }
        break;

    case 'PUT': // UPDATE: Mengubah data jadwal yang sudah ada
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? null;
        $matkul = $data['mata_kuliah'] ?? "";
        $mulai = $data['jam_mulai'] ?? "";
        $selesai = $data['jam_selesai'] ?? "";
        $hari = $data['hari'] ?? "";
        $dosen = $data['dosen'] ?? "";
        $ruangan = $data['ruangan'] ?? "";
        $catatan = $data['catatan'] ?? "";

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
            exit;
        }

        // Validasi bentrok dengan mengecualikan ID jadwal saat ini
        if (cekBentrok($conn, $nim, $hari, $mulai, $selesai, $id)) {
            echo json_encode(['status' => 'error', 'message' => 'Update gagal, jam baru bentrok dengan jadwal lain!']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE jadwal_kuliah SET mata_kuliah=?, dosen=?, ruangan=?, hari=?, jam_mulai=?, jam_selesai=?, catatan=? WHERE id=? AND nim=?");
        $stmt->bind_param("sssssssis", $matkul, $dosen, $ruangan, $hari, $mulai, $selesai, $catatan, $id, $nim);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate data']);
        }
        break;

    case 'DELETE': // DELETE: Menghapus jadwal
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM jadwal_kuliah WHERE id=? AND nim=?");
            $stmt->bind_param("is", $id, $nim);
            if ($stmt->execute()) echo json_encode(['status' => 'success']);
            else echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data']);
        }
        break;
}