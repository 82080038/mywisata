<?php include APP_ROOT . '/app/views/layouts/admin_header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Laporan & Analitik</h2>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="display-4"><?= $stats['total_users'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Bookings</h5>
                    <h2 class="display-4"><?= $stats['total_bookings'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Tiket</h5>
                    <h2 class="display-4"><?= $stats['total_tickets'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <h2 class="display-4"><?= View::currency($stats['total_revenue']) ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue Chart -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pendapatan Bulanan</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                    <?php if (empty($monthly_revenue)): ?>
                        <p class="text-muted text-center w-100 mt-3">Belum ada data pendapatan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Destinations -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Destinasi</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_destinations)): ?>
                        <p class="text-muted">Belum ada data.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_destinations as $dest): ?>
                                <tr>
                                    <td><?= View::e($dest['name']) ?></td>
                                    <td><?= $dest['order_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Top Tour Guides -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top Tour Guide</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($top_guides)): ?>
                        <p class="text-muted">Belum ada data.</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Booking</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_guides as $guide): ?>
                                <tr>
                                    <td><?= View::e($guide['name']) ?></td>
                                    <td><?= $guide['booking_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Export Laporan</h5>
                </div>
                <div class="card-body">
                    <form id="exportForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipe Laporan</label>
                                <select class="form-select" name="type" id="exportType">
                                    <option value="revenue">Pendapatan</option>
                                    <option value="bookings">Booking</option>
                                    <option value="tickets">Tiket</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" id="startDate" value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" name="end_date" id="endDate" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-primary w-100" onclick="exportReport()">
                                    <i class="fas fa-download me-2"></i>Export
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
var monthlyData = <?= json_encode(array_reverse($monthly_revenue ?? [])) ?>;

if (monthlyData && monthlyData.length > 0) {
    var ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthlyData.map(function(d) { return d.month; }),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: monthlyData.map(function(d) { return d.revenue; }),
                backgroundColor: '#0d6efd',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString(); } } }
            }
        }
    });
}

function exportReport() {
    var type = document.getElementById('exportType').value;
    var startDate = document.getElementById('startDate').value;
    var endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Pilih tanggal mulai dan akhir' });
        return;
    }
    
    fetch(window.APP_URL + 'reports/export?type=' + type + '&start_date=' + startDate + '&end_date=' + endDate)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                var csv = convertToCSV(data.data);
                downloadCSV(csv, type + '_report_' + startDate + '_' + endDate + '.csv');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Gagal export' });
            }
        });
}

function convertToCSV(data) {
    if (!data || data.length === 0) return 'No data';
    var headers = Object.keys(data[0]);
    var csv = headers.join(',') + '\n';
    data.forEach(function(row) {
        csv += headers.map(function(h) { return '"' + (row[h] || '') + '"'; }).join(',') + '\n';
    });
    return csv;
}

function downloadCSV(csv, filename) {
    var blob = new Blob([csv], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>

<?php include APP_ROOT . '/app/views/layouts/admin_footer.php'; ?>
