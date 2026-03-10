<?php
// print_tab9.php
require_once('../includes/initialiser.php');

if (!$session->is_logged_in()) redirect_to('../login.php');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect_to('tab9.php?action=list_tab9&error=معرف غير صالح');
$tableau = Tableau9::trouve_par_id($id);
if (!$tableau) redirect_to('tab9.php?action=list_tab9&error=الجدول غير موجود');
$societe = Societe::trouve_par_id($tableau->id_societe);
if (!$societe) redirect_to('tab9.php?action=list_tab9&error=المؤسسة غير موجودة');
$createur = Accounts::trouve_par_id($tableau->id_user);
$details = DetailTab9::trouve_par_tableau($id);
$annee = $tableau->annee;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة الجدول 9</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
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
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary">طباعة</button>
        <a href="tab9.php?action=list_tab9" class="btn btn-secondary">رجوع</a>
    </div>
      <div class="republic-header">
            <div class="republic-title">الجمهورية الجزائرية الديمقراطية الشعبية</div>
            <div class="document-ref">الجدول رقم : 09</div>
            <div class="document-ref">  قائمة إحصائية للأعوان المتعاقدين    </div>
            <div class="document-ref">لعام <?php echo $annee; ?></div>
        </div>
    <div><strong>المؤسسة :</strong> <?php echo $societe->raison_ar; ?></div>
    <div class="table-responsive mt-3">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>الرتبة</th>
                    <th>التصنيف</th>
                    <th>الإطار القانوني</th>
                    <th>توقيت كامل (1)</th>
                    <th>توقيت جزئي (1)</th>
                    <th>توقيت كامل (2)</th>
                    <th>توقيت جزئي (2)</th>
                    <th>توقيت كامل (3)</th>
                    <th>توقيت جزئي (3)</th>
                    <th>الملاحظات</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_p1 = 0; $total_p2 = 0; $total_p3 = 0;
                $total_pa1 = 0; $total_pa2 = 0; $total_pa3 = 0;
                foreach ($details as $d):
                    $grade = Grade::trouve_par_id($d->id_grade);
                    $total_p1 += $d->temps_plein_1;
                    $total_pa1 += $d->temps_partiel_1;
                    $total_p2 += $d->temps_plein_2;
                    $total_pa2 += $d->temps_partiel_2;
                    $total_p3 += $d->temps_plein_3;
                    $total_pa3 += $d->temps_partiel_3;
                ?>
                <tr>
                    <td><?php echo $grade ? $grade->grade : '---'; ?></td>
                    <td><?php echo htmlspecialchars($d->classification); ?></td>
                    <td><?php echo htmlspecialchars($d->cadre_juridique); ?></td>
                    <td><?php echo $d->temps_plein_1; ?></td>
                    <td><?php echo $d->temps_partiel_1; ?></td>
                    <td><?php echo $d->temps_plein_2; ?></td>
                    <td><?php echo $d->temps_partiel_2; ?></td>
                    <td><?php echo $d->temps_plein_3; ?></td>
                    <td><?php echo $d->temps_partiel_3; ?></td>
                    <td><?php echo htmlspecialchars($d->observations); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="4" class="text-end">المجموع العام</td>
                    <td><?php echo $total_p1; ?></td>
                    <td><?php echo $total_pa1; ?></td>
                    <td><?php echo $total_p2; ?></td>
                    <td><?php echo $total_pa2; ?></td>
                    <td><?php echo $total_p3; ?></td>
                    <td><?php echo $total_pa3; ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-around">
        <div>رئيس المؤسسة</div>
        <div>المدقق</div>
        <div>المسؤول عن التعبئة<br><small><?php echo $createur ? $createur->prenom . ' ' . $createur->nom : ''; ?></small></div>
    </div>
    <div class="text-center mt-3">تمت الطباعة: <?php echo date('d/m/Y H:i'); ?></div>
</div>
<script>
        // Lancement automatique de l'impression (optionnel, décommentez si souhaité)
         window.onload = function() { window.print(); };
    </script>
</body>
</html>