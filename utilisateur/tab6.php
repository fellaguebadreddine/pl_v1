<?php
// tab6.php
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
$annee_courante = $exercice_actif ? $exercice_actif->annee : date('Y');

$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab6';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 6 - الموظفون في سن التقاعد";
$active_menu = "tab_6";
$active_submenu = "tab_6";
$header = array('select2');
require_once("composit/header.php");
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">الجدول 6 -    بيان توقعي للاحالة على التقاعد <?php echo $annee_courante;?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الجدول 6</li>
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

            <?php if ($action == "list_tab6"): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>  الجدول رقم 6</h5>
                        <?php
                        $existe_tab6 = Tableau6::existe_pour_societe_annee($societe->id_societe, $annee_courante);
                        if ($exercice_actif && !$existe_tab6): ?>
                            <a href="?action=add_tab6" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إنشاء جدول التقاعد للسنة <?php echo $annee_courante; ?></a>
                        <?php elseif ($existe_tab6):
                            $tab6_existant = Tableau6::trouve_par_societe_annee($societe->id_societe, $annee_courante);
                            if ($tab6_existant && $tab6_existant->statut != 'validé'): ?>
                            <a href="?action=edit_tab6&id=<?php echo $tab6_existant->id; ?>" class="btn btn-warning">
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
                                         <th width="10%" class="text-center">الملاحظات</th>
                                        <th width="10%" class="text-center">المرفقات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tabls = Tableau6::trouve_par_societe($societe->id_societe);
                                if (!empty($tabls)):
                                    foreach ($tabls as $row):
                                        $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab6.php?id=<?php echo $row->id; ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
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
        <button type="button" class="btn btn-sm btn-warning" onclick="uploadAttachment('tab6', <?php echo $row->id; ?>)" title="تغيير المرفق">
            <i class="fas fa-upload"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAttachment('tab6', <?php echo $row->id; ?>)" title="حذف المرفق">
            <i class="fas fa-trash"></i>
        </button>
    <?php else: ?>
        <button type="button" class="btn btn-sm btn-success" onclick="uploadAttachment('tab6', <?php echo $row->id; ?>)" title="إضافة مرفق">
            <i class="fas fa-upload"></i> إضافة
        </button>
    <?php endif; ?>
</td>
                                        <td class="text-center">
                                            <a href="?action=edit_tab6&id=<?php echo $tab6_existant->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل"><i class="fas fa-edit"></i></a>
                                            <button onclick="supprimerTableau6(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger" title="حذف"><i class="fas fa-trash"></i></button>
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
                <!-- tableau 6 2-->

                <div class="col-sm-12">
                    <h4 class="mb-0">الجدول 6 -        مكرر : قائمة الموظفين الذين لهم الحق في التقاعد و الذين تم إستدعائهم لمزاولة النشاط بعنوان سنة   <?php echo $annee_courante;?></h4>
                </div>
                <br>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>   الجدول رقم 6 مكرر</h5>
                        <?php
                        $existe_tab6_1 = Tableau6_1::existe_pour_societe_annee($societe->id_societe, $annee_courante);
                        if ($exercice_actif && !$existe_tab6_1): ?>
                            <a href="?action=add_tab6_1" class="btn btn-primary"><i class="fas fa-plus me-1"></i>    إضافة الجدول 6 مكرر <?php echo $annee_courante; ?></a>
                        <?php elseif ($existe_tab6):
                            $tab6_1_existant = Tableau6_1::trouve_par_societe_annee($societe->id_societe, $annee_courante);
                            if ($tab6_1_existant && $tab6_1_existant->statut != 'validé'): ?>
                            <a href="?action=edit_tab6_1&id=<?php echo $tab6_1_existant->id; ?>" class="btn btn-warning">
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
                                         <th width="10%" class="text-center">الملاحظات</th>
                                        <th width="10%" class="text-center">المرفقات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tabls6_1 = Tableau6_1::trouve_par_societe($societe->id_societe);
                                if (!empty($tabls6_1)):
                                    foreach ($tabls6_1 as $row):
                                        $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab6.php?id=<?php echo $row->id; ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
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
        <button type="button" class="btn btn-sm btn-warning" onclick="uploadAttachment('tab6_1', <?php echo $row->id; ?>)" title="تغيير المرفق">
            <i class="fas fa-upload"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAttachment('tab6_1', <?php echo $row->id; ?>)" title="حذف المرفق">
            <i class="fas fa-trash"></i>
        </button>
    <?php else: ?>
        <button type="button" class="btn btn-sm btn-success" onclick="uploadAttachment('tab6_1', <?php echo $row->id; ?>)" title="إضافة مرفق">
            <i class="fas fa-upload"></i> إضافة
        </button>
    <?php endif; ?>
</td>
                                        <td class="text-center">
                                            <a href="?action=edit_tab6&id=<?php echo $tab6_existant->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل"><i class="fas fa-edit"></i></a>
                                            <button onclick="supprimerTableau6(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger" title="حذف"><i class="fas fa-trash"></i></button>
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

                  <!-- tableau 6 3 -->
                   <div class="col-sm-12">
                    <h4 class="mb-0">الجدول 6 -        مكرر : قائمة الموظفين الذين لهم الحق في التقاعد و الذين تم الاحتفاظ بهم بعنوان سنة  <?php echo $annee_courante;?></h4>
                </div>
                <br>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>  الجدول رقم 6 مكرر </h5>
                        <?php
                        $existe_tab6_2 = Tableau6_2::existe_pour_societe_annee($societe->id_societe, $annee_courante);
                        if ($exercice_actif && !$existe_tab6_2): ?>
                            <a href="?action=add_tab6_2" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إنشاء جدول التقاعد للسنة <?php echo $annee_courante; ?></a>
                        <?php elseif ($existe_tab6_2):
                            $tab6_2_existant = Tableau6_2::trouve_par_societe_annee($societe->id_societe, $annee_courante);
                            if ($tab6_2_existant && $tab6_2_existant->statut != 'validé'): ?>
                            <a href="?action=edit_tab6_2&id=<?php echo $tab6_2_existant->id; ?>" class="btn btn-warning">
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
                                        <th width="10%" class="text-center">الملاحظات</th>
                                        <th width="10%" class="text-center">المرفقات</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $tabls6_2 = Tableau6_2::trouve_par_societe($societe->id_societe);
                                if (!empty($tabls6_2)):
                                    foreach ($tabls6_2 as $row):
                                        $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab6.php?id=<?php echo $row->id; ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
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
        <button type="button" class="btn btn-sm btn-warning" onclick="uploadAttachment('tab6_2', <?php echo $row->id; ?>)" title="تغيير المرفق">
            <i class="fas fa-upload"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="deleteAttachment('tab6_2', <?php echo $row->id; ?>)" title="حذف المرفق">
            <i class="fas fa-trash"></i>
        </button>
    <?php else: ?>
        <button type="button" class="btn btn-sm btn-success" onclick="uploadAttachment('tab6_2', <?php echo $row->id; ?>)" title="إضافة مرفق">
            <i class="fas fa-upload"></i> إضافة
        </button>
    <?php endif; ?>
</td>
                                        <td class="text-center">
                                            <a href="?action=edit_tab6_2&id=<?php echo $tab6_existant->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل"><i class="fas fa-edit"></i></a>
                                            <button onclick="supprimerTableau6(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger" title="حذف"><i class="fas fa-trash"></i></button>
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

            <?php elseif ($action == "add_tab6" || $action == "edit_tab6"): ?>
    <?php
    $tableau = null;
    $details = array();
    $annee = $annee_courante;

    if ($action == "edit_tab6" && $id > 0) {
        $tableau = Tableau6::trouve_par_id($id);
        if ($tableau) {
            $annee = $tableau->annee;
            $details = DetailTab6::trouve_par_tableau($id);
        }
    } else {
        // Mode ajout : récupérer les employés concernés
        // On ne crée pas de tableau, on affiche directement les employés
        $employes = Employees::trouve_retraite_par_societe($societe->id_societe, $annee);
    }
    ?>

    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == "edit_tab6" ? 'تعديل جدول التقاعد' : 'إنشاء جدول التقاعد'; ?></h5>
            <div><span class="badge bg-warning me-2">السنة: <?php echo $annee; ?></span><?php if ($tableau && $tableau->statut == 'brouillon'): ?><span class="badge bg-info">مسودة</span><?php endif; ?></div>
        </div>
        <div class="card-body">
            <form id="formulaireTableau6" method="POST" action="ajax/traitement_tab6.php">
                <input type="hidden" name="action" value="<?php echo $action == "edit_tab6" ? 'update_tab6' : 'add_tab6'; ?>">
                <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i> قائمة الموظفين في سن التقاعد</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>الاسم</th>
                                        <th>اللقب</th>
                                        <th>تاريخ الميلاد</th>
                                        <th>الدرجة</th>
                                        <th>تاريخ التقاعد</th>
                                        <th>الملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_details6">
                                    <?php if ($action == "edit_tab6"): ?>
                                        <?php foreach ($details as $detail): 
                                            $grade = Grade::trouve_par_id($detail->id_grade);
                                        ?>
                                        <tr data-id="<?php echo $detail->id; ?>">
                                            <td>
                                                <input type="hidden" name="details[<?php echo $detail->id; ?>][id]" value="<?php echo $detail->id; ?>">
                                                <input type="hidden" name="details[<?php echo $detail->id; ?>][id_grade]" value="<?php echo $detail->id_grade; ?>">
                                                <?php echo htmlspecialchars($detail->prenom); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($detail->nom); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($detail->date_naissance)); ?></td>
                                            <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                            <td>
                                                <input type="date" name="details[<?php echo $detail->id; ?>][date_retraite]" class="form-control" value="<?php echo $detail->date_retraite; ?>">
                                            </td>
                                            <td>
                                                <textarea name="details[<?php echo $detail->id; ?>][observations]" class="form-control" rows="1"><?php echo htmlspecialchars($detail->observations); ?></textarea>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: // mode ajout ?>
                                        <?php if (empty($employes)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                لا يوجد موظفون في سن التقاعد لهذه السنة.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($employes as $emp): 
                                                $grade = Grade::trouve_par_id($emp->id_grade);
                                                // Calcul de la date de retraite (60 ans après naissance)
                                                $date_retraite = date('Y-m-d', strtotime($emp->date_naissance . ' +60 years'));
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="employes[<?php echo $emp->id; ?>][id_employee]" value="<?php echo $emp->id; ?>">
                                                    <input type="hidden" name="employes[<?php echo $emp->id; ?>][id_grade]" value="<?php echo $emp->id_grade; ?>">
                                                    <?php echo htmlspecialchars($emp->prenom); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($emp->nom); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($emp->date_naissance)); ?></td>
                                                <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                                <td>
                                                    <input type="date" name="employes[<?php echo $emp->id; ?>][date_retraite]" class="form-control" value="<?php echo $date_retraite; ?>">
                                                </td>
                                                <td>
                                                    <textarea name="employes[<?php echo $emp->id; ?>][observations]" class="form-control" rows="1"></textarea>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="?action=list_tab6" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع للقائمة</a>
                            <div>
                                
                                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab6" ? 'تحديث الجدول' : 'إنشاء الجدول'; ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- add tableau 6- 1 -->

       <?php elseif ($action == "add_tab6_1" || $action == "edit_tab6_1"): ?>
    <?php
    $tableau = null;
    $details = array();
    $annee = $annee_courante;

    if ($action == "edit_tab6_1" && $id > 0) {
        $tableau = Tableau6_1::trouve_par_id($id);
    
    }
    ?>

    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == "edit_tab6" ? 'تعديل جدول مكرر' : 'إنشاء جدول مكرر'; ?></h5>
            <div><span class="badge bg-warning me-2">السنة: <?php echo $annee; ?></span><?php if ($tableau && $tableau->statut == 'brouillon'): ?><span class="badge bg-info">مسودة</span><?php endif; ?></div>
        </div>
        <div class="card-body">
            <form id="formulaireTableau6_1" method="POST" action="ajax/traitement_tab6_1.php">
                <input type="hidden" name="action" value="<?php echo $action == "edit_tab6_1" ? 'update_tab6_1' : 'add_tab6_1'; ?>">
                <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i> قائمة الموظفين   </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>الاسم</th>
                                        <th>اللقب</th>
                                        <th> السلك أو الرتبة</th>
                                        <th>الوظيفة الممارسة</th>
                                        <th>تاريخ التقاعد</th>
                                        <th>تاريخ العودة</th>
                                        <th>الملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_details6">
                                    <?php if ($action == "edit_tab6"): ?>
                                        <?php foreach ($details as $detail): 
                                            $grade = Grade::trouve_par_id($detail->id_grade);
                                        ?>
                                        <tr data-id="<?php echo $detail->id; ?>">
                                            <td>
                                                <input type="hidden" name="details[<?php echo $detail->id; ?>][id]" value="<?php echo $detail->id; ?>">
                                                <input type="hidden" name="details[<?php echo $detail->id; ?>][id_grade]" value="<?php echo $detail->id_grade; ?>">
                                                <?php echo htmlspecialchars($detail->prenom); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($detail->nom); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($detail->date_naissance)); ?></td>
                                            <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                            <td>
                                                <input type="date" name="details[<?php echo $detail->id; ?>][date_retraite]" class="form-control" value="<?php echo $detail->date_retraite; ?>">
                                            </td>
                                            <td>
                                                <textarea name="details[<?php echo $detail->id; ?>][observations]" class="form-control" rows="1"><?php echo htmlspecialchars($detail->observations); ?></textarea>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: // mode ajout ?>
                                        <?php if (empty($employes)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                               لا شيئ
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($employes as $emp): 
                                                $grade = Grade::trouve_par_id($emp->id_grade);
                                                // Calcul de la date de retraite (60 ans après naissance)
                                                $date_retraite = date('Y-m-d', strtotime($emp->date_naissance . ' +60 years'));
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="employes[<?php echo $emp->id; ?>][id_employee]" value="<?php echo $emp->id; ?>">
                                                    <input type="hidden" name="employes[<?php echo $emp->id; ?>][id_grade]" value="<?php echo $emp->id_grade; ?>">
                                                    <?php echo htmlspecialchars($emp->prenom); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($emp->nom); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($emp->date_naissance)); ?></td>
                                                <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                                <td>
                                                    <input type="date" name="employes[<?php echo $emp->id; ?>][date_retraite]" class="form-control" value="<?php echo $date_retraite; ?>">
                                                </td>
                                                <td>
                                                    <textarea name="employes[<?php echo $emp->id; ?>][observations]" class="form-control" rows="1"></textarea>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="?action=list_tab6" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع للقائمة</a>
                            <div>
                                
                                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab6_1" ? 'تحديث الجدول' : 'إنشاء الجدول'; ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- add tableau 6 - 2 -->
    <?php elseif ($action == "add_tab6_2" || $action == "edit_tab6_2"): ?>
    <?php
    $tableau = null;
    $details = array();
    $annee = $annee_courante;

    if ($action == "edit_tab6_2" && $id > 0) {
        $tableau = Tableau6_2::trouve_par_id($id);
        if ($tableau) {
            $annee = $tableau->annee;
            $details = DetailTab6_2::trouve_par_tableau($id);
        }
    } else {
        // Mode ajout : récupérer les employés concernés
        // On ne crée pas de tableau, on affiche directement les employés
        $employes = Employees::trouve_retraite_par_age_par_societe($societe->id_societe, $annee);
    }
    ?>

    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == "edit_tab6_2" ? 'تعديل جدول ' : 'إنشاء جدول '; ?></h5>
            <div><span class="badge bg-warning me-2">السنة: <?php echo $annee; ?></span><?php if ($tableau && $tableau->statut == 'brouillon'): ?><span class="badge bg-info">مسودة</span><?php endif; ?></div>
        </div>
        <div class="card-body">
            <form id="formulaireTableau6_2" method="POST" action="ajax/traitement_tab6_2.php">
                <input type="hidden" name="action" value="<?php echo $action == "edit_tab6_2" ? 'update_tab6_2' : 'add_tab6_2'; ?>">
                <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i> الجدول رقم 6 مكر</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>الاسم</th>
                                        <th>اللقب</th>
                                        <th>تاريخ الميلاد</th>
                                        <th>الوظيفة أو الرتبة</th>
                                        <th> الاقدمية إلى غاية 31-12- <?php echo $annee;?></th>
                                        <th>الملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_details6">
                                    <?php if ($action == "edit_tab6_2"): ?>
                                        <?php foreach ($details as $detail): 
                                            $grade = Grade::trouve_par_id($detail->id_grade);
                                        ?>
                                        <tr data-id="<?php echo $detail->id; ?>">
                                            <td>
                                                <input type="hidden" name="details[<?php echo $detail->id; ?>][id]" value="<?php echo $detail->id; ?>">
                                                <input type="hidden" name="details[<?php echo $detail->id; ?>][id_grade]" value="<?php echo $detail->id_grade; ?>">
                                                <?php echo htmlspecialchars($detail->prenom); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($detail->nom); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($detail->date_naissance)); ?></td>
                                            <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                            <td>
                                                <?php  echo $detail->nbr_annee; ?>
                                            </td>
                                            <td>
                                                <textarea name="details[<?php echo $detail->id; ?>][observations]" class="form-control" rows="1"><?php echo htmlspecialchars($detail->observations); ?></textarea>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: // mode ajout ?>
                                        <?php if (empty($employes)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                لا يوجد موظفون في سن التقاعد لهذه السنة.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($employes as $emp): 
                                                $grade = Grade::trouve_par_id($emp->id_grade);
                                                // Calcul de la date de retraite (60 ans après naissance)
                                                $date_retraite = date('Y-m-d', strtotime($emp->date_naissance . ' +60 years'));
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="employes[<?php echo $emp->id; ?>][id_employee]" value="<?php echo $emp->id; ?>">
                                                    <input type="hidden" name="employes[<?php echo $emp->id; ?>][id_grade]" value="<?php echo $emp->id_grade; ?>">
                                                    <?php echo htmlspecialchars($emp->prenom); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($emp->nom); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($emp->date_naissance)); ?></td>
                                                <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                                <td class="text-center">
                                                     <input type="hidden" name="employes[<?php echo $emp->id; ?>][nbr_annee]" class="form-control" value="<?php echo $emp->nbr_annee; ?>"> 
                                                   <?php  echo $emp->nbr_annee; ?>
                                                </td>
                                                <td>
                                                    <textarea name="employes[<?php echo $emp->id; ?>][observations]" class="form-control" rows="1"></textarea>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="?action=list_tab6" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع للقائمة</a>
                            <div>
                                
                                <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab6" ? 'تحديث الجدول' : 'إنشاء الجدول'; ?></button>
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

 <?php if ($action == "add_tab6" || $action == "edit_tab6"): ?>
<script>
// Variables globales
let compteurLignes = <?php echo isset($details) ? count($details) : 0; ?>;

function enregistrerBrouillon6() {
    const form = $('#formulaireTableau6')[0];
    const input = $('<input>').attr({ type: 'hidden', name: 'statut', value: 'brouillon' });
    $(form).append(input);
    if (confirm('هل تريد حفظ الجدول كمسودة؟')) {
        soumettreFormulaire6(form, 'brouillon');
    } else {
        input.remove();
    }
}

// Gestion de la soumission du formulaire
document.getElementById('formulaireTableau6')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('هل أنت متأكد من رغبتك في حفظ الجدول؟')) {
        soumettreFormulaire6(this, 'validé');
    }
});

function soumettreFormulaire6(form, statut) {
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...').prop('disabled', true);

    fetch('ajax/traitement_tab6.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showMessage6(data.message, 'success');
            setTimeout(() => {
                if (statut == 'brouillon') window.location.reload();
                else window.location.href = '?action=list_tab6&success=1';
            }, 1500);
        } else {
            showMessage6(data.message, 'danger');
            submitBtn.html(originalText).prop('disabled', false);
        }
    })
    .catch(() => {
        showMessage6('حدث خطأ أثناء الاتصال بالخادم', 'danger');
        submitBtn.html(originalText).prop('disabled', false);
    });
}

function showMessage6(msg, type) {
    const alert = $('<div class="alert alert-'+type+' alert-dismissible fade show" role="alert">'+msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('.app-content .container-fluid').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}

function supprimerTableau6(id) {
    if (confirm('هل أنت متأكد من حذف هذا الجدول؟')) {
        fetch('ajax/traitement_tab6.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=delete_tab6&id='+id })
            .then(res => res.json())
            .then(data => {
                if (data.success) { showMessage6(data.message, 'success'); setTimeout(() => window.location.reload(), 1500); }
                else showMessage6(data.message, 'danger');
            })
            .catch(() => showMessage6('حدث خطأ أثناء الاتصال بالخادم', 'danger'));
    }
}
</script>
<?php endif;?>

<?php if ($action == "add_tab6_1" || $action == "edit_tab6_1"): ?>
<script>
    // Gestion de la soumission du formulaire
document.getElementById('formulaireTableau6_1')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('هل أنت متأكد من رغبتك في حفظ الجدول؟')) {
        soumettreFormulaire6_1(this, 'validé');
    }
});

function soumettreFormulaire6_1(form, statut) {
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...').prop('disabled', true);

    fetch('ajax/traitement_tab6_1.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showMessage6(data.message, 'success');
            setTimeout(() => {
                if (statut == 'brouillon') window.location.reload();
                else window.location.href = '?action=list_tab6&success=1';
            }, 1500);
        } else {
            showMessage6(data.message, 'danger');
            submitBtn.html(originalText).prop('disabled', false);
        }
    })
    .catch(() => {
        showMessage6('حدث خطأ أثناء الاتصال بالخادم', 'danger');
        submitBtn.html(originalText).prop('disabled', false);
    });
}

function showMessage6(msg, type) {
    const alert = $('<div class="alert alert-'+type+' alert-dismissible fade show" role="alert">'+msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('.app-content .container-fluid').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}
</script>
<?php endif;?>

<?php if ($action == "add_tab6_2" || $action == "edit_tab6_2"): ?>
<script>
    // Variables globales
let compteurLignes = <?php echo isset($details) ? count($details) : 0; ?>;
    // Gestion de la soumission du formulaire
document.getElementById('formulaireTableau6_2')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('هل أنت متأكد من رغبتك في حفظ الجدول؟')) {
        soumettreFormulaire6_2(this, 'validé');
    }
});

function soumettreFormulaire6_2(form, statut) {
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...').prop('disabled', true);

    fetch('ajax/traitement_tab6_2.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showMessage6(data.message, 'success');
            setTimeout(() => {
                if (statut == 'brouillon') window.location.reload();
                else window.location.href = '?action=list_tab6&success=1';
            }, 1500);
        } else {
            showMessage6(data.message, 'danger');
            submitBtn.html(originalText).prop('disabled', false);
        }
    })
    .catch(() => {
        showMessage6('حدث خطأ أثناء الاتصال بالخادم', 'danger');
        submitBtn.html(originalText).prop('disabled', false);
    });
}

function showMessage6(msg, type) {
    const alert = $('<div class="alert alert-'+type+' alert-dismissible fade show" role="alert">'+msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('.app-content .container-fluid').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}
</script>
<?php endif;?>
<style>
/* styles éventuels */
</style>

<?php require_once("composit/footer.php"); ?>