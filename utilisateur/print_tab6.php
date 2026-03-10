<?php
// print_tab5.php
require_once('../includes/initialiser.php');

// Vérification de l'utilisateur
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || ($current_user->type !== 'utilisateur' && $current_user->type !== 'administrateur' && $current_user->type !== 'super_admin')) {
    redirect_to('../login.php');
}

// Récupération de l'ID du tableau
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    redirect_to('tab6.php?action=list_tab6&error=معرف غير صالح');
}

// Chargement du tableau principal
$tableau = Tableau6::trouve_par_id($id);
if (!$tableau) {
    redirect_to('tab6.php?action=list_tab6&error=الجدول غير موجود');
}

// Vérification des droits : l'utilisateur doit appartenir à la même société ou être admin/super admin
$societe = Societe::trouve_par_id($tableau->id_societe);
if (!$societe) {
    redirect_to('tab6.php?action=list_tab6&error=المؤسسة غير موجودة');
}
if ($current_user->type == 'utilisateur' && $current_user->id_societe != $societe->id_societe) {
    redirect_to('tab6.php?action=list_tab6&error=غير مصرح بالاطلاع على هذا الجدول');
}

// Chargement des détails
$details = DetailTab6::trouve_par_tableau($id);
$createur = Accounts::trouve_par_id($tableau->id_user);
$annee = $tableau->annee;

$titre = "طباعة الجدول 6 - " . $societe->raison_ar . " - " . $annee;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre; ?></title>
    
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/css/all.min.css">
    
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
                font-size: 9pt;
                line-height: 1.3;
            }
            .no-print {
                display: none !important;
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
                max-width: 1400px;
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
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Bouton d'impression (caché à l'impression) -->
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i> طباعة
            </button>
            <a href="tab5.php?action=list_tab5" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-2"></i> رجوع
            </a>
        </div>
        
        <!-- En-tête républicain -->
        <div class="republic-header">
            <div class="republic-title">الجمهورية الجزائرية الديمقراطية الشعبية</div>
            <div class="document-ref">الجدول رقم : 06</div>
            <div class="document-ref">بيان توقعي لإحالة على التقاعد </div>
            <div class="document-ref">لعام <?php echo $annee; ?></div>
        </div>
        
        <!-- Informations de la société -->
        <div style="margin-bottom: 20px;">
            <table style="width:100%; border:none;">
                <tr>
                    <td style="text-align:right;"><strong>المؤسسة :</strong> <?php echo $societe->raison_ar; ?></td>
                </tr>
                <tr>
                    <td style="text-align:right;"><strong>السنة :</strong> <?php echo $annee; ?></td>
                    <td style="text-align:left;"><strong>تاريخ الإنشاء :</strong> <?php echo date('d/m/Y', strtotime($tableau->date_creation)); ?></td>
                </tr>
                <tr>
                    <td style="text-align:right;"><strong>المسؤول عن التعبئة :</strong> <?php echo $createur ? $createur->prenom . ' ' . $createur->nom : '---'; ?></td>
                    <td style="text-align:left;"><strong>الحالة :</strong>
                        <?php
                        if ($tableau->statut == 'validé') echo 'مصادق عليه';
                        elseif ($tableau->statut == 'brouillon') echo 'مسودة';
                        elseif ($tableau->statut == 'en_attente') echo 'في انتظار المراجعة';
                        else echo $tableau->statut;
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Tableau principal -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                      <tr>
                                        <th>الاسم</th>
                                        <th>اللقب</th>
                                        <th>تاريخ الميلاد</th>
                                        <th>السلك</th>
                                        <th>تاريخ التقاعد</th>
                                        <th>الملاحظات</th>
                                    </tr>
                    
                </thead>
                <tbody>
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $detail): 
                            $grade = Grade::trouve_par_id($detail->id_grade);
                        ?>
                        <tr>                          
                            <td><?php echo $detail->nom; ?></td>
                            <td><?php echo $detail->prenom; ?></td>
                            <td><?php echo $detail->date_naissance; ?></td>
                            <td><?php echo $grade ? $grade->grade : ''; ?></td>
                            <td><?php echo $detail->date_retraite; ?></td>
                            <td><?php echo $detail->observations; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="14" class="text-center">لا توجد بيانات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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