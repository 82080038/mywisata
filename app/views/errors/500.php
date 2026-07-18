<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | MyWisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .error-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ff6a00 0%, #ee0979 100%); }
        .error-code { font-size: 8rem; font-weight: 700; color: #fff; line-height: 1; }
        .error-text { color: rgba(255,255,255,0.9); }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="text-center">
            <div class="error-code">500</div>
            <h2 class="error-text mb-3">Terjadi Kesalahan Server</h2>
            <p class="error-text mb-4">Maaf, terjadi kesalahan pada server. Silakan coba lagi nanti.</p>
            <a href="<?= BASE_URL ?>" class="btn btn-light btn-lg">
                <i class="fas fa-home me-2"></i>Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
