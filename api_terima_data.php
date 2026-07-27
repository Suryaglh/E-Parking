<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// [KODE BARU] 1. SET TIMEZONE KE WIB AGAR TIDAK SELISIH 7 JAM
date_default_timezone_set('Asia/Jakarta');

// --- 2. OTENTIKASI API KEY ---
$api_key_diterima = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';
$api_key_server = '!Muhammadiyah1912'; // Sesuai kunci yang sudah kita sepakati

if ($api_key_diterima !== $api_key_server) {
    header("HTTP/1.1 403 Forbidden");
    exit("Akses ditolak: API Key tidak valid.");
}

// 3. Konfigurasi Database
$host = "localhost";
$user = "root";        
$pass = "!Muhammadiyah1912";           
$db   = "sistem_anpr";  

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("ERROR 1: KONEKSI DATABASE GAGAL - " . $conn->connect_error);
}

// 4. Tangkap data dari Jetson
$plat_nomor = isset($_POST['plat_nomor']) ? $_POST['plat_nomor'] : 'UNKNOWN';
$status     = isset($_POST['status']) ? $_POST['status'] : 'UNKNOWN';
$akurasi    = isset($_POST['akurasi']) ? $_POST['akurasi'] : 0.0;

// --- REVISI WAKTU MILIDETIK ---
// A. Tangkap waktu asli dari Jetson. Jika alat gagal mengirim, default ke waktu sekarang + .000
$waktu_dari_jetson = isset($_POST['waktu_kirim']) ? $_POST['waktu_kirim'] : date('Y-m-d H:i:s.000');

// B. Buat waktu terima di Server PHP pakai microtime agar dapat 3 digit milidetik
$waktu_micro = microtime(true);
$milidetik = sprintf("%03d", ($waktu_micro - floor($waktu_micro)) * 1000);
$waktu_di_server = date('Y-m-d H:i:s.', (int)$waktu_micro) . $milidetik;
// (Catatan: Saya menambahkan (int) di depan $waktu_micro agar PHP kamu tidak error jika versinya sangat ketat)

$foto_path  = "";

// === FITUR ANTI-DOUBLE BERDASARKAN STATUS TERAKHIR ===
// Kita mengecek status terakhir dari plat nomor ini di database
$stmt = $conn->prepare("SELECT status FROM log_kendaraan WHERE plat_nomor = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $plat_nomor);
$stmt->execute();
$cek_result = $stmt->get_result();

if ($cek_result->num_rows > 0) {
    $row = $cek_result->fetch_assoc();
    $status_terakhir = $row['status'];
    
    // Jika status terakhirnya SAMA dengan status yang mau dikirim sekarang, berarti ini SPAM (mobil masih diam di gerbang)
    if ($status_terakhir == $status) {
        echo "SKIP: Data duplikat (Mobil dengan plat $plat_nomor sedang berada di status $status).";
        $stmt->close();
        $conn->close();
        exit();
    }
}
$stmt->close();

// 5. Cek & Simpan Foto
if (isset($_FILES['foto'])) {
    $nama_file = basename($_FILES['foto']['name']);
    $tmp_file  = $_FILES['foto']['tmp_name'];
    if (move_uploaded_file($tmp_file, "uploads/" . $nama_file)) {
        $foto_path = $nama_file;
    }
}

// 6. Masukkan ke Database (Prepared Statement)
$stmt = $conn->prepare("INSERT INTO log_kendaraan (plat_nomor, status, akurasi, foto_path, waktu_deteksi, waktu_kirim) VALUES (?, ?, ?, ?, ?, ?)");

// Memasukkan $waktu_dari_jetson ke 'waktu_deteksi' dan $waktu_di_server ke 'waktu_kirim'
$stmt->bind_param("ssdsss", $plat_nomor, $status, $akurasi, $foto_path, $waktu_dari_jetson, $waktu_di_server);

if ($stmt->execute()) {
    echo "Data sukses masuk beserta milidetiknya!";
} else {
    echo "ERROR: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
