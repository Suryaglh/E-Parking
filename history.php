<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History & Analytics - ANPR System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Library Chart.js untuk Grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #333; margin: 0; overflow-x: hidden; }
        .wrapper { display: flex; min-height: 100vh; }
        
        /* SIDEBAR & TOGGLE ANIMATION */
        .sidebar { background-color: #ffffff; width: 260px; padding: 20px; display: flex; flex-direction: column; border-right: 1px solid #e0e0e0; position: fixed; height: 100vh; z-index: 1000; transition: all 0.3s ease; overflow-x: hidden; white-space: nowrap; }
        .sidebar.collapsed { width: 80px; padding: 20px 10px; }
        .sidebar.collapsed .brand { justify-content: center; }
        .sidebar.collapsed .brand span { display: none; }
        .sidebar.collapsed .menu-item { justify-content: center; padding: 12px 0; }
        .sidebar.collapsed .menu-item i { margin-right: 0; font-size: 1.4rem; }
        .sidebar.collapsed .menu-item span { display: none; }

        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.1rem; color: #2c3e50; margin-bottom: 40px; }
        .brand i { color: #0d6efd; font-size: 1.5rem; }
        
        .menu-item { color: #6c757d; text-decoration: none; display: flex; align-items: center; padding: 12px 15px; margin-bottom: 5px; font-weight: 500; border-radius: 8px; transition: 0.2s; cursor: pointer; }
        .menu-item i { font-size: 1.2rem; margin-right: 15px; width: 20px; text-align: center; }
        .menu-item:hover { background-color: #f8f9fa; color: #0d6efd; }
        .menu-item.active { background-color: #e9f2ff; color: #0d6efd; }
        
        /* FIX WARNA MERAH UNTUK TOMBOL RESET & LOGOUT */
        .reset-btn:hover { background-color: #fee2e2 !important; color: #dc3545 !important; }
        .logout-btn { margin-top: auto; color: #6c757d; }
        .logout-btn:hover { background-color: #fee2e2 !important; color: #dc3545 !important; }
        
        /* KONTEN UTAMA */
        .main-content { margin-left: 260px; padding: 25px 40px; width: calc(100% - 260px); transition: all 0.3s ease; }
        .main-content.expanded { margin-left: 80px; width: calc(100% - 80px); }

        .top-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; border-bottom: 1px solid #e0e0e0; padding-bottom: 20px; }
        
        .toggle-btn { font-size: 1.8rem; cursor: pointer; color: #2c3e50; margin-right: 15px; transition: 0.2s; margin-top: 2px; }
        .toggle-btn:hover { color: #0d6efd; }

        .title-section h2 { font-weight: 600; font-size: 1.4rem; margin-bottom: 5px; color: #212529; }
        .title-section p { color: #888; font-size: 0.9rem; margin: 0; }
        
        .header-info { display: flex; align-items: center; gap: 25px; font-size: 0.9rem; color: #555; }
        
        /* AREA GRAFIK */
        .chart-area { background: white; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; margin-bottom: 30px; height: 300px; }
        
        /* FILTER ACTIONS */
        .filter-actions { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .btn-filter { background: white; border: 1px solid #ddd; padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; color: #555; cursor: pointer; transition: 0.2s; font-weight: 500; }
        .btn-filter:hover, .btn-filter.active { background: #0d6efd; color: white; border-color: #0d6efd; }
        
        .btn-export { background: #198754; color: white; border: none; padding: 6px 15px; border-radius: 20px; font-size: 0.85rem; cursor: pointer; margin-left: auto; font-weight: 500; }
        .btn-export:hover { background: #146c43; }
        
        .date-picker { padding: 5px 15px; border-radius: 20px; border: 1px solid #ddd; outline: none; font-size: 0.85rem; color: #555; cursor: pointer; background: white; }
        
        /* KARTU STATISTIK */
        .stat-card { background: white; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; display: flex; align-items: center; gap: 20px; }
        .icon-box { width: 60px; height: 60px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 1.8rem; color: white; }
        .icon-blue { background-color: #0d6efd; }
        .icon-green { background-color: #198754; }
        .icon-orange { background-color: #fd7e14; }
        .icon-purple { background-color: #6f42c1; }
        
        .stat-info h5 { font-size: 0.85rem; color: #6c757d; margin-bottom: 5px; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; margin: 0; color: #212529; }
        .stat-info span { font-size: 0.8rem; color: #6c757d; }

        /* AREA TABEL */
        .table-area { background: white; border: 1px solid #e0e0e0; border-radius: 10px; padding: 25px; }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .table-header h5 { font-weight: 600; margin: 0; color: #212529; }
        
        .search-box { background: #f8f9fa; border: 1px solid #ddd; border-radius: 20px; padding: 5px 15px; display: flex; align-items: center; width: 250px; }
        .search-box input { border: none; outline: none; width: 100%; font-size: 0.9rem; margin-left: 8px; background: transparent; }
        .search-box i { color: #888; }
        
        table { width: 100%; border-collapse: collapse; text-align: center; font-size: 0.9rem; }
        th { font-weight: 600; color: #555; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        td { padding: 15px 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        /* EFEK ZOOM FOTO DI TABEL */
        .foto-plat { max-width: 90px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: zoom-in; transition: transform 0.2s; }
        .foto-plat:hover { transform: scale(1.1); box-shadow: 0 4px 8px rgba(0,0,0,0.2); position: relative; z-index: 10; }
        
        .badge-status { padding: 5px 15px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; display: inline-block; }
        .badge-masuk { background-color: #e6f4ea; color: #1e8e3e; }
        .badge-keluar { background-color: #fce8e6; color: #d93025; }
        
        .table-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 0.85rem; color: #6c757d; }
        .pagination-container { display: flex; gap: 5px; align-items: center; }
        .page-item { width: 30px; height: 30px; display: flex; justify-content: center; align-items: center; border-radius: 5px; cursor: pointer; color: #555; }
        .page-item:hover { background-color: #f1f1f1; }
        .page-item.active { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <i class="bi bi-camera-fill"></i>
            <span>ANPR SYSTEM</span>
        </div>
        
        <a class="menu-item" onclick="window.location.href='index.php'">
            <i class="bi bi-grid-1x2-fill"></i> <span>Dashboard</span>
        </a>
        <a class="menu-item" data-bs-toggle="modal" data-bs-target="#printModal">
            <i class="bi bi-printer"></i> <span>Print / PDF</span>
        </a>
        <!-- Menu History Active di halaman ini -->
        <a class="menu-item active" onclick="window.location.href='history.php'">
            <i class="bi bi-bar-chart-fill"></i> <span>History & Analytics</span>
        </a>
        <a class="menu-item reset-btn" onclick="resetDatabase()">
            <i class="bi bi-trash"></i> <span>Reset</span>
        </a>
        
        <a class="menu-item logout-btn" onclick="confirmLogout()">
            <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
        </a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content" id="mainContent">
        <div class="top-header">
            <div class="title-section" style="display: flex; align-items: flex-start;">
                <!-- Tombol Garis Tiga (Hamburger) -->
                <i class="bi bi-list toggle-btn" onclick="toggleSidebar()"></i>
                <div>
                    <h2>Data Analytics & History</h2>
                    <p>Visualisasi tren parkir dan riwayat kendaraan</p>
                </div>
            </div>
            <div class="header-info">
                <span><i class="bi bi-person-circle fs-5"></i> <?php echo isset($_SESSION['username']) ? ucfirst($_SESSION['username']) : 'Admin'; ?></span>
            </div>
        </div>

        <!-- QUICK FILTERS & EXPORT -->
        <div class="filter-actions">
            <button class="btn-filter" id="btn-semua" onclick="setFilter('semua')">Semua Data</button>
            <button class="btn-filter" id="btn-7hari" onclick="setFilter('7hari')">7 Hari Terakhir</button>
            <button class="btn-filter" id="btn-bulan" onclick="setFilter('bulan')">Bulan Ini</button>
            <input type="date" id="datePicker" class="date-picker" onchange="setFilter('custom')">
            
            <button class="btn-export" onclick="exportToCSV()"><i class="bi bi-file-earmark-excel"></i> Export CSV</button>
        </div>

        <!-- GRAFIK CHART.JS -->
        <div class="chart-area">
            <canvas id="parkingChart"></canvas>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon-box icon-blue"><i class="bi bi-car-front-fill"></i></div>
                    <div class="stat-info">
                        <h5>Total Detection</h5>
                        <h3 id="stat-total">0</h3>
                        <span>Filtered Data</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon-box icon-green"><i class="bi bi-box-arrow-in-right"></i></div>
                    <div class="stat-info">
                        <h5>Entries</h5>
                        <h3 id="stat-masuk">0</h3>
                        <span>Filtered Data</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon-box icon-orange"><i class="bi bi-box-arrow-right"></i></div>
                    <div class="stat-info">
                        <h5>Exits</h5>
                        <h3 id="stat-keluar">0</h3>
                        <span>Filtered Data</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon-box icon-purple"><i class="bi bi-bullseye"></i></div>
                    <div class="stat-info">
                        <h5>Average Accuracy</h5>
                        <h3 id="stat-akurasi">0%</h3>
                        <span>Filtered Data</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL DATA -->
        <div class="table-area">
            <div class="table-header">
                <h5 id="table-title"><i class="bi bi-archive-fill"></i> Riwayat Kendaraan</h5>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari Plat..." onkeyup="processData()">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Waktu Deteksi</th>
                        <th>Plat Nomor</th>
                        <th>Status</th>
                        <th>Akurasi</th>
                        <th>Foto</th>
                    </tr>
                </thead>
                <tbody id="tabel-body">
                    <tr><td colspan="6">Loading data...</td></tr>
                </tbody>
            </table>

            <div class="table-footer">
                <div id="showing-text">Showing 0 to 0 of 0 results</div>
                <div class="pagination-container" id="paginationControls"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Tanggal Cetak -->
<div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-printer"></i> Pilih Tanggal Laporan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label text-muted fw-semibold">Tanggal yang ingin dicetak/disimpan:</label>
        <input type="date" id="printDateInput" class="form-control">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="prosesCetak()">Cetak Sekarang</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Zoom Foto -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="background-color: transparent; border: none;">
      <div class="modal-header" style="border-bottom: none; justify-content: flex-end;">
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background-color: white; border-radius: 50%; opacity: 1; padding: 10px;"></button>
      </div>
      <div class="modal-body text-center p-0">
        <img id="zoomedImage" src="" alt="Zoomed Plat" class="img-fluid" style="max-height: 80vh; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let allData = []; 
    let historyData = []; 
    let currentPage = 1;
    const rowsPerPage = 5; 
    let myChart = null; 
    let currentFilter = 'semua';

    // FITUR TOGGLE SIDEBAR
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
    }

    // FUNGSI ZOOM FOTO BESAR (MODAL)
    function zoomFoto(imageSrc) {
        document.getElementById('zoomedImage').src = imageSrc;
        const fotoModal = new bootstrap.Modal(document.getElementById('fotoModal'));
        fotoModal.show();
    }

    // AMBIL DATA DARI SERVER
    async function loadAllData() {
        try {
            const response = await fetch('api_tampil_data.php');
            const data = await response.json();
            
            if (!data.error) {
                allData = data;
                setFilter('semua'); 
            }
        } catch (error) {
            console.error("Gagal memuat data", error);
        }
    }

    // LOGIKA FILTER CEPAT
    function setFilter(filterType) {
        currentFilter = filterType;
        let btnSemua = document.getElementById('btn-semua');
        let btn7Hari = document.getElementById('btn-7hari');
        let btnBulan = document.getElementById('btn-bulan');
        let datePicker = document.getElementById('datePicker');
        let title = document.getElementById('table-title');
        
        btnSemua.classList.remove('active');
        btn7Hari.classList.remove('active');
        btnBulan.classList.remove('active');

        const now = new Date();
        let filtered = [];

        if (filterType === 'semua') {
            btnSemua.classList.add('active');
            datePicker.value = '';
            title.innerHTML = '<i class="bi bi-archive-fill"></i> Riwayat Kendaraan (Semua Data)';
            filtered = [...allData];
        } 
        else if (filterType === '7hari') {
            btn7Hari.classList.add('active');
            datePicker.value = '';
            title.innerHTML = '<i class="bi bi-archive-fill"></i> Riwayat Kendaraan (7 Hari Terakhir)';
            
            let past7Days = new Date();
            past7Days.setDate(now.getDate() - 7);
            
            filtered = allData.filter(row => {
                let rowDate = new Date(row.waktu_deteksi.split(' ')[0]);
                return rowDate >= past7Days && rowDate <= now;
            });
        }
        else if (filterType === 'bulan') {
            btnBulan.classList.add('active');
            datePicker.value = '';
            title.innerHTML = '<i class="bi bi-archive-fill"></i> Riwayat Kendaraan (Bulan Ini)';
            
            let currentMonth = String(now.getMonth() + 1).padStart(2, '0');
            let currentYear = now.getFullYear();
            let monthStr = `${currentYear}-${currentMonth}`;
            
            filtered = allData.filter(row => row.waktu_deteksi.startsWith(monthStr));
        }
        else if (filterType === 'custom') {
            let selDate = datePicker.value;
            title.innerHTML = `<i class="bi bi-archive-fill"></i> Riwayat Kendaraan (${selDate})`;
            filtered = allData.filter(row => row.waktu_deteksi.startsWith(selDate));
        }

        historyData = filtered;
        document.getElementById('searchInput').value = ''; 
        processData();
    }

    // PROSES PENCARIAN & STATISTIK
    function processData() {
        const searchVal = document.getElementById('searchInput').value.toLowerCase();
        let finalData = historyData.filter(row => row.plat_nomor.toLowerCase().includes(searchVal));
        
        // Update Statistik Kartu
        let total = finalData.length;
        let masuk = 0;
        let keluar = 0;
        let totalAkurasi = 0;

        finalData.forEach(row => {
            if(row.status.toUpperCase() === 'MASUK') masuk++;
            if(row.status.toUpperCase() === 'KELUAR') keluar++;
            totalAkurasi += parseFloat(row.akurasi);
        });

        let rataAkurasi = total > 0 ? ((totalAkurasi / total) * 100).toFixed(1) : 0;

        document.getElementById('stat-total').innerText = total;
        document.getElementById('stat-masuk').innerText = masuk;
        document.getElementById('stat-keluar').innerText = keluar;
        document.getElementById('stat-akurasi').innerText = rataAkurasi + "%";

        currentPage = 1; 
        renderTable(finalData);
        updateChart(finalData); 
    }

    // UPDATE GRAFIK
    function updateChart(dataForChart) {
        const ctx = document.getElementById('parkingChart').getContext('2d');
        
        let dateCounts = {};
        dataForChart.forEach(row => {
            let dateOnly = row.waktu_deteksi.split(' ')[0]; 
            if(!dateCounts[dateOnly]) dateCounts[dateOnly] = 0;
            dateCounts[dateOnly]++;
        });

        let sortedDates = Object.keys(dateCounts).sort();
        let counts = sortedDates.map(date => dateCounts[date]);

        if (myChart != null) {
            myChart.destroy();
        }

        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: sortedDates,
                datasets: [{
                    label: 'Jumlah Kendaraan',
                    data: counts,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // RENDER TABEL
    function renderTable(dataToRender) {
        const tbody = document.getElementById('tabel-body');
        tbody.innerHTML = '';

        const totalFiltered = dataToRender.length;
        if (totalFiltered === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="color:#888;">Tidak ada data ditemukan.</td></tr>';
            document.getElementById('showing-text').innerText = `Showing 0 to 0 of 0 results`;
            renderPagination(0, dataToRender);
            return;
        }

        const totalPages = Math.ceil(totalFiltered / rowsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, totalFiltered);
        const pageData = dataToRender.slice(startIndex, endIndex);

        document.getElementById('showing-text').innerText = `Showing ${startIndex + 1} to ${endIndex} of ${totalFiltered} results`;

        pageData.forEach((row, index) => {
            const noUrut = startIndex + index + 1;
            const akurasiPersen = (row.akurasi * 100).toFixed(1) + '%';
            
            // TAMPILKAN FOTO LANGSUNG DENGAN ONCLICK ZOOM
            const fotoHtml = row.foto_path ? `<img src="uploads/${row.foto_path}" class="foto-plat" onclick="zoomFoto('uploads/${row.foto_path}')" title="Klik untuk memperbesar">` : '<span class="text-muted">-</span>';
            
            let textStatus = row.status.toUpperCase();
            let badgeClass = textStatus === 'MASUK' ? 'badge-masuk' : 'badge-keluar';
            let labelStatus = textStatus === 'MASUK' ? 'ENTRY' : 'EXIT';

            tbody.innerHTML += `
                <tr>
                    <td>${noUrut}</td>
                    <td>${row.waktu_deteksi}</td>
                    <td style="font-weight:600;">${row.plat_nomor}</td>
                    <td><span class="badge-status ${badgeClass}">${labelStatus}</span></td>
                    <td>${akurasiPersen}</td>
                    <td>${fotoHtml}</td>
                </tr>
            `;
        });

        renderPagination(totalPages, dataToRender);
    }

    function renderPagination(totalPages, dataToRender) {
        const pagContainer = document.getElementById('paginationControls');
        pagContainer.innerHTML = '';
        if (totalPages <= 1) return;

        pagContainer.innerHTML += `<div class="page-item" onclick="changePage(${currentPage - 1}, true)"><i class="bi bi-chevron-left"></i></div>`;

        for (let i = 1; i <= totalPages; i++) {
            if(i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                let activeClass = (i === currentPage) ? 'active' : '';
                pagContainer.innerHTML += `<div class="page-item ${activeClass}" onclick="changePage(${i}, false)">${i}</div>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                pagContainer.innerHTML += `<div style="padding: 0 5px; color: #888;">...</div>`;
            }
        }
        pagContainer.innerHTML += `<div class="page-item" onclick="changePage(${currentPage + 1}, true)"><i class="bi bi-chevron-right"></i></div>`;
    }

    function changePage(newPage, isArrow) {
        const searchVal = document.getElementById('searchInput').value.toLowerCase();
        let currentData = historyData.filter(row => row.plat_nomor.toLowerCase().includes(searchVal));
        const totalPages = Math.ceil(currentData.length / rowsPerPage);
        
        if (newPage >= 1 && newPage <= totalPages) {
            currentPage = newPage;
            renderTable(currentData);
        }
    }

    // EXPORT CSV
    function exportToCSV() {
        const searchVal = document.getElementById('searchInput').value.toLowerCase();
        let exportData = historyData.filter(row => row.plat_nomor.toLowerCase().includes(searchVal));
        
        if (exportData.length === 0) {
            alert("Tidak ada data untuk di-export!");
            return;
        }

        let csvContent = "data:text/csv;charset=utf-8,No,Waktu Deteksi,Plat Nomor,Status,Akurasi\n";

        exportData.forEach((row, index) => {
            let no = index + 1;
            let akurasi = (row.akurasi * 100).toFixed(1) + "%";
            let line = `${no},${row.waktu_deteksi},${row.plat_nomor},${row.status},${akurasi}`;
            csvContent += line + "\n";
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        
        let dateStr = new Date().toISOString().split('T')[0];
        link.setAttribute("download", `Laporan_ANPR_${dateStr}.csv`);
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // CETAK LAPORAN
    function prosesCetak() {
        const tgl = document.getElementById('printDateInput').value;
        if(tgl === "") {
            alert("Silakan pilih tanggal terlebih dahulu!");
            return;
        }
        const printModal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
        printModal.hide();
        window.open('cetak.php?tanggal=' + tgl, '_blank');
    }

    async function resetDatabase() {
        if (confirm("⚠️ PERINGATAN!\n\nHapus SEMUA data dan foto secara permanen?")) {
            try {
                const response = await fetch('api_reset_data.php');
                const result = await response.json();
                alert(result.pesan);
                loadAllData(); 
            } catch (error) { alert("Gagal mereset database."); }
        }
    }
    
    function confirmLogout() {
        if (confirm("Apakah Anda yakin ingin keluar dari sistem?")) { window.location.href = "logout.php"; }
    }

    loadAllData();

</script>
</body>
</html>
