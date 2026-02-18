<?php
$titre = "403 - غير مصرح بالوصول";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= $titre ?></title>

    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            min-height: 100vh;
        }
        .error-box {
            max-width: 500px;
            border-radius: 16px;
        }
        .error-code {
            font-size: 80px;
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow error-box text-center p-4">
        <div class="card-body">

            <div class="error-code">403</div>

            <h4 class="mb-3">غير مصرح بالوصول</h4>

            <p class="text-muted mb-4">
                عذراً، ليس لديك الصلاحيات اللازمة للدخول إلى هذه الصفحة.
            </p>

            <div class="d-flex justify-content-center gap-2">
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-house-door"></i> الصفحة الرئيسية
                </a>

                <a href="logout.php" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> تسجيل الخروج
                </a>
            </div>

        </div>
    </div>
</div>

</body>
</html>
