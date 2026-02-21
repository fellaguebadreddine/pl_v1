<?php
require_once('../includes/initialiser.php');

// Vérification de la connexion
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    $session->logout();
    redirect_to('../login.php');
}

// Récupération de l'ID du tableau
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    redirect_to('admin_tableaux1.php?error=معرف غير صالح');
}

$tableau = Tableau1::trouve_par_id($id);
if (!$tableau) {
    redirect_to('admin_tableaux1.php?error=الجدول غير موجود');
}

// Récupération des détails
$societe = Societe::trouve_par_id($tableau->id_societe);
$admin_createur = Accounts::trouve_par_id($tableau->id_user);
$details_hf = DetailTab1::trouve_par_tableau($id);
$details_hp = DetailTab1_hp::trouve_par_tableau($id);

$annee = $tableau->annee;
$date_fin = '31/12/' . $annee;

$titre = "تفاصيل الجدول رقم " . $id;
$active_menu = "tab_1";
$active_submenu = "tab_1";

require_once("composit/header.php");
?>

<!-- Modal pour ajouter/modifier une note -->
<div class="modal fade" id="noteModal" tabindex="-1" aria-labelledby="noteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white" id="noteModalLabel">
                    <i class="fas fa-comment me-2"></i> إضافة ملاحظة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="noteForm">
                    <input type="hidden" name="id_tableau" id="modal_id_tableau" value="<?php echo $id; ?>">
                    <div class="mb-3">
                        <label for="modal_commentaire" class="form-label">الملاحظة</label>
                        <textarea class="form-control" id="modal_commentaire" name="commentaire" rows="5" placeholder="اكتب ملاحظتك هنا..."><?php echo htmlspecialchars($tableau->commentaire_admin ?? ''); ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> إلغاء
                </button>
                <button type="button" class="btn btn-primary" onclick="sauvegarderNoteDepuisModal()">
                    <i class="fas fa-save me-1"></i> حفظ الملاحظة
                </button>
            </div>
        </div>
    </div>
</div>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-file-alt me-2"></i> تفاصيل الجدول رقم <?php echo $id; ?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="tab1.php?action=list_tab1">الجداول</a></li>
                        <li class="breadcrumb-item active">تفاصيل الجدول</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <!-- Bouton retour et note -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
                <div>
                    <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#noteModal">
                        <i class="fas fa-comment me-1"></i> إضافة ملاحظة
                    </button>
                    <a href="print_tab_1.php?id=<?php echo $id; ?>" class="btn btn-info" target="_blank">
                        <i class="fas fa-print me-1"></i> طباعة
                    </a>
                </div>
            </div>

            <!-- En-tête républicain (optionnel) -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">معلومات عامة</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>المؤسسة :</th>
                                    <td><?php echo $societe ? $societe->raison_ar : '---'; ?></td>
                                </tr>
                                <tr>
                                    <th>السنة المالية :</th>
                                    <td><?php echo $annee; ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>المسؤول عن التعبئة :</th>
                                    <td><?php echo $admin_createur ? $admin_createur->prenom . ' ' . $admin_createur->nom : '---'; ?></td>
                                </tr>
                                <tr>
                                    <th>تاريخ الإنشاء :</th>
                                    <td><?php echo date('d/m/Y H:i', strtotime($tableau->date_creation)); ?></td>
                                </tr>
                                <tr>
                                    <th>الحالة :</th>
                                    <td>
                                        <?php
                                        $statut_badge = 'secondary';
                                        $statut_texte = $tableau->statut;
                                        if ($tableau->statut == 'validé') {
                                            $statut_badge = 'success';
                                            $statut_texte = 'مصادق عليه';
                                        } elseif ($tableau->statut == 'brouillon') {
                                            $statut_badge = 'warning';
                                            $statut_texte = 'مسودة';
                                        } elseif ($tableau->statut == 'en_attente') {
                                            $statut_badge = 'info';
                                            $statut_texte = 'في انتظار المراجعة';
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $statut_badge; ?>"><?php echo $statut_texte; ?></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section الوظائف العليا -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">الوظائف العليا</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>الرمز</th>
                                    <th>الوظيفة</th>
                                    <th>عدد المناصب المالية (<?php echo ($annee-1); ?>)</th>
                                    <th>عدد المناصب الحقيقية (<?php echo $annee; ?>)</th>
                                    <th>بالنيابة</th>
                                    <th>النساء</th>
                                    <th>الفارق</th>
                                    <th>الملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_hf_poste = 0; $total_hf_reel = 0; $total_hf_intirim = 0; $total_hf_femme = 0;
                                foreach ($details_hf as $detail):
                                    $grade = Grade::trouve_par_id($detail->id_grade);
                                    $total_hf_poste += $detail->postes_total;
                                    $total_hf_reel += $detail->postes_reel;
                                    $total_hf_intirim += $detail->poste_intirim;
                                    $total_hf_femme += $detail->poste_femme;
                                ?>
                                <tr>
                                    <td><?php echo $grade ? $grade->id : ''; ?></td>
                                    <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                    <td><?php echo number_format($detail->postes_total); ?></td>
                                    <td><?php echo number_format($detail->postes_reel); ?></td>
                                    <td><?php echo number_format($detail->poste_intirim); ?></td>
                                    <td><?php echo number_format($detail->poste_femme); ?></td>
                                    <td><?php echo number_format($detail->postes_reel - $detail->postes_total); ?></td>
                                    <td><?php echo htmlspecialchars($detail->observations); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <td colspan="2" class="fw-bold text-end">المجموع الفرعي</td>
                                    <td class="fw-bold"><?php echo number_format($total_hf_poste); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hf_reel); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hf_intirim); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hf_femme); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hf_reel - $total_hf_poste); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section المناصب العليا -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">المناصب العليا</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>الرمز</th>
                                    <th>المنصب</th>
                                    <th>عدد المناصب المالية (<?php echo ($annee-1); ?>)</th>
                                    <th>عدد المناصب الحقيقية (<?php echo $annee; ?>)</th>
                                    <th>بالنيابة</th>
                                    <th>النساء</th>
                                    <th>الفارق</th>
                                    <th>الملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_hp_poste = 0; $total_hp_reel = 0; $total_hp_intirim = 0; $total_hp_femme = 0;
                                foreach ($details_hp as $detail):
                                    $grade = Grade::trouve_par_id($detail->id_grade_hp);
                                    $total_hp_poste += $detail->postes_total_hp;
                                    $total_hp_reel += $detail->postes_reel_hp;
                                    $total_hp_intirim += $detail->poste_intirim_hp;
                                    $total_hp_femme += $detail->poste_femme_hp;
                                ?>
                                <tr>
                                    <td><?php echo $grade ? $grade->id : ''; ?></td>
                                    <td><?php echo $grade ? $grade->grade : ''; ?></td>
                                    <td><?php echo number_format($detail->postes_total_hp); ?></td>
                                    <td><?php echo number_format($detail->postes_reel_hp); ?></td>
                                    <td><?php echo number_format($detail->poste_intirim_hp); ?></td>
                                    <td><?php echo number_format($detail->poste_femme_hp); ?></td>
                                    <td><?php echo number_format($detail->postes_reel_hp - $detail->postes_total_hp); ?></td>
                                    <td><?php echo htmlspecialchars($detail->observations_hp); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <td colspan="2" class="fw-bold text-end">المجموع الفرعي</td>
                                    <td class="fw-bold"><?php echo number_format($total_hp_poste); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hp_reel); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hp_intirim); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hp_femme); ?></td>
                                    <td class="fw-bold"><?php echo number_format($total_hp_reel - $total_hp_poste); ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Totaux généraux -->
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">المجموع العام</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th></th>
                            <th>عدد المناصب المالية (<?php echo ($annee-1); ?>)</th>
                            <th>عدد المناصب الحقيقية (<?php echo $annee; ?>)</th>
                            <th>بالنيابة</th>
                            <th>النساء</th>
                            <th>الفارق</th>
                        </tr>
                        <tr>
                            <td>الوظائف العليا</td>
                            <td><?php echo number_format($total_hf_poste); ?></td>
                            <td><?php echo number_format($total_hf_reel); ?></td>
                            <td><?php echo number_format($total_hf_intirim); ?></td>
                            <td><?php echo number_format($total_hf_femme); ?></td>
                            <td><?php echo number_format($total_hf_reel - $total_hf_poste); ?></td>
                        </tr>
                        <tr>
                            <td>المناصب العليا</td>
                            <td><?php echo number_format($total_hp_poste); ?></td>
                            <td><?php echo number_format($total_hp_reel); ?></td>
                            <td><?php echo number_format($total_hp_intirim); ?></td>
                            <td><?php echo number_format($total_hp_femme); ?></td>
                            <td><?php echo number_format($total_hp_reel - $total_hp_poste); ?></td>
                        </tr>
                        <tr class="table-active fw-bold">
                            <td>المجموع العام</td>
                            <td><?php echo number_format($total_hf_poste + $total_hp_poste); ?></td>
                            <td><?php echo number_format($total_hf_reel + $total_hp_reel); ?></td>
                            <td><?php echo number_format($total_hf_intirim + $total_hp_intirim); ?></td>
                            <td><?php echo number_format($total_hf_femme + $total_hp_femme); ?></td>
                            <td><?php echo number_format(($total_hf_reel + $total_hp_reel) - ($total_hf_poste + $total_hp_poste)); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions d'administration (validation/rejet) -->
            <?php if ($tableau->statut != 'validé'): ?>
            <div class="card mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i> إجراءات المراجعة</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php if ($tableau->statut == 'en_attente'): ?>
                            هذا الجدول في انتظار المراجعة. يمكنك المصادقة عليه أو طلب تعديل.
                        <?php else: ?>
                            هذا الجدول مسودة. يمكنك المصادقة عليه مباشرة أو إضافة ملاحظة لطلب تعديل.
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-center gap-3">
                        <!-- Bouton de validation -->
                        <button type="button" class="btn btn-success btn-lg" onclick="validerTableau(<?php echo $id; ?>)">
                            <i class="fas fa-check-circle me-2"></i> مصادقة على الجدول
                        </button>
                        
                        <!-- Bouton de demande de modification (rejet) -->
                        <button type="button" class="btn btn-warning btn-lg" onclick="demanderModification(<?php echo $id; ?>)">
                            <i class="fas fa-undo-alt me-2"></i> طلب تعديل
                        </button>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i> الجدول مصادق عليه</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        تمت المصادقة على هذا الجدول في <?php echo date('d/m/Y H:i', strtotime($tableau->date_valide)); ?>
                        <?php if (!empty($tableau->id_admin_validateur)): 
                            $validateur = Accounts::trouve_par_id($tableau->id_admin_validateur);
                        ?>
                            بواسطة <?php echo $validateur ? $validateur->prenom . ' ' . $validateur->nom : '---'; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>
<script> var tableauType = 'tab4'; // exemple </script>


<?php require_once("composit/footer.php"); ?>