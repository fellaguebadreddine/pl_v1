<?php
require_once('../includes/initialiser.php');

// Vérifie si l'utilisateur est connecté
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}

// Récupère les infos de l'utilisateur connecté
$current_user = Accounts::trouve_par_id($session->id_utilisateur);

if (!$current_user) {
    $session->logout();
    redirect_to('../login.php');
}

// Vérifie si l'utilisateur est bien un utilisateur (admin de société)
if ($current_user->type !== 'utilisateur') {
    $session->logout();
    redirect_to('../login.php');
}

// Récupérer la société de l'utilisateur
if (!$current_user->id_societe) {
    $session->logout();
    redirect_to('../login.php');
}

$societe = Societe::trouve_par_id($current_user->id_societe);

if (!$societe) {
    $session->logout();
    redirect_to('../login.php');
}

// Récupérer l'exercice actif
$exercice_actif = Exercice::get_exercice_actif();

// Déterminer l'action
$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab2';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 2 -   لجان المستخدمين";
$active_menu = "tab_2";
$active_submenu = "tabl_2";
$header = array('select2');

require_once("composit/header.php");
?>
<?php
$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe = Tableau2::existe_pour_societe_annee($current_user->id_societe, $annee);

$tabls = Tableau2::trouve_tableau_2_par_id($societe->id_societe);


if ($action == "add_tab2") {

    $annee = $exercice_actif ? $exercice_actif->annee : date('Y');

    $existe = Tableau2::existe_pour_societe_annee(
        $current_user->id_societe,
        $annee
    );

    if ($existe) {
        redirect_to("?action=list_tab2");
        exit;
    }
}
?>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">الجدول 2 - لجان المستخدمين / لجان الطعن </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الجدول 2</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content Header-->

    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    تم حفظ الجدول بنجاح!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action == "list_tab2"): ?>
                <?php if (!empty($tabls->commentaire_admin) && $tabls->statut != 'validé'): ?>
                    <div class="alert alert-info mt-2">
                        <strong>ملاحظة الإدارة :</strong>
                        <?php echo nl2br(htmlspecialchars($tabls->commentaire_admin)); ?>
                    </div>
                <?php endif; ?>
                <!-- Liste des tableaux existants -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>الجدول 2  
                        </h5>
                        <?php if ($exercice_actif): ?>
                            <?php if (!$existe): ?>
                                <a href="?action=add_tab2" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> إضافة جدول رقم 2
                                </a>

                                <?php else:
                                if (isset($tabls->statut) && $tabls->statut != 'validé'): ?>

                                    <a href="?action=edit_tab2&id=<?php echo $existe; ?>" class="btn btn-warning">
                                        <i class="fas fa-edit me-1"></i> تعديل الجدول الحالي
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">ID</th>
                                        <th width="10%" class="text-center">السنة</th>
                                        <th width="10%" class="text-center">الحالة</th>
                                        <th width="15%" class="text-center">تاريخ التقديم</th>
                                        <th width="10%" class="text-center">  الملاحظات</th>
                                        <th width="15%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php

                                    if (!empty($tabls)):

                                        $statut_badge = $tabls->statut == 'validé' ? 'success' : ($tabls->statut == 'en_attente' ? 'warning' : 'secondary');
                                    ?>
                                        <tr>
                                            <td class="text-center">

                                                <a href="print_tab1.php?id=<?php echo $tabls->id; ?>" class="btn btn-sm btn-info" target="_blank">
                                                    <i class="fa fa-print "></i> <?php echo $tabls->id; ?>
                                                </a>
                                            </td>
                                            <td class="text-center"><?php echo $tabls->annee; ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?php echo $statut_badge; ?>">
                                                    <?php echo $tabls->statut; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php echo $tabls->date_valide ? date('d/m/Y', strtotime($tabls->date_valide)) : '---'; ?>
                                            </td>
                                            <td class="text-center"><?php echo $tabls->commentaire_admin; ?></td>  
                                            <td class="text-center">
                                                <?php if ($exercice_actif && $tabls->statut != 'validé'): ?>
                                                    <a href="edit_tableau.php?id=<?php echo $tabls->id; ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="supprimerTableau(<?php echo $tabls->id; ?>)"
                                                        class="btn btn-sm btn-danger" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-4">
                                                <i class="fas fa-table fa-2x text-muted mb-3 d-block"></i>
                                                لا توجد جداول مسجلة
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action == "add_tab2" || $action == "edit_tab2"): ?>
                <div class="row">
                    <div class="col-12">
                        <!-- Overlay de chargement -->
                        <div id="loadingOverlay" class="loading-overlay" style="display: none;">
                            <div class="spinner"></div>
                            <div class="mt-3 text-primary fw-bold">جاري معالجة البيانات...</div>
                        </div>

                        <!-- Messages d'alerte -->
                        <div id="alertContainer"></div>

                        <?php
                        // Récupérer les données existantes si en mode édition
                        $tableau = null;
                        $details = array();
                        $annee = $exercice_actif ? $exercice_actif->annee : date('Y');

                        if ($action == "edit_tab2" && $id > 0) {
                            $tableau = Tableau2::trouve_par_id($id);
                            if ($tableau) {
                                $annee = $tableau->annee;
                                $details = DetailTab2::trouve_tab_vide_par_admin($id);
                            }
                        } else {
                            // En mode ajout, vérifier s'il y a un brouillon
                            $details = DetailTab2::trouve_tab_vide_par_admin($current_user->id, $current_user->id_societe);
                        }

                        // Récupérer tous les grades
                        $grades = Grade::trouve_tous();
                        ?>

                        <div class="portlet-body table-responsive hauts_fonctionnaires">
                            <table class="table table-bordered table-striped">
                                <thead>
                                        <th width="15%" rowspan="3"> الأسلاك</th>
                                        <th colspan="4">لجان الموظفين</th>
                                        <th colspan="4">لجان الطعن</th>
                                        <th rowspan="3">الملاحظات</th>
                                        <th rowspan="3">الإجراءات</th>
                                    </tr>
                                    <!-- Niveau 2 -->
                                    <tr>
                                        <!-- لجان الموظفين -->
                                        <th rowspan="2">مرجع القرار المتعلق بالإنشاء</th>
                                        <th rowspan="2">حدود الصلاحيات</th>
                                        <th colspan="2">التمديد</th>
                                        <!-- لجان الطعن -->
                                        <th rowspan="2">المرجع</th>
                                        <th rowspan="2">حدود الصلاحيات</th>
                                        <th colspan="2">التمديد</th>
                                    </tr>
                                    <!-- Niveau 3 -->
                                    <tr>
                                        <!-- تمديد الموظفين -->
                                        <th>المرجع</th>
                                        <th>الحدود</th>
                                        <!-- تمديد الطعن -->
                                        <th>المرجع</th>
                                        <th>الحدود</th>
                                    </tr>

                                </thead>
                                <tbody id="existing_consiel">
                                    <?php if (!empty($details)): ?>
                                        <?php foreach ($details as $detail):
                                            $grade = Grade::trouve_par_id($detail->id_grade);
                                        ?>
                                            <tr data-id="<?php echo $detail->id; ?>">
                                                <td>
                                                    <?php echo $grade ? $grade->corps : ''; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->loi_consiel_employer; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->date_fin_consiel_employer; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->reference_consiel_employer_prolong; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->date_fin_consiel_employer_prolong; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->reference_consiel_recours; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->date_fin_consiel_recours; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->reference_consiel_recours_prolong; ?>
                                                </td>
                                                <td>
                                                    <?php echo $detail->date_fin_consiel_recours_prolong; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($detail->observations); ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm delete-btn" >
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="noData">
                                            <td colspan="11" class="text-center text-muted">
                                                لا توجد بيانات مسجلة
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tbody id="tbody_hauts_fonctionnaires">
                                    <tr class="item-row">
                                        <td>
                                            <select name="id_grade" class="form-control select2 grade-select" required>
                                                <option value="">اختر السلك</option>
                                                <?php foreach ($grades as $g): ?>
                                                    <option value="<?php echo $g->id; ?>" data-code="<?php echo $g->id; ?>">
                                                        <?php echo $g->corps; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text" name="loi_consiel_employer" id="loi_consiel_employer" class="form-control text-center" required>
                                        </td>
                                        <td>
                                            <input type="date" name="date_fin_consiel_employer" id="date_fin_consiel_employer" class="form-control text-center postes-reel" required>
                                        </td>
                                        <td>
                                            <input type="text" name="reference_consiel_employer_prolong" id="reference_consiel_employer_prolong" class="form-control text-center poste-intirim">
                                        </td>
                                        <td>
                                            <input type="date" name="date_fin_consiel_employer_prolong" id="date_fin_consiel_employer_prolong" class="form-control">
                                        </td>
                                        <td>
                                            <input type="text" name="reference_consiel_recours" id="reference_consiel_recours" class="form-control text-center ">
                                        </td>
                                        <td>
                                            <input type="date" name="date_fin_consiel_recours" id="date_fin_consiel_recours" class="form-control text-center ">
                                        </td>
                                        <td>
                                            <input type="text" name="reference_consiel_recours_prolong" id="reference_consiel_recours_prolong" class="form-control text-center ">
                                        </td>
                                        <td>
                                            <input type="date" name="date_fin_consiel_recours_prolong" id="date_fin_consiel_recours_prolong" class="form-control text-center ">
                                        </td>
                                        <td>
                                            <input type="text" name="observations" id="observations" class="form-control text-center ">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-success btn-sm add-consiel-btn">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br>
                <!-- Boutons d'action -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="?action=list_tab2" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-success" onclick="saveTableau2()">
                                    <i class="fas fa-paper-plane me-1"></i> حفظ وتقديم
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>

    <!-- JavaScript AJAX -->


    <style>
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            backdrop-filter: blur(2px);
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
            vertical-align: middle;
        }

        .table td {
            vertical-align: middle;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .card-header {
            border-bottom: 2px solid rgba(0, 0, 0, .125);
        }

        .difference {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
<?php endif; ?>
</div>
</div>
<!--end::App Content-->
</main>
<!--end::App Main-->

<?php require_once("composit/footer.php"); ?>