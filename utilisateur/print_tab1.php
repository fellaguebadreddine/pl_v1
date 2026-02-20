<?php
require_once('../includes/initialiser.php');

// Vérifie si l'utilisateur est connecté
if (!$session->is_logged_in()) {
    redirect_to('login.php');
}

// Récupère l'ID du tableau
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    redirect_to('tab1.php?action=list_tab1&error=معرف غير صالح');
}

// Récupère les informations du tableau
$tableau = Tableau1::trouve_par_id($id);
if (!$tableau) {
    redirect_to('tab1.php?action=list_tab1&error=الجدول غير موجود');
}

// Récupère l'utilisateur connecté
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    $session->logout();
    redirect_to('login.php');
}

// Récupère la société
$societe = Societe::trouve_par_id($tableau->id_societe);
if (!$societe) {
    redirect_to('tab1.php?action=list_tab1&error=المؤسسة غير موجودة');
}

// Récupère la wilaya de la société (à adapter selon votre structure)
$wilaya = '';
if (isset($societe->wilayas)) {
    $wilaya = Wilayas::trouve_par_id($societe->wilayas);
} else {
    $wilaya = '---';
}

// Récupère les détails du tableau
$details_hf = DetailTab1::trouve_par_tableau($id);
$details_hp = DetailTab1_hp::trouve_par_tableau($id);

// Récupère l'admin qui a créé le tableau
$admin = Accounts::trouve_par_id($current_user->id);

// Année
$annee = $tableau->annee;
$date_fin = '31/12/' . $annee;

// Titre de la page
$titre = "الجدول رقم 01 - " . $societe->raison_ar . " - " . $annee;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre; ?></title>
    <link href='https://fonts.googleapis.com/css?family=Tajawal' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <!-- Inclure les fichiers CSS -->
<link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.rtl.css">
<link rel="stylesheet" href="assets/datatable/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="assets/datatable/css/custom-datatable.css">
 <link rel="preload" href="../css/adminlte.rtl.css" as="style" />
    <!-- Styles d'impression -->
    <style>
        /* Styles généraux pour l'impression */
        @media print {
            @page {
                size: landscape;
                margin: 1.2cm;
            }
            
            body {
                font-family: 'Tajawal', 'Tahoma', 'Times New Roman', sans-serif;
                background: white;
                color: black;
                font-size: 11pt;
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
            }
            
            .table th {
                background-color: #f2f2f2 !important;
                color: black !important;
                font-weight: bold;
                padding: 6px;
                border: 1px solid #333;
                text-align: center;
                vertical-align: middle;
            }
            
            .table td {
                padding: 5px;
                border: 1px solid #333;
                text-align: center;
                vertical-align: middle;
            }
            
            .card {
                border: 1px solid #333;
                margin-bottom: 20px;
                page-break-inside: avoid;
            }
            
            .card-header {
                background-color: #e9ecef !important;
                border-bottom: 2px solid #333;
                padding: 8px 12px;
                font-weight: bold;
                text-align: right;
            }
            
            .fw-bold {
                font-weight: bold;
            }
            
            .text-center {
                text-align: center;
            }
            
            .text-end {
                text-align: right;
            }
            
            /* En-tête républicain */
            .republic-header {
                text-align: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }
            
            .republic-title {
                font-size: 20pt;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .document-ref {
                font-size: 16pt;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .wilaya-info {
                font-size: 14pt;
                margin-bottom: 5px;
            }
            
            .date-info {
                font-size: 12pt;
                margin-bottom: 10px;
            }
            
            .section-title {
                font-size: 14pt;
                font-weight: bold;
                margin: 15px 0 5px;
                text-align: right;
                border-bottom: 1px solid #333;
                padding-bottom: 5px;
            }
            
            .total-row {
                background-color: #f8f9fa !important;
                font-weight: bold;
            }
            
            .footer {
                position: fixed;
                bottom: 0;
                width: 100%;
                text-align: center;
                font-size: 9pt;
                color: #666;
                border-top: 1px solid #333;
                padding-top: 8px;
                margin-top: 20px;
            }
        }
        
        /* Styles à l'écran */
        @media screen {
            body {
                font-family: 'Tajawal', 'Tahoma', sans-serif;
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
            
            .no-print {
                margin-bottom: 20px;
            }
            
            .btn {
                padding: 8px 16px;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                font-size: 14px;
                margin-right: 5px;
            }
            
            .btn-primary {
                background: #007bff;
                color: white;
            }
            
            .btn-secondary {
                background: #6c757d;
                color: white;
            }
            .republic-title {
                font-size: 20pt;
                font-weight: bold;
                margin-bottom: 5px;
            }
        }
        
        /* Styles communs */
        .table-bordered {
            border: 1px solid #333;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Boutons d'impression (cachés à l'impression) -->
        <div class="no-print text-start mb-4">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i> طباعة
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="fas fa-times me-2"></i> إغلاق
            </button>
        </div>
        
        <!-- Informations de l'organisme -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%; border: none;">
                <tr><div class="republic-title text-center">الجمهورية الجزائرية الديمقراطية الشعبية</div></tr>
                <tr>
                    <td style="text-align: right; width: 33%;">
                        <strong>المؤسسة:</strong> <?php echo $societe->raison_ar; ?>-<?php echo $wilaya->nom; ?>
                    </td>
                    <td style="text-align: left; width: 33%;">
                        <div class="date-info">جدول يتعلق بهيكلة التعدادات إلى غاية <?php echo $date_fin; ?></div>
                    </td>
                    <td style="text-align: left; width: 33%;">
                        <div class="date-info">جدول رقم : 01</div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Section 1: الوظائف العليا -->
        <div class="section-title">الوظائف العليا :</div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="5%">الرمز</th>
                        <th width="25%">الوظائف العليا</th>
                        <th width="10%">عدد المناصب المالية</th>
                        <th width="10%">عدد المناصب الحقيقية</th>
                        <th width="10%">بالنيابة</th>
                        <th width="10%">النساء</th>
                        <th width="10%">الفارق</th>
                        <th width="20%">الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_hf_poste = 0;
                    $total_hf_reel = 0;
                    $total_hf_intirim = 0;
                    $total_hf_femme = 0;
                    $total_hf_diff = 0;
                    
                    if (!empty($details_hf)): 
                        foreach ($details_hf as $detail): 
                            $grade = Grade::trouve_par_id($detail->id_grade);
                            
                            $total_hf_poste += $detail->postes_total;
                            $total_hf_reel += $detail->postes_reel;
                            $total_hf_intirim += $detail->poste_intirim;
                            $total_hf_femme += $detail->poste_femme;
                            $diff = $detail->postes_reel - $detail->postes_total;
                            $total_hf_diff += $diff;
                    ?>
                    <tr>
                        <td><?php echo $grade ? $grade->id : '---'; ?></td>
                        <td style="text-align: right;">
                            <?php echo $grade ? $grade->grade : '---'; ?>
                            <?php if ($grade && !empty($grade->loi)): ?>
                                <br><small>(<?php echo $grade->loi; ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($detail->postes_total, 0, '', ' '); ?></td>
                        <td><?php echo number_format($detail->postes_reel, 0, '', ' '); ?></td>
                        <td><?php echo number_format($detail->poste_intirim, 0, '', ' '); ?></td>
                        <td><?php echo number_format($detail->poste_femme, 0, '', ' '); ?></td>
                        <td><?php echo number_format($diff, 0, '', ' '); ?></td>
                        <td><?php echo htmlspecialchars($detail->observations); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">لا توجد بيانات مسجلة</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="2" class="text-end fw-bold">المجموع الفرعي</td>
                        <td class="fw-bold"><?php echo number_format($total_hf_poste, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_reel, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_intirim, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_femme, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_diff, 0, '', ' '); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Section 2: المناصب العليا -->
        <div class="section-title">المناصب العليا :</div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="5%">الرمز</th>
                        <th width="25%">المناصب العليا</th>
                        <th width="10%">عدد المناصب المالية</th>
                        <th width="10%">عدد المناصب الحقيقية</th>
                        <th width="10%">بالنيابة</th>
                        <th width="10%">النساء</th>
                        <th width="10%">الفارق</th>
                        <th width="20%">الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_hp_poste = 0;
                    $total_hp_reel = 0;
                    $total_hp_intirim = 0;
                    $total_hp_femme = 0;
                    $total_hp_diff = 0;
                    
                    if (!empty($details_hp)): 
                        foreach ($details_hp as $detail): 
                            $grade = Grade::trouve_par_id($detail->id_grade_hp);
                            
                            $total_hp_poste += $detail->postes_total_hp;
                            $total_hp_reel += $detail->postes_reel_hp;
                            $total_hp_intirim += $detail->poste_intirim_hp;
                            $total_hp_femme += $detail->poste_femme_hp;
                            $diff = $detail->postes_reel_hp - $detail->postes_total_hp;
                            $total_hp_diff += $diff;
                    ?>
                    <tr>
                        <td><?php echo $grade ? $grade->id : '---'; ?></td>
                        <td style="text-align: right;">
                            <?php echo $grade ? $grade->grade : '---'; ?>
                            <?php if ($grade && !empty($grade->loi)): ?>
                                <br><small>(<?php echo $grade->loi; ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($detail->postes_total_hp, 0, '', ' '); ?></td>
                        <td><?php echo number_format($detail->postes_reel_hp, 0, '', ' '); ?></td>
                        <td><?php echo number_format($detail->poste_intirim_hp, 0, '', ' '); ?></td>
                        <td><?php echo number_format($detail->poste_femme_hp, 0, '', ' '); ?></td>
                        <td><?php echo number_format($diff, 0, '', ' '); ?></td>
                        <td><?php echo htmlspecialchars($detail->observations_hp); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">لا توجد بيانات مسجلة</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="2" class="text-end fw-bold">المجموع الفرعي</td>
                        <td class="fw-bold"><?php echo number_format($total_hp_poste, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hp_reel, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hp_intirim, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hp_femme, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hp_diff, 0, '', ' '); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Totaux généraux -->
        <div style="margin-top: 30px;">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th colspan="2" class="text-center">المجموع العام</th>
                        <th>عدد المناصب المالية</th>
                        <th>عدد المناصب الحقيقية</th>
                        <th>بالنيابة</th>
                        <th>النساء</th>
                        <th>الفارق</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" class="fw-bold text-center">المجموع العام</td>
                        <td class="fw-bold"><?php echo number_format($total_hf_poste + $total_hp_poste, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_reel + $total_hp_reel, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_intirim + $total_hp_intirim, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_femme + $total_hp_femme, 0, '', ' '); ?></td>
                        <td class="fw-bold"><?php echo number_format($total_hf_diff + $total_hp_diff, 0, '', ' '); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Signatures -->
        <div style="margin-top: 40px; display: flex; justify-content: space-between;">
            <div style="text-align: center; width: 30%;">
                <div style="border-top: 1px solid #333; width: 100%; margin-top: 30px; padding-top: 5px;">
                    <strong>رئيس المؤسسة</strong>
                </div>
            </div>
            <div style="text-align: center; width: 30%;">
                <div style="border-top: 1px solid #333; width: 100%; margin-top: 30px; padding-top: 5px;">
                    <strong>المدقق</strong>
                </div>
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