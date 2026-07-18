# DAFTAR ISI — Tour Guide Application Documentation

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 2.0  
> **Tanggal:** 2026-07-18  
> **Total Dokumen:** 50+ modul

---

## TENTANG DOKUMENTASI INI

Dokumentasi ini merupakan landasan pembangunan aplikasi **Tour Guide Application**
berbasis PHP Native (Simple MVC), MySQL, Bootstrap, jQuery, dan OpenStreetMap/Leaflet.
Setiap dokumen fokus pada aspek tertentu dari siklus pengembangan perangkat lunak,
mulai dari konsep, analisis, desain, implementasi modul, hingga deployment.

---

## STRUKTUR DOKUMENTASI

### Bagian I — Konsep & Analisis

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 01 | `01_KONSEP_DAN_ANALISIS_SISTEM.md` | Latar belakang, tujuan, scope, stakeholder, analisis pasar, keunggulan kompetitif, persyaratan teknis, analisis risiko |
| 02 | `02_SRS_REQUIREMENT_SYSTEM.md` | Software Requirement Specification: functional & non-functional requirements, use case, MoSCoW prioritization |

### Bagian II — Desain Arsitektur & Struktur

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 03 | `03_DESAIN_ARSITEKTUR_APLIKASI.md` | Arsitektur MVC, request flow, core classes, AJAX/JSON, security architecture, frontend architecture, OpenStreetMap |
| 04 | `04_STRUKTUR_FOLDER_PHP_NATIVE.md` | Struktur folder lengkap, core files, .htaccess/Nginx, naming conventions, coding conventions, dependency management |

### Bagian III — Desain Database

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 05 | `05_DESAIN_DATABASE_MYSQL_ERD.md` | DDL SQL 33 tabel, ERD tekstual, relasi antar tabel, index strategy, seed data |
| 06 | `06_KAMUS_DATA_DATABASE.md` | Kamus data (data dictionary) untuk setiap kolom di 33 tabel, ENUM values reference |

### Bagian IV — Modul Aplikasi (Per Role)

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 07 | `07_MODUL_ADMINISTRATOR.md` | Modul Admin: dashboard, user management, guide approval, destinasi, hotel/restoran approval, transaksi, report, settings |
| 08 | `08_MODUL_WISATAWAN.md` | Modul Wisatawan: dashboard, search guide, booking, tiket, hotel, restoran, event, audio guide, AI chat, review |
| 09 | `09_MODUL_TOUR_GUIDE.md` | Modul Tour Guide: dashboard, profil, jadwal, booking masuk, pendapatan, review |

### Bagian V — Modul Fungsional (Per Fitur)

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 10 | `10_MODUL_MAP_GPS_OPENSTREETMAP.md` | Peta interaktif Leaflet, marker, geolocation, routing, itinerary |
| 11 | `11_MODUL_BOOKING_DAN_TRANSAKSI.md` | Alur booking, status flow, kode booking, controller, payment proof |
| 12 | `12_MODUL_TIKET_WISATA.md` | E-ticket, QR code generation, pembelian, verifikasi |
| 13 | `13_MODUL_HOTEL_HOMESTAY.md` | Pendaftaran, pencarian, booking akomodasi |
| 14 | `14_MODUL_RESTORAN_UMKM.md` | Pendaftaran, manajemen menu, pemesanan, keranjang |
| 15 | `15_MODUL_EVENT_BUDAYA.md` | CRUD event, kalender, pendaftaran, notifikasi pengingat |
| 16 | `16_MODUL_AUDIO_GUIDE.md` | Upload audio, player multibahasa, transkrip |
| 17 | `17_MODUL_AI_TOUR_GUIDE.md` | Chatbot rule-based, intent detection, rekomendasi, itinerary |
| 18 | `18_MODUL_NOTIFICATION.md` | In-app notification, email, badge real-time, broadcast |
| 19 | `19_MODUL_REPORT_ANALYTIC.md` | Dashboard statistik, grafik, export CSV, pendapatan guide |

### Bagian VI — Security & API

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 20 | `20_SECURITY_SYSTEM.md` | Autentikasi, RBAC, CSRF, SQL injection, XSS, input validation, audit log, rate limiting, security headers |
| 21 | `API_GUIDE.md` | API documentation lengkap: request/response format, authentication, endpoints, error handling, rate limiting, OpenAPI specification |
| 22 | `22_USER_ROLE_PERMISSION.md` | Role definition, permission matrix per modul, implementation, flow registrasi |

### Bagian VII — Operasional & Deployment

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 23 | `23_DATABASE_BACKUP_RECOVERY.md` | Strategi backup, script otomatis, recovery, backup via PHP admin panel |
| 24 | `TESTING_GUIDE.md` | Panduan testing lengkap: Playwright E2E, manual testing, load testing, security testing, performance testing |
| 25 | `DEPLOYMENT_GUIDE.md` | Panduan deployment lengkap: server setup, LAMP/LNMP, Apache/Nginx config, SSL, cron jobs, optimization, security hardening |
| 26 | `26_ROADMAP_PENGEMBANGAN.md` | Timeline 4 fase (22 minggu), post-launch roadmap, metrik kesuksesan, risiko, referensi aplikasi |

### Bagian VIII — Panduan Tambahan

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 27 | `SETUP_GUIDE.md` | Panduan instalasi lengkap: prerequisites, installation steps, configuration, database setup, running application, troubleshooting |
| 28 | `28_STANDAR_KODE_KONTRIBUSI.md` | Standar penulisan kode PHP/JS/CSS, Git workflow, kontribusi tim, code review checklist |
| 29 | `29_CHECKLIST_PENGEMBANGAN.md` | Checklist per fase pengembangan, acceptance criteria per modul, definisi selesai (Definition of Done) |

### Bagian Tambahan — Prompting System

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| - | `prompting/README.md` | Panduan sistem prompting untuk autonomous development |
| - | `prompting/README_SETUP.md` | Panduan setup konfigurasi multi-environment (Windows & Linux) |
| - | `prompting/config.json` | Konfigurasi terpusat untuk development di multiple komputer |

### Bagian IX — Diagram, Glosarium & Audit

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 30 | `30_DIAGRAM_ALUR_BISNIS.md` | Diagram alur (flowchart) Mermaid untuk semua proses bisnis: auth, booking, tiket, hotel, restoran, event, AI, notifikasi, admin approval, payment, backup, session, rate limiting, file upload |
| 31 | `31_KAMUS_ISTILAH_GLOSARIUM.md` | Kamus istilah bisnis & pariwisata, teknis (backend, frontend, database, security, infrastruktur, DevOps), singkatan, dan kode format |
| 32 | `32_AUDIT_KEAMANAN_CHECKLIST.md` | Checklist audit keamanan berbasis OWASP Top 10 (2021) + ASVS Level 1: access control, crypto, injection, auth, logging, security headers, file upload, API, database, deployment |

### Bagian X — Panduan Tambahan (100% Completion)

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 33 | `DEVELOPER_GUIDE.md` | **Panduan developer lengkap** - Architecture, project structure, coding standards, configuration, database, API, testing, deployment, troubleshooting |
| 34 | `PROJECT_STRUCTURE.md` | Struktur project detail - Directory overview, file naming conventions, database structure, routing pattern, dependency flow |
| 35 | `34_USER_MANUAL.md` | Panduan penggunaan aplikasi untuk wisatawan (end-user) |
| 36 | `35_ADMIN_MANUAL.md` | Panduan lengkap untuk admin panel dan manajemen sistem |
| 37 | `41_VISUAL_DIAGRAMS.md` | Kumpulan diagram visual (architecture, ERD, flow, sequence) dalam format Mermaid |
| 38 | `42_THIRD_PARTY_API_INTEGRATION.md` | Integrasi dengan platform pihak ketiga (Traveloka, Booking.com, Agoda) untuk menerima pesanan hotel/homestay dan pembayaran via webhook |
| 39 | `43_GAP_ANALYSIS_COMPARISON.md` | Analisis gap dan perbandingan dengan aplikasi sejenis |

### Bagian XI — Panduan Fitur Advanced

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 40 | `ai_integration_guide.md` | Panduan integrasi OpenAI untuk AI Tour Guide features |
| 41 | `openai_setup_guide.md` | Setup OpenAI API key dan konfigurasi |
| 42 | `payment_gateway_guide.md` | Panduan integrasi Midtrans payment gateway |
| 43 | `cdn_integration_guide.md` | Panduan integrasi Cloudflare CDN |
| 44 | `cloudflare_setup_guide.md` | Setup Cloudflare account dan konfigurasi |
| 45 | `load_testing_guide.md` | Panduan load testing dengan Apache Bench dan JMeter |
| 46 | `pwa_guide.md` | Panduan PWA implementation dengan service worker |

### Bagian XII — Laporan & Status

| No | Dokumen | Deskripsi |
|----|---------|-----------|
| 47 | `PLAYWRIGHT_COMPREHENSIVE_TEST_REPORT.md` | Laporan lengkap Playwright E2E testing: test results, coverage, issues, recommendations |

---

## CARA MEMBACA DOKUMENTASI

```
Bagian I (01-02)      → Pahami KEBUTUHAN & SCOPE
        ↓
Bagian II (03-04)     → Pahami ARSITEKTUR & STRUKTUR
        ↓
Bagian III (05-06)    → Pahami DATABASE & DATA MODEL
        ↓
Bagian IV (07-09)     → Pahami MODUL PER ROLE
        ↓
Bagian V (10-19)      → Pahami MODUL PER FITUR
        ↓
Bagian VI (20-22)     → Pahami SECURITY & API
        ↓
Bagian VII (23-26)    → Pahami OPERASIONAL & DEPLOYMENT
        ↓
Bagian VIII (27-29)   → Pahami PANDUAN DEVELOPMENT
        ↓
Bagian IX (30-32)     → Pahami DIAGRAM, GLOSARIUM & AUDIT
        ↓
Bagian X (33-39)      → Pahami PANDUAN TAMBAHAN (100% COMPLETION)
        ↓
Bagian XI (40-46)     → Pahami FITUR ADVANCED
        ↓
Bagian XII (47)       → Pahami LAPORAN & STATUS
```

---

## TABEL VERSI DOKUMEN

| Versi | Tanggal | Perubahan |
|-------|---------|-----------|
| 1.0 | 2026-06-30 | Dokumen awal 01-26 |
| 1.1 | 2026-06-30 | Penambahan modul 27-29, update doc 05/06 (tabel `rate_limits`), update doc 03/04/21 |
| 1.2 | 2026-06-30 | Penambahan modul 30-32: diagram alur bisnis, kamus istilah, audit keamanan |
| 2.0 | 2026-07-18 | Reorganisasi dokumentasi: merge deployment (6→1), testing (4→1), API (2→1). Tambah DEVELOPER_GUIDE, PROJECT_STRUCTURE, SETUP_GUIDE. Hapus file duplikat/obsolete. Update struktur dokumentasi menjadi 12 bagian. |

---

## TEKNOLOGI STACK RINGKASAN

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8.1+ (Native, Simple MVC) |
| Database | MySQL 8.0+ (utf8mb4) |
| Frontend | Bootstrap 5.3.x, jQuery 3.7.x |
| Peta | OpenStreetMap + Leaflet 1.9.x |
| Icons | Font Awesome 6.x |
| Tables | DataTables 1.13.x |
| Select | Select2 4.1.x |
| Alert | SweetAlert2 11.x |
| Charts | Chart.js 4.x |
| Web Server | Apache (mod_rewrite) / Nginx (PHP-FPM) |

---

> **Catatan:** Dokumentasi ini bersifat **living document** — diperbarui seiring perkembangan aplikasi. Setiap perubahan harus dicatat dalam tabel versi di atas.
