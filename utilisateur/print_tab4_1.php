<?php
// print_tab4_1.php
require_once('../includes/initialiser.php');

// Vérification de la connexion
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}

// Récupération de l'ID du tableau annexe
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    redirect_to('tab4.php?action=list_tab4&error=معرف غير صالح');
}

// Chargement du tableau annexe
$tableau = Tableau4_1::trouve_par_id($id);
if (!$tableau) {
    redirect_to('tab4.php?action=list_tab4&error=الملحق غير موجود');
}

// Vérification des droits : l'utilisateur doit appartenir à la même société ou être admin/super admin
$societe = Societe::trouve_par_id($tableau->id_societe);
if (!$societe) {
    redirect_to('tab4.php?action=list_tab4&error=المؤسسة غير موجودة');
}
$wilaya = Wilayas::trouve_par_id($societe->wilayas);

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    $session->logout();
    redirect_to('../login.php');
}
if ($current_user->type == 'utilisateur' && $current_user->id_societe != $societe->id_societe) {
    redirect_to('tab4.php?action=list_tab4&error=غير مصرح بالاطلاع على هذا الملحق');
}

// Chargement des détails
$details = DetailTab4_1::trouve_par_tableau($id); // méthode à implémenter dans DetailTab4_1

// Récupération du créateur
$createur = Accounts::trouve_par_id($tableau->id_user);

$annee = $tableau->annee;

// Liste des champs numériques pour l'affichage et les totaux
$numeric_fields = [
    'temps_complete_contrat_annee',
    'temps_partiel_contrat_annee',
    'temps_complete_permanente_annee',
    'temps_partiel_permanente_annee',
    'temps_complete_contrat_annee_1',
    'temps_partiel_contrat_annee_1',
    'temps_complete_permanente_annee_1',
    'temps_partiel_permanente_annee_1',
    'temps_complete_contrat_vacant',
    'temps_partiel_contrat_vacant',
    'temps_complete_permanente_vacant',
    'temps_partiel_permanente_vacant'
];

// Libellés en arabe pour les colonnes (adaptés)
$field_labels = [
    'temps_complete_contrat_annee' => 'دوام كامل (عقد)',
    'temps_partiel_contrat_annee' => 'دوام جزئي (عقد)',
    'temps_complete_permanente_annee' => 'دوام كامل (دائم)',
    'temps_partiel_permanente_annee' => 'دوام جزئي (دائم)',
    'temps_complete_contrat_annee_1' => 'دوام كامل (عقد - سنة 1)',
    'temps_partiel_contrat_annee_1' => 'دوام جزئي (عقد - سنة 1)',
    'temps_complete_permanente_annee_1' => 'دوام كامل (دائم - سنة 1)',
    'temps_partiel_permanente_annee_1' => 'دوام جزئي (دائم - سنة 1)',
    'temps_complete_contrat_vacant' => 'دوام كامل (عقد - شاغر)',
    'temps_partiel_contrat_vacant' => 'دوام جزئي (عقد - شاغر)',
    'temps_complete_permanente_vacant' => 'دوام كامل (دائم - شاغر)',
    'temps_partiel_permanente_vacant' => 'دوام جزئي (دائم - شاغر)'
];

// Calcul des totaux
$totals = array_fill_keys($numeric_fields, 0);
foreach ($details as $d) {
    foreach ($numeric_fields as $field) {
        $totals[$field] += $d->$field ?? 0;
    }
}

$titre = "طباعة الملحق 4/1 - " . $societe->raison_ar . " - " . $annee;
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
            <a href="tab4.php?action=list_tab4" class="btn btn-secondary">
                <i class="fas fa-arrow-right me-2"></i> رجوع
            </a>
        </div>

        <!-- En-tête républicain -->
        <div class="republic-header">
            <div class="republic-title">الجمهورية الجزائرية الديمقراطية الشعبية</div>
            <div class="document-ref">  جدول يتعلق بتوظيف الأعوان المتعاقدين في إطار المادة:<br>19 من القانون الأساسي العام للوظيفة العمومية
 <?php echo $annee; ?> </div>
            <div class="wilaya-info">ولاية : <?php echo $wilaya->nom ?? '---'; ?></div>
        </div>

        <!-- Informations de la société et du tableau principal -->
        <div style="margin-bottom: 20px;">
            <table style="width:100%; border:none;">
                <tr>
                    <td style="text-align:right;"><strong>المؤسسة :</strong> <?php echo $societe->raison_ar; ?></td>
                
                    <td style="text-align:right;"><strong>تاريخ الإنشاء :</strong> <?php echo date('d/m/Y', strtotime($tableau->date_creation)); ?></td>
                
                   
                    <td style="text-align:right;"><strong>الحالة :</strong>
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

        <!-- Tableau des détails -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                     <tr>
                                                    <th rowspan="2">تحديد منصب الشغل</th>
                                                    <th rowspan="2" colspan="2" class="text-center align-middle">التصنيف</th>
                                                    <th colspan="4" class="text-center align-middle">التعــداد المالي لسنة <?php echo $annee;?></th>
                                                    <th colspan="4" class="text-center align-middle">التعداد الحقيقي الى غاية 31/12/<?php echo $annee-1;?></th>
                                                    <th colspan="4" class="text-center align-middle">مناصـــب شـــاغرة</th>
                                                    <th rowspan="4"   class="text-center align-middle">الملاحظات</th>
                                                    <th rowspan="4"  class="text-center align-middle">الإجراءات</th>
                                                </tr>
                                                <tr>
                                                    
                                                 
                                                    <th colspan="2" class="text-center align-middle">عقد محدد المدة   </th>
                                                    <th colspan="2" class="text-center align-middle">عقد غير محدد المدة</th>
                                                     <th colspan="2" class="text-center align-middle">عقد محدد المدة   </th>
                                                    <th colspan="2" class="text-center align-middle">عقد غير محدد المدة</th>
                                                     <th colspan="2" class="text-center align-middle">عقد محدد المدة   </th>
                                                    <th colspan="2" class="text-center align-middle">عقد غير محدد المدة</th>
                                                </tr>
                    <tr>
                                                    <th rowspan="2" class="text-center align-middle">السلك</th>
                                                    <th rowspan="2" class="text-center align-middle">التصنيف</th>
                                                    <th rowspan="2" class="text-center align-middle">رقم الإستدلالي</th>
                                                    <?php foreach ($numeric_fields as $field): ?>
                                                        <th class="text-center"><?php echo $field_labels[$field] ?? $field; ?></th>
                                                    <?php endforeach; ?>
                                                    
                                                </tr>
                    <tr>
                        <!-- deuxième ligne vide -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $d): 
                            $grade = Grade::trouve_par_id($d->id_grade);
                        ?>
                        <tr>
                            <td><?php echo $grade ? $grade->id : ''; ?></td>
                            <td><?php echo $grade ? $grade->grade : ''; ?></td>
                            <td><?php echo htmlspecialchars($d->categorie); ?></td>
                            <td><?php echo $d->num_categorie; ?></td>
                            <?php foreach ($numeric_fields as $field): ?>
                                <td><?php echo $d->$field ?? 0; ?></td>
                            <?php endforeach; ?>
                            <td><?php echo htmlspecialchars($d->observation); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo 4 + count($numeric_fields) + 1; ?>" class="text-center">لا توجد بيانات</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="4" class="fw-bold text-end">المجموع</td>
                        <?php foreach ($numeric_fields as $field): ?>
                            <td class="fw-bold"><?php echo $totals[$field]; ?></td>
                        <?php endforeach; ?>
                        <td></td>
                    </tr>
                </tfoot>
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