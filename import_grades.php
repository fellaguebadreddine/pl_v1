<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'softdz38_pl'); // ← MODIFIEZ ICI
define('DB_USER', 'softdz38_pl_user'); // ← MODIFIEZ ICI
define('DB_PASS', '4sN*{7#ld9Gy0phs'); // ← MODIFIEZ ICI

$message = '';
$message_type = '';

// Inclure PhpSpreadsheet (via Composer)
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\IOFactory;
} else {
    $message = "خطأ: مكتبة PhpSpreadsheet غير مثبتة. يرجى تشغيل: composer require phpoffice/phpspreadsheet";
    $message_type = "danger";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_excel']) && empty($message)) {
    $allowed_types = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    $max_size = 5 * 1024 * 1024; // 5 Mo

    // Validation du fichier
    if ($_FILES['fichier_excel']['error'] !== UPLOAD_ERR_OK) {
        $message = "خطأ: حدث خطأ أثناء تحميل الملف (كود الخطأ: " . $_FILES['fichier_excel']['error'] . ")";
        $message_type = "danger";
    } elseif (!in_array($_FILES['fichier_excel']['type'], $allowed_types)) {
        $message = "خطأ: يرجى تحميل ملف Excel بتنسيق .xlsx فقط";
        $message_type = "danger";
    } elseif ($_FILES['fichier_excel']['size'] > $max_size) {
        $message = "خطأ: حجم الملف كبير جدًا (الحد الأقصى 5 ميجابايت)";
        $message_type = "danger";
    } else {
        try {
            // Lire le fichier Excel
            $spreadsheet = IOFactory::load($_FILES['fichier_excel']['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) < 2) {
                throw new Exception("الملف فارغ أو يحتوي على سطر واحد فقط (يجب أن يحتوي على العناوين والبيانات)");
            }

            // Connexion PDO avec UTF8MB4
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );

            // Vérifier que la table existe
            $check_table = $pdo->query("SHOW TABLES LIKE 'grades'");
            if ($check_table->rowCount() === 0) {
                throw new Exception("الجدول 'grades' غير موجود في قاعدة البيانات");
            }

            // Préparer l'insertion (id_societe=1, id_wilaya=2 fixes)
            $stmt = $pdo->prepare("INSERT INTO grades (grade, lois, id_societe, id_wilaya) 
                                   VALUES (:grade, :lois, 1, 2)");

            $imported = 0;
            $errors = [];
            $duplicates = 0;

            // Ignorer la première ligne (en-têtes) et traiter les données
            foreach (array_slice($rows, 1) as $index => $row) {
                // Nettoyer les données
                $grade = trim($row['A'] ?? '');
                $lois = trim($row['B'] ?? '');

                // Ignorer les lignes vides
                if (empty($grade) && empty($lois)) {
                    continue;
                }

                // Validation minimale
                if (empty($grade)) {
                    $errors[] = "السطر " . ($index + 2) . ": حقل 'grade' فارغ";
                    continue;
                }

                // Vérifier les doublons (optionnel mais recommandé)
                $check_dup = $pdo->prepare("SELECT id FROM grades WHERE grade = :grade AND id_societe = 1 AND id_wilaya = 2");
                $check_dup->execute([':grade' => $grade]);
                if ($check_dup->rowCount() > 0) {
                    $duplicates++;
                    continue; // Ignorer les doublons
                }

                // Insérer
                $stmt->execute([
                    ':grade' => $grade,
                    ':lois' => $lois
                ]);

                $imported++;
            }

            // Message de succès
            $message = "تم الاستيراد بنجاح:<br>";
            $message .= "✓ $imported سجل جديد مضاف";
            if ($duplicates > 0) {
                $message .= "<br>⚠ $duplicates سجلات مكررة تم تجاهلها";
            }
            $message_type = "success";

            if (!empty($errors)) {
                $message .= "<br><br>التحذيرات:<br>" . implode('<br>', $errors);
                $message_type = "warning";
            }

        } catch (Exception $e) {
            $message = "خطأ: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استيراد الدرجات (Grades)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15); }
        .table-arabic th { background-color: #e9ecef; }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary"><i class="fas fa-file-excel me-2"></i>استيراد الدرجات</h2>
        <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>

    <div class="row">
        <!-- Formulaire d'upload -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>تحميل ملف Excel</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="importForm">
                        <div class="mb-3">
                            <label for="fichier_excel" class="form-label fw-bold">اختر ملف Excel (.xlsx)</label>
                            <input type="file" class="form-control" id="fichier_excel" name="fichier_excel" accept=".xlsx, .xls" required>
                            <div class="form-text text-muted">
                                <small>
                                    ✓ التنسيق المطلوب: عمود A = grade، عمود B = lois<br>
                                    ✓ سيتم تعيين id_societe=1 و id_wilaya=2 تلقائياً<br>
                                    ✓ تأكد من حفظ الملف بترميز UTF-8 لدعم العربية
                                </small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100" id="submitBtn">
                            <i class="fas fa-file-import me-2"></i>استيراد البيانات
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Exemple de structure -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>هيكل الملف المطلوب</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-arabic">
                            <thead class="table-light">
                                <tr>
                                    <th>العمود A</th>
                                    <th>العمود B</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>grade</strong></td>
                                    <td><strong>lois</strong></td>
                                </tr>
                                <tr>
                                    <td>ممتاز</td>
                                    <td>القانون 01-20</td>
                                </tr>
                                <tr>
                                    <td>جيد جداً</td>
                                    <td>القانون 02-21</td>
                                </tr>
                                <tr>
                                    <td>جيد</td>
                                    <td>القانون 03-22</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-light mt-3 p-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i> 
                            سيتم تعيين الحقول التالية تلقائياً:<br>
                            • id_societe = <strong>1</strong><br>
                            • id_wilaya = <strong>2</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aperçu des données existantes -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-database me-2"></i>البيانات الحالية في الجدول</h5>
            <span class="badge bg-light text-dark">
                <?php
                try {
                    $pdo_preview = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
                    $pdo_preview->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $count = $pdo_preview->query("SELECT COUNT(*) FROM grades WHERE id_societe=1 AND id_wilaya=2")->fetchColumn();
                    echo "$count سجل";
                } catch (Exception $e) {
                    echo "غير متاح";
                }
                ?>
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>grade</th>
                            <th>lois</th>
                            <th>id_societe</th>
                            <th>id_wilaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt_preview = $pdo_preview->prepare("SELECT id, grade, lois, id_societe, id_wilaya FROM grades WHERE id_societe=1 AND id_wilaya=2 ORDER BY id DESC LIMIT 10");
                            $stmt_preview->execute();
                            $results = $stmt_preview->fetchAll();
                            
                            if (count($results) > 0) {
                                foreach ($results as $row) {
                                    echo "<tr>
                                        <td>{$row['id']}</td>
                                        <td>" . htmlspecialchars($row['grade']) . "</td>
                                        <td>" . htmlspecialchars($row['lois']) . "</td>
                                        <td>{$row['id_societe']}</td>
                                        <td>{$row['id_wilaya']}</td>
                                    </tr>";
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center text-muted">لا توجد بيانات حالياً</td></tr>';
                            }
                        } catch (Exception $e) {
                            echo '<tr><td colspan="5" class="text-center text-danger">خطأ في جلب البيانات: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <small class="text-muted">يتم عرض آخر 10 سجلات فقط</small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
// Désactiver le bouton pendant le traitement
document.getElementById('importForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> جاري المعالجة...';
    btn.disabled = true;
});
</script>
</body>
</html>