# MODUL 21 — API DESIGN (AJAX + JSON)

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Spesifikasi endpoint API untuk komunikasi AJAX antara frontend (jQuery) dan
backend (PHP Native). Semua response dalam format JSON.

---

## 2. FORMAT RESPONSE STANDAR

```json
{
  "status": "success | error",
  "message": "Deskripsi pesan",
  "data": { ... } | [ ... ],
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20
  }
}
```

---

## 3. HTTP STATUS CODES

| Code | Arti |
|------|------|
| 200 | OK / Success |
| 201 | Created |
| 400 | Bad Request (validation error) |
| 401 | Unauthorized (not logged in) |
| 403 | Forbidden (wrong role) |
| 404 | Not Found |
| 419 | CSRF Token Mismatch |
| 422 | Unprocessable Entity |
| 429 | Rate Limit Exceeded |
| 500 | Internal Server Error |

---

## 4. DAFTAR ENDPOINTS LENGKAP

### 4.1 Auth

| Method | URL | Body | Response |
|--------|-----|------|----------|
| POST | `api/auth/login` | email, password | user data + session |
| POST | `api/auth/register` | name, email, password, role | user id |
| POST | `api/auth/logout` | - | success |
| POST | `api/auth/forgot-password` | email | success |
| GET | `api/auth/me` | - | current user |

### 4.2 Tour Guide

| Method | URL | Params | Response |
|--------|-----|--------|----------|
| GET | `api/guides` | city, language, specialization, min_rating, max_rate, page | list guides |
| GET | `api/guide/{id}` | - | guide detail |
| GET | `api/guide/{id}/languages` | - | languages list |
| GET | `api/guide/{id}/schedules` | month, year | available dates |
| GET | `api/guide/{id}/reviews` | page | reviews list |
| POST | `api/guide/profile/update` | name, bio, rates, etc | success |
| POST | `api/guide/language/add` | language, proficiency | success |
| POST | `api/guide/language/remove` | id | success |
| POST | `api/guide/specialization/add` | specialization | success |
| POST | `api/guide/schedule/add` | date, start, end | success |
| POST | `api/guide/schedule/toggle` | date | success |
| POST | `api/guide/document/upload` | file | success |

### 4.3 Booking

| Method | URL | Body | Response |
|--------|-----|------|----------|
| POST | `api/booking/create` | guide_id, date, time, duration, participants | booking_id, code |
| POST | `api/booking/upload-proof/{id}` | file | success |
| POST | `api/booking/cancel/{id}` | reason | success |
| GET | `api/booking/my` | status, page | bookings list |
| GET | `api/booking/guide/{status}` | - | bookings list |
| POST | `api/booking/accept/{id}` | - | success |
| POST | `api/booking/reject/{id}` | reason | success |
| POST | `api/booking/complete/{id}` | - | success |

### 4.4 Destinasi & Tiket

| Method | URL | Params | Response |
|--------|-----|--------|----------|
| GET | `api/destinations` | category, city, search, page | list |
| GET | `api/destination/{id}` | - | detail |
| GET | `api/destination/{id}/images` | - | images |
| GET | `api/destination/{id}/tickets` | - | ticket types |
| GET | `api/destination/{id}/audio` | lang | audio guide |
| POST | `api/ticket/buy` | ticket_id, visit_date, quantity | order_id |
| GET | `api/ticket/my` | status, page | my tickets |
| POST | `api/ticket/verify` | ticket_code | valid/invalid |

### 4.5 Hotel

| Method | URL | Params | Response |
|--------|-----|--------|----------|
| GET | `api/hotels` | city, type, check_in, check_out, page | list |
| GET | `api/hotel/{id}` | - | detail + rooms |
| POST | `api/hotel/book` | room_id, check_in, check_out, num_rooms | booking_id |
| GET | `api/hotel/my-bookings` | status, page | list |
| POST | `api/hotel/register` | hotel data | success |
| POST | `api/hotel/room/add` | room data | success |

### 4.6 Restoran

| Method | URL | Params | Response |
|--------|-----|--------|----------|
| GET | `api/restaurants` | city, type, cuisine, page | list |
| GET | `api/restaurant/{id}` | - | detail + menu |
| POST | `api/restaurant/order` | restaurant_id, items[], order_type | order_id |
| GET | `api/restaurant/my-orders` | status, page | list |
| POST | `api/restaurant/order/status/{id}` | status | success |
| POST | `api/restaurant/register` | restaurant data | success |
| POST | `api/restaurant/menu/add` | menu data | success |

### 4.7 Event

| Method | URL | Params | Response |
|--------|-----|--------|----------|
| GET | `api/events` | category, page | list upcoming |
| GET | `api/event/{id}` | - | detail |
| POST | `api/event/register` | event_id, num_tickets | reg_id |
| GET | `api/event/my-registrations` | page | list |

### 4.8 Map

| Method | URL | Params | Response |
|--------|-----|--------|----------|
| GET | `api/map/markers` | category | markers array |
| GET | `api/map/nearby` | lat, lng, radius | destinations |

### 4.9 AI Chat

| Method | URL | Body | Response |
|--------|-----|------|----------|
| POST | `api/ai/chat` | message | response + quick_replies |
| GET | `api/ai/history` | - | chat messages |

### 4.10 Notifikasi

| Method | URL | Response |
|--------|-----|----------|
| GET | `api/notification/unread` | count |
| GET | `api/notification/list` | list |
| POST | `api/notification/read/{id}` | success |
| POST | `api/notification/read-all` | success |

### 4.11 Review

| Method | URL | Body | Response |
|--------|-----|------|----------|
| POST | `api/review/create` | type, id, rating, comment | success |
| GET | `api/reviews/{type}/{id}` | page | list |

### 4.12 Admin

| Method | URL | Response |
|--------|-----|----------|
| GET | `api/admin/users` | user list |
| POST | `api/admin/user/update/{id}` | success |
| POST | `api/admin/user/delete/{id}` | success |
| POST | `api/admin/guide/approve/{id}` | success |
| POST | `api/admin/hotel/approve/{id}` | success |
| POST | `api/admin/restaurant/approve/{id}` | success |
| GET | `api/admin/transactions` | transaction list |
| POST | `api/admin/transaction/verify/{id}` | success |
| GET | `api/admin/report/dashboard` | stats |
| POST | `api/admin/notification/broadcast` | success |

---

## 5. JAVASCRIPT API HELPER

```javascript
const API = {
    baseUrl: BASE_URL + 'api/',

    request(url, method, data, callback, errorCallback) {
        $.ajax({
            url: this.baseUrl + url,
            method: method,
            data: method === 'POST' ? JSON.stringify(data) : data,
            contentType: method === 'POST' ? 'application/json' : 'application/x-www-form-urlencoded',
            dataType: 'json',
            headers: { 'X-CSRF-Token': CSRF_TOKEN },
            beforeSend() { $('#loading').show(); },
            success(response) {
                if (response.status === 'success') callback(response);
                else if (errorCallback) errorCallback(response);
                else Swal.fire('Error', response.message, 'error');
            },
            error(xhr) {
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                Swal.fire('Error', msg, 'error');
            },
            complete() { $('#loading').hide(); }
        });
    },

    get(url, params, callback) {
        this.request(url, 'GET', params, callback);
    },

    post(url, data, callback) {
        this.request(url, 'POST', data, callback);
    }
};
```

---

## 6. PAGINATION

```json
{
  "status": "success",
  "data": [...],
  "meta": {
    "total": 150,
    "page": 1,
    "per_page": 20,
    "total_pages": 8
  }
}
```

---

## 7. ERROR HANDLING PATTERNS

### 7.1 Controller Error Response Pattern

```php
// Validation error (422)
$this->json([
    'status' => 'error',
    'message' => 'Validasi gagal',
    'errors' => $v->errors()
], 422);

// Not found (404)
$this->json([
    'status' => 'error',
    'message' => 'Data tidak ditemukan'
], 404);

// Unauthorized (401)
$this->json([
    'status' => 'error',
    'message' => 'Silakan login terlebih dahulu'
], 401);

// Forbidden (403)
$this->json([
    'status' => 'error',
    'message' => 'Anda tidak memiliki akses'
], 403);

// Server error (500)
$this->json([
    'status' => 'error',
    'message' => APP_DEBUG ? $e->getMessage() : 'Terjadi kesalahan internal'
], 500);
```

### 7.2 Frontend Error Handling

```javascript
// API helper dengan error callback
API.post('booking/create', data, function(res) {
    // Success handler
    Swal.fire('Berhasil!', res.message, 'success');
}, function(err) {
    // Error handler — per HTTP status
    if (err.status === 422) {
        // Validation errors — tampilkan per field
        let html = '<ul>';
        Object.values(err.errors).forEach(function(msg) {
            html += '<li>' + msg + '</li>';
        });
        html += '</ul>';
        Swal.fire({ title: 'Validasi Gagal', html: html, icon: 'error' });
    } else if (err.status === 401) {
        Swal.fire('Unauthorized', 'Silakan login kembali', 'warning')
           .then(() => location.href = BASE_URL + 'auth/login');
    } else if (err.status === 403) {
        Swal.fire('Forbidden', 'Anda tidak memiliki akses', 'error');
    } else if (err.status === 429) {
        Swal.fire('Terlalu Banyak Request', 'Mohon tunggu sebentar', 'warning');
    } else {
        Swal.fire('Error', err.message || 'Terjadi kesalahan', 'error');
    }
});
```

### 7.3 Global AJAX Error Handler

```javascript
// Tambahkan di header.php — berlaku untuk semua AJAX
$(document).ajaxError(function(event, xhr, settings, error) {
    if (xhr.status === 401) {
        Swal.fire('Sesi Berakhir', 'Silakan login kembali', 'warning')
           .then(() => location.href = BASE_URL + 'auth/login');
    } else if (xhr.status === 419) {
        Swal.fire('Sesi Kadaluarsa', 'Refresh halaman dan coba lagi', 'warning')
           .then(() => location.reload());
    } else if (xhr.status === 500 && !APP_DEBUG) {
        Swal.fire('Error', 'Terjadi kesalahan server', 'error');
    }
});
```

---

## 8. PAGINATION IMPLEMENTATION

### 8.1 Backend (Controller)

```php
public function list() {
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 20);
    $perPage = min($perPage, 50);  // Max 50 per page
    $offset = ($page - 1) * $perPage;

    $model = $this->model('Destination');
    $data = $model->getPaginated($perPage, $offset);
    $total = $model->countAll();

    $this->json([
        'status' => 'success',
        'data' => $data,
        'meta' => [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ]
    ]);
}
```

### 8.2 Backend (Model)

```php
public function getPaginated($perPage, $offset) {
    $sql = "SELECT * FROM {$this->table} 
            WHERE is_active = 1 
            ORDER BY created_at DESC 
            LIMIT :per_page OFFSET :offset";
    return $this->db->query($sql, [
        'per_page' => $perPage,
        'offset' => $offset
    ])->fetchAll();
}

public function countAll() {
    return (int)$this->db->query(
        "SELECT COUNT(*) as cnt FROM {$this->table} WHERE is_active = 1"
    )->fetch()['cnt'];
}
```

### 8.3 Frontend (JavaScript)

```javascript
let currentPage = 1;

function loadPage(page) {
    currentPage = page;
    API.get('destinations', { page: page, per_page: 20 }, function(res) {
        renderList(res.data);
        renderPagination(res.meta);
    });
}

function renderPagination(meta) {
    let html = '';
    const maxVisible = 5;
    let start = Math.max(1, meta.page - Math.floor(maxVisible / 2));
    let end = Math.min(meta.total_pages, start + maxVisible - 1);

    html += '<nav><ul class="pagination">';
    
    // Prev button
    if (meta.page > 1) {
        html += `<li class="page-item">
            <a class="page-link" href="#" onclick="loadPage(${meta.page - 1})">«</a>
        </li>`;
    }

    // Page numbers
    for (let i = start; i <= end; i++) {
        html += `<li class="page-item ${i === meta.page ? 'active' : ''}">
            <a class="page-link" href="#" onclick="loadPage(${i})">${i}</a>
        </li>`;
    }

    // Next button
    if (meta.page < meta.total_pages) {
        html += `<li class="page-item">
            <a class="page-link" href="#" onclick="loadPage(${meta.page + 1})">»</a>
        </li>`;
    }

    html += '</ul></nav>';
    $('#pagination').html(html);
}

// Initial load
loadPage(1);
```

### 8.4 DataTables Integration (Server-side)

```javascript
$('#data-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: BASE_URL + 'api/admin/transactions',
        type: 'GET',
        dataSrc: function(res) {
            return res.data;
        },
        data: function(d) {
            d.page = Math.floor(d.start / d.length) + 1;
            d.per_page = d.length;
            d.search = d.search.value;
        }
    },
    columns: [
        { data: 'transaction_code' },
        { data: 'name' },
        { data: 'type' },
        { data: 'net_amount' },
        { data: 'payment_status' },
        { data: 'created_at' }
    ]
});
```

---

> **Modul Selanjutnya:** `22_USER_ROLE_PERMISSION.md`
