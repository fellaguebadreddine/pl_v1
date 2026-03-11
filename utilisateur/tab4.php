<?php
// tab4.php
require_once('../includes/initialiser.php');

if (!$session->is_logged_in()) redirect_to('../login.php');
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) { $session->logout(); redirect_to('../login.php'); }
if ($current_user->type !== 'utilisateur') { $session->logout(); redirect_to('../login.php'); }
if (!$current_user->id_societe) redirect_to('../login.php');
$societe = Societe::trouve_par_id($current_user->id_societe);
if (!$societe) redirect_to('../login.php');

$exercice_actif = Exercice::get_exercice_actif();
$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab4';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 4";
$active_menu = "tab_4";
$active_submenu = "tab_4";
$header = array('select2');

require_once("composit/header.php");
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">الجدول  رقم 04: المخطط التوقعي للتوظيف بعنوان سنة <?php echo $annee;?></h3>
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
                <!-- Tableau principal 4 -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i> الجدول 4</h5>
                        <?php 
                        $existe_tab4 = Tableau4::existe_pour_societe_annee($societe->id_societe, $annee);
                        if ($exercice_actif && !$existe_tab4): ?>
                            <a href="?action=add_tab4" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة جدول رقم 4</a>
                        <?php elseif ($existe_tab4): 
                            $tab4_existant = Tableau4::trouve_par_societe_annee($societe->id_societe, $annee);
                            if ($tab4_existant && $tab4_existant->statut != 'validé'): ?>
                            <a href="?action=edit_tab4&id=<?php echo $tab4_existant->id; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> تعديل الجدول الحالي
                            </a>
                        <?php endif; endif; ?>
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
                                        <th width="10%" class="text-center">  الملاحظات</th>
                                        <th width="10%" class="text-center">المرفقات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tabls4 = Tableau4::trouve_par_societe($societe->id_societe);
                                if (!empty($tabls4)):
                                    foreach ($tabls4 as $row):
                                        $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab4.php?id=<?php echo $row->id; ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
                                        <td class="text-center"><?php echo $societe->raison_ar; ?></td>
                                        <td class="text-center"><?php echo $row->annee; ?></td>
                                        <td class="text-center"><span class="badge bg-<?php echo $statut_badge; ?>"><?php echo $row->statut; ?></span></td>
                                        <td class="text-center"><?php echo $row->date_valide ? date('d/m/Y', strtotime($row->date_valide)) : '---'; ?></td>
                                         <td class="text-center"><?php echo $row->commentaire_admin; ?></td>  
                                        <td class="text-center">
    <?php if (!empty($row->attachment)): ?>
        <a href="../<?php echo htmlspecialchars($row->attachment); ?>" target="_blank" class="btn btn-sm btn-info" title="تحميل المرفق">
            <i class="fas fa-file-download"></i>
        </a>
        <button type="button" class="btn btn-sm btn-warning" onclick="uploadAttachment('tab2', <?php echo $row->id; ?>)" title="تغيير المرفق">
            <i class="fas fa-upload"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAttachment('tab2', <?php echo $row->id; ?>)" title="حذف المرفق">
            <i class="fas fa-trash"></i>
        </button>
    <?php else: ?>
        <button type="button" class="btn btn-sm btn-success" onclick="uploadAttachment('tab2', <?php echo $row->id; ?>)" title="إضافة مرفق">
            <i class="fas fa-upload"></i> إضافة
        </button>
    <?php endif; ?>
</td>
                                        <td class="text-center">
                                             <?php if ($exercice_actif && $row->statut != 'validé'): ?>
                                            <a href="?action=edit_tab4&id=<?php echo $row->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل"><i class="fas fa-edit"></i></a>
                                            <button onclick="supprimerTableau4(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger" title="حذف"><i class="fas fa-trash"></i></button>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <tr><td colspan="8" class="text-center py-4"><i class="fas fa-table fa-2x text-muted mb-3 d-block"></i>لا توجد جداول مسجلة</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Annexe 4/1 -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i> مكرر 4/1</h5>
                        
                        <?php 
                        $existe_tab4_1 = Tableau4_1::existe_pour_societe_annee($societe->id_societe, $annee);
                        if ($exercice_actif && !$existe_tab4_1): ?>
                            <a href="?action=add_tab4_1" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة جدول رقم 4/1</a>
                        <?php elseif ($existe_tab4_1): 
                            $tab4_1_existant = Tableau4_1::trouve_par_societe_annee($societe->id_societe, $annee);
                            if ($tab4_1_existant && $tab4_1_existant->statut != 'validé'): ?>
                            <a href="?action=edit_tab4_1&id=<?php echo $tab4_1_existant->id; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> تعديل الجدول الحالي
                            </a>
                        <?php endif; endif; ?>
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
                                         <th width="10%" class="text-center">  الملاحظات</th>
                                        <th width="10%" class="text-center">المرفقات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tabls4_1 = Tableau4_1::trouve_par_societe($societe->id_societe);
                                if (!empty($tabls4_1)):
                                    foreach ($tabls4_1 as $row):
                                        $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab4_1.php?id=<?php echo $row->id; ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
                                        <td class="text-center"><?php echo $societe->raison_ar; ?></td>
                                        <td class="text-center"><?php echo $row->annee; ?></td>
                                        <td class="text-center"><span class="badge bg-<?php echo $statut_badge; ?>"><?php echo $row->statut; ?></span></td>
                                        <td class="text-center"><?php echo $row->date_valide ? date('d/m/Y', strtotime($row->date_valide)) : '---'; ?></td>
                                         <td class="text-center"><?php echo $row->commentaire_admin; ?></td>  
                                        <td class="text-center">
    <?php if (!empty($row->attachment)): ?>
        <a href="../<?php echo htmlspecialchars($row->attachment); ?>" target="_blank" class="btn btn-sm btn-info" title="تحميل المرفق">
            <i class="fas fa-file-download"></i>
        </a>
        <button type="button" class="btn btn-sm btn-warning" onclick="uploadAttachment('tab2', <?php echo $tabls->id; ?>)" title="تغيير المرفق">
            <i class="fas fa-upload"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAttachment('tab2', <?php echo $tabls->id; ?>)" title="حذف المرفق">
            <i class="fas fa-trash"></i>
        </button>
    <?php else: ?>
        <button type="button" class="btn btn-sm btn-success" onclick="uploadAttachment('tab2', <?php echo $tabls->id; ?>)" title="إضافة مرفق">
            <i class="fas fa-upload"></i> إضافة
        </button>
    <?php endif; ?>
</td>
                                        <td class="text-center">
                                             <?php if ($exercice_actif && $row->statut != 'validé'): ?>
                                            <a href="?action=edit_tab4_1&id=<?php echo $tab4_1_existant->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل"><i class="fas fa-edit"></i></a>
                                            <button onclick="supprimerTableau4_1(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger" title="حذف"><i class="fas fa-trash"></i></button>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <tr><td colspan="8" class="text-center py-4"><i class="fas fa-table fa-2x text-muted mb-3 d-block"></i>لا توجد ملحقات مسجلة</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action == "add_tab4" || $action == "edit_tab4"): ?>
                <!-- Formulaire pour le tableau 4 -->
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
                                    <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne4()"><i class="fas fa-plus me-1"></i> إضافة سطر</button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th rowspan="2" class="text-center align-middle">الوظائف السامة المناصب العليا الأسلاك أو الرتب</th>
                                                    <th class="text-center">   المناصب المالية</th>
                                                    <th class="text-center">  المناصب المشغولة</th>
                                                    <th class="text-center">   المناصب الشاغرة</th>
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
                                            <tbody id="tbody_details4">
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
                                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne4(this)"><i class="fas fa-trash"></i></button></td>
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
                                                    <td class="fw-bold" id="total_postes_vacants4">0</td>
                                                    <td class="fw-bold" id="total_produit_formation4">0</td>
                                                    <td class="fw-bold" id="total_concours4">0</td>
                                                    <td class="fw-bold" id="total_debutants4">0</td>
                                                    <td class="fw-bold" id="total_ouvriers4">0</td>
                                                    <td class="fw-bold" id="total_methode4">0</td>
                                                    <td class="fw-bold" id="total_examen_mini4">0</td>
                                                    <td class="fw-bold" id="total_test_mini4">0</td>
                                                    <td class="fw-bold" id="total_postes_financiers4">0</td>
                                                    <td class="fw-bold" id="total_nombre_postes4">0</td>
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
                                            <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon4()"><i class="fas fa-save me-1"></i> حفظ كمسودة</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab4" ? 'تحديث الجدول' : 'تقديم الجدول'; ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                

            <?php elseif ($action == "add_tab4_1" || $action == "edit_tab4_1"): ?>
                <?php
                // Récupérer les grades
                $grades = Grade::trouve_tous();
                $grades_js = array();
                foreach ($grades as $grade) {
                    $grades_js[] = array('id' => $grade->id, 'code' => $grade->id, 'designation' => $grade->grade);
                }

                // Définir la liste des champs numériques pour faciliter l'affichage et les totaux
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

                // Libellés arabes pour les colonnes (à adapter selon vos besoins)
                $field_labels = [
                    'temps_complete_contrat_annee' => 'التوقيت كامل ',
                    'temps_partiel_contrat_annee' => 'التوقيت جزئي ',
                    'temps_complete_permanente_annee' => 'التوقيت كامل ',
                    'temps_partiel_permanente_annee' => 'التوقيت جزئي ',
                    'temps_complete_contrat_annee_1' => 'التوقيت كامل  ',
                    'temps_partiel_contrat_annee_1' => 'التوقيت جزئي ',
                    'temps_complete_permanente_annee_1' => 'التوقيت كامل ',
                    'temps_partiel_permanente_annee_1' => 'التوقيت جزئي ',
                    'temps_complete_contrat_vacant' => 'التوقيت كامل ',
                    'temps_partiel_contrat_vacant' => 'التوقيت جزئي ',
                    'temps_complete_permanente_vacant' => 'التوقيت كامل ',
                    'temps_partiel_permanente_vacant' => 'التوقيت جزئي '
                ];

                // Récupérer le tableau principal (s'il existe)
                $tableau = null;
                $details = array();
                $annee = $exercice_actif ? $exercice_actif->annee : date('Y');
                $id_tableau_4 = isset($_GET['id_tableau_4']) ? intval($_GET['id_tableau_4']) : 0; // Si lien depuis tableau 4

                if ($action == "edit_tab4_1" && $id > 0) {
                    $tableau = Tableau4_1::trouve_par_id($id);
                    if ($tableau) {
                        $annee = $tableau->annee;
                        $id_tableau_4 = $tableau->id; // récupère la référence
                        $details = DetailTab4_1::trouve_par_tableau($id);
                    }
                } else {
                    // Mode ajout : vérifier s'il y a un brouillon pour ce tableau parent
                    if ($id_tableau_4 > 0) {
                        $tableau_brouillon = Tableau4_1::trouve_tab_vide_par_admin($current_user->id, $societe->id_societe, $id_tableau_4);
                    } else {
                        $tableau_brouillon = Tableau4_1::trouve_tab_vide_par_admin($current_user->id, $societe->id_societe);
                    }
                    if ($tableau_brouillon) {
                        $tableau = $tableau_brouillon;
                        $details = DetailTab4_1::trouve_par_tableau($tableau->id);
                        $id_tableau_4 = $tableau->id_tableau_4;
                    }
                }
                ?>

                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == "edit_tab4_1" ? 'تعديل الملحق 4/1' : 'إضافة ملحق 4/1'; ?></h5>
                        <div><span class="badge bg-warning me-2">السنة: <?php echo $annee; ?></span><?php if ($tableau && $tableau->statut == 'brouillon'): ?><span class="badge bg-info">مسودة</span><?php endif; ?></div>
                    </div>
                    <div class="card-body">
                        <form id="formulaireTableau4_1" method="POST" action="ajax/traitement_tab4_1.php">
                            <input type="hidden" name="action" value="<?php echo $action == "edit_tab4_1" ? 'update_tab4_1' : 'add_tab4_1'; ?>">
                            <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                            <input type="hidden" name="id_tableau_4" value="<?php echo $id_tableau_4; ?>">
                            <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                            <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                            <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i>بيانات الملحق</h5>
                                    <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne4_1()"><i class="fas fa-plus me-1"></i> إضافة سطر</button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th rowspan="2"> منصب العمل</th>
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
                                                    <th rowspan="2" class="text-center align-middle">الصنف</th>
                                                    <th rowspan="2" class="text-center align-middle">رقم الإستدلالي</th>
                                                    <?php foreach ($numeric_fields as $field): ?>
                                                        <th class="text-center"><?php echo $field_labels[$field] ?? $field; ?></th>
                                                    <?php endforeach; ?>
                                                    
                                                </tr>
                                                <tr>
                                                    <!-- deuxième ligne vide pour aligner les en-têtes numériques (déjà dans la ligne précédente) -->
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_details4_1">
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
                                                    <td><input type="text" name="details[<?php echo $index; ?>][categorie]" class="form-control" value="<?php echo htmlspecialchars($detail->categorie ?? ''); ?>"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][num_categorie]" class="form-control" value="<?php echo $detail->num_categorie ?? 0; ?>" min="0"></td>
                                                    <?php foreach ($numeric_fields as $field): ?>
                                                        <td><input type="number" name="details[<?php echo $index; ?>][<?php echo $field; ?>]" class="form-control numeric-field" value="<?php echo $detail->$field ?? 0; ?>" min="0"></td>
                                                    <?php endforeach; ?>
                                                    <td><textarea name="details[<?php echo $index; ?>][observation]" class="form-control" rows="1"><?php echo htmlspecialchars($detail->observation ?? ''); ?></textarea></td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne4_1(this)"><i class="fas fa-trash"></i></button></td>
                                                </tr>
                                                <?php
                                                        $index++;
                                                    endforeach;
                                                endif;
                                                ?>
                                            </tbody>
                                            <tfoot class="table-secondary">
                                                <tr>
                                                    <td colspan="3" class="fw-bold">المجموع</td>
                                                    <?php foreach ($numeric_fields as $field): ?>
                                                        <td class="fw-bold" id="total_<?php echo $field; ?>">0</td>
                                                    <?php endforeach; ?>
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
                                            <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon4_1()"><i class="fas fa-save me-1"></i> حفظ كمسودة</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab4_1" ? 'تحديث الملحق' : 'تقديم الملحق'; ?></button>
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