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
$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab3';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 3 -  ";
$active_menu = "tab_3";
$active_submenu = "tab_3";
$header = array('select2');

require_once("composit/header.php");
?>
<?php
$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe = Tableau3::existe_pour_societe_annee($current_user->id_societe, $annee);
$tabls = Tableau3::trouve_tableau_1_par_id($societe->id_societe);


if ($action == "add_tab3") {

$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe = Tableau3::existe_pour_societe_annee(
    $current_user->id_societe,
    $annee
);

if ($existe) {
    redirect_to("?action=list_tab3");
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
                    <h3 class="mb-0">الجدول 3 -  قرارات الاطار المتعلقة بالامتحانات و المسابقات</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الجدول 3</li>
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

            <?php if ($action == "list_tab3"): ?>
                <!-- Liste des tableaux existants -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>قائمة الجداول المسجلة
                        </h5>
                        <?php if ($exercice_actif):?>
                        <?php if (!$existe): ?>
                        <a href="?action=add_tab3" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> إضافة جدول رقم 3
                        </a>
                        <?php else: 
                            if ($tabls->statut != 'validé'):?>

                        <a href="?action=edit_tab3&id=<?php echo $existe; ?>" class="btn btn-warning">
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
                                        <th width="15%" class="text-center">المؤسسة</th>
                                        <th width="10%" class="text-center">السنة</th>
                                        <th width="10%" class="text-center">الحالة</th>
                                        <th width="15%" class="text-center">تاريخ التقديم</th>
                                        <th width="15%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                   
                                    if (!empty($tabls)): 
                                       
                                            $statut_badge = $tabls->statut == 'validé' ? 'success' : 
                                                          ($tabls->statut == 'brouillon' ? 'warning' : 'secondary');
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <a href="print_tab3.php?id=<?php echo $tabls->id; ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fa fa-print"></i> <?php echo $tabls->id; ?>
                                            </a>
                                        </td>
                                        <td class="text-center"><?php echo $societe->raison_ar; ?></td>
                                        <td class="text-center"><?php echo $tabls->annee; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $statut_badge; ?>">
                                                <?php echo $tabls->statut; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $tabls->date_creation ? date('d/m/Y', strtotime($tabls->date_creation)) : '---'; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="?action=edit_tab3&id=<?php echo $tabls->id; ?>" 
                                               class="btn btn-sm btn-warning me-1" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="supprimerTableau(<?php echo $tabls->id; ?>)" 
                                                    class="btn btn-sm btn-danger" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
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

            <?php elseif ($action == "add_tab3" || $action == "edit_tab3"): ?>
                <!-- Formulaire d'ajout ou modification -->
                <?php
                // Récupérer les données existantes si en mode édition
                $tableau = null;
                $details = array();
                $annee = $exercice_actif ? $exercice_actif->annee : date('Y');

                if ($action == "edit_tab3" && $id > 0) {
                    $tableau = Tableau3::trouve_par_id($id);
                    if ($tableau) {
                        $annee = $tableau->annee;
                        $details = DetailTab3::trouve_par_tableau($id);
                    }
                } else {
                    // En mode ajout, vérifier s'il y a un brouillon
                    $tableau_brouillon = Tableau3::trouve_tab_vide_par_admin($current_user->id, $societe->id_societe);
                    if ($tableau_brouillon) {
                        $tableau = $tableau_brouillon;
                        $details = DetailTab3::trouve_par_tableau($tableau->id);
                    }
                }

                // Récupérer tous les grades
                $grades = Grade::trouve_tous();

                // Créer un tableau JavaScript avec tous les grades
                $grades_js = array();
                foreach ($grades as $grade) {
                    $grades_js[] = array(
                        'id' => $grade->id,
                        'code' => $grade->id,
                        'designation' => $grade->grade,
                        'loi' => $grade->lois
                    );
                }
                ?>

                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit me-2"></i>
                            <?php echo $action == "edit_tab3" ? 'تعديل الجدول رقم 3' : 'إضافة جدول رقم 3'; ?>
                        </h5>
                        <div>
                            <span class="badge bg-warning me-2">السنة المالية: <?php echo $annee; ?></span>
                            <?php if ($tableau && $tableau->statut == 'brouillon'): ?>
                                <span class="badge bg-info">مسودة</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formulaireTableau3" method="POST" action="ajax/traitement_tab3.php">
                            <input type="hidden" name="action" value="<?php echo $action == "edit_tab3" ? 'update_tab3' : 'add_tab3'; ?>">
                            <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                            <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                            <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                            <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-table me-2"></i>بيانات الحركة
                                    </h5>
                                    <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne()">
                                        <i class="fas fa-plus me-1"></i> إضافة سطر
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th rowspan="2" class="text-center align-middle">الرمز</th>
                                                    <th rowspan="2" class="text-center align-middle">السلك</th>
                                                    <th colspan="2" class="text-center">الإلتحاق بالتكوين</th>
                                                    <th colspan="2" class="text-center">التوظيف الخارجي</th>
                                                    <th colspan="2" class="text-center">الترقيبية</th>
                                                    <th rowspan="2" class="text-center align-middle"> التثبيت</th>
                                                    <th rowspan="2" class="text-center align-middle">إدماج</th>
                                                    <th rowspan="2" class="text-center align-middle">الملاحظات</th>
                                                    <th rowspan="2" class="text-center align-middle">الإجراءات</th>
                                                </tr>
                                                <tr>
                                                    <th class="text-center">داخلي</th>
                                                    <th class="text-center">خارجي</th>
                                                    <th class="text-center">مسابقة على أساس الإختبارات والفحوص المبنية على الشهادة</th>
                                                    <th class="text-center">مسابقة على أساس الإختبارات والفحوص</th>
                                                    <th class="text-center">إمتحان</th>
                                                    <th class="text-center">فحص</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_details">
                                                <?php
                                                $index = 0;
                                                if (!empty($details)):
                                                    foreach ($details as $detail):
                                                        $grade = Grade::trouve_par_id($detail->id_grade);
                                                ?>
                                                <tr data-id-detail="<?php echo $detail->id; ?>">
                                                    <td>
                                                        <input type="hidden" name="details[<?php echo $index; ?>][id]" value="<?php echo $detail->id; ?>">
                                                        <input type="hidden" name="details[<?php echo $index; ?>][id_grade]" value="<?php echo $grade->id; ?>">
                                                        <input type="text" class="form-control text-center code-grade" value="<?php echo $grade->id; ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <select name="details[<?php echo $index; ?>][id_grade_select]" class="form-control select-grade" required>
                                                            <option value="">اختر السلك</option>
                                                            <?php foreach ($grades as $g): ?>
                                                                <option value="<?php echo $g->id; ?>" data-code="<?php echo $g->id; ?>" <?php echo $g->id == $grade->id ? 'selected' : ''; ?>>
                                                                    <?php echo $g->grade; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="details[<?php echo $index; ?>][interne]" value="1" <?php echo $detail->interne ? 'checked' : ''; ?>>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="details[<?php echo $index; ?>][externe]" value="1" <?php echo $detail->externe ? 'checked' : ''; ?>>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="details[<?php echo $index; ?>][diplome]" value="1" <?php echo $detail->diplome ? 'checked' : ''; ?>>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="details[<?php echo $index; ?>][concour]" value="1" <?php echo $detail->concour ? 'checked' : ''; ?>>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="details[<?php echo $index; ?>][examen_pro]" value="1" <?php echo $detail->examen_pro ? 'checked' : ''; ?>>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" name="details[<?php echo $index; ?>][test_pro]" value="1" <?php echo $detail->test_pro ? 'checked' : ''; ?>>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="details[<?php echo $index; ?>][loi]" class="form-control" value="<?php echo htmlspecialchars($detail->loi); ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="details[<?php echo $index; ?>][nomination]" class="form-control" value="<?php echo $detail->nomination; ?>">
                                                    </td>
                                                    <td>
                                                        <textarea name="details[<?php echo $index; ?>][observation]" class="form-control" rows="1"><?php echo htmlspecialchars($detail->observation); ?></textarea>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php
                                                        $index++;
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=list_tab3" class="btn btn-secondary">
                                            <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon()">
                                                <i class="fas fa-save me-1"></i> حفظ كمسودة
                                            </button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-paper-plane me-1"></i>
                                                <?php echo $action == "edit_tab3" ? 'تحديث الجدول' : 'تقديم الجدول'; ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->


<style>
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
</style>

<?php require_once("composit/footer.php"); ?>