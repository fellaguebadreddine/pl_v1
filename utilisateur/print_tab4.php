<?php
// print_tab4.php
require_once('../includes/initialiser.php');
if (!$session->is_logged_in()) redirect_to('../login.php');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect_to('tab4.php?action=list_tab4&error=معرف غير صالح');
$tableau = Tableau4::trouve_par_id($id);
if (!$tableau) redirect_to('tab4.php?action=list_tab4&error=الجدول غير موجود');
$societe = Societe::trouve_par_id($tableau->id_societe);
$createur = Accounts::trouve_par_id($tableau->id_user);
$details = DetailTab4::trouve_par_tableau($id);
$annee = $tableau->annee;
$date_fin = '31/12/' . $annee;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة الجدول 4</title>
              <!-- Inclure les fichiers CSS -->
<link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.rtl.css">
<link rel="stylesheet" href="assets/datatable/css/responsive.bootstrap5.min.css">
<link rel="stylesheet" href="assets/datatable/css/custom-datatable.css">
 <link rel="preload" href="../css/adminlte.rtl.css" as="style" />
     <link href='https://fonts.googleapis.com/css?family=Tajawal' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead><tr><th>الرمز</th><th>السلك</th><th>عدد المناصب الشاغرة (خارجي)</th><th>منتوج التكوين (شبه الطبيبي)</th><th>مسابقة على أساس الشهادة</th><th>المبتدئين المتعاقدين</th><th>العمال المبنى المتعاقدين</th><th>طريقة على أساس الشهادة</th><th>امتحان ميني</th><th>فحص ميني (العمال المبنى صنف)</th><th>المناصب المالية التي تم استغلالها</th><th>عدد المناصب المالية التي تم استغلالها</th><th>الملاحظات</th></tr></thead>
            <tbody>
                <?php foreach ($details as $d): $g = Grade::trouve_par_id($d->id_grade); ?>
                <tr><td><?php echo $g->id; ?></td><td><?php echo $g->grade; ?></td><td><?php echo $d->postes_vacants_externe; ?></td><td><?php echo $d->produit_formation_paramedicale; ?></td><td><?php echo $d->concours_sur_titre; ?></td><td><?php echo $d->debutants_contractuels; ?></td><td><?php echo $d->ouvriers_batiment_contractuels; ?></td><td><?php echo $d->methode_sur_titre; ?></td><td><?php echo $d->examen_mini; ?></td><td><?php echo $d->test_mini_ouvriers; ?></td><td><?php echo $d->postes_financiers_exploites; ?></td><td><?php echo $d->nombre_postes_financiers_exploites; ?></td><td><?php echo $d->observations; ?></td></tr>
                <?php endforeach; ?>
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
        <script>
        // Lancement automatique de l'impression (optionnel, décommentez si souhaité)
         window.onload = function() { window.print(); };
    </script>
</div>
</body>
</html>