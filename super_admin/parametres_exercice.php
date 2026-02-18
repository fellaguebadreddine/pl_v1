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

// Vérifie si l'utilisateur est bien un super administrateur
if ($current_user->type !== 'super_admin') {
    $session->logout();
    redirect_to('../login.php');
}

// Récupérer tous les exercices
$exercices = Exercice::trouve_tous('DESC');
$exercice_actif = Exercice::get_exercice_actif();

// Traiter les actions POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'ajouter':
            // Traitement sera fait via AJAX
            break;
        case 'fermer':
            // Traitement sera fait via AJAX
            break;
        case 'prolonger':
            // Traitement sera fait via AJAX
            break;
    }
}
?>

<?php
$titre = "إدارة السنوات المالية";
$active_menu = "parametres";
$active_submenu = "exercices";
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
                    <h3 class="mb-0">إدارة السنوات المالية</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="parametres.php">الإعدادات</a></li>
                        <li class="breadcrumb-item active" aria-current="page">السنوات المالية</li>
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
                    <!-- Bannière de l'exercice actif -->
                    <?php if ($exercice_actif): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5><i class="fas fa-calendar-check me-2"></i> تمرين نشط حاليا</h5>
                        <p class="mb-1">
                            السنة: <strong><?php echo $exercice_actif->annee; ?></strong> | 
                            الفترة: من <strong><?php echo date('d/m/Y', strtotime($exercice_actif->date_debut)); ?></strong> 
                            إلى <strong><?php echo date('d/m/Y', strtotime($exercice_actif->date_fin)); ?></strong>
                        </p>
                        <p class="mb-0">
                            الحالة: <span class="badge bg-success"><?php echo $exercice_actif->statut == 'prolongation' ? 'تمديد' : 'مفتوح'; ?></span>
                            <?php if ($exercice_actif->statut == 'prolongation'): ?>
                                | تم التمديد إلى: <strong><?php echo date('d/m/Y', strtotime($exercice_actif->periode_extension)); ?></strong>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h5><i class="fas fa-calendar-times me-2"></i> لا يوجد تمرين نشط</h5>
                        <p class="mb-0">حاليا، لا توجد سنة مالية نشطة. يرجى فتح سنة مالية جديدة للسماح للمؤسسات بتقديم نماذجها.</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Section des Exercices -->
                    <section class="py-3">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="fw-bold text-primary mb-2">
                                            <i class="fa fa-calendar me-3"></i>السنوات المالية 
                                        </h2>
                                        <p class="text-muted">إدارة وعرض جميع السنوات المالية للمؤسسات</p>
                                    </div>
                                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#ajouterExerciceModal" 
                                        <?php echo $exercice_actif ? 'disabled title="يوجد تمرين نشط. يجب إغلاقه أولا"' : ''; ?>>
                                        <i class="fas fa-plus me-2"></i>إضافة سنة مالية
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tableau des exercices -->
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>السنة</th>
                                                <th>الفترة</th>
                                                <th>المدة</th>
                                                <th>الحالة</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($exercices)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="fas fa-calendar-alt fa-2x mb-3"></i><br>
                                                    لا توجد سنوات مالية مسجلة بعد
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                            <?php foreach ($exercices as $index => $exercice): ?>
                                            <tr class="<?php echo $exercice->id == ($exercice_actif ? $exercice_actif->id : 0) ? 'table-success' : ''; ?>">
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <strong><?php echo $exercice->annee; ?></strong>
                                                    <?php if ($exercice->id == ($exercice_actif ? $exercice_actif->id : 0)): ?>
                                                    <span class="badge bg-success ms-2">نشط</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($exercice->date_debut)); ?> - 
                                                    <?php echo date('d/m/Y', strtotime($exercice->date_fin)); ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $debut = new DateTime($exercice->date_debut);
                                                    $fin = new DateTime($exercice->date_fin);
                                                    $interval = $debut->diff($fin);
                                                    echo $interval->days + 1 . ' يوم';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $badge_class = 'secondary';
                                                    if ($exercice->statut == 'ouvert') $badge_class = 'success';
                                                    if ($exercice->statut == 'ferme') $badge_class = 'danger';
                                                    if ($exercice->statut == 'prolongation') $badge_class = 'warning';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php 
                                                        $statut_text = array(
                                                            'ouvert' => 'مفتوح',
                                                            'ferme' => 'مغلق',
                                                            'prolongation' => 'تمديد'
                                                        );
                                                        echo $statut_text[$exercice->statut] ?? $exercice->statut;
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="modifierExercice(<?php echo $exercice->id; ?>)"
                                                                title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        
                                                        <?php if ($exercice->statut == 'ouvert' || $exercice->statut == 'prolongation'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                onclick="fermerExercice(<?php echo $exercice->id; ?>)"
                                                                title="إغلاق">
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($exercice->statut == 'ferme'): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                onclick="reouvrirExercice(<?php echo $exercice->id; ?>)"
                                                                title="إعادة الفتح">
                                                            <i class="fas fa-unlock"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="supprimerExercice(<?php echo $exercice->id; ?>)"
                                                                title="حذف"
                                                                <?php echo $exercice->id == ($exercice_actif ? $exercice_actif->id : 0) ? 'disabled' : ''; ?>>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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

<!-- Modal Ajouter Exercice -->
<div class="modal fade" id="ajouterExerciceModal" tabindex="-1" aria-labelledby="ajouterExerciceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <form id="formAjouterExercice">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ajouterExerciceModalLabel">
                        <i class="fas fa-calendar-plus me-2"></i>فتح سنة مالية جديدة
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div id="messageAlertExercice" class="alert d-none"></div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>ملاحظة:</strong> عند فتح سنة مالية جديدة، ستتمكن جميع المؤسسات من تقديم نماذجها خلال الفترة المحددة.
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="annee" class="form-label">السنة المالية <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="annee" name="annee" required 
                                   min="2000" max="2100" value="<?php echo date('Y'); ?>">
                            <div class="form-text">أدخل السنة المالية (مثال: 2024)</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="statut" class="form-label">الحالة الأولية</label>
                            <select class="form-select" id="statut" name="statut" disabled>
                                <option value="ouvert" selected>مفتوح (تلقائيا)</option>
                            </select>
                            <div class="form-text">سيتم فتح السنة المالية تلقائيا</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="date_debut" class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" required
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="date_fin" class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                        </div>
                        
                        <div class="col-12">
                            <label for="commentaire" class="form-label">ملاحظات</label>
                            <textarea class="form-control" id="commentaire" name="commentaire" rows="3" 
                                      placeholder="ملاحظات حول السنة المالية..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitExercice">
                        <i class="fas fa-calendar-plus me-1"></i> فتح السنة المالية
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Prolonger Exercice -->
<div class="modal fade" id="prolongerExerciceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <form id="formProlongerExercice">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-clock me-2"></i>تمديد سنة مالية
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="messageAlertProlonger" class="alert d-none"></div>
                    <input type="hidden" id="exercice_id_prolonger" name="exercice_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>تحذير:</strong> بعض المؤسسات لم تقم بتقديم نماذجها بعد. هل ترغب في تمديد الفترة؟
                    </div>
                    
                    <div class="mb-3">
                        <label for="nouvelle_date_fin" class="form-label">التاريخ الجديد للنهاية <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="nouvelle_date_fin" name="nouvelle_date_fin" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="commentaire_prolongation" class="form-label">سبب التمديد</label>
                        <textarea class="form-control" id="commentaire_prolongation" name="commentaire" rows="3" 
                                  placeholder="سبب تمديد الفترة..."></textarea>
                    </div>
                    
                    <div id="societesManquantes" class="mt-3">
                        <!-- Les sociétés manquantes seront ajoutées ici dynamiquement -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">تمديد الفترة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les dates
    const aujourdhui = new Date();
    const dateDebutInput = document.getElementById('date_debut');
    const dateFinInput = document.getElementById('date_fin');
    
    if (dateDebutInput) {
        // Définir min à aujourd'hui + 1 jour
        const demain = new Date(aujourdhui);
        demain.setDate(demain.getDate() + 1);
        dateDebutInput.min = demain.toISOString().split('T')[0];
        
        // Définir une date par défaut (dans 7 jours)
        const dans7jours = new Date(aujourdhui);
        dans7jours.setDate(dans7jours.getDate() + 7);
        dateDebutInput.value = dans7jours.toISOString().split('T')[0];
        
        // Mettre à jour date_fin quand date_debut change
        dateDebutInput.addEventListener('change', function() {
            if (this.value) {
                const dateDebut = new Date(this.value);
                const dateFinMin = new Date(dateDebut);
                dateFinMin.setDate(dateFinMin.getDate() + 1);
                dateFinInput.min = dateFinMin.toISOString().split('T')[0];
                
                // Définir date_fin par défaut à date_debut + 30 jours
                const dateFinDefaut = new Date(dateDebut);
                dateFinDefaut.setDate(dateFinDefaut.getDate() + 30);
                dateFinInput.value = dateFinDefaut.toISOString().split('T')[0];
            }
        });
        
        // Déclencher l'événement change pour initialiser
        dateDebutInput.dispatchEvent(new Event('change'));
    }
    
    // Gérer l'ajout d'exercice
    const formAjouter = document.getElementById('formAjouterExercice');
    if (formAjouter) {
        formAjouter.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnSubmit = document.getElementById('btnSubmitExercice');
            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الفتح...';
            btnSubmit.disabled = true;
            
            const formData = new FormData(this);
            formData.append('action', 'ajouter');
            
            fetch('ajax/traitement_exercices.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success', 'messageAlertExercice');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage(data.message, 'danger', 'messageAlertExercice');
                    btnSubmit.innerHTML = originalText;
                    btnSubmit.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ أثناء فتح السنة المالية', 'danger', 'messageAlertExercice');
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
            });
        });
    }
    
    // Gérer la prolongation d'exercice
    const formProlonger = document.getElementById('formProlongerExercice');
    if (formProlonger) {
        formProlonger.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'prolonger');
            
            fetch('ajax/traitement_exercices.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success', 'messageAlertProlonger');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage(data.message, 'danger', 'messageAlertProlonger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ أثناء التمديد', 'danger', 'messageAlertProlonger');
            });
        });
    }
});

// Fonction pour fermer un exercice
function fermerExercice(id) {
    if (confirm('هل تريد إغلاق هذه السنة المالية؟ سيتم التحقق من تقديم جميع المؤسسات.')) {
        const formData = new FormData();
        formData.append('action', 'fermer');
        formData.append('id', id);
        
        fetch('ajax/traitement_exercices.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else if (data.need_extension) {
                // Afficher modal de prolongation
                document.getElementById('exercice_id_prolonger').value = id;
                
                // Afficher les sociétés manquantes
                const societesDiv = document.getElementById('societesManquantes');
                if (data.societes_manquantes && data.societes_manquantes.length > 0) {
                    let html = '<h6>المؤسسات التي لم تقدم:</h6><ul class="list-group">';
                    data.societes_manquantes.forEach(societe => {
                        html += `<li class="list-group-item">${societe.nom}</li>`;
                    });
                    html += '</ul>';
                    societesDiv.innerHTML = html;
                }
                
                // Ouvrir modal
                const modal = new bootstrap.Modal(document.getElementById('prolongerExerciceModal'));
                modal.show();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء محاولة الإغلاق');
        });
    }
}

// Fonction pour réouvrir un exercice
function reouvrirExercice(id) {
    if (confirm('هل تريد إعادة فتح هذه السنة المالية؟')) {
        const formData = new FormData();
        formData.append('action', 'reouvrir');
        formData.append('id', id);
        
        fetch('ajax/traitement_exercices.php', {
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
            alert('حدث خطأ أثناء محاولة إعادة الفتح');
        });
    }
}

// Fonction pour modifier un exercice
function modifierExercice(id) {
    fetch(`ajax/traitement_exercices.php?action=details&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ouvrir modal de modification
                alert('هذه الخاصية قيد التطوير');
            } else {
                alert(data.message);
            }
        });
}

// Fonction pour supprimer un exercice
function supprimerExercice(id) {
    if (confirm('هل أنت متأكد من حذف هذه السنة المالية؟ لا يمكن التراجع عن هذه العملية.')) {
        const formData = new FormData();
        formData.append('action', 'supprimer');
        formData.append('id', id);
        
        fetch('ajax/traitement_exercices.php', {
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
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Réinitialiser les modals
document.getElementById('ajouterExerciceModal')?.addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('formAjouterExercice');
    const alert = document.getElementById('messageAlertExercice');
    if (form) form.reset();
    if (alert) alert.classList.add('d-none');
});

document.getElementById('prolongerExerciceModal')?.addEventListener('hidden.bs.modal', function () {
    const form = document.getElementById('formProlongerExercice');
    const alert = document.getElementById('messageAlertProlonger');
    if (form) form.reset();
    if (alert) alert.classList.add('d-none');
    document.getElementById('societesManquantes').innerHTML = '';
});
</script>

<?php require_once("composit/footer.php"); ?>