<?php
// Izinkan format JSON dan akses dari luar
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// ----------------------------------------------------
// KONFIGURASI DATABASE (WAJIB SAMA DENGAN api_terima_data.php)
// ----------------------------------------------------
$host = "localhost";
$user = "root";         // Pastikan username DB Anda benar
$pass = "!Muhammadiyah1912";             // Pastikan password DB Anda benar
$db   = "sistem_anpr";  // Pastikan nama DB Anda benar

// Membuat koneksi ke database
$conn = new mysqli($host, $user, $pass, $db);

// Jika koneksi gagal, kembalikan pesan error berformat JSON
if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi Database Gagal: " . $conn->connect_error]));
}

// ----------------------------------------------------
// AMBIL DATA DARI TABEL
// ----------------------------------------------------
// Mengambil 50 data terbaru, diurutkan berdasarkan waktu (paling baru di atas)
$sql = "SELECT * FROM log_kendaraan ORDER BY waktu_deteksi DESC LIMIT 50";
$result = $conn->query($sql);

// Jika nama tabel salah atau query gagal, kembalikan pesan error
if (!$result) {
    die(json_encode(["error" => "Query Gagal (Cek nama tabel): " . $conn->error]));
}

// ----------------------------------------------------
// SUSUN DATA DAN KIRIM KE WEB
// ----------------------------------------------------
$data = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Cetak data ke layar (ini yang akan dibaca oleh index.html)
echo json_encode($data);

$conn->close();
?>
