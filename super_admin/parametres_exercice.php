<?php
// exercices.php
require_once('../includes/initialiser.php');

// Vérification de l'utilisateur (super admin)
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'super_admin') {
    $session->logout();
    redirect_to('../login.php');
}

// Récupérer tous les exercices (tri décroissant)
$exercices = Exercice::trouve_tous('DESC');
$exercice_actif = Exercice::get_exercice_actif();

$titre = "إدارة السنوات المالية";
$active_menu = "parametres";
$active_submenu = "exercices";
$header = array(); // pas de librairies spécifiques, mais on peut inclure si besoin
require_once("composit/header.php");
?>

<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>إدارة السنوات المالية</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="parametres.php">الإعدادات</a></li>
                        <li class="breadcrumb-item active">السنوات المالية</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
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
                        | تم التمديد إلى: <strong><?php echo date('d/m/Y', strtotime($exercice_actif->date_fin)); ?></strong>
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
                                        <td colspan="6" class="text-center text-muted py-4">
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
                                            $statut_text = 'غير معروف';
                                            if ($exercice->statut == 'ouvert') { $badge_class = 'success'; $statut_text = 'مفتوح'; }
                                            if ($exercice->statut == 'ferme') { $badge_class = 'danger'; $statut_text = 'مغلق'; }
                                            if ($exercice->statut == 'prolongation') { $badge_class = 'warning'; $statut_text = 'تمديد'; }
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>"><?php echo $statut_text; ?></span>
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
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="prolongerExercice(<?php echo $exercice->id; ?>)"
                                                        title="تمديد">
                                                    <i class="fas fa-clock"></i> تمديد
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
</main>

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
                    <div id="messageAlertAjouter" class="alert d-none"></div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>ملاحظة:</strong> عند فتح سنة مالية جديدة، ستتمكن جميع المؤسسات من تقديم نماذجها خلال الفترة المحددة.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="annee" class="form-label">السنة المالية <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="annee" name="annee" required 
                                   min="2000" max="2100" value="<?php echo date('Y'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="date_debut" class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                        </div>
                        <div class="col-md-6">
                            <label for="date_fin" class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                        </div>
                        <div class="col-12">
                            <label for="commentaire" class="form-label">ملاحظات</label>
                            <textarea class="form-control" id="commentaire" name="commentaire" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">فتح السنة</button>
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
                    <div class="mb-3">
                        <label for="nouvelle_date_fin" class="form-label">التاريخ الجديد للنهاية <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="nouvelle_date_fin" name="nouvelle_date_fin" required>
                    </div>
                    <div class="mb-3">
                        <label for="commentaire_prolongation" class="form-label">سبب التمديد</label>
                        <textarea class="form-control" id="commentaire_prolongation" name="commentaire" rows="3"></textarea>
                    </div>
                    <div id="societesManquantes" class="mt-3"></div>
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
    // Initialisation des dates pour le formulaire d'ajout
    const aujourdhui = new Date();
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    if (dateDebut) {
        // Date min = aujourd'hui + 1 jour
        const demain = new Date(aujourdhui);
        demain.setDate(demain.getDate() + 1);
        dateDebut.min = demain.toISOString().split('T')[0];
        // Valeur par défaut : aujourd'hui + 7 jours
        const dans7jours = new Date(aujourdhui);
        dans7jours.setDate(dans7jours.getDate() + 7);
        dateDebut.value = dans7jours.toISOString().split('T')[0];
        dateDebut.dispatchEvent(new Event('change'));
    }
    dateDebut.addEventListener('change', function() {
        if (this.value) {
            const debut = new Date(this.value);
            const minFin = new Date(debut);
            minFin.setDate(minFin.getDate() + 1);
            dateFin.min = minFin.toISOString().split('T')[0];
            // Défaut : +30 jours
            const defautFin = new Date(debut);
            defautFin.setDate(defautFin.getDate() + 30);
            dateFin.value = defautFin.toISOString().split('T')[0];
        }
    });

    // Gestionnaire du formulaire d'ajout
    document.getElementById('formAjouterExercice').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'ajouter');
        fetch('ajax/traitement_exercices.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('messageAlertAjouter', data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('messageAlertAjouter', data.message, 'danger');
            }
        })
        .catch(() => showMessage('messageAlertAjouter', 'حدث خطأ في الاتصال', 'danger'));
    });

    // Gestionnaire du formulaire de prolongation
    document.getElementById('formProlongerExercice').addEventListener('submit', function(e) {
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
                showMessage('messageAlertProlonger', data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('messageAlertProlonger', data.message, 'danger');
            }
        })
        .catch(() => showMessage('messageAlertProlonger', 'حدث خطأ في الاتصال', 'danger'));
    });
});

function showMessage(elementId, message, type) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = message;
        el.className = `alert alert-${type}`;
        el.classList.remove('d-none');
        // Faire défiler jusqu'au message
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function prolongerExercice(id) {
    document.getElementById('exercice_id_prolonger').value = id;
    document.getElementById('nouvelle_date_fin').value = '';
    document.getElementById('commentaire_prolongation').value = '';
    document.getElementById('societesManquantes').innerHTML = '';
    new bootstrap.Modal(document.getElementById('prolongerExerciceModal')).show();
}

function fermerExercice(id) {
    if (!confirm('هل تريد إغلاق هذه السنة المالية؟ سيتم التحقق من تقديم جميع المؤسسات.')) return;
    const formData = new FormData();
    formData.append('action', 'fermer');
    formData.append('id', id);
    formData.append('commentaire', '');
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
            // Afficher le modal de prolongation avec la liste des sociétés manquantes
            document.getElementById('exercice_id_prolonger').value = id;
            if (data.societes_manquantes && data.societes_manquantes.length > 0) {
                let html = '<h6>المؤسسات التي لم تقدم:</h6><ul class="list-group">';
                data.societes_manquantes.forEach(s => {
                    html += `<li class="list-group-item">${s.nom}</li>`;
                });
                html += '</ul>';
                document.getElementById('societesManquantes').innerHTML = html;
            }
            new bootstrap.Modal(document.getElementById('prolongerExerciceModal')).show();
        } else {
            alert(data.message);
        }
    })
    .catch(() => alert('حدث خطأ في الاتصال'));
}

function reouvrirExercice(id) {
    if (!confirm('هل تريد إعادة فتح هذه السنة المالية؟')) return;
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
    .catch(() => alert('حدث خطأ في الاتصال'));
}

function supprimerExercice(id) {
    if (!confirm('هل أنت متأكد من حذف هذه السنة المالية؟')) return;
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
    })
    .catch(() => alert('حدث خطأ في الاتصال'));
}

function modifierExercice(id) {
    // Optionnel : à implémenter si nécessaire
    alert('وظيفة التعديل قيد التطوير');
}
</script>

<?php require_once("composit/footer.php"); ?>