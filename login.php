<?php
require_once("includes/initialiser.php");

// Si déjà connecté, on redirige automatiquement
if ($session->is_logged_in()) {
    $utilisateur = Accounts::trouve_par_id($session->id_utilisateur);
    if ($utilisateur) {
        Accounts::redirection_par_role($utilisateur);
        exit;
    }
}

$message = "";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $mot_passe = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username !== "" && $mot_passe !== "") {
        $found_user = Accounts::trouver_par_login($username, $mot_passe);

        if ($found_user) {
            $session->login($found_user);
            Accounts::redirection_par_role($found_user);
            exit;
        } else {
            $message = "إسم المستخدم أو كلمة المرور غير صحيحة.";
        }
    } else {
        $message = "جميع الحقول مطلوبة.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>   - تسجيل الدخول</title>
    <!-- Bootstrap 5 RTL -->
    <link  href="css/adminlte.rtl.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Tajawal' rel='stylesheet'>
    <!-- Custom CSS -->
   
</head>
<body class="login-page">
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-6 col-xl-4">
                <div class="card login-card shadow-lg border-0">
                    <div class="card-header bg-primary text-white py-4 border-0">
                        <div class="text-center">
                            <i class="fas fa-landmark fa-3x mb-3"></i>
                            <h1 class="h3 mb-2">خطة الإدارة للقطاع العام</h1>
                        </div>
                    </div>
                    <div class="card-body p-5">
                    <?php if (!empty($message)) : ?>
                        <div class="alert alert-danger text-center"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>
                        <form id="loginForm" action="login.php" method="post">
                            <div class="mb-4">
                                <label for="username" class="form-label fw-bold">
                                    <i class="fas fa-user me-2"></i>اسم المستخدم
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="اسم المستخدم " required>
                                </div>
                                <small class="form-text text-muted">استخدم هوية الخدمة الخاصة بك</small>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-2"></i>كلمة المرور
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="أدخل كلمة المرور" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">كلمة المرور حساسة لحالة الأحرف</small>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg" name="b_login"  id = "b_login">
                                    <i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3 bg-light">
                        <small class="text-muted">
                            &copy; 2026   . جميع الحقوق محفوظة.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   
    <script>
        // التحكم في عرض/إخفاء كلمة المرور
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
        /* =====================
        Simple validation
        ===================== */
        document.getElementById("loginForm").addEventListener("submit", function (e) {
            const user = document.getElementById("username").value.trim();
            const pass = document.getElementById("password").value.trim();

            if (user === "" || pass === "") {
                e.preventDefault();
                alert("يرجى إدخال اسم المستخدم وكلمة المرور");
            }
        });

    </script>
</body>
</html>