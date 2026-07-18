<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; }
        .header { text-align: center; color: #0d6efd; margin-bottom: 20px; }
        .details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .details p { margin: 5px 0; }
        .btn { display: inline-block; background: #0d6efd; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Konfirmasi Booking</h2>
            <p>MyWisata</p>
        </div>
        <p>Halo,</p>
        <p>Booking Anda telah berhasil dibuat. Berikut detail booking:</p>
        <div class="details">
            <?php if (isset($booking_code)): ?>
            <p><strong>Kode Booking:</strong> <?= htmlspecialchars($booking_code) ?></p>
            <?php endif; ?>
            <?php if (isset($service_type)): ?>
            <p><strong>Tipe Layanan:</strong> <?= htmlspecialchars($service_type) ?></p>
            <?php endif; ?>
            <?php if (isset($start_date)): ?>
            <p><strong>Tanggal:</strong> <?= htmlspecialchars($start_date) ?></p>
            <?php endif; ?>
            <?php if (isset($total_amount)): ?>
            <p><strong>Total:</strong> Rp <?= number_format($total_amount) ?></p>
            <?php endif; ?>
            <?php if (isset($status)): ?>
            <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>
            <?php endif; ?>
        </div>
        <p>Silakan selesaikan pembayaran untuk mengkonfirmasi booking Anda.</p>
        <p style="text-align: center;">
            <a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>payments" class="btn">Bayar Sekarang</a>
        </p>
        <p>Terima kasih telah menggunakan MyWisata.</p>
    </div>
</body>
</html>
