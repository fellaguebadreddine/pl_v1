<?php
// details_tableau4.php
require_once('../includes/initialiser.php');
if (!$session->is_logged_in()) redirect_to('../login.php');
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) { $session->logout(); redirect_to('../login.php'); }
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) redirect_to('tab4.php?action=list_tab4&error=معرف غير صالح');
$tableau = Tableau4::trouve_par_id($id);
if (!$tableau) redirect_to('tab4.php?action=list_tab4&error=الجدول غير موجود');
$societe = Societe::trouve_par_id($tableau->id_societe);
$createur = Accounts::trouve_par_id($tableau->id_user);
$details = DetailTab4::trouve_par_tableau($id);
$annee = $tableau->annee;

$titre = "تفاصيل الجدول رقم 4 - ".$id;
$active_menu = "tab_4";
$active_submenu = "tab_4";
$header = array('select2', 'sweetalert2');
require_once("composit/header.php");
?>

<!-- Modal pour note -->
<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white"><i class="fas fa-comment me-2"></i> إضافة ملاحظة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <input type="hidden" id="modal_id_tableau" value="<?php echo $id; ?>">
                    <textarea id="modal_commentaire" class="form-control" rows="5" placeholder="اكتب ملاحظتك هنا..."><?php echo htmlspecialchars($tableau->commentaire_admin ?? ''); ?></textarea>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="sauvegarderNote()"><i class="fas fa-save me-1"></i> حفظ</button>
            </div>
        </div>
    </div>
</div>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0"><i class="fas fa-file-alt me-2"></i> تفاصيل الجدول رقم 4 - <?php echo $id; ?></h3></div>
                <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li><li class="breadcrumb-item"><a href="tab4.php?action=list_tab4">الجداول</a></li><li class="breadcrumb-item active">تفاصيل الجدول</li></ol></div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع</a>
                <div>
                    <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#noteModal"><i class="fas fa-comment me-1"></i> إضافة ملاحظة</button>
                    <a href="../utilisateur/print_tab4.php?id=<?php echo $id; ?>" class="btn btn-info" target="_blank"><i class="fas fa-print me-1"></i> طباعة</a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">معلومات عامة</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><th>المؤسسة :</th><td><?php echo $societe ? $societe->raison_ar : '---'; ?></td></tr>
                                <tr><th>السنة المالية :</th><td><?php echo $annee; ?></td></tr>
                                <tr><th>تاريخ الإنشاء :</th><td><?php echo date('d/m/Y H:i', strtotime($tableau->date_creation)); ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><th>المسؤول عن التعبئة :</th><td><?php echo $createur ? $createur->prenom . ' ' . $createur->nom : '---'; ?></td></tr>
                                <tr><th>الحالة :</th><td><span class="badge bg-<?php echo $tableau->statut == 'validé' ? 'success' : ($tableau->statut == 'brouillon' ? 'warning' : 'info'); ?>"><?php echo $tableau->statut == 'validé' ? 'مصادق عليه' : ($tableau->statut == 'brouillon' ? 'مسودة' : 'في انتظار المراجعة'); ?></span></td></tr>
                                <?php if (!empty($tableau->date_valide)): ?><tr><th>تاريخ المصادقة :</th><td><?php echo date('d/m/Y H:i', strtotime($tableau->date_valide)); ?></td></tr><?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-info text-white">بيانات الجدول</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>الرمز</th><th>السلك</th><th>عدد المناصب الشاغرة (خارجي)</th><th>منتوج التكوين (شبه الطبيبي)</th><th>مسابقة على أساس الشهادة</th><th>المبتدئين المتعاقدين</th><th>العمال المبنى المتعاقدين</th><th>طريقة على أساس الشهادة</th><th>امتحان ميني</th><th>فحص ميني (العمال المبنى صنف)</th><th>المناصب المالية التي تم استغلالها</th><th>عدد المناصب المالية التي تم استغلالها</th><th>الملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($details)): foreach ($details as $d): $grade = Grade::trouve_par_id($d->id_grade); ?>
                                <tr>
                                    <td><?php echo $grade ? $grade->id : ''; ?></td>
                                    <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                    <td><?php echo $d->postes_vacants_externe; ?></td>
                                    <td><?php echo $d->produit_formation_paramedicale; ?></td>
                                    <td><?php echo $d->concours_sur_titre; ?></td>
                                    <td><?php echo $d->debutants_contractuels; ?></td>
                                    <td><?php echo $d->ouvriers_batiment_contractuels; ?></td>
                                    <td><?php echo $d->methode_sur_titre; ?></td>
                                    <td><?php echo $d->examen_mini; ?></td>
                                    <td><?php echo $d->test_mini_ouvriers; ?></td>
                                    <td><?php echo $d->postes_financiers_exploites; ?></td>
                                    <td><?php echo $d->nombre_postes_financiers_exploites; ?></td>
                                    <td><?php echo htmlspecialchars($d->observations); ?></td>
                                </tr>
                                <?php endforeach; else: ?><tr><td colspan="13" class="text-center">لا توجد بيانات</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!empty($tableau->commentaire_admin)): ?>
            <div class="alert alert-info"><strong><i class="fas fa-comment"></i> ملاحظة الإدارة :</strong><br><?php echo nl2br(htmlspecialchars($tableau->commentaire_admin)); ?></div>
            <?php endif; ?>

            <?php if ($current_user->type == 'administrateur' || $current_user->type == 'super_administrateur'): ?>
                <?php if ($tableau->statut != 'validé'): ?>
                <div class="card mb-4 border-danger">
                    <div class="card-header bg-danger text-white"><i class="fas fa-tasks me-2"></i> إجراءات المراجعة</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-success btn-lg" onclick="validerTableau(<?php echo $id; ?>, 'tab4')"><i class="fas fa-check-circle me-2"></i> مصادقة على الجدول</button>
                            <button type="button" class="btn btn-warning btn-lg" onclick="demanderModification(<?php echo $id; ?>, 'tab4')"><i class="fas fa-undo-alt me-2"></i> طلب تعديل</button>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card mb-4 border-success">
                    <div class="card-header bg-success text-white"><i class="fas fa-check-circle me-2"></i> الجدول مصادق عليه</div>
                    <div class="card-body">
                        <div class="alert alert-success">تمت المصادقة في <?php echo date('d/m/Y H:i', strtotime($tableau->date_valide)); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<script> var tableauType = 'tab4'; // exemple </script>

<?php require_once("composit/footer.php"); ?>