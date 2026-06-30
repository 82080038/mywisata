# MODUL 19 — MODUL REPORT & ANALYTIC

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Modul laporan dan analitik untuk admin dan tour guide: dashboard statistik,
grafik tren, dan export laporan.

---

## 2. LAPORAN ADMIN

### 2.1 Dashboard Statistik

```php
<?php
class ReportController extends Controller {

    public function __construct() {
        Middleware::requireRole('admin');
    }

    public function dashboard() {
        $trxModel = $this->model('Transaction');
        $userModel = $this->model('User');
        $bookingModel = $this->model('Booking');
        $destModel = $this->model('Destination');

        $this->view('admin/reports/dashboard', [
            'title' => 'Laporan & Analitik',
            'stats' => [
                'total_users' => count($userModel->all(['status' => 'active'])),
                'total_revenue' => $trxModel->getTotalRevenue(),
                'monthly_revenue' => $trxModel->getMonthlyRevenue(),
                'total_bookings' => $bookingModel->countAll(),
                'monthly_bookings' => $bookingModel->countThisMonth(),
                'total_destinations' => count($destModel->all(['is_active' => 1]))
            ],
            'revenue_chart' => $trxModel->getMonthlyRevenueChart(12),
            'top_destinations' => $destModel->getTopSold(5),
            'top_guides' => $bookingModel->getTopGuides(5)
        ]);
    }
}
```

### 2.2 Model Queries

```php
// Transaction model
public function getTotalRevenue() {
    return $this->db->query(
        "SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions WHERE payment_status = 'paid'"
    )->fetch()['total'];
}

public function getMonthlyRevenue() {
    return $this->db->query(
        "SELECT COALESCE(SUM(net_amount), 0) as total FROM transactions
         WHERE payment_status = 'paid'
         AND MONTH(created_at) = MONTH(CURRENT_DATE())
         AND YEAR(created_at) = YEAR(CURRENT_DATE())"
    )->fetch()['total'];
}

public function getMonthlyRevenueChart($months = 12) {
    return $this->db->query(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                COALESCE(SUM(net_amount), 0) as revenue
         FROM transactions WHERE payment_status = 'paid'
         AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL ? MONTH)
         GROUP BY month ORDER BY month"
    , [$months])->fetchAll();
}

// Destination model
public function getTopSold($limit = 5) {
    return $this->db->query(
        "SELECT d.name, d.city, COUNT(t.id) as tickets_sold,
                COALESCE(SUM(t.total_amount), 0) as revenue
         FROM destinations d
         LEFT JOIN ticket_orders t ON t.id IN (
             SELECT toi.order_id FROM ticket_order_items toi
             JOIN tickets tk ON toi.ticket_id = tk.id
             WHERE tk.destination_id = d.id
         )
         WHERE d.is_active = 1
         GROUP BY d.id ORDER BY tickets_sold DESC LIMIT ?"
    , [$limit])->fetchAll();
}

// Booking model
public function getTopGuides($limit = 5) {
    return $this->db->query(
        "SELECT u.name, g.rating_avg, g.total_tours,
                COUNT(b.id) as total_bookings,
                COALESCE(SUM(b.total_amount), 0) as revenue
         FROM tour_guides g
         JOIN users u ON g.user_id = u.id
         LEFT JOIN bookings b ON b.guide_id = g.id AND b.status = 'completed'
         WHERE g.is_verified = 1
         GROUP BY g.id ORDER BY total_bookings DESC LIMIT ?"
    , [$limit])->fetchAll();
}
```

---

## 3. VIEW: Dashboard Report

```php
<!-- app/views/admin/reports/dashboard.php -->
<?php include 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-3">
    <h2>Laporan & Analitik</h2>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body">
            <h6>Total Pendapatan</h6><h4>Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body">
            <h6>Pendapatan Bulan Ini</h6><h4>Rp <?= number_format($stats['monthly_revenue'], 0, ',', '.') ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body">
            <h6>Total Booking</h6><h4><?= $stats['total_bookings'] ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white"><div class="card-body">
            <h6>Booking Bulan Ini</h6><h4><?= $stats['monthly_bookings'] ?></h4>
        </div></div></div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Tren Pendapatan 12 Bulan</div>
                <div class="card-body"><canvas id="revenueChart" height="100"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Destinasi Terlaris</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <?php foreach ($top_destinations as $d): ?>
                        <tr><td><?= $d['name'] ?></td><td class="text-end"><?= $d['tickets_sold'] ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
new Chart($('#revenueChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($revenue_chart, 'month')) ?>,
        datasets: [{
            label: 'Pendapatan',
            data: <?= json_encode(array_column($revenue_chart, 'revenue')) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.5)'
        }]
    }
});
</script>
<?php include 'app/views/layouts/footer.php'; ?>
```

---

## 4. EXPORT CSV

```php
public function exportTransactions() {
    Middleware::requireRole('admin');
    $trxModel = $this->model('Transaction');
    $data = $trxModel->getAllWithDetails();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_' . date('Ymd') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Kode', 'User', 'Type', 'Amount', 'Method', 'Status', 'Tanggal']);
    foreach ($data as $row) {
        fputcsv($output, [$row['transaction_code'], $row['name'], $row['type'],
            $row['net_amount'], $row['payment_method'], $row['payment_status'], $row['created_at']]);
    }
    fclose($output);
    exit;
}
```

---

## 5. LAPORAN TOUR GUIDE

```php
public function guideEarnings() {
    Middleware::requireRole('tour_guide');
    $guideModel = $this->model('TourGuide');
    $guide = $guideModel->findByUserId($_SESSION['user_id']);

    $this->view('tourguide/earnings', [
        'title' => 'Pendapatan Saya',
        'total' => $guideModel->getTotalEarnings($guide['id']),
        'monthly' => $guideModel->getEarnings($guide['id'], 'month'),
        'history' => $guideModel->getEarnings($guide['id'])
    ]);
}
```

---

## 6. API ENDPOINTS

| Method | URL | Fungsi |
|--------|-----|--------|
| GET | `api/admin/report/dashboard` | Data dashboard |
| GET | `api/admin/report/transactions` | Laporan transaksi |
| GET | `api/admin/report/destinations` | Statistik destinasi |
| GET | `api/admin/report/guides` | Statistik guide |
| GET | `api/admin/report/export-csv` | Export CSV |
| GET | `api/guide/earnings` | Pendapatan guide |

---

> **Modul Selanjutnya:** `20_SECURITY_SYSTEM.md`
