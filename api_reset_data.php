<?php
// 1. Matikan tampilan error bawaan PHP agar tidak merusak format JSON
error_reporting(0);
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

// 2. Konfigurasi Database
$host = "localhost";
$user = "root";         
$pass = "!Muhammadiyah1912";             
$db   = "sistem_anpr";  

$conn = new mysqli($host, $user, $pass, $db);

// Jika koneksi gagal, hentikan dan kirim JSON error
if ($conn->connect_error) {
    echo json_encode(["status" => "gagal", "pesan" => "Koneksi database gagal."]);
    exit;
}

// 3. Eksekusi hapus data tabel (Kembalikan ID ke 1)
$reset_db = $conn->query("TRUNCATE TABLE log_kendaraan");

if ($reset_db) {
    // 4. Hapus foto jika tabel berhasil direset
    $files = glob('uploads/*'); 
    foreach($files as $file){
        if(is_file($file)) {
            // Tambahkan simbol '@' untuk menekan error jika file terkunci oleh sistem Linux
            @unlink($file); 
        }
    }
    // Kirim balasan sukses yang bersih ke JavaScript
    echo json_encode(["status" => "sukses", "pesan" => "Tabel Database dan File Foto berhasil dibersihkan!"]);
} else {
    // Kirim balasan gagal jika tabel tidak bisa dikosongkan
    echo json_encode(["status" => "gagal", "pesan" => "Gagal mengosongkan tabel database."]);
}

$conn->close();
?>
