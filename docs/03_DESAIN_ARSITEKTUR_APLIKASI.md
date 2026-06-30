# MODUL 03 — DESAIN ARSITEKTUR APLIKASI

> **Aplikasi:** Tour Guide Application  
> **Versi Dokumen:** 1.0  
> **Tanggal:** 2026-06-30

---

## 1. ARSITEKTUR SISTEM SECARA UMUM

### 1.1 Pola Arsitektur: MVC Sederhana

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT (Browser)                      │
│  HTML5 + CSS + Bootstrap 5 + jQuery + Leaflet           │
│  AJAX Request ──► JSON Response                          │
└─────────────┬───────────────────────────┬───────────────┘
              │                           │
         HTTP/HTTPS                  AJAX/JSON
              │                           │
┌─────────────▼───────────────────────────▼───────────────┐
│                  WEB SERVER (Apache/Nginx)               │
│              .htaccess / nginx.conf routing              │
└─────────────┬───────────────────────────────────────────┘
              │
┌─────────────▼───────────────────────────────────────────┐
│              PHP NATIVE APPLICATION                      │
│                                                          │
│  ┌──────────┐   ┌──────────┐   ┌──────────┐            │
│  │ Controller│──►│  Model   │──►│  View    │            │
│  │ (Logic)  │   │ (Data)   │   │ (UI)     │            │
│  └──────────┘   └────┬─────┘   └──────────┘            │
│                      │                                   │
│              ┌───────▼───────┐                          │
│              │   Database    │                          │
│              │   (MySQL 8)   │                          │
│              └───────────────┘                          │
└─────────────────────────────────────────────────────────┘
```

### 1.2 Alur Request

```
1. Browser → HTTP Request → index.php (Front Controller)
2. index.php → Routing → Controller sesuai URL
3. Controller → Model (query database via PDO)
4. Model → MySQL → return data array
5. Controller → View (render HTML) atau JSON (untuk AJAX)
6. Response → Browser
```

---

## 2. ARSITEKTUR MVC DETAIL

### 2.1 Front Controller Pattern

Semua request melewati satu pintu masuk: `index.php`

```
URL: index.php?url=controller/method/param1/param2

Contoh:
index.php?url=tourguide/list          → TourGuideController::list()
index.php?url=booking/create          → BookingController::create()
index.php?url=api/destinations        → ApiController::destinations() (JSON)
```

### 2.2 Struktur MVC

```
app/
├── controllers/          # Controller — logika bisnis
│   ├── AuthController.php
│   ├── TourGuideController.php
│   ├── BookingController.php
│   ├── MapController.php
│   ├── DestinationController.php
│   ├── HotelController.php
│   ├── RestaurantController.php
│   ├── EventController.php
│   ├── AudioGuideController.php
│   ├── AIGuideController.php
│   ├── NotificationController.php
│   ├── ReportController.php
│   └── ApiController.php       # Endpoint AJAX/JSON
│
├── models/               # Model — interaksi database
│   ├── User.php
│   ├── TourGuide.php
│   ├── Booking.php
│   ├── Destination.php
│   ├── Hotel.php
│   ├── Restaurant.php
│   ├── Event.php
│   ├── AudioGuide.php
│   ├── Notification.php
│   └── Transaction.php
│
├── views/                # View — template HTML
│   ├── layouts/
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── sidebar.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── admin/
│   ├── wisatawan/
│   ├── tourguide/
│   └── errors/
│       └── 404.php
│
├── core/                 # Core framework files
│   ├── App.php           # Front controller & routing
│   ├── Controller.php    # Base controller class
│   ├── Model.php         # Base model class (PDO wrapper)
│   ├── View.php          # View renderer
│   ├── Database.php      # PDO connection singleton
│   ├── Session.php       # Session manager
│   ├── Auth.php          # Authentication helper
│   ├── Middleware.php    # RBAC middleware
│   └── Helper.php        # Utility functions
│
└── config/
    ├── config.php        # App configuration
    └── database.php      # Database credentials
```

### 2.3 Base Controller Class

```php
<?php
// app/core/Controller.php
abstract class Controller {
    protected $model;
    protected $view;

    public function __construct() {
        $this->view = new View();
    }

    // Load model dynamically
    protected function model($modelName) {
        $modelFile = 'app/models/' . $modelName . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $modelName();
        }
        throw new Exception("Model {$modelName} not found");
    }

    // Render view with layout
    protected function view($viewName, $data = []) {
        $this->view->render($viewName, $data);
    }

    // Return JSON response (for AJAX)
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Redirect
    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }
}
```

### 2.4 Base Model Class

```php
<?php
// app/core/Model.php
abstract class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Find by ID
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }

    // Find all with optional conditions
    public function all($conditions = [], $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($limit) $sql .= " LIMIT {$limit}";
        return $this->db->query($sql, $params)->fetchAll();
    }

    // Insert
    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }

    // Update
    public function update($id, $data) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "{$key} = :{$key}";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = :id";
        $data['id'] = $id;
        return $this->db->query($sql, $data)->rowCount();
    }

    // Delete
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id])->rowCount();
    }
}
```

### 2.5 Database Connection (PDO Singleton)

```php
<?php
// app/core/Database.php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $config = require 'app/config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
```

---

## 3. ARSITEKTUR KOMUNIKASI DATA (AJAX + JSON)

### 3.1 Pola AJAX Request

```
Frontend (jQuery AJAX)
    │
    ├── POST /index.php?url=api/booking/create
    │     Body: { guide_id: 5, date: "2026-07-01", duration: 8 }
    │     Response: { status: "success", booking_id: 123, code: "TG-20260701-001" }
    │
    ├── GET /index.php?url=api/destinations?category=alam
    │     Response: { status: "success", data: [...], total: 15 }
    │
    └── GET /index.php?url=api/map/markers
          Response: { status: "success", markers: [{lat, lng, name, ...}] }
```

### 3.2 Format Response JSON Standar

```json
{
  "status": "success" | "error",
  "message": "Deskripsi pesan",
  "data": { ... } | [ ... ],
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20
  }
}
```

### 3.3 ApiController Pattern

```php
<?php
// app/controllers/ApiController.php
class ApiController extends Controller {

    // GET /api/destinations
    public function destinations() {
        $this->requireAuth();
        $model = $this->model('Destination');
        $category = $_GET['category'] ?? null;
        $data = $model->all($category ? ['category' => $category] : []);
        $this->json([
            'status' => 'success',
            'data' => $data,
            'meta' => ['total' => count($data)]
        ]);
    }

    // POST /api/booking/create
    public function createBooking() {
        $this->requireAuth();
        $input = json_decode(file_get_contents('php://input'), true);
        // Validation...
        $booking = $this->model('Booking');
        $id = $booking->insert($input);
        $this->json([
            'status' => 'success',
            'message' => 'Booking berhasil dibuat',
            'data' => ['booking_id' => $id]
        ]);
    }
}
```

---

## 4. ARSITEKTUR KEAMANAN

### 4.1 Lapisan Keamanan

```
┌─────────────────────────────────────┐
│  Layer 1: HTTPS/SSL (Transport)     │  ← Enkripsi jaringan
├─────────────────────────────────────┤
│  Layer 2: Authentication (Session)  │  ← Login + session
├─────────────────────────────────────┤
│  Layer 3: RBAC (Authorization)      │  ← Role-based access
├─────────────────────────────────────┤
│  Layer 4: Input Validation           │  ← Server-side validation
├─────────────────────────────────────┤
│  Layer 5: PDO Prepared Statements    │  ← Anti SQL injection
├─────────────────────────────────────┤
│  Layer 6: Output Escaping            │  ← Anti XSS
├─────────────────────────────────────┤
│  Layer 7: CSRF Token                 │  ← Anti CSRF
└─────────────────────────────────────┘
```

### 4.2 RBAC Middleware

```php
<?php
// app/core/Middleware.php
class Middleware {
    public static function requireRole($roles) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        if (!in_array($_SESSION['role'], (array)$roles)) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    public static function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
    }

    public static function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf($token) {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
```

---

## 5. ARSITEKTUR FRONTEND

### 5.1 Layout System

```
┌─────────────────────────────────────────────┐
│  Header (Navbar + Logo + Notif Badge)       │
├──────────┬──────────────────────────────────┤
│          │                                  │
│  Sidebar │     Content Area (View)          │
│  (Role-  │                                  │
│  based   │     ┌──────────────────────┐    │
│  menu)   │     │  Page Content        │    │
│          │     │  (Bootstrap grid)    │    │
│          │     └──────────────────────┘    │
│          │                                  │
├──────────┴──────────────────────────────────┤
│  Footer (Copyright + Links)                 │
└─────────────────────────────────────────────┘
```

### 5.2 Template Inheritance

```php
<!-- app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Tour Guide App' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <?php include 'app/views/layouts/navbar.php'; ?>

<!-- app/views/layouts/footer.php -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= BASE_URL ?>assets/js/app.js"></script>
</body>
</html>
```

### 5.3 AJAX Helper (jQuery)

```javascript
// assets/js/app.js
const API = {
    request: function(url, method, data, callback) {
        $.ajax({
            url: BASE_URL + 'api/' + url,
            method: method,
            data: data,
            dataType: 'json',
            beforeSend: function() {
                $('#loading').show();
            },
            success: function(response) {
                if (response.status === 'success') {
                    callback(response);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Terjadi kesalahan: ' + xhr.statusText, 'error');
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    },
    get: function(url, callback) {
        this.request(url, 'GET', {}, callback);
    },
    post: function(url, data, callback) {
        this.request(url, 'POST', data, callback);
    }
};
```

---

## 6. ARSITEKTUR DATABASE CONNECTION

### 6.1 Konfigurasi Database

```php
<?php
// app/config/database.php
return [
    'host'    => 'localhost',
    'dbname'  => 'tour_guide_app',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
];
```

### 6.2 Config Utama

```php
<?php
// app/config/config.php
define('BASE_URL', 'http://localhost/wisata/');
define('APP_NAME', 'Tour Guide Application');
define('APP_VERSION', '1.0.0');
define('DEFAULT_LANGUAGE', 'id');
define('SESSION_TIMEOUT', 1800); // 30 menit
define('UPLOAD_PATH', 'public/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
```

---

## 7. ARSITEKTUR PETA (OpenStreetMap + Leaflet)

### 7.1 Komponen Peta

```
┌─────────────────────────────────────┐
│  Leaflet.js (Frontend)              │
│  ├── Tile Layer (OpenStreetMap)     │
│  ├── Marker Layer (Destinasi)       │
│  ├── Popup Layer (Info Destinasi)   │
│  ├── Route Layer (Itinerary)        │
│  └── Cluster Layer (Marker Group)   │
├─────────────────────────────────────┤
│  AJAX API (Backend)                 │
│  ├── GET /api/map/markers           │  ← Ambil semua marker
│  ├── GET /api/map/route/:id         │  ← Ambil rute itinerary
│  └── GET /api/map/nearby?lat&lng    │  ← Destinasi terdekat
└─────────────────────────────────────┘
```

### 7.2 Inisialisasi Peta

```javascript
// assets/js/map.js
let map = L.map('map').setView([-2.5, 118], 5); // Indonesia

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Load markers via AJAX
API.get('map/markers', function(response) {
    response.data.forEach(function(dest) {
        let marker = L.marker([dest.latitude, dest.longitude])
            .bindPopup(`
                <strong>${dest.name}</strong><br>
                ${dest.description}<br>
                <a href="${BASE_URL}destination/detail/${dest.id}">Lihat Detail</a>
            `)
            .addTo(map);
    });
});
```

---

## 8. DIAGRAM ARSITEKTUR MODUL

```
                    ┌──────────────┐
                    │  Auth Module │
                    │  (Login/Reg) │
                    └──────┬───────┘
                           │
           ┌───────────────┼───────────────┐
           │               │               │
    ┌──────▼──────┐ ┌──────▼──────┐ ┌──────▼──────┐
    │   Admin     │ │  Wisatawan  │ │ Tour Guide  │
    │  Dashboard  │ │  Dashboard  │ │  Dashboard  │
    └──────┬──────┘ └──────┬──────┘ └──────┬──────┘
           │               │               │
    ┌──────┴───────────────┴───────────────┴──────┐
    │              SHARED MODULES                  │
    │                                              │
    │  ┌─────────┐ ┌─────────┐ ┌─────────┐       │
    │  │ Tour    │ │ Booking │ │  Map &  │       │
    │  │ Guide   │ │ & Trans │ │  GPS    │       │
    │  └─────────┘ └─────────┘ └─────────┘       │
    │  ┌─────────┐ ┌─────────┐ ┌─────────┐       │
    │  │ Tiket   │ │ Hotel & │ │Restoran │       │
    │  │ Wisata  │ │Homestay │ │ & UMKM  │       │
    │  └─────────┘ └─────────┘ └─────────┘       │
    │  ┌─────────┐ ┌─────────┐ ┌─────────┐       │
    │  │ Event & │ │ Audio   │ │   AI    │       │
    │  │ Budaya  │ │ Guide   │ │  Guide  │       │
    │  └─────────┘ └─────────┘ └─────────┘       │
    │  ┌─────────┐ ┌─────────┐                    │
    │  │ Notif   │ │ Report  │                    │
    │  └─────────┘ └─────────┘                    │
    └──────────────────────────────────────────────┘
```

---

## 9. ARSITEKTUR FILE UPLOAD

```
Upload Flow:
1. Form POST (multipart/form-data)
2. Controller validate (type, size)
3. Move to public/uploads/{module}/
4. Save path to database
5. Return URL for display

Directory Structure:
public/uploads/
├── guides/          # Foto profil tour guide
├── destinations/    # Foto destinasi wisata
├── hotels/          # Foto hotel/homestay
├── restaurants/     # Foto restoran & menu
├── events/          # Foto event
├── audio/           # File audio guide
├── documents/       # Dokumen verifikasi guide
└── tickets/         # QR code e-ticket
```

---

## 10. ARSITEKTUR SESSION & AUTHENTICATION

```
Login Flow:
1. User submit email + password
2. AuthController → User model → verify password (password_verify)
3. Create session: $_SESSION['user_id'], $_SESSION['role'], $_SESSION['name']
4. Redirect to role-based dashboard

Session Data:
$_SESSION = [
    'user_id'   => 1,
    'role'      => 'admin' | 'wisatawan' | 'tour_guide',
    'name'      => 'John Doe',
    'email'     => 'john@example.com',
    'csrf_token'=> 'a1b2c3d4...',
    'login_time'=> 1688123456
];

Logout Flow:
1. session_destroy()
2. Redirect to login page
```

---

## 11. ERROR HANDLING STRATEGY

### 11.1 Environment-based Error Display

```php
// app/config/config.php
define('APP_ENV', 'development');  // development | production
define('APP_DEBUG', APP_ENV === 'development');

// app/core/App.php — di method run()
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}
```

### 11.2 Global Exception Handler

```php
// app/core/App.php
set_exception_handler(function($e) {
    Logger::error('Unhandled exception', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    if (APP_DEBUG) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan internal'
        ]);
    }
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    Logger::error("PHP Error [{$errno}]", [
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    if (APP_DEBUG) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    return true;
});
```

### 11.3 Error Pages

| HTTP Code | View File | Deskripsi |
|-----------|-----------|-----------|
| 401 | `errors/401.php` | Unauthorized — belum login |
| 403 | `errors/403.php` | Forbidden — salah role |
| 404 | `errors/404.php` | Not Found — halaman/route tidak ada |
| 419 | `errors/419.php` | CSRF Token Mismatch |
| 429 | `errors/429.php` | Too Many Requests — rate limit |
| 500 | `errors/500.php` | Internal Server Error |

```php
// app/core/App.php — handle 404
private function handle404() {
    http_response_code(404);
    if ($this->isAjax()) {
        $this->json(['status' => 'error', 'message' => 'Endpoint not found'], 404);
    } else {
        View::render('errors/404');
    }
    exit;
}
```

### 11.4 Database Transaction Pattern

```php
try {
    $this->db->beginTransaction();
    // Multiple operations
    $this->db->commit();
} catch (PDOException $e) {
    $this->db->rollBack();
    Logger::error('Transaction failed', ['error' => $e->getMessage()]);
    $this->json(['status' => 'error', 'message' => 'Operasi gagal'], 500);
}
```

---

## 12. CORS CONFIGURATION

### 12.1 Same-Origin (Default)

Aplikasi ini menggunakan AJAX same-origin (frontend dan backend di domain yang sama),
sehingga CORS **tidak perlu** dikonfigurasi untuk pengembangan normal.

### 12.2 Development (Jika Frontend Terpisah)

Jika frontend berjalan di port berbeda (contoh: `localhost:3000` untuk Vite/webpack dev server):

```php
// app/core/App.php — di awal method run()
if (APP_ENV === 'development') {
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
    header('Access-Control-Allow-Credentials: true');

    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
```

### 12.3 Production (API untuk Mobile App — Masa Depan)

```php
// Hanya jika API diakses dari domain berbeda
$allowedOrigins = ['https://app.yourdomain.com', 'https://m.yourdomain.com'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
```

> **Peringatan:** Jangan pernah set `Access-Control-Allow-Origin: *` dengan `Allow-Credentials: true`.

---

## 13. ENVIRONMENT MANAGEMENT

### 13.1 Konfigurasi Per Environment

| Setting | Development | Production |
|---------|-------------|------------|
| `APP_ENV` | `development` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `display_errors` | `On` | `Off` |
| `log_errors` | `On` | `On` |
| `BASE_URL` | `http://localhost/wisata/` | `https://yourdomain.com/` |
| DB Password | (kosong / root) | (strong password) |
| CORS | Allow localhost:3000 | Same-origin only |
| HTTPS | Optional | Wajib (HSTS) |

### 13.2 Environment via .env (Opsional)

Jika menggunakan library `vlucas/phpdotenv` (Composer):

```env
# .env (TIDAK di-commit, ada di .gitignore)
APP_ENV=development
APP_DEBUG=true
DB_HOST=localhost
DB_NAME=tour_guide_app
DB_USER=root
DB_PASS=
BASE_URL=http://localhost/wisata/
```

```php
// app/config/config.php
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        putenv($line);
    }
}

define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
// dst.
```

### 13.3 Tanpa .env (PHP Native Murni)

Gunakan file config terpisah per environment:

```
app/config/
├── config.php          → Default config (production)
├── config.local.php    → Override untuk development (TIDAK di-commit)
```

```php
// app/config/config.php
define('APP_ENV', 'production');
define('APP_DEBUG', false);

// Override dengan local config jika ada
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}
```

```php
// app/config/config.local.php (TIDAK di-commit)
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('BASE_URL', 'http://localhost/wisata/');
```

---

> **Modul Selanjutnya:** `04_STRUKTUR_FOLDER_PHP_NATIVE.md` — Struktur folder project secara lengkap
