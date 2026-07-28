<?php
// Matikan detektor error yang membuat layar blank
error_reporting(0);

// Ambil tanggal dari URL
$tanggal_cetak = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Koneksi Database (Sama seperti biasanya)
$conn = new mysqli("localhost", "root", "!Muhammadiyah1912", "sistem_anpr");

// Ambil data
$query = "SELECT * FROM log_kendaraan WHERE waktu_deteksi LIKE '$tanggal_cetak%' ORDER BY id ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak PDF - ANPR</title>
    <style>
        /* Gaya Tampilan Kop Surat */
        body { font-family: 'Times New Roman', Times, serif; color: #000; margin: 0; padding: 20px; font-size: 12pt; background-color: #f0f0f0; }
        
        /* Kertas putih di tengah layar agar seperti PDF sungguhan */
        .kertas { background-color: #fff; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 2cm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #000; padding-bottom: 15px; }
        .header h2 { margin: 0; font-size: 18pt; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12pt; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #e0e0e0; font-weight: bold; }
        .foto-plat { max-width: 80px; max-height: 40px; }
        
        .footer-ttd { width: 100%; margin-top: 50px; text-align: right; }
        .ttd-box { display: inline-block; text-align: center; width: 250px; }
        .ttd-space { height: 80px; }

        /* Tombol melayang untuk klik Download */
        .tombol-print-area { text-align: center; padding: 20px; margin-bottom: 20px; background-color: #0d6efd; color: white; border-radius: 8px; font-family: sans-serif; }
        .btn-print { background-color: #fff; color: #0d6efd; border: none; padding: 10px 20px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; transition: 0.3s; }
        .btn-print:hover { background-color: #ffc107; color: #000; }

        /* Sembunyikan hal yang tidak perlu saat masuk mode Print/PDF */
        @media print {
            body { background-color: #fff; }
            .kertas { box-shadow: none; margin: 0; padding: 0; width: auto; min-height: auto; }
            .tombol-print-area { display: none !important; }
            @page { size: A4 portrait; margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="tombol-print-area">
        <h3 style="margin-top:0;">Preview Laporan Tanggal: <?php echo $tanggal_cetak; ?></h3>
        <p>Klik tombol di bawah ini, lalu pilih <b>"Save as PDF"</b> pada bagian <i>Destination / Printer</i>.</p>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Download PDF Sekarang</button>
    </div>

    <div class="kertas">
        <div class="header">
            <h2>Laporan Pemantauan Kendaraan (ANPR)</h2>
            <p>Sistem Parkir Cerdas Berbasis Edge AI</p>
        </div>

        <b>Tanggal Laporan:</b> <?php echo date('d F Y', strtotime($tanggal_cetak)); ?><br>
        <b>Dicetak Oleh:</b> Administrator

        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Waktu Deteksi</th>
                    <th width="25%">Plat Nomor</th>
                    <th width="15%">Status</th>
                    <th width="15%">Akurasi</th>
                    <th width="20%">Foto Bukti</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && $result->num_rows > 0) {
                    $no = 1;
                    while($row = $result->fetch_assoc()) {
                        $akurasi = number_format($row['akurasi'] * 100, 1) . '%';
                        $foto = $row['foto_path'] ? "<img src='uploads/".$row['foto_path']."' class='foto-plat'>" : "-";
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['waktu_deteksi']}</td>
                                <td><b>{$row['plat_nomor']}</b></td>
                                <td>{$row['status']}</td>
                                <td>{$akurasi}</td>
                                <td>{$foto}</td>
                              </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6'>Tidak ada data kendaraan yang masuk/keluar pada tanggal ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="footer-ttd">
            <div class="ttd-box">
                <p>Yogyakarta, <?php echo date('d F Y'); ?></p>
                <p>Mengetahui,</p>
                <div class="ttd-space"></div>
                <p><b><u>Admin ANPR System</u></b></p>
            </div>
        </div>
    </div>

</body>
</html>
