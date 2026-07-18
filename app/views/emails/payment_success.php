<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; }
        .header { text-align: center; color: #198754; margin-bottom: 20px; }
        .details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .details p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Pembayaran Berhasil</h2>
            <p>MyWisata</p>
        </div>
        <p>Halo,</p>
        <p>Pembayaran Anda telah berhasil diproses. Berikut detail transaksi:</p>
        <div class="details">
            <?php if (isset($transaction_id)): ?>
            <p><strong>ID Transaksi:</strong> <?= htmlspecialchars($transaction_id) ?></p>
            <?php endif; ?>
            <?php if (isset($amount)): ?>
            <p><strong>Jumlah:</strong> Rp <?= number_format($amount) ?></p>
            <?php endif; ?>
            <?php if (isset($payment_method)): ?>
            <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($payment_method) ?></p>
            <?php endif; ?>
            <?php if (isset($status)): ?>
            <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>
            <?php endif; ?>
        </div>
        <p>Terima kasih atas pembayaran Anda.</p>
        <p>Tim MyWisata</p>
    </div>
</body>
</html>
