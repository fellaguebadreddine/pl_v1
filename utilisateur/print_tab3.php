<?php
require_once('../includes/initialiser.php');

// Vérification de la connexion
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    $session->logout();
    redirect_to('../login.php');
}

// Récupération de l'ID du tableau
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    redirect_to('tab3.php?action=list_tab3&error=معرف غير صالح');
}

// Chargement du tableau
$tableau = Tableau3::trouve_par_id($id);
if (!$tableau) {
    redirect_to('tab3.php?action=list_tab3&error=الجدول غير موجود');
}

// Vérification des droits : l'utilisateur doit appartenir à la même société ou être admin/super admin
$societe = Societe::trouve_par_id($tableau->id_societe);
if (!$societe) {
    redirect_to('tab3.php?action=list_tab3&error=المؤسسة غير موجودة');
}
if ($current_user->type == 'utilisateur' && $current_user->id_societe != $societe->id_societe) {
    redirect_to('tab3.php?action=list_tab3&error=غير مصرح بالاطلاع على هذا الجدول');
}
// Récupère la wilaya de la société (à adapter selon votre structure)
$wilaya = '';
if (isset($societe->wilayas)) {
    $wilaya = Wilayas::trouve_par_id($societe->wilayas);
} else {
    $wilaya = '---';
}
// Chargement des détails
$details = DetailTab3::trouve_par_tableau($id); // méthode à implémenter dans DetailTab3

// Récupération du créateur (admin de la société)
$createur = Accounts::trouve_par_id($tableau->id_user);

$annee = $tableau->annee;
$date_fin = '31/12/' . $annee;

// Titre de la page
$titre = "طباعة الجدول رقم 3 - " . $societe->raison_ar . " - " . $annee;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre; ?></title>

           <!-- Inclure les fichiers CSS -->
<link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.rtl.css">
<link rel="stylesheet" href="assets/datatable/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="assets/datatable/css/custom-datatable.css">
 <link rel="preload" href="../css/adminlte.rtl.css" as="style" />
     <link href='https://fonts.googleapis.com/css?family=Tajawal' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Styles d'impression -->
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 1.5cm;
            }
            body {
                font-family: 'Tajawal', 'Tahoma', 'Times New Roman', sans-serif;
                background: white;
                color: black;
                font-size: 10pt;
                line-height: 1.3;
            }
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            .container-fluid {
                width: 100%;
                padding: 0;
                margin: 0;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
                border: 1px solid #333;
            }
            .table th {
                background-color: #f2f2f2 !important;
                color: black !important;
                font-weight: bold;
                padding: 4px;
                border: 1px solid #333;
                text-align: center;
                vertical-align: middle;
            }
            .table td {
                padding: 3px;
                border: 1px solid #333;
                text-align: center;
                vertical-align: middle;
            }
            .republic-header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            .republic-title {
                font-size: 18pt;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .document-ref {
                font-size: 14pt;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .wilaya-info {
                font-size: 12pt;
                margin-bottom: 5px;
            }
            .date-info {
                font-size: 11pt;
                margin-bottom: 10px;
            }
            .signature {
                margin-top: 40px;
                display: flex;
                justify-content: space-between;
            }
            .signature > div {
                text-align: center;
                width: 30%;
            }
            .signature-line {
                border-top: 1px solid #333;
                margin-top: 30px;
                padding-top: 5px;
            }
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                text-align: center;
                font-size: 8pt;
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 5px;
                margin-top: 20px;
            }
        }

        @media screen {
            body {
                font-family: 'Arial', 'Tahoma', sans-serif;
                background: #f5f5f5;
                padding: 20px;
            }
            .print-container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                padding: 30px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border-radius: 5px;
            }
            .btn-print {
                margin-bottom: 20px;
            }
        }

        .checkbox-cell {
            font-size: 1.2em;
        }
        .checkbox-cell .fa-check {
            color: green;
        }
        .checkbox-cell .fa-times {
            color: red;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Bouton d'impression (caché à l'impression) -->
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i> طباعة
            </button>
            <a href="tab3.php?action=list_tab3" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-2"></i> رجوع
            </a>
        </div>

        <!-- En-tête républicain -->
        <div class="republic-header">
            <div class="republic-title">الجمهورية الجزائرية الديمقراطية الشعبية</div>
            <div><strong>المؤسسة :</strong> <?php echo $societe->raison_ar; ?></td></div>
            <div class="document-ref">الجدول رقم : 03</div>
            <div class="wilaya-info">ولاية : <?php echo $societe->wilaya ?? '---'; ?></div>
            <div class="date-info">جدول يتعلق بحركة الموظفين إلى غاية <?php echo $date_fin; ?></div>
        </div>

        <!-- Tableau principal -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle">الرمز</th>
                        <th rowspan="2" class="align-middle">السلك</th>
                        <th colspan="2" class="text-center">الإلتحاق بالتكوين</th>
                        <th colspan="2" class="text-center">التوظيف الخارجي</th>
                        <th colspan="2" class="text-center">الترقية</th>
                        <th rowspan="2" class="align-middle">التثبيه حسب</th>
                        <th rowspan="2" class="align-middle">إدماج</th>
                        <th rowspan="2" class="align-middle">الملاحظات</th>
                    </tr>
                    <tr>
                        <th>داخلي</th>
                        <th>خارجي</th>
                        <th>مسابقة على أساس الإختبارات والفحوص المبنية على الشهادة</th>
                        <th>مسابقة على أساس الإختبارات والفحوص</th>
                        <th>إمتحان</th>
                        <th>فحص</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $detail):
                            $grade = Grade::trouve_par_id($detail->id_grade);
                            if (!$grade) continue;
                        ?>
                        <tr>
                            <td><?php echo $grade->id; ?></td>
                            <td><?php echo $grade->grade; ?></td>
                            <td class="checkbox-cell">
                                <?php if ($detail->interne): ?>
                                    <i class="fas fa-check text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td class="checkbox-cell">
                                <?php if ($detail->externe): ?>
                                    <i class="fas fa-check text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td class="checkbox-cell">
                                <?php if ($detail->diplome): ?>
                                    <i class="fas fa-check text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td class="checkbox-cell">
                                <?php if ($detail->concour): ?>
                                    <i class="fas fa-check text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td class="checkbox-cell">
                                <?php if ($detail->examen_pro): ?>
                                    <i class="fas fa-check text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td class="checkbox-cell">
                                <?php if ($detail->test_pro): ?>
                                    <i class="fas fa-check text-success"></i>
                                <?php else: ?>
                                    <i class="fas fa-times text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($detail->loi); ?></td>
                            <td><?php echo $detail->nomination; ?></td>
                            <td><?php echo htmlspecialchars($detail->observation); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="11" class="text-center">لا توجد بيانات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        

        <!-- Signatures -->
        <div class="signature">
            <div>
                <div class="signature-line">رئيس المؤسسة</div>
            </div>
            <div>
                <div class="signature-line">المدقق</div>
            </div>
            <div>
                <div class="signature-line">المسؤول عن التعبئة</div>
                <div><small><?php echo $createur ? $createur->prenom . ' ' . $createur->nom : '---'; ?></small></div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            تمت الطباعة في: <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <script>
        // Lancement automatique de l'impression (optionnel, décommentez si souhaité)
         window.onload = function() { window.print(); };
    </script>
</body>
</html>