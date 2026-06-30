# MODUL 20 — SECURITY SYSTEM

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Lapisan keamanan aplikasi: autentikasi, otorisasi (RBAC), proteksi serangan,
audit log, dan best practices.

---

## 2. AUTENTIKASI

### 2.1 Password Hashing

```php
// Hash saat registrasi
$hash = password_hash($password, PASSWORD_BCRYPT);

// Verify saat login
if (password_verify($input, $hash)) {
    // Login success
}
```

### 2.2 Session Management

```php
<?php
class Session {
    public static function start() {
        session_set_cookie_params([
            'lifetime' => 1800,     // 30 menit
            'httponly'  => true,     // JS tidak bisa akses
            'secure'    => isset($_SERVER['HTTPS']), // HTTPS only
            'samesite'  => 'Lax'
        ]);
        session_start();

        // Regenerate ID setiap 30 menit
        if (isset($_SESSION['last_regeneration']) &&
            time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }

        // Timeout check
        if (isset($_SESSION['last_activity']) &&
            time() - $_SESSION['last_activity'] > 1800) {
            session_destroy();
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}
```

---

## 3. RBAC (ROLE-BASED ACCESS CONTROL)

```php
<?php
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
}
```

### Matrix Akses

| Modul | Admin | Wisatawan | Tour Guide |
|-------|-------|-----------|------------|
| Dashboard Admin | ✓ | ✗ | ✗ |
| User Management | ✓ | ✗ | ✗ |
| Guide Approval | ✓ | ✗ | ✗ |
| CRUD Destinasi | ✓ | ✗ | ✗ |
| Cari Guide | ✓ | ✓ | ✗ |
| Booking Guide | ✓ | ✓ | ✗ |
| Accept/Reject Booking | ✓ | ✗ | ✓ (own) |
| Profil Guide | ✓ (all) | view | ✓ (own) |
| Jadwal Guide | ✗ | ✗ | ✓ (own) |
| Pendapatan | ✓ (all) | ✗ | ✓ (own) |
| Beli Tiket | ✓ | ✓ | ✗ |
| Booking Hotel | ✓ | ✓ | ✗ |
| Pesan Restoran | ✓ | ✓ | ✓ |
| Daftar Event | ✓ | ✓ | ✓ |
| Audio Guide | ✓ (manage) | ✓ (play) | ✗ |
| AI Chat | ✓ | ✓ | ✓ |
| Report | ✓ (all) | ✗ | ✓ (own) |
| Settings | ✓ | ✗ | ✗ |
| Audit Log | ✓ | ✗ | ✗ |

---

## 4. CSRF PROTECTION

```php
// Generate token
public static function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify token
public static function verifyCsrf($token) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('CSRF token mismatch');
    }
}
```

### Usage di form:
```php
<input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
```

### Usage di AJAX:
```javascript
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
    }
});
```

---

## 5. SQL INJECTION PREVENTION

Semua query menggunakan **PDO prepared statements**:

```php
// BENAR
$stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);

// SALAH (jangan lakukan)
$stmt = $this->db->query("SELECT * FROM users WHERE email = '$email'");
```

---

## 6. XSS PREVENTION

```php
// Escape output
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// Helper
class Helper {
    public static function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
```

### Di view:
```php
<p><?= Helper::e($guide['name']) ?></p>
```

---

## 7. INPUT VALIDATION

```php
<?php
class Validator {
    private $data;
    private $errors = [];
    private $rules;

    public function __construct($data) {
        $this->data = $data;
    }

    public function required($fields) {
        foreach ($fields as $field) {
            if (empty($this->data[$field])) {
                $this->errors[$field] = "{$field} wajib diisi";
            }
        }
        return $this;
    }

    public function email($field) {
        if (!filter_var($this->data[$field] ?? '', FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Format email tidak valid";
        }
        return $this;
    }

    public function min($field, $length) {
        if (strlen($this->data[$field] ?? '') < $length) {
            $this->errors[$field] = "Minimal {$length} karakter";
        }
        return $this;
    }

    public function max($field, $length) {
        if (strlen($this->data[$field] ?? '') > $length) {
            $this->errors[$field] = "Maksimal {$length} karakter";
        }
        return $this;
    }

    public function numeric($field) {
        if (!is_numeric($this->data[$field] ?? '')) {
            $this->errors[$field] = "Harus berupa angka";
        }
        return $this;
    }

    public function fails() {
        return !empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }

    public function firstError() {
        return reset($this->errors) ?: null;
    }
}
```

---

## 8. AUDIT LOG

```php
<?php
class Logger {
    public static function audit($action, $module, $description) {
        $log = new AuditLog();
        $log->insert([
            'user_id' => $_SESSION['user_id'] ?? null,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    public static function error($message, $context = []) {
        $log = date('Y-m-d H:i:s') . " ERROR: {$message}";
        if (!empty($context)) $log .= " " . json_encode($context);
        file_put_contents(LOG_PATH . '/error.log', $log . "\n", FILE_APPEND);
    }
}
```

---

## 9. FILE UPLOAD SECURITY

```php
public static function uploadFile($file, $targetDir) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp',
                     'audio/mpeg', 'audio/ogg', 'application/pdf'];
    $maxSize = MAX_UPLOAD_SIZE;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error');
    }
    if ($file['size'] > $maxSize) {
        throw new Exception('File terlalu besar (max 5MB)');
    }
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipe file tidak diizinkan');
    }

    // Generate safe filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $target = $targetDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new Exception('Gagal menyimpan file');
    }
    return $target;
}
```

---

## 10. RATE LIMITING

```php
<?php
class RateLimiter {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function check($key, $maxRequests, $windowSeconds) {
        $sql = "SELECT COUNT(*) as cnt FROM rate_limits
                WHERE api_key = :key AND created_at > DATE_SUB(NOW(), INTERVAL :window SECOND)";
        $count = $this->db->query($sql, ['key' => $key, 'window' => $windowSeconds])->fetch()['cnt'];

        if ($count >= $maxRequests) {
            http_response_code(429);
            echo json_encode(['status' => 'error', 'message' => 'Rate limit exceeded']);
            exit;
        }

        $this->db->query("INSERT INTO rate_limits (api_key) VALUES (:key)", ['key' => $key]);
    }
}

// Usage di ApiController
$limiter = new RateLimiter();
$limiter->check($_SESSION['user_id'] . '_api', 60, 60); // 60 requests per minute
```

---

## 11. HTTPS & SECURITY HEADERS

```apache
# .htaccess
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

## 12. CHECKLIST KEAMANAN

- [x] Password hash bcrypt
- [x] PDO prepared statements
- [x] CSRF token semua form
- [x] XSS escaping semua output
- [x] RBAC middleware
- [x] Session timeout 30 menit
- [x] HttpOnly + Secure cookie
- [x] File upload validation
- [x] Rate limiting API
- [x] Audit log
- [x] Security headers
- [x] HTTPS di production

---

> **Modul Selanjutnya:** `21_API_DESIGN_AJAX_JSON.md`
