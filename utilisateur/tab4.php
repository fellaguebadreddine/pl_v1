<?php
// tab4.php - Page principale pour les utilisateurs société
require_once('../includes/initialiser.php');

if (!$session->is_logged_in()) redirect_to('../login.php');
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) { $session->logout(); redirect_to('../login.php'); }
if ($current_user->type !== 'utilisateur') { $session->logout(); redirect_to('../login.php'); }
if (!$current_user->id_societe) redirect_to('../login.php');
$societe = Societe::trouve_par_id($current_user->id_societe);
if (!$societe) redirect_to('../login.php');

$exercice_actif = Exercice::get_exercice_actif();
$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab4';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 4";
$active_menu = "tab_4";
$active_submenu = "tab_4";
$header = array('select2');

require_once("composit/header.php");
?>
<?php
$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe = Tableau4::existe_pour_societe_annee($current_user->id_societe, $annee);
$tabls = Tableau4::trouve_tableau_1_par_id($societe->id_societe);


if ($action == "add_tab4") {

$annee = $exercice_actif ? $exercice_actif->annee : date('Y');

$existe = Tableau4::existe_pour_societe_annee(
    $current_user->id_societe,
    $annee
);

if ($existe) {
    redirect_to("?action=list_tab4");
    exit;
}
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">الجدول 4 - <?php echo $societe->raison_ar; ?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الجدول 4</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> تم حفظ الجدول بنجاح!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action == "list_tab4"): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>قائمة الجداول المسجلة</h5>
                         <?php if ($exercice_actif):?>
                        <?php if (!$existe): ?>
                        <a href="?action=add_tab4" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة جدول رقم 4</a>
                        <?php else: 
                            if ($tabls->statut != 'validé'):?>

                        <a href="?action=edit_tab4&id=<?php echo $existe; ?>" class="btn btn-warning">
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
                                        <th>المؤسسة</th>
                                        <th>السنة</th>
                                        <th>الحالة</th>
                                        <th>تاريخ التقديم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tabls = Tableau4::trouve_par_societe($societe->id_societe);
                                if (!empty($tabls)):
                                    foreach ($tabls as $row):
                                        $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab4.php?id=<?php echo $row->id; ?>"  target="_blank" class="btn btn-sm btn-info"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
                                        <td class="text-center"><?php echo $societe->raison_ar; ?></td>
                                        <td class="text-center"><?php echo $row->annee; ?></td>
                                        <td class="text-center"><span class="badge bg-<?php echo $statut_badge; ?>"><?php echo $row->statut; ?></span></td>
                                        <td class="text-center"><?php echo $row->date_valide ? date('d/m/Y', strtotime($row->date_valide)) : '---'; ?></td>
                                        <td class="text-center">
                                            <a href="?action=edit_tab4&id=<?php echo $row->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل"><i class="fas fa-edit"></i></a>
                                            
                                            <button onclick="supprimerTableau(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger" title="حذف"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <tr><td colspan="6" class="text-center py-4"><i class="fas fa-table fa-2x text-muted mb-3 d-block"></i>لا توجد جداول مسجلة</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action == "add_tab4" || $action == "edit_tab4"): ?>
                <?php
                $tableau = null;
                $details = array();
                $annee = $exercice_actif ? $exercice_actif->annee : date('Y');

                if ($action == "edit_tab4" && $id > 0) {
                    $tableau = Tableau4::trouve_par_id($id);
                    if ($tableau) {
                        $annee = $tableau->annee;
                        $details = DetailTab4::trouve_par_tableau($id);
                    }
                } else {
                    $tableau_brouillon = Tableau4::trouve_tab_vide_par_admin($current_user->id, $societe->id_societe);
                    if ($tableau_brouillon) {
                        $tableau = $tableau_brouillon;
                        $details = DetailTab4::trouve_par_tableau($tableau->id);
                    }
                }

                $grades = Grade::trouve_tous();
                $grades_js = array();
                foreach ($grades as $grade) {
                    $grades_js[] = array('id' => $grade->id, 'code' => $grade->id, 'designation' => $grade->grade);
                }
                ?>

                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == "edit_tab4" ? 'تعديل الجدول رقم 4' : 'إضافة جدول رقم 4'; ?></h5>
                        <div><span class="badge bg-warning me-2">السنة المالية: <?php echo $annee; ?></span><?php if ($tableau && $tableau->statut == 'brouillon'): ?><span class="badge bg-info">مسودة</span><?php endif; ?></div>
                    </div>
                    <div class="card-body">
                        <form id="formulaireTableau4" method="POST" action="ajax/traitement_tab4.php">
                            <input type="hidden" name="action" value="<?php echo $action == "edit_tab4" ? 'update_tab4' : 'add_tab4'; ?>">
                            <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                            <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                            <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                            <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i>بيانات الجدول</h5>
                                    <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne()"><i class="fas fa-plus me-1"></i> إضافة سطر</button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th rowspan="2" class="text-center align-middle">الرمز</th>
                                                    <th rowspan="2" class="text-center align-middle">السلك</th>
                                                    <th class="text-center">عدد المناصب الشاغرة (خارجي)</th>
                                                    <th class="text-center">منتوج التكوين (شبه الطبيبي)</th>
                                                    <th class="text-center">مسابقة على أساس الشهادة</th>
                                                    <th class="text-center">المبتدئين المتعاقدين</th>
                                                    <th class="text-center">العمال المبنى المتعاقدين</th>
                                                    <th class="text-center">طريقة على أساس الشهادة</th>
                                                    <th class="text-center">امتحان ميني</th>
                                                    <th class="text-center">فحص ميني (العمال المبنى صنف)</th>
                                                    <th class="text-center">المناصب المالية التي تم استغلالها</th>
                                                    <th class="text-center">عدد المناصب المالية التي تم استغلالها</th>
                                                    <th rowspan="2" class="text-center align-middle">الملاحظات</th>
                                                    <th rowspan="2" class="text-center align-middle">الإجراءات</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="10" class="text-center">(القيم)</th>
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
                                                                <option value="<?php echo $g->id; ?>" data-code="<?php echo $g->id; ?>" <?php echo $g->id == $grade->id ? 'selected' : ''; ?>><?php echo $g->grade; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][postes_vacants_externe]" class="form-control" value="<?php echo $detail->postes_vacants_externe; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][produit_formation_paramedicale]" class="form-control" value="<?php echo $detail->produit_formation_paramedicale; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][concours_sur_titre]" class="form-control" value="<?php echo $detail->concours_sur_titre; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][debutants_contractuels]" class="form-control" value="<?php echo $detail->debutants_contractuels; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][ouvriers_batiment_contractuels]" class="form-control" value="<?php echo $detail->ouvriers_batiment_contractuels; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][methode_sur_titre]" class="form-control" value="<?php echo $detail->methode_sur_titre; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][examen_mini]" class="form-control" value="<?php echo $detail->examen_mini; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][test_mini_ouvriers]" class="form-control" value="<?php echo $detail->test_mini_ouvriers; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][postes_financiers_exploites]" class="form-control" value="<?php echo $detail->postes_financiers_exploites; ?>" min="0"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][nombre_postes_financiers_exploites]" class="form-control" value="<?php echo $detail->nombre_postes_financiers_exploites; ?>" min="0"></td>
                                                    <td><textarea name="details[<?php echo $index; ?>][observations]" class="form-control" rows="1"><?php echo htmlspecialchars($detail->observations); ?></textarea></td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)"><i class="fas fa-trash"></i></button></td>
                                                </tr>
                                                <?php
                                                        $index++;
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                            <tfoot class="table-secondary">
                                                <tr>
                                                    <td colspan="2" class="fw-bold text-end">المجموع</td>
                                                    <td class="fw-bold" id="total_postes_vacants">0</td>
                                                    <td class="fw-bold" id="total_produit_formation">0</td>
                                                    <td class="fw-bold" id="total_concours">0</td>
                                                    <td class="fw-bold" id="total_debutants">0</td>
                                                    <td class="fw-bold" id="total_ouvriers">0</td>
                                                    <td class="fw-bold" id="total_methode">0</td>
                                                    <td class="fw-bold" id="total_examen_mini">0</td>
                                                    <td class="fw-bold" id="total_test_mini">0</td>
                                                    <td class="fw-bold" id="total_postes_financiers">0</td>
                                                    <td class="fw-bold" id="total_nombre_postes">0</td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=list_tab4" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع للقائمة</a>
                                        <div>
                                            <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon()"><i class="fas fa-save me-1"></i> حفظ كمسودة</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab4" ? 'تحديث الجدول' : 'تقديم الجدول'; ?></button>
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
</main>



<?php require_once("composit/footer.php"); ?>