<?php
// tab7.php
require_once('../includes/initialiser.php');

if (!$session->is_logged_in()) redirect_to('../login.php');
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'utilisateur') {
    $session->logout();
    redirect_to('../login.php');
}
if (!$current_user->id_societe) redirect_to('../login.php');
$societe = Societe::trouve_par_id($current_user->id_societe);
if (!$societe) redirect_to('../login.php');

$exercice_actif = Exercice::get_exercice_actif();
$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab7';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 7 - تكوين الموظفين";
$active_menu = "tab_7";
$active_submenu = "tab_7";
$header = array('select2', 'sweetalert2');
require_once("composit/header.php");

$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe = Tableau7::existe_pour_societe_annee($societe->id_societe, $annee);
$tabls = Tableau7::trouve_par_societe($societe->id_societe);

if ($action == "add_tab7") {
    if ($existe) redirect_to("?action=list_tab7");
}
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">الجدول 7 - تكوين الموظفين</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الجدول 7</li>
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

            <?php if ($action == "list_tab7"): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>قائمة الجداول المسجلة</h5>
                        <?php if ($exercice_actif && !$existe): ?>
                            <a href="?action=add_tab7" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة جدول رقم 7</a>
                        <?php elseif ($existe):
                            $tab_existant = Tableau7::trouve_par_societe_annee($societe->id_societe, $annee);
                            if ($tab_existant && $tab_existant->statut != 'validé'): ?>
                                <a href="?action=edit_tab7&id=<?php echo $tab_existant->id; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit me-1"></i> تعديل الجدول الحالي
                                </a>
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
                                        <th>المرفقات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($tabls)): foreach ($tabls as $row): 
                                    $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab7.php?id=<?php echo $row->id; ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
                                        <td><?php echo $societe->raison_ar; ?></td>
                                        <td><?php echo $row->annee; ?></td>
                                        <td><span class="badge bg-<?php echo $statut_badge; ?>"><?php echo $row->statut; ?></span></td>
                                        <td><?php echo $row->date_valide ? date('d/m/Y', strtotime($row->date_valide)) : '---'; ?></td>
                                        <td>
                                            <?php if (!empty($row->attachment)): ?>
                                                <a href="<?php echo $row->attachment; ?>" target="_blank" class="btn btn-sm btn-info" title="تحميل المرفق"><i class="fas fa-file-download"></i></a>
                                                <button class="btn btn-sm btn-warning" onclick="uploadAttachment('tab7', <?php echo $row->id; ?>)" title="تغيير"><i class="fas fa-upload"></i></button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteAttachment('tab7', <?php echo $row->id; ?>)" title="حذف"><i class="fas fa-trash"></i></button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success" onclick="uploadAttachment('tab7', <?php echo $row->id; ?>)" title="إضافة"><i class="fas fa-upload"></i> إضافة</button>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                             <?php if ($exercice_actif && $row->statut != 'validé'): ?>
                                            <a href="?action=edit_tab7&id=<?php echo $row->id; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <button onclick="supprimerTableau(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                            <?php endif;?>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="7" class="text-center py-4">لا توجد جداول</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action == "add_tab7" || $action == "edit_tab7"): ?>
                <?php
                $tableau = null;
                $details = array();
                $annee = $annee;

                if ($action == "edit_tab7" && $id > 0) {
                    $tableau = Tableau7::trouve_par_id($id);
                    if ($tableau) {
                        $annee = $tableau->annee;
                        $details = DetailTab7::trouve_par_tableau($id);
                    }
                } else {
                    $tableau_brouillon = Tableau7::trouve_tab_vide_par_admin($current_user->id, $societe->id_societe, $annee);
                    if ($tableau_brouillon) {
                        $tableau = $tableau_brouillon;
                        $details = DetailTab7::trouve_par_tableau($tableau->id);
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
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == "edit_tab7" ? 'تعديل الجدول 7' : 'إضافة جدول 7'; ?></h5>
                        <div><span class="badge bg-warning me-2">السنة: <?php echo $annee; ?></span><?php if ($tableau && $tableau->statut == 'brouillon'): ?><span class="badge bg-info">مسودة</span><?php endif; ?></div>
                    </div>
                    <div class="card-body">
                        <form id="formulaireTableau7" method="POST" action="ajax/traitement_tab7.php">
                            <input type="hidden" name="action" value="<?php echo $action == "edit_tab7" ? 'update_tab7' : 'add_tab7'; ?>">
                            <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                            <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                            <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                            <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i>بيانات التكوين</h5>
                                    <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne()"><i class="fas fa-plus me-1"></i> إضافة سطر</button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>الدرجة</th>
                                                    <th colspan="3">تكوين الأولي</th>
                                                    <th colspan="3">تكوين تكميلي</th>
                                                    <th colspan="3">  تحسين المستوى</th>
                                                    <th colspan="3">  تجديد المعلومات (الرسكلة)</th>
                                                    <th>المجموع</th>
                                                    <th>الملاحظات</th>
                                                    <th>الإجراءات</th>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <th>عدد الأعوان</th><th>تاريخ الإلتحاق</th><th>المدة التكوين</th>
                                                   <th>عدد الأعوان</th><th>تاريخ الإلتحاق</th><th>المدة التكوين</th>
                                                    <th>عدد الأعوان</th><th>تاريخ الإلتحاق</th><th>المدة التكوين</th>
                                                   <th>عدد الأعوان</th><th>تاريخ الإلتحاق</th><th>المدة التكوين</th>
                                                    <th></th><th></th><th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody_details7">
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
                                                        <select name="details[<?php echo $index; ?>][id_grade_select]" class="form-control select-grade" required>
                                                            <option value="">اختر الدرجة</option>
                                                            <?php foreach ($grades as $g): ?>
                                                                <option value="<?php echo $g->id; ?>" data-code="<?php echo $g->id; ?>" <?php echo $g->id == $grade->id ? 'selected' : ''; ?>><?php echo $g->grade; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <!-- Formation initiale -->
                                                    <td><input type="number" name="details[<?php echo $index; ?>][nbr_agent_formation_initiale]" class="form-control" value="<?php echo $detail->nbr_agent_formation_initiale; ?>" min="0"></td>
                                                    <td><input type="date" name="details[<?php echo $index; ?>][date_agent_formation_initiale]" class="form-control" value="<?php echo $detail->date_agent_formation_initiale; ?>"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][duree_agent_formation_initiale]" class="form-control" value="<?php echo $detail->duree_agent_formation_initiale; ?>" min="0"></td>
                                                    <!-- Formation supplémentaire -->
                                                    <td><input type="number" name="details[<?php echo $index; ?>][nbr_agent_formation_supplementaire]" class="form-control" value="<?php echo $detail->nbr_agent_formation_supplementaire; ?>" min="0"></td>
                                                    <td><input type="date" name="details[<?php echo $index; ?>][date_agent_formation_supplementaire]" class="form-control" value="<?php echo $detail->date_agent_formation_supplementaire; ?>"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][duree_agent_formation_supplementaire]" class="form-control" value="<?php echo $detail->duree_agent_formation_supplementaire; ?>" min="0"></td>
                                                    <!-- Formation mise à niveau -->
                                                    <td><input type="number" name="details[<?php echo $index; ?>][nbr_agent_formation_mise_niveau]" class="form-control" value="<?php echo $detail->nbr_agent_formation_mise_niveau; ?>" min="0"></td>
                                                    <td><input type="date" name="details[<?php echo $index; ?>][date_agent_formation_mise_niveau]" class="form-control" value="<?php echo $detail->date_agent_formation_mise_niveau; ?>"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][duree_agent_formation_mise_niveau]" class="form-control" value="<?php echo $detail->duree_agent_formation_mise_niveau; ?>" min="0"></td>
                                                    <!-- Formation recyclage -->
                                                    <td><input type="number" name="details[<?php echo $index; ?>][nbr_agent_formation_recyclage]" class="form-control" value="<?php echo $detail->nbr_agent_formation_recyclage; ?>" min="0"></td>
                                                    <td><input type="date" name="details[<?php echo $index; ?>][date_agent_formation_recyclage]" class="form-control" value="<?php echo $detail->date_agent_formation_recyclage; ?>"></td>
                                                    <td><input type="number" name="details[<?php echo $index; ?>][duree_agent_formation_recyclage]" class="form-control" value="<?php echo $detail->duree_agent_formation_recyclage; ?>" min="0"></td>
                                                    <!-- Total -->
                                                    <td><input type="number" name="details[<?php echo $index; ?>][total]" class="form-control" value="<?php echo $detail->total; ?>" readonly></td>
                                                    <!-- Observations -->
                                                    <td><textarea name="details[<?php echo $index; ?>][observations]" class="form-control" rows="1"><?php echo htmlspecialchars($detail->observations); ?></textarea></td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)"><i class="fas fa-trash"></i></button></td>
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

                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <a href="?action=list_tab7" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع</a>
                                        <div>
                                            <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon()"><i class="fas fa-save me-1"></i> حفظ كمسودة</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab7" ? 'تحديث' : 'تقديم'; ?></button>
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

<!-- Modal d'upload -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" dir="rtl">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i> رفع مرفق</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="table_type" id="upload_table_type">
                    <input type="hidden" name="record_id" id="upload_record_id">
                    <div class="mb-3"><label for="file" class="form-label">اختر ملفاً</label><input type="file" class="form-control" id="file" name="file" required></div>
                </form>
                <div id="uploadProgress" class="progress d-none"><div class="progress-bar" style="width:0%">0%</div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" id="uploadSubmit"><i class="fas fa-upload me-1"></i> رفع</button>
            </div>
        </div>
    </div>
</div>



<?php require_once("composit/footer.php"); ?>