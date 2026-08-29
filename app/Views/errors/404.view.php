<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #B6D0EF 0%, #63A3F1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .error-card {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #4F8A9E;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <h1 class="error-code">404</h1>
        <h3 class="mb-3">Halaman Tidak Ditemukan</h3>
        <p class="text-muted mb-4"><?= htmlspecialchars($message ?? 'Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.') ?></p>
        <a href="index.php?url=auth/login" class="btn btn-primary btn-lg rounded-pill px-4">Kembali ke Halaman Utama</a>
    </div>
</body>
</html>
