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

$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab1';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 01  ";
$active_menu = "tab_1";
$active_submenu = "tabl_1";
$header = array('select2');

require_once("composit/header.php");
?>
<?php
$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe = Tableau1::existe_pour_societe_annee($current_user->id_societe, $annee);
$tabls = Tableau1::trouve_tableau_1_par_id($societe->id_societe);


if ($action == "add_tab1") {

$annee = $exercice_actif ? $exercice_actif->annee : date('Y');

$existe = Tableau1::existe_pour_societe_annee(
    $current_user->id_societe,
    $annee
);

if ($existe) {
    redirect_to("?action=list_tab1");
    exit;
}
}
?>
<?php
$existe_tab_1_1 = Tableau1_1::existe_pour_societe_annee($current_user->id_societe, $annee);
$tabl_1_1 = Tableau1_1::trouve_tableau_1_1_par_id($societe->id_societe);


if ($action == "add_tab1_1") {
$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe_tab_1_1 = Tableau1_1::existe_pour_societe_annee(
    $current_user->id_societe,
    $annee
);

if ($existe_tab_1_1) {
    redirect_to("?action=list_tab1");
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
                    <h3 class="mb-0">الجدول رقم 1 </h3>
                    <h4>جدول يتعلق بهيكل التعددات إلى غاية : <?php echo '31-12-'. ($annee -1);?></h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الجدول 1</li>
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
            
            <?php if ($action == "list_tab1"): ?>
                 <?php if (!empty($tabls->commentaire_admin) && $tabls->statut !='validé'): ?>
                    <div class="alert alert-info mt-2">
                        <strong>ملاحظة الإدارة :</strong>
                        <?php echo nl2br(htmlspecialchars($tabls->commentaire_admin)); ?>
                    </div>
                <?php endif; ?>
                <!-- Liste des tableaux existants -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>  الجدول رقم 1
                        </h5>
                        <?php if ($exercice_actif):?>
                        <?php if (!$existe): ?>
                        <a href="?action=add_tab1" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> إضافة جدول رقم 1
                        </a>

                        <?php else: 
                            if ($tabls->statut != 'validé'):?>

                        <a href="?action=edit_tab1&id=<?php echo $existe; ?>" class="btn btn-warning">
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
                                        <th width="10%" class="text-center">الملاحظات</th>
                                        <th width="15%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    
                                    if (!empty($tabls)): 
                                         
                                            $statut_badge = $tabls->statut == 'validé' ? 'success' : 
                                                          ($tabls->statut == 'en_attente' ? 'warning' : 'secondary');
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
                                        <?php if ($exercice_actif && $tabls->statut != 'validé'):?>
                                            <a href="?action=edit_tab1&id=<?php echo $existe; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick=" (<?php echo $tabls->id; ?>)" 
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
                <!-- tableau annexe 1_1 -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>   الجدول 1 مكرر</h5>
                        <?php if ($exercice_actif):?>
                        <?php if (!$existe_tab_1_1): ?>
                        <a href="?action=add_tab1_1" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> إضافة جدول رقم 1 مكرر
                        </a>
                        <?php else: 
                            if ($tabl_1_1->statut != 'validé'):?>
                        <a href="?action=edit_tab1_1&id=<?php echo $existe_tab_1_1; ?>" class="btn btn-warning">
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
                                        <th>ID</th>
                                        <th>السنة</th>
                                        <th>الحالة</th>
                                        <th>تاريخ التقديم</th>
                                        <th width="10%" class="text-center">الملاحظات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    
                                    if (!empty($tabl_1_1)): 
                                         
                                            $statut_badge = $tabl_1_1->statut == 'validé' ? 'success' : 
                                                          ($tabl_1_1->statut == 'en_attente' ? 'warning' : 'secondary');
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            
                                            <a href="print_tab1_1.php?id=<?php echo $tabl_1_1->id; ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fa fa-print "></i> <?php echo $tabl_1_1->id; ?>
                                            </a>
                                        </td>
                                        <td class="text-center"><?php echo $tabl_1_1->annee; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $statut_badge; ?>">
                                                <?php echo $tabl_1_1->statut; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $tabl_1_1->date_valide ? date('d/m/Y', strtotime($tabl_1_1->date_valide)) : '---'; ?>
                                        </td>
                                        <td class="text-center"><?php echo $tabl_1_1->commentaire_admin; ?></td>
                                        <td class="text-center">
                                        <?php if ($exercice_actif && $tabl_1_1->statut != 'validé'):?>
                                            <a href="?action=edit_tab1_1&id=<?php echo $existe_tab_1_1; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick=" (<?php echo $tabl_1_1->id; ?>)" 
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
                
                <?php elseif ($action == "add_tab1" || $action == "edit_tab1"): ?>
<div class="row">
    <div class="col-12">
        <!-- Overlay de chargement -->
        <div id="loadingOverlay" class="loading-overlay" style="display: none;">
            <div class="spinner"></div>
            <div class="mt-3 text-primary fw-bold">جاري معالجة البيانات...</div>
        </div>

        <!-- Messages d'alerte -->
        <div id="alertContainer"></div>

        <!-- Section الوظائف العليا -->
        <?php
        // Récupérer les données existantes si en mode édition
        $tableau = null;
        $details = array();
        //$sup_details = array();
        $annee = $exercice_actif ? $exercice_actif->annee : date('Y');
        
        if ($action == "edit_tab1" && $id > 0) {
            $tableau = Tableau1::trouve_par_id($id);
            if ($tableau) {
                $annee = $tableau->annee;
                $details = DetailTab1::trouve_par_tableau($id);
                $details_hp = DetailTab1_hp::trouve_par_tableau($id);
                //$sup_details = DetailTab1_sup::trouve_par_tableau($id);
            }
        } else {
            // En mode ajout, vérifier s'il y a un brouillon
            $details = DetailTab1::trouve_tab_vide_par_admin($current_user->id,$current_user->id_societe);
             $details_hp = DetailTab1_hp::trouve_tab_vide_par_admin($current_user->id,$current_user->id_societe);
           // $sup_details = DetailTab1_sup::trouve_tab_vide_par_admin($current_user->id,$current_user->id_societe);
        }
        
        // Récupérer tous les grades
        $grades = Grade::trouve_tous();
        
        // Calculer les totaux
        $total_hf = array_sum(array_column($details, 'postes_total'));
        $total_hf_reel = array_sum(array_column($details, 'postes_reel'));
        $total_hf_intirim = array_sum(array_column($details, 'poste_intirim'));
        $total_hf_femme = array_sum(array_column($details, 'poste_femme'));
        ?>
        
        <div class="portlet-body table-responsive hauts_fonctionnaires">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    
                    <tr>
                        <th colspan="3"></th>
                        <th colspan="3">تعداد المناصب الحقيقية</th>
                        <th colspan="3"></th>
                    </tr>
                    <tr class="table-light">
                        <th width="5%" class="text-center">الدليل</th>
                        <th width="25%" class="text-center">الوظائف السامية و المناصب العليا</th>
                        <th width="10%" class="text-center">تعداد المناصب المالية</th>
                        
                        <th width="10%" class="text-center">المناصب الحقيقية</th>
                        <th width="10%" class="text-center">بالنيابة</th>
                        <th width="10%" class="text-center">النساء</th>
                        <th width="10%" class="text-center">الفارق</th>
                        <th width="20%" class="text-center">الملاحظات</th>
                        <th width="10%" class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tr>
                        <th colspan="9" class="fw-bold text-center bg-primary text-white">الوظائف العليا</th>
                    </tr>
                <tbody id="existing_hauts_fonctionnaires">
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $detail): 
                            $grade = Grade::trouve_par_id($detail->id_grade);
                        ?>
                        <tr data-id="<?php echo $detail->id; ?>">
                            <td>
                                <input type="hidden" class="form-control text-center" 
                                       value="<?php echo $grade ? $grade->id : ''; ?>" readonly>
                                    <?php echo $grade ? $grade->classe : ''; ?>
                            </td>
                            <td>
                                <?php echo $grade ? $grade->grade : ''; ?>
                            </td>
                            <td>
                                <?php echo $detail->postes_total; ?>
                            </td>
                            <td>
                                <?php echo $detail->postes_reel; ?>
                            </td>
                            <td>
                                <?php echo $detail->poste_intirim; ?>
                            </td>
                            <td>
                                <?php echo $detail->poste_femme; ?>
                            </td>
                            <td>
                                <?php echo $detail->difference; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($detail->observations); ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteDetail(<?php echo $detail->id; ?>, 'hf')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noDataHf">
                            <td colspan="9" class="text-center text-muted">
                                لا توجد بيانات مسجلة
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tbody id="tbody_hauts_fonctionnaires">
                    <tr class="item-row">
                        <td>
                            <input type="text" class="form-control text-center code-input" readonly>
                        </td>
                        <td>
                            <select name="id_grade" class="form-control select2 grade-select" required>
                                <option value="">اختر الوظيفة</option>
                                <?php foreach ($grades as $g): ?>
                                    <option value="<?php echo $g->id; ?>" data-code="<?php echo $g->id; ?>">
                                        <?php echo $g->grade; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="postes_total" class="form-control text-center postes-total" value="0" min="0" required>
                        </td>
                        <td>
                            <input type="number" name="postes_reel" class="form-control text-center postes-reel" value="0" min="0" required>
                        </td>
                        <td>
                            <input type="number" name="poste_intirim" class="form-control text-center poste-intirim" value="0" min="0">
                        </td>
                        <td>
                            <input type="number" name="poste_femme" class="form-control text-center poste-femme" value="0" min="0">
                        </td>
                        <td>
                            <input type="number" name="difference" class="form-control text-center difference" value="0" readonly>
                        </td>
                        <td>
                            <textarea class="form-control observations" rows="1" placeholder="ملاحظات..."></textarea>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-success btn-sm add-hf-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="2" class="text-end fw-bold">المجموع الفرعي:</td>
                        <td class="text-center fw-bold total-hf-postes-total"><?php echo $total_hf; ?></td>
                        <td class="text-center fw-bold total-hf-postes-reel"><?php echo $total_hf_reel; ?></td>
                        <td class="text-center fw-bold total-hf-poste-intirim"><?php echo $total_hf_intirim; ?></td>
                        <td class="text-center fw-bold total-hf-poste-femme"><?php echo $total_hf_femme; ?></td>
                        <td class="text-center fw-bold total-hf-difference"><?php echo $total_hf_reel - $total_hf; ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<br>

<!-- Section المناصب العليا -->
<div class="row">
    <div class="col-12">
        <?php       
        
        // Calculer les totaux HP
        $total_hp = array_sum(array_column($details_hp, 'postes_total_hp'));
        $total_hp_reel = array_sum(array_column($details_hp, 'postes_reel_hp'));
        $total_hp_intirim = array_sum(array_column($details_hp, 'poste_intirim_hp'));
        $total_hp_femme = array_sum(array_column($details_hp, 'poste_femme_hp'));
        ?>
        
        <div class="portlet-body table-responsive hauts_postes">
            <table class="table table-bordered table-striped">
                <thead class="table-info">
                    <tr>
                        <th colspan="9" class="fw-bold text-center bg-info text-white">المناصب العليا</th>
                    </tr>
                    <tr class="table-light">
                        <th width="5%" class="text-center">الدليل</th>
                        <th width="25%" class="text-center">الوظائف السامية و المناصب العليا</th>
                        <th width="10%" class="text-center"> تعداد المناصب المالية</th>
                        <th width="10%" class="text-center"> المناصب الحقيقية</th>
                        <th width="10%" class="text-center">بالنيابة</th>
                        <th width="10%" class="text-center">النساء</th>
                        <th width="10%" class="text-center">الفارق</th>
                        <th width="20%" class="text-center">الملاحظات</th>
                        <th width="10%" class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="existing_hauts_postes">
                    <?php if (!empty($details_hp)): ?>
                        <?php foreach ($details_hp as $detail): 
                            $grade = Grade::trouve_par_id($detail->id_grade_hp);
                        ?>
                        <tr data-id="<?php echo $detail->id; ?>">
                            <td>
                                <input type="text" class="form-control text-center" 
                                       value="<?php echo $grade ? $grade->id : ''; ?>" readonly>
                            </td>
                            <td>
                                <?php echo $grade ? $grade->grade : ''; ?>
                            </td>
                            <td>
                                <?php echo $detail->postes_total_hp; ?>
                            </td>
                            <td>
                                <?php echo $detail->postes_reel_hp; ?>
                            </td>
                            <td>
                                <?php echo $detail->poste_intirim_hp; ?>
                            </td>
                            <td>
                                <?php echo $detail->poste_femme_hp; ?>
                            </td>
                            <td>
                                <?php echo $detail->difference_hp; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($detail->observations_hp); ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteDetail(<?php echo $detail->id; ?>, 'hp')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noDataHp">
                            <td colspan="9" class="text-center text-muted">
                                لا توجد بيانات مسجلة
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tbody id="tbody_hauts_postes">
                    <tr class="item-row">
                        <td>
                            <input type="text" class="form-control text-center code-input-hp" readonly>
                        </td>
                        <td>
                            <select name="id_grade_hp" class="form-control select2 grade-select-hp" required>
                                <option value="">اختر المنصب</option>
                                <?php foreach ($grades as $g): ?>
                                    <option value="<?php echo $g->id; ?>" data-code="<?php echo $g->id; ?>">
                                        <?php echo $g->grade; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="postes_total_hp" class="form-control text-center postes-total-hp" value="0" min="0" required>
                        </td>
                        <td>
                            <input type="number" name="postes_reel_hp" class="form-control text-center postes-reel-hp" value="0" min="0" required>
                        </td>
                        <td>
                            <input type="number" name="poste_intirim_hp" class="form-control text-center poste-intirim-hp" value="0" min="0">
                        </td>
                        <td>
                            <input type="number" name="poste_femme_hp" class="form-control text-center poste-femme-hp" value="0" min="0">
                        </td>
                        <td>
                            <input type="number" name="difference_hp" class="form-control text-center difference-hp" value="0" readonly>
                        </td>
                        <td>
                            <textarea class="form-control observations-hp" rows="1" placeholder="ملاحظات..."></textarea>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-success btn-sm add-hp-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <td colspan="2" class="text-end fw-bold">المجموع الفرعي:</td>
                        <td class="text-center fw-bold total-hp-postes-total"><?php echo $total_hp; ?></td>
                        <td class="text-center fw-bold total-hp-postes-reel"><?php echo $total_hp_reel; ?></td>
                        <td class="text-center fw-bold total-hp-poste-intirim"><?php echo $total_hp_intirim; ?></td>
                        <td class="text-center fw-bold total-hp-poste-femme"><?php echo $total_hp_femme; ?></td>
                        <td class="text-center fw-bold total-hp-difference"><?php echo $total_hp_reel - $total_hp; ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<br>

<!-- Total Général -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center">المجموع العام</th>
                                <th class="text-center">تعداد المناصب </th>
                                <th class="text-center">المناصب الحقيقية</th>
                                <th class="text-center">بالنيابة</th>
                                <th class="text-center">النساء</th>
                                <th class="text-center">الفارق</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center fw-bold">المجموع العام</td>
                                <td class="text-center fw-bold total-general-postes-total">
                                    <?php echo ($total_hf + $total_hp); ?>
                                </td>
                                <td class="text-center fw-bold total-general-postes-reel">
                                    <?php echo ($total_hf_reel + $total_hp_reel); ?>
                                </td>
                                <td class="text-center fw-bold total-general-poste-intirim">
                                    <?php echo ($total_hf_intirim + $total_hp_intirim); ?>
                                </td>
                                <td class="text-center fw-bold total-general-poste-femme">
                                    <?php echo ($total_hf_femme + $total_hp_femme); ?>
                                </td>
                                <td class="text-center fw-bold total-general-difference">
                                    <?php echo ($total_hf_reel + $total_hp_reel) - ($total_hf + $total_hp); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Boutons d'action -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="?action=list_tab1" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success" onclick="saveTableau()">
                            <i class="fas fa-paper-plane me-1"></i> حفظ وتقديم
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 <?php elseif ($action == "add_tab1_1" || $action == "edit_tab1_1"): ?>
<div class="row">
    <div class="col-12">
        <!-- Overlay de chargement -->
        <div id="loadingOverlay" class="loading-overlay" style="display: none;">
            <div class="spinner"></div>
            <div class="mt-3 text-primary fw-bold">جاري معالجة البيانات...</div>
        </div>

        <!-- Messages d'alerte -->
        <div id="alertContainer"></div>

        <!-- Section  الرتب العادية -->
        <?php
        // Récupérer les données existantes si en mode édition
        $tableau = null;
        $details = array();
        $annee = $exercice_actif ? $exercice_actif->annee : date('Y');
        
        if ($action == "edit_tab1_1" && $id > 0) {
            $tableau = Tableau1_1::trouve_par_id($id);
            if ($tableau) {
                $annee = $tableau->annee;
                $details = DetailTab1_1::trouve_par_tableau($id);
            }
        } else {
            // En mode ajout, vérifier s'il y a un brouillon
            $details = DetailTab1_1::trouve_tab_vide_par_admin($current_user->id,$current_user->id_societe);
           
        }
        
        // Récupérer tous les grades
        $grades = Grade::trouve_tous();
        
        // Calculer les totaux
        $tol_titulaires = array_sum(array_column($details, 'tol_titulaires'));
        $tol_stagaires = array_sum(array_column($details, 'tol_stagaires'));
        $total_hf_intirim = array_sum(array_column($details, 'poste_intirim'));
        $total_hf_femme = array_sum(array_column($details, 'poste_femme'));
        ?>
        <?php echo $id; ?>
        <div class="portlet-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    
                    <tr>
                        <th colspan="4"></th>
                        <th colspan="5">  التعداد الحقيقي إلى غاية 31-12 <?php echo $annee;?></th>
                        <th colspan="4">عقد غير محدد المدة</th>
                        <th colspan="6">عقد محدد المدة</th>
                    </tr>
                    <tr class="table-light">
                        <th  class="text-center">القانون الأساسي</th>
                        <th width="15%" class="text-center">السلك و الرتبة </th>
                        <th class="text-center">  التعداد الحقيقي </th>                        
                        <th  class="text-center"> التعداد المالي لسنة <?php echo $annee;?></th>
                        <th  class="text-center">المرسمون</th>
                        <th  class="text-center">المتربصون</th>
                        <th  class="text-center">المجموع </th>
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
                        <th>الملاحظة</th>
                        <th># </th>
                    </tr>
                </thead>
                <tbody id="existing_hauts_fonctionnaires">
                    <?php if (!empty($details)): ?>
                        <?php foreach ($details as $detail): 
                          $grade = Grade::trouve_par_id($detail->id_grade);
                            ?>
                                                    
                       <tr data-id="<?php echo $detail->id; ?>">
                            <td>
                                <?php echo $grade->loi; ?>
                            </td>
                            <td>
                                 <?php echo $grade ? $grade->grade : ''; ?>
                            </td>
                            <td>
                                <?php echo $detail->effectif_reel_31_dec; ?>
                            </td>
                            <td>
                                <?php echo $detail->effectif_reel_annee_1 ?>
                            </td>
                            <td>
                                <?php echo $detail->titulaires; ?>
                            </td>
                            <td>
                                <?php echo $detail->stagaires; ?>
                            </td>
                            <td>
                                <?php echo $detail->tol_titu_stag; ?>
                            </td>
                            <td>
                                <?php echo $detail->femmes; ?>
                            </td>
                            <td>
                                <?php echo $detail->difrence; ?>
                            </td>
                            <td>
                                <?php echo $detail->titulaie_temps_complet; ?>
                            </td>
                            <td>
                                <?php echo $detail->titulaie_femmes_complet; ?>
                            </td>
                            <td>
                                <?php echo $detail->titulaie_temps_partiel; ?>
                            </td>
                            <td>
                                <?php echo $detail->titulaie_femmes_partiel; ?>
                            </td>
                            <td>
                                <?php echo $detail->contrat_temps_complet; ?>
                            </td>
                            <td>
                                <?php echo $detail->contrat_femme_complet; ?>
                            </td>
                            <td>
                                <?php echo $detail->contrat_temps_pratiel; ?>
                            </td>
                            <td>
                                <?php echo $detail->contrat_femmes_pratiel; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($detail->observations); ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm delete-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="noData">
                            <td colspan="19" class="text-center text-muted">
                                لا توجد بيانات مسجلة
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tbody id="body_tab_1_1">
                    <tr class="item-row">
                        <td class="loi"></td>
                        <td width="15%">
                            <select name="id_grade" class="form-control select2 grade-select" required>
                                <option value="">اختر المنصب</option>
                                <?php foreach ($grades as $g): ?>
                                    <option value="<?php echo $g->id; ?>" data-code="<?php echo $g->id; ?>">
                                        <?php echo $g->grade; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="effectif_reel_31_dec" id="effectif_reel_31_dec" class="form-control"></td>
                        <td><input type="number" name="effectif_reel_annee_1" id="effectif_reel_annee_1" class="form-control"></td>
                        <td><input type="number" name="titulaires" id="titulaires" class="form-control"></td>
                        <td><input type="number" name="stagaires" id="stagaires" class="form-control"></td>
                        <td><input type="number" name="tol_titu_stag" id="tol_titu_stag" class="form-control"></td>
                        <td><input type="number" name="femmes" id="femmes" class="form-control"></td>
                       <td><input type="number" name="difrence" id="difrence" class="form-control"></td>
                       <td><input type="number" name="titulaie_temps_complet" id="titulaie_temps_complet" class="form-control"></td>
                       <td><input type="number" name="titulaie_femmes_complet" id="titulaie_femmes_complet" class="form-control"></td>
                       <td><input type="number" name="titulaie_temps_partiel" id="titulaie_temps_partiel" class="form-control"></td>
                       <td><input type="number" name="titulaie_femmes_partiel" id="titulaie_femmes_partiel" class="form-control"></td>
                       <td><input type="number" name="contrat_temps_complet" id="contrat_temps_complet" class="form-control"></td>
                       <td><input type="number" name="contrat_femme_complet" id="contrat_femme_complet" class="form-control"></td>
                       <td><input type="number" name="contrat_temps_pratiel" id="contrat_temps_pratiel" class="form-control"></td>
                       <td><input type="number" name="contrat_femmes_pratiel" id="contrat_femmes_pratiel" class="form-control"></td>
                       <td><input type="text" name="observations" id="observations" class="form-control"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-success btn-sm add-tab1_1-btn">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>

                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-secondary fw-bold" id="totalRow">
                        <td colspan="2" class="text-center">المجموع العام</td>
                        <td class="total_effectif_reel_31_dec">0</td>
                        <td class="total_effectif_reel_annee_1">0</td>
                        <td class="total_titulaires">0</td>
                        <td class="total_stagaires">0</td>
                        <td class="total_tol_titu_stag">0</td>
                        <td class="total_femmes">0</td>
                        <td class="total_difrence">0</td>
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
        <br>
                         <!-- Boutons d'action -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="?action=list_tab1" class="btn btn-secondary">
                            <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success" onclick="saveTableau_1_1()">
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
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
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
    border-bottom: 2px solid rgba(0,0,0,.125);
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