<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; }
        .header { text-align: center; color: #0d6efd; margin-bottom: 20px; }
        .btn { display: inline-block; background: #0d6efd; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Reset Password</h2>
            <p>MyWisata</p>
        </div>
        <p>Halo,</p>
        <p>Anda telah meminta untuk mereset password akun MyWisata Anda.</p>
        <p>Klik tombol di bawah untuk mereset password Anda:</p>
        <p style="text-align: center;">
            <a href="<?= htmlspecialchars($reset_link ?? '') ?>" class="btn">Reset Password</a>
        </p>
        <p><small>Jika Anda tidak meminta reset password, abaikan email ini.</small></p>
        <p>Terima kasih,</p>
        <p>Tim MyWisata</p>
    </div>
</body>
</html>
