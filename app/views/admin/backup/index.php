<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1">Backup Database</h2>
                    <p class="text-muted mb-0">Kelola backup dan restore database aplikasi</p>
                </div>
                <button class="btn btn-primary" id="btn-create-backup">
                    <i class="fas fa-download me-2"></i>Buat Backup Baru
                </button>
            </div>

            <!-- Backup Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-0">Total Backup</h6>
                                    <h3 class="mb-0" id="stat-total-backups">-</h3>
                                </div>
                                <i class="fas fa-database fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-0">Total Size</h6>
                                    <h3 class="mb-0" id="stat-total-size">-</h3>
                                </div>
                                <i class="fas fa-hdd fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="card-title mb-0">Backup Terakhir</h6>
                                    <h5 class="mb-0" id="stat-latest-backup">-</h5>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup List -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Daftar Backup</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="backup-table">
                            <thead>
                                <tr>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="backup-list">
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.backup-actions .btn {
    margin-right: 5px;
}
</style>

<script>
// Load backup statistics
function loadStats() {
    API.get('backup/stats', {}, function(res) {
        if (res.status === 'success') {
            $('#stat-total-backups').text(res.data.total_backups);
            $('#stat-total-size').text(res.data.total_size_formatted);
            
            if (res.data.latest_backup) {
                const date = new Date(res.data.latest_backup.created_at * 1000);
                $('#stat-latest-backup').text(date.toLocaleString('id-ID'));
            } else {
                $('#stat-latest-backup').text('Belum ada');
            }
        }
    });
}

// Load backup list
function loadBackups() {
    API.get('backup/list', {}, function(res) {
        if (res.status === 'success') {
            let html = '';
            
            if (res.data.length === 0) {
                html = '<tr><td colspan="4" class="text-center text-muted">Belum ada backup database</td></tr>';
            } else {
                res.data.forEach(function(backup) {
                    const date = new Date(backup.created_at * 1000);
                    const sizeFormatted = formatBytes(backup.size);
                    
                    html += '<tr>';
                    html += '<td><code class="text-primary">' + backup.filename + '</code></td>';
                    html += '<td>' + sizeFormatted + '</td>';
                    html += '<td>' + date.toLocaleString('id-ID') + '</td>';
                    html += '<td class="backup-actions">';
                    html += '<a href="' + BASE_URL + 'backup/download/' + backup.filename + '" class="btn btn-sm btn-info" title="Download">';
                    html += '<i class="fas fa-download"></i>';
                    html += '</a>';
                    html += '<button class="btn btn-sm btn-warning btn-restore" data-file="' + backup.filename + '" title="Restore">';
                    html += '<i class="fas fa-undo"></i>';
                    html += '</button>';
                    html += '<button class="btn btn-sm btn-danger btn-delete" data-file="' + backup.filename + '" title="Hapus">';
                    html += '<i class="fas fa-trash"></i>';
                    html += '</button>';
                    html += '</td>';
                    html += '</tr>';
                });
            }
            
            $('#backup-list').html(html);
        }
    });
}

// Format bytes to human readable
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Create backup
$('#btn-create-backup').click(function() {
    Swal.fire({
        title: 'Buat Backup Database?',
        text: 'Proses backup akan memakan waktu beberapa saat tergantung ukuran database.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buat Backup',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            API.post('backup/create', {}, function(res) {
                Swal.close();
                
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        loadBackups();
                        loadStats();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: res.message,
                        icon: 'error'
                    });
                }
            });
        }
    });
});

// Restore backup
$(document).on('click', '.btn-restore', function() {
    const filename = $(this).data('file');
    
    Swal.fire({
        title: 'Restore Database?',
        text: 'Data saat ini akan ditimpa dengan data dari backup. Tindakan ini tidak dapat dibatalkan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Restore',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            API.post('backup/restore', {filename: filename}, function(res) {
                Swal.close();
                
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success'
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: res.message,
                        icon: 'error'
                    });
                }
            });
        }
    });
});

// Delete backup
$(document).on('click', '.btn-delete', function() {
    const filename = $(this).data('file');
    
    Swal.fire({
        title: 'Hapus Backup?',
        text: 'File backup akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            API.post('backup/delete', {filename: filename}, function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        loadBackups();
                        loadStats();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: res.message,
                        icon: 'error'
                    });
                }
            });
        }
    });
});

// Initial load
$(document).ready(function() {
    loadStats();
    loadBackups();
});
</script>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
