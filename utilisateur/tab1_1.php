<?php
// tab1_1.php
require_once('../includes/initialiser.php');

if (!$session->is_logged_in()) redirect_to('../login.php');
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) { $session->logout(); redirect_to('../login.php'); }
if ($current_user->type !== 'utilisateur') { $session->logout(); redirect_to('../login.php'); }
if (!$current_user->id_societe) redirect_to('../login.php');
$societe = Societe::trouve_par_id($current_user->id_societe);
if (!$societe) redirect_to('../login.php');

$exercice_actif = Exercice::get_exercice_actif();
$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab1_1';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_tableau_1 = isset($_GET['id_tableau_1']) ? intval($_GET['id_tableau_1']) : 0; // pour lier au tableau 1 parent

$titre = "الملحق 1/1";
$active_menu = "tab_1";
$active_submenu = "tab_1_1";
$header = array('select2');

require_once("composit/header.php");

$grades = Grade::trouve_tous();
$grades_js = array();
foreach ($grades as $grade) {
    $grades_js[] = array('id' => $grade->id, 'code' => $grade->id, 'designation' => $grade->grade);
}

// Liste des champs numériques
$numeric_fields = [
    'effectif_reel_annee_1',
    'moyennes',
    'employes',
    'effectif_reel_31_dec_2023',
    'nb_indetermine',
    'nb_determine',
    'nb_contrat_determine'
];

$field_labels = [
    'effectif_reel_annee_1' => 'التعداد الحقيقي لسنة (1)',
    'moyennes' => 'المتوسطات',
    'employes' => 'الموظفون',
    'effectif_reel_31_dec_2023' => 'التعداد الحقيقي إلى غاية 31 ديسمبر 2023',
    'nb_indetermine' => 'عدد غير محددة المدة',
    'nb_determine' => 'عدد محددة المدة',
    'nb_contrat_determine' => 'عقد محدد المدة'
];
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">الملحق 1/1 - <?php echo $societe->raison_ar; ?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="tab1.php?action=list_tab1">الجدول 1</a></li>
                        <li class="breadcrumb-item active">الملحق 1/1</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> تم حفظ الملحق بنجاح!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action == "list_tab1_1"): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i> قائمة الملحقات 1/1</h5>
                        <?php if ($id_tableau_1 > 0): ?>
                            <a href="?action=add_tab1_1&id_tableau_1=<?php echo $id_tableau_1; ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة ملحق</a>
                        <?php else: ?>
                            <span class="text-muted">يرجى اختيار جدول 1 أولاً</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>الجدول 1 المرتبط</th>
                                        <th>السنة</th>
                                        <th>الحالة</th>
                                        <th>تاريخ التقديم</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                if ($id_tableau_1 > 0) {
                                    $tabls = Tableau1_1::trouve_par_tableau_parent($id_tableau_1);
                                } else {
                                    $tabls = Tableau1_1::trouve_par_societe($societe->id_societe);
                                }
                                if (!empty($tabls)):
                                    foreach ($tabls as $row):
                                        $statut_badge = $row->statut == 'validé' ? 'success' : ($row->statut == 'brouillon' ? 'warning' : 'secondary');
                                ?>
                                    <tr>
                                        <td class="text-center"><a href="print_tab1_1.php?id=<?php echo $row->id; ?>" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-print"></i> <?php echo $row->id; ?></a></td>
                                        <td class="text-center"><?php echo $row->id_tableau_1; ?></td>
                                        <td class="text-center"><?php echo $row->annee; ?></td>
                                        <td class="text-center"><span class="badge bg-<?php echo $statut_badge; ?>"><?php echo $row->statut; ?></span></td>
                                        <td class="text-center"><?php echo $row->date_valide ? date('d/m/Y', strtotime($row->date_valide)) : '---'; ?></td>
                                        <td class="text-center">
                                            <a href="?action=edit_tab1_1&id=<?php echo $row->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل"><i class="fas fa-edit"></i></a>
                                            <button onclick="supprimerTableau(<?php echo $row->id; ?>)" class="btn btn-sm btn-danger" title="حذف"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php
                                    endforeach;
                                else:
                                ?>
                                    <tr><td colspan="6" class="text-center py-4"><i class="fas fa-table fa-2x text-muted mb-3 d-block"></i>لا توجد ملحقات مسجلة</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action == "add_tab1_1" || $action == "edit_tab1_1"): ?>
                <?php
                $tableau = null;
                $details = array();
                $annee = $exercice_actif ? $exercice_actif->annee : date('Y');

                if ($action == "edit_tab1_1" && $id > 0) {
                    $tableau = Tableau1_1::trouve_par_id($id);
                    if ($tableau) {
                        $annee = $tableau->annee;
                        $id_tableau_1 = $tableau->id_tableau_1;
                        $details = DetailTab1_1::trouve_par_tableau($id);
                    }
                } else {
                    // Mode ajout
                    if (!$id_tableau_1) {
                        echo "<div class='alert alert-danger'>يجب تحديد الجدول 1 المرتبط</div>";
                        exit;
                    }
                    $tableau_brouillon = Tableau1_1::trouve_tab_vide_par_admin($current_user->id, $societe->id_societe, $id_tableau_1);
                    if ($tableau_brouillon) {
                        $tableau = $tableau_brouillon;
                        $details = DetailTab1_1::trouve_par_tableau($tableau->id);
                    }
                }
                ?>

                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-edit me-2"></i><?php echo $action == "edit_tab1_1" ? 'تعديل الملحق 1/1' : 'إضافة ملحق 1/1'; ?></h5>
                        <div><span class="badge bg-warning me-2">السنة: <?php echo $annee; ?></span><?php if ($tableau && $tableau->statut == 'brouillon'): ?><span class="badge bg-info">مسودة</span><?php endif; ?></div>
                    </div>
                    <div class="card-body">
                        <form id="formulaireTableau1_1" method="POST" action="ajax/traitement_tab1_1.php">
                            <input type="hidden" name="action" value="<?php echo $action == "edit_tab1_1" ? 'update_tab1_1' : 'add_tab1_1'; ?>">
                            <input type="hidden" name="id_tableau" value="<?php echo $tableau ? $tableau->id : '0'; ?>">
                            <input type="hidden" name="id_tableau_1" value="<?php echo $id_tableau_1; ?>">
                            <input type="hidden" name="annee" value="<?php echo $annee; ?>">
                            <input type="hidden" name="id_societe" value="<?php echo $societe->id_societe; ?>">
                            <input type="hidden" name="id_user" value="<?php echo $current_user->id; ?>">

                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i>بيانات الملحق</h5>
                                    <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne()"><i class="fas fa-plus me-1"></i> إضافة سطر</button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th rowspan="2" class="text-center align-middle">الرمز</th>
                                                    <th rowspan="2" class="text-center align-middle">السلك</th>
                                                    <th rowspan="2" class="text-center align-middle">القوانين</th>
                                                    <th rowspan="2" class="text-center align-middle">السلوك أو الرتبة</th>
                                                    <?php foreach ($numeric_fields as $field): ?>
                                                        <th class="text-center"><?php echo $field_labels[$field]; ?></th>
                                                    <?php endforeach; ?>
                                                    <th rowspan="2" class="text-center align-middle">الملاحظات</th>
                                                    <th rowspan="2" class="text-center align-middle">الإجراءات</th>
                                                </tr>
                                                <tr>
                                                    <!-- deuxième ligne vide pour l'alignement -->
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
                                                    <td><input type="text" name="details[<?php echo $index; ?>][loi]" class="form-control" value="<?php echo htmlspecialchars($detail->loi); ?>"></td>
                                                    <td><input type="text" name="details[<?php echo $index; ?>][categorie]" class="form-control" value="<?php echo htmlspecialchars($detail->categorie); ?>"></td>
                                                    <?php foreach ($numeric_fields as $field): ?>
                                                        <td><input type="number" name="details[<?php echo $index; ?>][<?php echo $field; ?>]" class="form-control numeric-field" value="<?php echo $detail->$field; ?>" min="0"></td>
                                                    <?php endforeach; ?>
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
                                                    <td colspan="4" class="fw-bold text-end">المجموع</td>
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
                                        <a href="?action=list_tab1_1&id_tableau_1=<?php echo $id_tableau_1; ?>" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع للقائمة</a>
                                        <div>
                                            <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon()"><i class="fas fa-save me-1"></i> حفظ كمسودة</button>
                                            <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane me-1"></i> <?php echo $action == "edit_tab1_1" ? 'تحديث الملحق' : 'تقديم الملحق'; ?></button>
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

<script>
let compteurLignes = <?php echo isset($index) ? $index : 0; ?>;
let tousLesGrades = <?php echo json_encode($grades_js); ?>;

$(document).ready(function() {
    $('.select-grade').select2({
        placeholder: "ابحث أو اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl",
        language: { noResults: () => "لا توجد نتائج", searching: () => "جاري البحث..." }
    });
    $(document).on('change', '.select-grade', function() {
        const row = $(this).closest('tr');
        const selected = $(this).find('option:selected');
        const code = selected.data('code') || '';
        const idGrade = selected.val();
        row.find('.code-grade').val(code);
        row.find('input[name*="[id_grade]"]').val(idGrade);
    });
    calculerTotaux();
});

function ajouterLigne() {
    const tbody = $('#tbody_details');
    const index = compteurLignes++;
    let options = '<option value="">اختر السلك</option>';
    tousLesGrades.forEach(g => options += `<option value="${g.id}" data-code="${g.code}">${g.designation}</option>`);

    let numericHtml = '';
    <?php foreach ($numeric_fields as $field): ?>
        numericHtml += `<td><input type="number" name="details[${index}][<?php echo $field; ?>]" class="form-control numeric-field" value="0" min="0"></td>`;
    <?php endforeach; ?>

    const row = `
        <tr>
            <td><input type="hidden" name="details[${index}][id]" value="0"><input type="hidden" name="details[${index}][id_grade]" value=""><input type="text" class="form-control text-center code-grade" readonly></td>
            <td><select name="details[${index}][id_grade_select]" class="form-control select-grade" required>${options}</select></td>
            <td><input type="text" name="details[${index}][loi]" class="form-control" placeholder="القوانين"></td>
            <td><input type="text" name="details[${index}][categorie]" class="form-control" placeholder="السلوك أو الرتبة"></td>
            ${numericHtml}
            <td><textarea name="details[${index}][observations]" class="form-control" rows="1"></textarea></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)"><i class="fas fa-trash"></i></button></td>
        </tr>
    `;
    tbody.append(row);
    tbody.find('tr:last .select-grade').select2({ placeholder: "ابحث أو اختر...", allowClear: true, width: '100%', dir: "rtl" });
}

function supprimerLigne(btn) {
    const row = $(btn).closest('tr');
    const idDetail = row.data('id-detail');
    if (idDetail && idDetail > 0) {
        if (confirm('هل أنت متأكد من حذف هذا السطر؟')) {
            $('<input>').attr({ type: 'hidden', name: 'supprimer_details[]', value: idDetail }).appendTo(row.parent());
            row.hide();
        }
    } else {
        row.remove();
    }
    calculerTotaux();
}

function calculerTotaux() {
    let totals = {};
    <?php foreach ($numeric_fields as $field): ?>
        totals['<?php echo $field; ?>'] = 0;
    <?php endforeach; ?>

    $('#tbody_details tr:visible').each(function() {
        <?php foreach ($numeric_fields as $field): ?>
            totals['<?php echo $field; ?>'] += parseFloat($(this).find('input[name*="[<?php echo $field; ?>]"]').val()) || 0;
        <?php endforeach; ?>
    });

    <?php foreach ($numeric_fields as $field): ?>
        $('#total_<?php echo $field; ?>').text(totals['<?php echo $field; ?>']);
    <?php endforeach; ?>
}

function enregistrerBrouillon() {
    const form = $('#formulaireTableau1_1')[0];
    const input = $('<input>').attr({ type: 'hidden', name: 'statut', value: 'brouillon' });
    $(form).append(input);
    if (confirm('هل تريد حفظ الملحق كمسودة؟')) {
        soumettreFormulaire(form, 'brouillon');
    } else {
        input.remove();
    }
}

document.getElementById('formulaireTableau1_1')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (confirm('هل أنت متأكد من رغبتك في حفظ الملحق؟')) {
        const selects = this.querySelectorAll('.select-grade');
        selects.forEach(select => {
            const row = select.closest('tr');
            const hidden = row.querySelector('input[name*="[id_grade]"]');
            if (hidden) hidden.value = select.value;
        });
        soumettreFormulaire(this, 'validé');
    }
});

function soumettreFormulaire(form, statut) {
    const formData = new FormData(form);
    const submitBtn = $(form).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...').prop('disabled', true);

    fetch('ajax/traitement_tab1_1.php', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => {
                    if (statut == 'brouillon') window.location.reload();
                    else window.location.href = '?action=list_tab1_1&id_tableau_1=<?php echo $id_tableau_1; ?>&success=1';
                }, 1500);
            } else {
                showMessage(data.message, 'danger');
                submitBtn.html(originalText).prop('disabled', false);
            }
        })
        .catch(() => {
            showMessage('حدث خطأ أثناء الاتصال بالخادم', 'danger');
            submitBtn.html(originalText).prop('disabled', false);
        });
}

function showMessage(msg, type) {
    const alert = $('<div class="alert alert-'+type+' alert-dismissible fade show" role="alert">'+msg+'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('.app-content .container-fluid').prepend(alert);
    setTimeout(() => alert.alert('close'), 5000);
}

function supprimerTableau(id) {
    if (confirm('هل أنت متأكد من حذف هذا الملحق؟')) {
        fetch('ajax/traitement_tab1_1.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=delete_tab1_1&id='+id })
            .then(res => res.json())
            .then(data => {
                if (data.success) { showMessage(data.message, 'success'); setTimeout(() => window.location.reload(), 1500); }
                else showMessage(data.message, 'danger');
            })
            .catch(() => showMessage('حدث خطأ أثناء الاتصال بالخادم', 'danger'));
    }
}
</script>

<?php require_once("composit/footer.php"); ?>