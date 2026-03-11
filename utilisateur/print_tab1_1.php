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
$tableau = Tableau1_1::trouve_par_id($id);
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
$details = DetailTab1_1::trouve_par_tableau($id);

// Récupère l'admin qui a créé le tableau
$admin = Accounts::trouve_par_id($current_user->id);

// Année
$annee = $tableau->annee;
$date_fin = '31/12/2025';


// Titre de la page
$titre = "الجدول رقم 01 - " . $societe->raison_ar . " - " . $annee;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titre; ?></title>
    <!--end::Accessibility Meta Tags-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


    <!--end::Primary Meta Tags-->
    <!-- begin data table -->


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Inclure les fichiers CSS -->
    <link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.rtl.css">
    <link rel="stylesheet" href="assets/datatable/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/datatable/css/custom-datatable.css">


    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="../css/adminlte.rtl.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link href='https://fonts.googleapis.com/css?family=Tajawal' rel='stylesheet'>
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        crossorigin="anonymous" />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../css/adminlte.rtl.css" />
    <!--end::Required Plugin(AdminLTE)-->
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
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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

        .table-light {
            background-color: #adafb1 !important;
            font-weight: bold;
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

        <!-- En-tête républicain -->
        <div class="republic-header">
            <div class="republic-title">الجمهورية الجزائرية الديمقراطية الشعبية</div>
            <div class="document-ref">تابع للجدول رقم : 01 : يتعلق بهيكل التعدادات إلى غاية : <?php echo $date_fin; ?></div>
        </div>

        <!-- Section 1: الوظائف العليا -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th colspan="4"></th>
                        <th colspan="5"> التعداد الحقيقي إلى غاية 31-12 <?php echo $annee; ?></th>
                        <th colspan="4">عقد غير محدد المدة</th>
                        <th colspan="6">عقد محدد المدة</th>
                    </tr>
                    <tr class="table-light">
                        <th class="text-center">القانون الأساسي</th>
                        <th class="text-center">السلك و الرتبة </th>
                        <th class="text-center"> التعداد الحقيقي </th>
                        <th class="text-center"> التعداد المالي لسنة <?php echo $annee; ?></th>
                        <th class="text-center">المرسمون</th>
                        <th class="text-center">المتربصون</th>
                        <th class="text-center">المجموع </th>
                        <th class="text-center">من بينهم نساء</th>
                        <th class="text-center">الفرق</th>
                        <th>التوقيت الكامل</th>
                        <th>من بينهم نساء </th>
                        <th>التوقيت الجزئي</th>
                        <th>من بينهم نساء </th>
                        <th>التوقيت الكامل</th>
                        <th>من بينهم نساء </th>
                        <th>التوقيت الجزئي</th>
                        <th>من بينهم نساء </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tol_effectif_reel_31_dec = 0;
                    $tol_effectif_reel_annee_1 = 0;
                    $tol_titulaires = 0;
                    $tol_stagaires = 0;
                    $tol_tol_titu_stag = 0;
                    $tol_femmes = 0;
                    $tol_difrence = 0;
                    if (!empty($details)):
                        foreach ($details as $detail):
                            $grade = Grade::trouve_par_id($detail->id_grade);
                            $tol_effectif_reel_31_dec += $detail->effectif_reel_31_dec;
                            $tol_effectif_reel_annee_1 += $detail->effectif_reel_annee_1;
                            $tol_titulaires += $detail->titulaires;
                            $tol_stagaires += $detail->stagaires;
                            $tol_tol_titu_stag = $tol_titulaires + $tol_stagaires;
                            $tol_femmes += $detail->femmes;
                            $tol_difrence += $detail->difrence;;
                    ?>
                            <tr>
                                <td><?php echo $grade->loi; ?></td>
                                <td>
                                    <?php echo $grade ? $grade->grade : ''; ?>
                                </td>
                                <td><?php echo number_format($detail->effectif_reel_31_dec, 0, '', ' '); ?></td>
                                <td><?php echo number_format($detail->effectif_reel_annee_1, 0, '', ' '); ?></td>
                                <td><?php echo number_format($detail->titulaires, 0, '', ' '); ?></td>
                                <td><?php echo number_format($detail->stagaires, 0, '', ' '); ?></td>
                                <td><?php echo number_format($detail->tol_titu_stag, 0, '', ' '); ?></td>
                                <td><?php echo number_format($detail->femmes); ?></td>
                                <td><?php echo number_format($detail->difrence); ?></td>
                                <td><?php echo number_format($detail->titulaie_temps_complet); ?></td>
                                <td><?php echo number_format($detail->titulaie_femmes_complet); ?></td>
                                <td><?php echo number_format($detail->titulaie_temps_partiel); ?></td>
                                <td><?php echo number_format($detail->titulaie_femmes_partiel); ?></td>
                                <td><?php echo number_format($detail->contrat_temps_complet); ?></td>
                                <td><?php echo number_format($detail->contrat_femme_complet); ?></td>
                                <td><?php echo number_format($detail->contrat_temps_pratiel); ?></td>
                                <td><?php echo number_format($detail->contrat_femmes_pratiel); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">لا توجد بيانات مسجلة</td>
                        </tr>
                    <?php endif; ?>

                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold" id="totalRow">
                        <td colspan="2" class="text-center">المجموع العام</td>
                        <td class="total_effectif_reel_31_dec"><?php echo number_format($tol_effectif_reel_31_dec, 0, '', ' '); ?></td>
                        <td class="total_effectif_reel_annee_1"><?php echo number_format($tol_effectif_reel_annee_1, 0, '', ' '); ?></td>
                        <td class="total_titulaires"><?php echo number_format($tol_titulaires, 0, '', ' '); ?></td>
                        <td class="total_stagaires"><?php echo number_format($tol_stagaires, 0, '', ' '); ?></td>
                        <td class="total_tol_titu_stag"><?php echo number_format($tol_tol_titu_stag, 0, '', ' '); ?></td>
                        <td class="total_femmes"><?php echo number_format($tol_femmes, 0, '', ' '); ?></td>
                        <td class="total_difrence"><?php echo number_format($tol_difrence, 0, '', ' '); ?></td>
                        <td class="total_titulaie_temps_complet">0</td>
                        <td class="total_titulaie_femmes_complet">0</td>
                        <td class="total_titulaie_temps_partiel">0</td>
                        <td class="total_titulaie_femmes_partiel">0</td>
                        <td class="total_contrat_temps_complet">0</td>
                        <td class="total_contrat_femme_complet">0</td>
                        <td class="total_contrat_temps_pratiel">0</td>
                        <td class="total_contrat_femmes_pratiel">0</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            تمت الطباعة في: <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <script>
        // Lancement automatique de l'impression (optionnel, décommentez si souhaité)
        window.onload = function() {
            window.print();
        };
    </script>

</body>

</html>