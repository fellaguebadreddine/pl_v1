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

// Vérifie si l'utilisateur est bien un administrateur
if ($current_user->type !== 'administrateur') {
    $session->logout();
    redirect_to('../login.php');
}

// Récupérer toutes les grades
$grades = Grade::trouve_tous();
$total_grades = Grade::compter_total();
$grades_actives = Grade::compter_actives();
?>

<?php

$titre = "إدارة الرتب";

$active_menu = "grades";

$active_submenu = "grades";

$header = array('todo');

require_once("composit/header.php");
?>
<!--end::Sidebar-->
<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">إدارة الرتب</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">الرتب</li>
                    </ol>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-12">
                    <!-- Section des Grades -->
                    <section class="py-3">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="fw-bold text-primary mb-2">
                                            <i class="fa fa-address-card me-3"></i>قائمة الرتب
                                        </h2>
                                        <p class="text-muted">إدارة وعرض جميع الرتب المسجلة في النظام</p>
                                    </div>
                                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#ajouterGradeModal">
                                        <i class="fas fa-plus me-2"></i>إضافة رتبة جديدة
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="row mb-4">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-start border-primary border-4 bg-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                                    <i class="fas fa-list-ol fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0"><?php echo $total_grades; ?></h5>
                                                <p class="text-muted mb-0">إجمالي الرتب</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-start border-success border-4 bg-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm bg-success bg-opacity-10 text-success rounded-circle">
                                                    <i class="fas fa-check-circle fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0"><?php echo $grades_actives; ?></h5>
                                                <p class="text-muted mb-0">رتب نشطة</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-start border-warning border-4 bg-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle">
                                                    <i class="fas fa-chart-bar fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0"><?php echo count(array_unique(array_column($grades, 'niveau'))); ?></h5>
                                                <p class="text-muted mb-0">مستويات مختلفة</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-start border-info border-4 bg-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                                    <i class="fas fa-layer-group fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0"><?php echo $total_grades - $grades_actives; ?></h5>
                                                <p class="text-muted mb-0">رتب غير نشطة</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table des Grades -->
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="ابحث عن رتبة...">
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active" data-filter="all">الكل</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="active">نشطة</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="inactive">غير نشطة</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="gradesTable" class="table table-hover mb-0" dir="rtl">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="50">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                                    </div>
                                                </th>
                                                <th>الرتبة</th>
                                                <th>القانون الأساسي</th>
                                                <th>الحالة</th>
                                                <th class="text-center">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach ($grades as $grade):
                                            ?>
                                            <tr class="align-middle" data-status="<?php echo $grade->actif == 1 ? 'active' : 'inactive'; ?>">
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input row-checkbox" type="checkbox" value="<?php echo $grade->id; ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-3">
                                                            <div class="avatar-initial bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="fas fa-ranking-star fs-5"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-semibold"><?php echo $grade->grade; ?></h6>
                                                            <small class="text-muted">ID: #<?php echo str_pad($grade->id, 3, '0', STR_PAD_LEFT); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo $grade->lois ?: '---'; ?>
                                                </td>
                                                <td>
                                                    <?php echo $grade->badge_etat_simple(); ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" 
                                                                class="btn btn-outline-primary" 
                                                                onclick="afficherDetailsGrade(<?php echo $grade->id; ?>)"
                                                                data-bs-toggle="tooltip" 
                                                                title="عرض التفاصيل">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-outline-success" 
                                                                onclick="modifierGrade(<?php echo $grade->id; ?>)"
                                                                data-bs-toggle="tooltip" 
                                                                title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                onclick="supprimerGrade(<?php echo $grade->id; ?>)"
                                                                data-bs-toggle="tooltip" 
                                                                title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer bg-white py-3 border-0">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <span class="me-3 text-muted">عدد النتائج:</span>
                                            <select id="rowsPerPage" class="form-select form-select-sm w-auto">
                                                <option value="5">5</option>
                                                <option value="10" selected>10</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination justify-content-end mb-0">
                                                <li class="page-item disabled">
                                                    <a class="page-link" href="#" tabindex="-1">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions en masse -->
                        <div class="mt-3 d-none" id="bulkActions">
                            <div class="card border-primary">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-primary fw-semibold me-3" id="selectedCount">0</span>
                                            <span class="text-muted">رتبة محددة</span>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-success me-2" onclick="activerSelection()">
                                                <i class="fas fa-check me-1"></i>تفعيل
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning me-2" onclick="desactiverSelection()">
                                                <i class="fas fa-ban me-1"></i>تعطيل
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="supprimerSelection()">
                                                <i class="fas fa-trash me-1"></i>حذف
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

<!-- Modal Ajouter Grade -->
<div class="modal fade" id="ajouterGradeModal" tabindex="-1" aria-labelledby="ajouterGradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <form id="formAjouterGrade" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ajouterGradeModalLabel">
                        <i class="fas fa-ranking-star me-2"></i>إضافة رتبة جديدة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Messages d'erreur/succès -->
                    <div id="messageAlertGrade" class="alert d-none"></div>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="grade" class="form-label">اسم الرتبة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="grade" name="grade" required placeholder="أدخل اسم الرتبة">
                            <div class="form-text">مثال: الرتبة الأولى، رئيس مصلحة، مهندس دولة</div>
                        </div>
                        
                        <div class="col-12">
                            <label for="description" class="form-label">الوصف</label>
                            <textarea class="form-control" id="description" name="lois" rows="3" placeholder="أدخل وصف الرتبة"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="actif" class="form-label">الحالة</label>
                            <select class="form-select" id="actif" name="actif">
                                <option value="1" selected>نشطة</option>
                                <option value="0">غير نشطة</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>ملاحظات هامة:</h6>
                                <ul class="mb-0">
                                    <li>الحقول التي تحمل علامة (<span class="text-danger">*</span>) إلزامية</li>
                                    <li>اسم الرتبة يجب أن يكون فريداً</li>
                                    <li>يمكن تعديل جميع المعلومات لاحقاً</li>
                                    <li>يجب تحديد المستوى بدقة حسب التسلسل الإداري</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnResetFormGrade">
                        <i class="fas fa-redo me-1"></i> مسح النموذج
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitGrade">
                        <i class="fas fa-save me-1"></i> حفظ الرتبة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Grade -->
<div class="modal fade" id="modifierGradeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <form id="formModifierGrade" method="POST">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>تعديل رتبة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="messageAlertModifierGrade" class="alert d-none"></div>
                    <input type="hidden" id="grade_id" name="grade_id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="grade_modifier" class="form-label">اسم الرتبة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="grade_modifier" name="grade" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="description_modifier" class="form-label">الوصف</label>
                            <textarea class="form-control" id="description_modifier" name="lois" rows="3"></textarea>
                        </div>>
                        
                        <div class="col-md-6">
                            <label for="actif_modifier" class="form-label">الحالة</label>
                            <select class="form-select" id="actif_modifier" name="actif">
                                <option value="1">نشطة</option>
                                <option value="0">غير نشطة</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">تحديث الرتبة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Style CSS additionnel -->
<style>
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-initial {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .table th {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .card.border-start {
        border-left-width: 4px !important;
    }
    
    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
</style>

<!-- JavaScript pour la page -->
<script>
// Variables globales
let selectedGrades = [];

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initTableFunctions();
    initGradeModal();
    initModifierGradeModal();
    initCheckboxSelection();
});

// Fonctions pour la table
function initTableFunctions() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('[data-filter]');
    
    // Recherche
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#gradesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    // Filtrage
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const rows = document.querySelectorAll('#gradesTable tbody tr');
            
            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const status = row.getAttribute('data-status');
                    row.style.display = (status === filter) ? '' : 'none';
                }
            });
        });
    });
    
    // Initialiser les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialiser le modal d'ajout grade
function initGradeModal() {
    const form = document.getElementById('formAjouterGrade');
    const btnReset = document.getElementById('btnResetFormGrade');
    const messageAlert = document.getElementById('messageAlertGrade');
    
    // Réinitialiser le formulaire
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            form.reset();
            messageAlert.classList.add('d-none');
        });
    }
    
    // Soumission du formulaire
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Désactiver le bouton
            const submitBtn = document.getElementById('btnSubmitGrade');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...';
            submitBtn.disabled = true;
            
            // Soumettre via AJAX
            const formData = new FormData(form);
            
            fetch('ajax/traitement_grades.php?action=ajouter', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success', 'messageAlertGrade');
                    
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('ajouterGradeModal'));
                        if (modal) {
                            modal.hide();
                        }
                        location.reload();
                    }, 1500);
                } else {
                    showMessage(data.message, 'danger', 'messageAlertGrade');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ أثناء حفظ البيانات', 'danger', 'messageAlertGrade');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}

// Initialiser le modal de modification
function initModifierGradeModal() {
    const form = document.getElementById('formModifierGrade');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch('ajax/traitement_grades.php?action=modifier', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success', 'messageAlertModifierGrade');
                    
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modifierGradeModal'));
                        if (modal) {
                            modal.hide();
                        }
                        location.reload();
                    }, 1500);
                } else {
                    showMessage(data.message, 'danger', 'messageAlertModifierGrade');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ أثناء تحديث البيانات', 'danger', 'messageAlertModifierGrade');
            });
        });
    }
}

// Gestion de la sélection multiple
function initCheckboxSelection() {
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            updateBulkActions();
        });
    }
    
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;
        
        selectedGrades = Array.from(checkedBoxes).map(cb => cb.value);
        
        if (selectedCount) {
            selectedCount.textContent = count;
        }
        
        if (bulkActions) {
            if (count > 0) {
                bulkActions.classList.remove('d-none');
            } else {
                bulkActions.classList.add('d-none');
            }
        }
        
        if (selectAll) {
            selectAll.checked = (count === rowCheckboxes.length) && (rowCheckboxes.length > 0);
        }
    }
}

// Fonctions d'action
function afficherDetailsGrade(id) {
    fetch(`ajax/traitement_grades.php?action=details&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalHTML = `
                    <div class="modal fade" id="detailsGradeModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content" dir="rtl">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-info-circle me-2"></i>تفاصيل الرتبة
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center mb-4">
                                            <div class="avatar avatar-xxl bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;">
                                                <i class="fas fa-ranking-star fa-3x"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <h4 class="text-primary mb-3">${data.grade}</h4>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <strong><i class="fas fa-id-badge me-2"></i>رقم التعريف:</strong>
                                                    <p class="mt-1 text-dark">#${String(data.id).padStart(3, '0')}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong><i class="fas fa-toggle-on me-2"></i>الحالة:</strong>
                                                    <p class="mt-1 text-dark">
                                                        <span class="badge bg-${data.actif == 1 ? 'success' : 'danger'}">
                                                            ${data.actif == 1 ? 'نشطة' : 'غير نشطة'}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong><i class="fas fa-calendar-alt me-2"></i>تاريخ الإضافة:</strong>
                                                    <p class="mt-1 text-dark">${data.date_creation_formatted}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <strong><i class="fas fa-history me-2"></i>آخر تعديل:</strong>
                                                    <p class="mt-1 text-dark">${data.date_modif_formatted}</p>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <strong><i class="fas fa-align-left me-2"></i>الوصف:</strong>
                                                    <p class="mt-1 text-dark">${data.lois || '---'}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                    <button type="button" class="btn btn-warning" onclick="modifierGrade(${data.id})">
                                        <i class="fas fa-edit me-1"></i>تعديل
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                if (!document.getElementById('detailsGradeModal')) {
                    document.body.insertAdjacentHTML('beforeend', modalHTML);
                }
                
                const modal = new bootstrap.Modal(document.getElementById('detailsGradeModal'));
                modal.show();
            } else {
                alert(data.message);
            }
        });
}

function modifierGrade(id) {
    // Charger les données de la grade
    fetch(`ajax/traitement_grades.php?action=details&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('grade_id').value = data.id;
                document.getElementById('grade_modifier').value = data.grade;
                document.getElementById('description_modifier').value = data.description || '';
                document.getElementById('niveau_modifier').value = data.niveau;
                document.getElementById('actif_modifier').value = data.actif;
                
                const modal = new bootstrap.Modal(document.getElementById('modifierGradeModal'));
                modal.show();
            } else {
                alert(data.message);
            }
        });
}

function supprimerGrade(id) {
    if (confirm('هل أنت متأكد من حذف هذه الرتبة؟ لا يمكن التراجع عن هذه العملية.')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('ajax/traitement_grades.php?action=supprimer', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء الاتصال بالخادم');
        });
    }
}

// Actions en masse
function activerSelection() {
    if (selectedGrades.length === 0) return;
    
    if (confirm(`هل تريد تفعيل ${selectedGrades.length} رتبة؟`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(selectedGrades));
        
        fetch('ajax/traitement_grades.php?action=activer_selection', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function desactiverSelection() {
    if (selectedGrades.length === 0) return;
    
    if (confirm(`هل تريد تعطيل ${selectedGrades.length} رتبة؟`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(selectedGrades));
        
        fetch('ajax/traitement_grades.php?action=desactiver_selection', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

function supprimerSelection() {
    if (selectedGrades.length === 0) return;
    
    if (confirm(`هل أنت متأكد من حذف ${selectedGrades.length} رتبة؟ لا يمكن التراجع عن هذه العملية.`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(selectedGrades));
        
        fetch('ajax/traitement_grades.php?action=supprimer_selection', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

// Fonction utilitaire pour afficher les messages
function showMessage(message, type, elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = message;
        element.className = `alert alert-${type}`;
        element.classList.remove('d-none');
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

// Réinitialiser les modals quand ils sont fermés
document.getElementById('ajouterGradeModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('formAjouterGrade')?.reset();
    document.getElementById('messageAlertGrade')?.classList.add('d-none');
});

document.getElementById('modifierGradeModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('formModifierGrade')?.reset();
    document.getElementById('messageAlertModifierGrade')?.classList.add('d-none');
});
</script>

<!--begin::Footer-->
<?php require_once("composit/footer.php"); ?>