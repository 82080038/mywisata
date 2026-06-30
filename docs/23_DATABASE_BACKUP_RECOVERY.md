# MODUL 23 — DATABASE BACKUP & RECOVERY

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Strategi backup dan recovery database MySQL untuk memastikan data aman dan
dapat dipulihkan saat terjadi kegagalan.

---

## 2. STRATEGI BACKUP

| Tipe | Frekuensi | Retensi | Metode |
|------|-----------|---------|--------|
| Full backup | Harian (02:00) | 7 hari | mysqldump |
| Weekly backup | Mingguan | 4 minggu | mysqldump compressed |
| Monthly backup | Bulanan | 12 bulan | mysqldump + upload cloud |

---

## 3. SCRIPT BACKUP OTOMATIS

```bash
#!/bin/bash
# /opt/scripts/backup_db.sh
# Cron: 0 2 * * * /opt/scripts/backup_db.sh

DB_NAME="tour_guide_app"
DB_USER="root"
DB_PASS=""
BACKUP_DIR="/opt/lampp/htdocs/wisata/database/backup"
DATE=$(date +%Y%m%d_%H%M%S)
FILE="${BACKUP_DIR}/${DB_NAME}_${DATE}.sql.gz"

# Create backup
mysqldump -u${DB_USER} -p${DB_PASS} ${DB_NAME} | gzip > ${FILE}

# Delete backups older than 7 days
find ${BACKUP_DIR} -name "*.sql.gz" -mtime +7 -delete

# Log
echo "$(date): Backup created ${FILE}" >> ${BACKUP_DIR}/backup.log
```

---

## 4. CRON JOB SETUP

```bash
# Edit crontab
crontab -e

# Tambahkan:
0 2 * * * /opt/scripts/backup_db.sh
0 3 * * 0 /opt/scripts/backup_db_weekly.sh
```

---

## 5. SCRIPT RECOVERY

```bash
#!/bin/bash
# /opt/scripts/restore_db.sh
# Usage: ./restore_db.sh backup_file.sql.gz

DB_NAME="tour_guide_app"
DB_USER="root"
FILE=$1

if [ -z "$FILE" ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    exit 1
fi

echo "Restoring from $FILE..."
gunzip -c $FILE | mysql -u${DB_USER} ${DB_NAME}
echo "Restore complete."
```

---

## 6. BACKUP VIA PHP (ADMIN PANEL)

```php
<?php
class BackupController extends Controller {

    public function __construct() {
        Middleware::requireRole('admin');
    }

    public function create() {
        $filename = 'tour_guide_app_' . date('Ymd_His') . '.sql';
        $path = 'database/backup/' . $filename;

        $command = "mysqldump -u" . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " > " . BASE_PATH . '/' . $path;
        system($command);

        Logger::audit('backup', 'database', "Manual backup: {$filename}");
        $this->json(['status' => 'success', 'message' => 'Backup dibuat', 'data' => ['file' => $filename]]);
    }

    public function listBackups() {
        $dir = BASE_PATH . '/database/backup/';
        $files = glob($dir . '*.sql');
        $backups = array_map(function($f) {
            return [
                'filename' => basename($f),
                'size' => filesize($f),
                'date' => filemtime($f)
            ];
        }, $files);
        usort($backups, fn($a, $b) => $b['date'] - $a['date']);
        $this->json(['status' => 'success', 'data' => $backups]);
    }

    public function download($filename) {
        $path = BASE_PATH . '/database/backup/' . $filename;
        if (!file_exists($path)) {
            http_response_code(404);
            die('File not found');
        }
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($path);
        exit;
    }

    public function restore() {
        $input = json_decode(file_get_contents('php://input'), true);
        $filename = $input['filename'];
        $path = BASE_PATH . '/database/backup/' . $filename;

        if (!file_exists($path)) {
            $this->json(['status' => 'error', 'message' => 'File tidak ditemukan'], 404);
        }

        $command = "mysql -u" . DB_USER . " -p" . DB_PASS . " " . DB_NAME . " < " . escapeshellarg($path);
        system($command, $retval);

        if ($retval === 0) {
            Logger::audit('restore', 'database', "Restored from {$filename}");
            $this->json(['status' => 'success', 'message' => 'Database dipulihkan']);
        } else {
            $this->json(['status' => 'error', 'message' => 'Restore gagal'], 500);
        }
    }
}
```

---

## 7. VIEW: Backup Panel

```php
<!-- app/views/admin/backup.php -->
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Backup Database</h5>
        <button class="btn btn-primary btn-sm" id="btn-backup">
            <i class="fas fa-download"></i> Backup Sekarang
        </button>
    </div>
    <div class="card-body">
        <table class="table" id="backup-table">
            <thead><tr><th>File</th><th>Size</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody id="backup-list"></tbody>
        </table>
    </div>
</div>

<script>
function loadBackups() {
    API.get('admin/backup/list', {}, function(res) {
        let html = '';
        res.data.forEach(function(b) {
            html += `<tr>
                <td>${b.filename}</td>
                <td>${(b.size/1024).toFixed(0)} KB</td>
                <td>${new Date(b.date*1000).toLocaleString()}</td>
                <td>
                    <a href="${BASE_URL}admin/backup/download/${b.filename}" class="btn btn-sm btn-info">Download</a>
                    <button class="btn btn-sm btn-warning btn-restore" data-file="${b.filename}">Restore</button>
                </td>
            </tr>`;
        });
        $('#backup-list').html(html);
    });
}

$('#btn-backup').click(function() {
    Swal.fire({title: 'Buat backup?', icon: 'question', showCancelButton: true})
    .then(r => { if (r.isConfirmed) {
        API.post('admin/backup/create', {}, function(res) {
            Swal.fire('Berhasil!', res.message, 'success').then(loadBackups);
        });
    }});
});

$(document).on('click', '.btn-restore', function() {
    let file = $(this).data('file');
    Swal.fire({title: 'Restore database?', text: 'Data saat ini akan ditimpa!', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Ya, Restore'})
    .then(r => { if (r.isConfirmed) {
        API.post('admin/backup/restore', {filename: file}, function(res) {
            Swal.fire('Berhasil!', res.message, 'success');
        });
    }});
});

loadBackups();
</script>
```

---

## 8. CHECKLIST BACKUP

- [ ] Cron job harian aktif
- [ ] Retensi 7 hari auto-cleanup
- [ ] Backup tercompress (gzip)
- [ ] Log backup tercatat
- [ ] Test restore minimal 1x/bulan
- [ ] Backup disimpan di lokasi terpisah (opsional: cloud storage)

---

> **Modul Selanjutnya:** `24_TESTING_SYSTEM.md`
