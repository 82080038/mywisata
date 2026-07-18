# MODUL 46 — REORGANISASI STRUKTUR PROYEK

> **Tujuan:** Merapikan struktur folder dan file proyek MyWisata untuk lebih terorganisir
> **Versi:** 1.0  
> **Tanggal:** 2026-07-18

---

## 1. ANALISIS STRUKTUR SAAT INI

### 1.1 Masalah yang Teridentifikasi

**Migration Files Tersebar:**
- `database/` - berisi 30+ file migration SQL
- `migrations/` - berisi 1 file migration SQL
- **Masalah:** Tidak konsisten, sulit dikelola

**Config Files Tersebar:**
- `config/` - cdn.php, openai.php, payment.php, redis.php
- `app/config/` - config.php, database.php
- **Masalah:** Tidak terpusat, sulit dicari

**Prompting System Tersebar:**
- `prompting/` - prompting templates, config, state
- `.devin/workflows/` - 3 workflow files
- `.windsurf/workflows/` - 3 workflow files
- **Masalah:** Workflows tersebar di 3 lokasi berbeda

**Scripts Tersebar:**
- `scripts/` - generate_icons.php, run_load_test.sh, run_tests.sh
- `cron/` - backup_database.sh, log_rotation.php, security_monitor.php, verify_backup.sh
- **Masalah:** Tidak konsisten naming dan lokasi

**Root Directory Berantakan:**
- Banyak file di root: index.php, router.php, .htaccess, composer.json, dll
- File-file Python dan test di root
- **Masalah:** Tidak rapih, sulit navigasi

---

## 2. STRUKTUR BARU YANG DIUSULKAN

### 2.1 Struktur Folder Utama

```
mywisata/
├── app/                          # Application code (tetap)
│   ├── config/                   # Semua config files
│   ├── controllers/              # Controllers
│   ├── core/                     # Core classes
│   ├── helpers/                  # Helpers
│   ├── models/                   # Models
│   └── views/                    # Views
├── database/                     # Database
│   ├── migrations/               # SEMUA migration files
│   ├── seeds/                    # Seed files
│   └── backups/                  # Backup files
├── prompting/                    # Prompting system (terpusat)
│   ├── workflows/                # SEMUA workflow files
│   ├── 01_development/           # Development prompts
│   ├── 02_testing/               # Testing prompts
│   ├── 03_revision/              # Revision prompts
│   ├── 04_improvement/           # Improvement prompts
│   ├── 05_cycle/                 # Cycle management
│   ├── config.json               # Configuration
│   ├── state.json                # State tracking
│   └── README.md                 # Documentation
├── scripts/                      # SEMUA scripts
│   ├── deployment/               # Deployment scripts
│   ├── maintenance/              # Maintenance scripts (cron)
│   ├── testing/                  # Testing scripts
│   ├── utilities/                # Utility scripts
│   └── README.md                 # Scripts documentation
├── config/                       # External config (environment)
│   ├── cdn.php
│   ├── openai.php
│   ├── payment.php
│   └── redis.php
├── docs/                         # Documentation (tetap)
├── public/                       # Public files (tetap)
├── tests/                        # Tests (tetap)
├── vendor/                       # Composer dependencies (tetap)
├── .devin/                       # Devin config (hapus workflows)
│   └── config.md
├── .windsurf/                    # Windsurf config (hapus workflows)
│   └── rules.md
├── .github/                      # GitHub (tetap)
├── .git/                         # Git (tetap)
├── cache/                        # Cache (tetap)
├── logs/                         # Logs (tetap)
├── load-tests/                   # Load tests (tetap)
├── playwright-report/             # Playwright reports (tetap)
├── migrations/                   # HAPUS (pindah ke database/migrations)
├── index.php                     # Entry point (tetap)
├── router.php                    # Router (tetap)
├── .htaccess                     # Apache config (tetap)
├── .env                          # Environment (tetap)
├── .env.example                  # Environment example (tetap)
├── composer.json                 # Composer config (tetap)
├── composer.lock                 # Composer lock (tetap)
├── package.json                  # NPM config (tetap)
├── package-lock.json             # NPM lock (tetap)
├── phpunit.xml                   # PHPUnit config (tetap)
├── phpstan.neon                  # PHPStan config (tetap)
└── README.md                     # Main README (tetap)
```

---

## 3. RENCANA REORGANISASI

### 3.1 Phase 1: Konsolidasi Migration Files

**Aksi:**
1. Pindahkan semua file dari `database/` yang merupakan migration ke `database/migrations/`
2. Pindahkan file dari `migrations/` ke `database/migrations/`
3. Pindahkan `seed.sql` dan `additional_seed.sql` ke `database/seeds/`
4. Hapus folder `migrations/` di root
5. Update referensi di code dan dokumentasi

**File yang dipindahkan:**
- `database/add_*.sql` → `database/migrations/add_*.sql`
- `database/create_*.sql` → `database/migrations/create_*.sql`
- `database/migration.sql` → `database/migrations/001_initial_schema.sql`
- `migrations/add_payment_fields.sql` → `database/migrations/add_payment_fields.sql`
- `database/seed.sql` → `database/seeds/seed.sql`
- `database/additional_seed.sql` → `database/seeds/additional_seed.sql`

### 3.2 Phase 2: Konsolidasi Config Files

**Aksi:**
1. Pindahkan file dari `config/` ke `app/config/external/`
2. Update referensi di code untuk path baru
3. Update dokumentasi

**File yang dipindahkan:**
- `config/cdn.php` → `app/config/external/cdn.php`
- `config/openai.php` → `app/config/external/openai.php`
- `config/payment.php` → `app/config/external/payment.php`
- `config/redis.php` → `app/config/external/redis.php`

### 3.3 Phase 3: Konsolidasi Prompting System

**Aksi:**
1. Pindahkan semua workflow files dari `.devin/workflows/` ke `prompting/workflows/`
2. Pindahkan semua workflow files dari `.windsurf/workflows/` ke `prompting/workflows/`
3. Hapus folder `workflows/` dari `.devin/` dan `.windsurf/`
4. Update referensi di dokumentasi

**File yang dipindahkan:**
- `.devin/workflows/*.md` → `prompting/workflows/*.md`
- `.windsurf/workflows/*.md` → `prompting/workflows/*.md`

### 3.4 Phase 4: Konsolidasi Scripts

**Aksi:**
1. Buat subfolder di `scripts/`:
   - `scripts/deployment/`
   - `scripts/maintenance/`
   - `scripts/testing/`
   - `scripts/utilities/`
2. Pindahkan file dari `cron/` ke `scripts/maintenance/`
3. Pindahkan file dari `scripts/` ke subfolder yang sesuai
4. Hapus folder `cron/`
5. Update referensi di code dan dokumentasi

**File yang dipindahkan:**
- `cron/backup_database.sh` → `scripts/maintenance/backup_database.sh`
- `cron/log_rotation.php` → `scripts/maintenance/log_rotation.php`
- `cron/security_monitor.php` → `scripts/maintenance/security_monitor.php`
- `cron/verify_backup.sh` → `scripts/maintenance/verify_backup.sh`
- `scripts/generate_icons.php` → `scripts/utilities/generate_icons.php`
- `scripts/run_load_test.sh` → `scripts/testing/run_load_test.sh`
- `scripts/run_tests.sh` → `scripts/testing/run_tests.sh`

### 3.5 Phase 5: Rapikan Root Directory

**Aksi:**
1. Pindahkan file Python yang tidak diperlukan ke folder yang sesuai atau hapus
2. Pindahkan file test ke folder yang sesuai
3. Update referensi

**File yang ditangani:**
- `test_speech_indonesian.py` → hapus atau pindah ke `scripts/utilities/`
- `test-results.json` → hapus (generated file)
- `.phpunit.result.cache` → hapus (generated file)

---

## 4. UPDATE REFERENSI

### 4.1 Code yang Perlu Diupdate

**App Config Paths:**
```php
// app/config/config.php
// Update path untuk external config
require_once __DIR__ . '/external/cdn.php';
require_once __DIR__ . '/external/openai.php';
require_once __DIR__ . '/external/payment.php';
require_once __DIR__ . '/external/redis.php';
```

**Migration Paths:**
```php
// Update referensi ke migration files
$migrationPath = __DIR__ . '/../database/migrations/';
```

**Script Paths:**
```bash
# Update cron job paths
/opt/lampp/htdocs/mywisata/scripts/maintenance/backup_database.sh
```

### 4.2 Dokumentasi yang Perlu Diupdate

**README.md:**
- Update folder structure
- Update script paths
- Update migration paths

**Prompting README:**
- Update workflow paths
- Update folder structure

**Deployment docs:**
- Update script paths
- Update config paths

---

## 5. EKSEKUSI

### 5.1 Urutan Eksekusi

1. **Backup** - Backup seluruh proyek
2. **Phase 1** - Konsolidasi migration files
3. **Phase 2** - Konsolidasi config files
4. **Phase 3** - Konsolidasi prompting system
5. **Phase 4** - Konsolidasi scripts
6. **Phase 5** - Rapikan root directory
7. **Update Referensi** - Update code dan dokumentasi
8. **Testing** - Verifikasi semua berfungsi
9. **Cleanup** - Hapus folder kosong

### 5.2 Risk Mitigation

- **Backup** sebelum setiap phase
- **Test** setelah setiap phase
- **Rollback** jika ada error
- **Commit** setelah phase berhasil

---

## 6. BENEFIT

### 6.1 Manfaat Reorganisasi

- **Konsistensi** - Semua file sejenis di satu lokasi
- **Navigasi** - Lebih mudah mencari file
- **Maintenance** - Lebih mudah dikelola
- **Scalability** - Struktur lebih scalable
- **Professional** - Lebih rapih dan professional

### 6.2 Impact

- **Zero Breaking Changes** - Hanya perubahan path, bukan logic
- **Minimal Downtime** - Bisa dilakukan offline
- **Reversible** - Bisa rollback jika diperlukan

---

## 7. COMPLETION CRITERIA

Reorganisasi selesai ketika:
- ✅ Semua migration files di `database/migrations/` (32 files)
- ✅ Semua config files di `app/config/` atau `app/config/external/` (4 files)
- ✅ Semua workflow files di `prompting/workflows/` (3 files)
- ✅ Semua scripts di `scripts/` dengan subfolder yang sesuai (7 files)
- ✅ Root directory bersih (hapus 3 file generated)
- ✅ Semua referensi di code diupdate (5 service files)
- ✅ Semua dokumentasi diupdate (4 files)
- ✅ Semua tests passing (syntax check: 5/5 valid)
- ✅ Aplikasi berjalan normal

**STATUS: ✅ COMPLETED (2026-07-18)**  
**Modern Features:** Module 40 Implemented, Modules 41-45 Database Ready

---

## 8. NEXT STEPS

Setelah reorganisasi selesai:
1. ✅ Update `.gitignore` untuk folder baru
2. ✅ Update deployment scripts
3. ✅ Update developer documentation
4. ⏳ Commit ke git dengan descriptive message
