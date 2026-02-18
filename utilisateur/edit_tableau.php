<?php
require_once('../includes/initialiser.php');

// Vérifie si l'utilisateur est connecté
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    $session->logout();
    redirect_to('../login.php');
}

// Récupère l'ID du tableau à modifier
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    redirect_to('tab1.php?action=list_tab1&error=معرف غير صالح');
}

$tableau = Tableau1::trouve_par_id($id);
if (!$tableau) {
    redirect_to('tab1.php?action=list_tab1&error=الجدول غير موجود');
}

// Vérifier que l'utilisateur a le droit de modifier ce tableau
// (Soit il est l'auteur du tableau, soit il est administrateur/super admin)
$autorise = false;
if ($current_user->type == 'super_administrateur' || $current_user->type == 'administrateur') {
    $autorise = true;
} elseif ($current_user->type == 'utilisateur' && $current_user->id_societe == $tableau->id_societe && $current_user->id == $tableau->id_user) {
    $autorise = true;
}

if (!$autorise) {
    redirect_to('tab1.php?action=list_tab1&error=غير مصرح بتعديل هذا الجدول');
}

// Récupérer la société
$societe = Societe::trouve_par_id($tableau->id_societe);
$annee = $tableau->annee;

// Récupérer les détails
$details_hf = DetailTab1::trouve_par_tableau($id);
$details_hp = DetailTab1_hp::trouve_par_tableau($id);

// Récupérer tous les grades
$grades = Grade::trouve_tous();

// Préparer les données pour JavaScript
$grades_js = array();
foreach ($grades as $grade) {
    $grades_js[] = array(
        'id' => $grade->id,
        'code' => $grade->id,
        'designation' => $grade->grade,
        'loi' => $grade->lois
    );
}

// Titre de la page
$titre = "تعديل الجدول رقم " . $id;
$active_menu = "tableaux";
$active_submenu = "tab1";
$header = array('select2', 'sweetalert2');

require_once("composit/header.php");
?>

<!-- Overlay de chargement -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="spinner"></div>
    <div class="mt-3 text-primary fw-bold">جاري معالجة البيانات...</div>
</div>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-edit me-2"></i> تعديل الجدول رقم <?php echo $id; ?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="tab1.php?action=list_tab1">الجداول</a></li>
                        <li class="breadcrumb-item active">تعديل الجدول</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i> تعديل الجدول رقم 1
                    </h5>
                    <div>
                        <span class="badge bg-warning me-2">السنة المالية: <?php echo $annee; ?></span>
                        <span class="badge bg-info"><?php echo $societe->raison_ar; ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <form id="formulaireTableau1" method="POST" action="ajax/traitement_tab1.php">
                        <input type="hidden" name="action" value="update_tab1">
                        <input type="hidden" name="id_tableau" value="<?php echo $tableau->id; ?>">
                        <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                        <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                        <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                        <!-- Section الوظائف العليا -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i> الوظائف العليا
                                </h5>
                                <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne('hauts_fonctionnaires')">
                                    <i class="fas fa-plus me-1"></i> إضافة سطر
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%" class="text-center">الرمز</th>
                                                <th width="25%" class="text-center">الوظيفة</th>
                                                <th width="10%" class="text-center">عدد المناصب المالية إلى غاية <?php echo ($annee - 1); ?>/12/31</th>
                                                <th width="10%" class="text-center">عدد المناصب الحقيقية في السنة <?php echo $annee; ?></th>
                                                <th width="10%" class="text-center">بالنيابة</th>
                                                <th width="10%" class="text-center">النساء</th>
                                                <th width="10%" class="text-center">الفارق</th>
                                                <th width="20%" class="text-center">الملاحظات</th>
                                                <th width="10%" class="text-center">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_hauts_fonctionnaires">
                                            <?php 
                                            $hf_index = 0;
                                            if (!empty($details_hf)):
                                                foreach ($details_hf as $detail):
                                                    $grade = Grade::trouve_par_id($detail->id_grade);
                                            ?>
                                            <tr data-id-detail="<?php echo $detail->id; ?>">
                                                <td>
                                                    <input type="hidden" name="details[<?php echo $hf_index; ?>][id]" value="<?php echo $detail->id; ?>">
                                                    <input type="hidden" name="details[<?php echo $hf_index; ?>][section]" value="hauts_fonctionnaires">
                                                    <input type="hidden" name="details[<?php echo $hf_index; ?>][id_grade]" value="<?php echo $grade->id; ?>">
                                                    <input type="text" class="form-control text-center code-grade" value="<?php echo $grade->id; ?>" readonly>
                                                </td>
                                                <td>
                                                    <select name="details[<?php echo $hf_index; ?>][id_grade_select]" 
                                                            class="form-control select-grade" data-section="hauts_fonctionnaires" required>
                                                        <option value="">اختر الوظيفة</option>
                                                        <?php foreach ($grades as $g): ?>
                                                            <option value="<?php echo $g->id; ?>" 
                                                                    data-code="<?php echo $g->id; ?>"
                                                                    <?php echo $g->id == $grade->id ? 'selected' : ''; ?>>
                                                                <?php echo $g->grade; ?>
                                                                <?php if (!empty($g->loi)): ?>
                                                                    <small class="text-muted">(<?php echo $g->loi; ?>)</small>
                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hf_index; ?>][postes_total]" 
                                                           class="form-control text-center postes-total" 
                                                           value="<?php echo $detail->postes_total; ?>" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hf_index; ?>][postes_reel]" 
                                                           class="form-control text-center postes-reel" 
                                                           value="<?php echo $detail->postes_reel; ?>" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hf_index; ?>][poste_intirim]" 
                                                           class="form-control text-center poste-intirim" 
                                                           value="<?php echo $detail->poste_intirim; ?>" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hf_index; ?>][poste_femme]" 
                                                           class="form-control text-center poste-femme" 
                                                           value="<?php echo $detail->poste_femme; ?>" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hf_index; ?>][difference]" 
                                                           class="form-control text-center difference" 
                                                           value="<?php echo $detail->difference; ?>" readonly>
                                                </td>
                                                <td>
                                                    <textarea name="details[<?php echo $hf_index; ?>][observations]" 
                                                              class="form-control" rows="1"><?php echo htmlspecialchars($detail->observations); ?></textarea>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php 
                                                $hf_index++;
                                                endforeach;
                                            endif;
                                            ?>
                                        </tbody>
                                        <tfoot class="table-secondary">
                                            <tr>
                                                <td colspan="2" class="text-end fw-bold">المجموع الفرعي</td>
                                                <td class="text-center fw-bold" id="total_hf_postes_total">0</td>
                                                <td class="text-center fw-bold" id="total_hf_postes_reel">0</td>
                                                <td class="text-center fw-bold" id="total_hf_poste_intirim">0</td>
                                                <td class="text-center fw-bold" id="total_hf_poste_femme">0</td>
                                                <td class="text-center fw-bold" id="total_hf_difference">0</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Section المناصب العليا -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-briefcase me-2"></i> المناصب العليا
                                </h5>
                                <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne('hauts_postes')">
                                    <i class="fas fa-plus me-1"></i> إضافة سطر
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%" class="text-center">الرمز</th>
                                                <th width="25%" class="text-center">المنصب</th>
                                                <th width="10%" class="text-center">عدد المناصب المالية إلى غاية <?php echo ($annee - 1); ?>/12/31</th>
                                                <th width="10%" class="text-center">عدد المناصب الحقيقية في السنة <?php echo $annee; ?></th>
                                                <th width="10%" class="text-center">بالنيابة</th>
                                                <th width="10%" class="text-center">النساء</th>
                                                <th width="10%" class="text-center">الفارق</th>
                                                <th width="20%" class="text-center">الملاحظات</th>
                                                <th width="10%" class="text-center">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_hauts_postes">
                                            <?php 
                                            $hp_index = $hf_index; // continuer l'indexation
                                            if (!empty($details_hp)):
                                                foreach ($details_hp as $detail):
                                                    $grade = Grade::trouve_par_id($detail->id_grade_hp);
                                            ?>
                                            <tr data-id-detail="<?php echo $detail->id; ?>">
                                                <td>
                                                    <input type="hidden" name="details[<?php echo $hp_index; ?>][id]" value="<?php echo $detail->id; ?>">
                                                    <input type="hidden" name="details[<?php echo $hp_index; ?>][section]" value="hauts_postes">
                                                    <input type="hidden" name="details[<?php echo $hp_index; ?>][id_grade]" value="<?php echo $grade->id; ?>">
                                                    <input type="text" class="form-control text-center code-grade" value="<?php echo $grade->id; ?>" readonly>
                                                </td>
                                                <td>
                                                    <select name="details[<?php echo $hp_index; ?>][id_grade_select]" 
                                                            class="form-control select-grade" data-section="hauts_postes" required>
                                                        <option value="">اختر المنصب</option>
                                                        <?php foreach ($grades as $g): ?>
                                                            <option value="<?php echo $g->id; ?>" 
                                                                    data-code="<?php echo $g->id; ?>"
                                                                    <?php echo $g->id == $grade->id ? 'selected' : ''; ?>>
                                                                <?php echo $g->grade; ?>
                                                                <?php if (!empty($g->loi)): ?>
                                                                    <small class="text-muted">(<?php echo $g->loi; ?>)</small>
                                                                <?php endif; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hp_index; ?>][postes_total]" 
                                                           class="form-control text-center postes-total" 
                                                           value="<?php echo $detail->postes_total_hp; ?>" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hp_index; ?>][postes_reel]" 
                                                           class="form-control text-center postes-reel" 
                                                           value="<?php echo $detail->postes_reel_hp; ?>" min="0" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hp_index; ?>][poste_intirim]" 
                                                           class="form-control text-center poste-intirim" 
                                                           value="<?php echo $detail->poste_intirim_hp; ?>" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hp_index; ?>][poste_femme]" 
                                                           class="form-control text-center poste-femme" 
                                                           value="<?php echo $detail->poste_femme_hp; ?>" min="0">
                                                </td>
                                                <td>
                                                    <input type="number" name="details[<?php echo $hp_index; ?>][difference]" 
                                                           class="form-control text-center difference" 
                                                           value="<?php echo $detail->difference_hp; ?>" readonly>
                                                </td>
                                                <td>
                                                    <textarea name="details[<?php echo $hp_index; ?>][observations]" 
                                                              class="form-control" rows="1"><?php echo htmlspecialchars($detail->observations_hp); ?></textarea>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php 
                                                $hp_index++;
                                                endforeach;
                                            endif;
                                            ?>
                                        </tbody>
                                        <tfoot class="table-secondary">
                                            <tr>
                                                <td colspan="2" class="text-end fw-bold">المجموع الفرعي</td>
                                                <td class="text-center fw-bold" id="total_hp_postes_total">0</td>
                                                <td class="text-center fw-bold" id="total_hp_postes_reel">0</td>
                                                <td class="text-center fw-bold" id="total_hp_poste_intirim">0</td>
                                                <td class="text-center fw-bold" id="total_hp_poste_femme">0</td>
                                                <td class="text-center fw-bold" id="total_hp_difference">0</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Total général -->
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0"><i class="fas fa-calculator me-2"></i> المجموع العام</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead class="table-success">
                                        <tr>
                                            <th class="text-center">البيان</th>
                                            <th class="text-center">عدد المناصب المالية (<?php echo ($annee-1); ?>)</th>
                                            <th class="text-center">عدد المناصب الحقيقية (<?php echo $annee; ?>)</th>
                                            <th class="text-center">بالنيابة</th>
                                            <th class="text-center">النساء</th>
                                            <th class="text-center">الفارق</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">الوظائف العليا</td>
                                            <td class="text-center" id="general_hf_postes_total">0</td>
                                            <td class="text-center" id="general_hf_postes_reel">0</td>
                                            <td class="text-center" id="general_hf_intirim">0</td>
                                            <td class="text-center" id="general_hf_femme">0</td>
                                            <td class="text-center" id="general_hf_diff">0</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">المناصب العليا</td>
                                            <td class="text-center" id="general_hp_postes_total">0</td>
                                            <td class="text-center" id="general_hp_postes_reel">0</td>
                                            <td class="text-center" id="general_hp_intirim">0</td>
                                            <td class="text-center" id="general_hp_femme">0</td>
                                            <td class="text-center" id="general_hp_diff">0</td>
                                        </tr>
                                        <tr class="table-active fw-bold">
                                            <td>المجموع العام</td>
                                            <td class="text-center" id="general_total_postes">0</td>
                                            <td class="text-center" id="general_total_reel">0</td>
                                            <td class="text-center" id="general_total_intirim">0</td>
                                            <td class="text-center" id="general_total_femme">0</td>
                                            <td class="text-center" id="general_total_diff">0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <a href="tab1.php?action=list_tab1" class="btn btn-secondary">
                                        <i class="fas fa-arrow-right me-1"></i> رجوع للقائمة
                                    </a>
                                    <div>
                                        <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon()">
                                            <i class="fas fa-save me-1"></i> حفظ كمسودة
                                        </button>
                                        <button type="button" class="btn btn-success" id="btnSubmit">
                                            <i class="fas fa-paper-plane me-1"></i> تحديث الجدول
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>



<style>
.loading-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(255,255,255,0.9);
    display: flex; flex-direction: column; justify-content: center; align-items: center;
    z-index: 99999; backdrop-filter: blur(2px);
}
.spinner {
    width: 60px; height: 60px;
    border: 5px solid #f3f3f3; border-top: 5px solid #3498db;
    border-radius: 50%; animation: spin 1s linear infinite;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<?php require_once("composit/footer.php"); ?>