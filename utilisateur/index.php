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

// Vérifie si l'utilisateur est bien un utilisateur (admin de société)
if ($current_user->type !== 'utilisateur') {
    $session->logout();
    redirect_to('../login.php');
}

// Récupérer la société de l'utilisateur
if (!$current_user->id_societe) {
    $session->logout();
    redirect_to('../login.php');
}

$societe = Societe::trouve_par_id($current_user->id_societe);

if (!$societe) {
    $session->logout();
    redirect_to('../login.php');
}
$exercice_actif = Exercice::get_exercice_actif();
?>

<?php
// Déterminer l'action
$action = isset($_GET['action']) ? $_GET['action'] : '';
$titre = "لوحة التحكم - " . $societe->raison_ar;

$active_menu = "dashboard";

$active_submenu = "dashboard";

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
                    <h3 class="mb-0">لوحة التحكم</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $societe->raison_ar; ?></li>
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
                <!-- Bienvenue Section -->
                <div class="col-12 mb-4">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h3 class="mb-2">مرحباً، <?php echo $current_user->prenom . ' ' . $current_user->nom; ?>! </h3>
                                    <p class="mb-0 ">
                                        أنت مسؤول عن مؤسسة <strong><?php echo $societe->raison_ar; ?></strong>. 
                                        يمكنك إدارة جميع العمليات المتعلقة بمؤسستك من خلال هذه اللوحة.
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="avatar avatar-xxl">
                                        <?php if($societe->logo): ?>
                                            <img src="../admin/uploads/logos/<?php echo $societe->logo; ?>" 
                                                 alt="<?php echo $societe->raison_ar; ?>" 
                                                 class="rounded-circle" 
                                                 style="width: 100px; height: 100px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="avatar-initial bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                                <i class="fas fa-building fa-3x"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- Section d'information sur l'exercice -->
                  <!--end::App Content Header-->
                <div class="app-content">
                    <!--begin::Container-->
                    <div class="container-fluid">
                        <!--begin::Row-->
                        <div class="row">
                            <!-- Alertes Importantes -->
                            <?php if (!empty($alertes_importantes)): ?>
                            <div class="col-12 mb-4">
                                <?php foreach ($alertes_importantes as $alerte): ?>
                                <div class="alert alert-<?php echo $alerte['type']; ?> alert-dismissible fade show" role="alert">
                                    <h5 class="alert-heading">
                                        <i class="fas <?php echo $alerte['icon']; ?> me-2"></i>
                                        <?php echo $alerte['message']; ?>
                                    </h5>
                                    <?php if ($alerte['type'] == 'warning' && $periode_saisie['autorise'] && !$formulaire_soumis): ?>
                                    <div class="mt-2">
                                        <a href="formulaire.php?exercice=<?php echo $exercice_actif->id; ?>" class="btn btn-outline-<?php echo $alerte['type']; ?> btn-sm">
                                            <i class="fas fa-file-upload me-1"></i> تقديم النموذج الآن
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Carte d'état de l'exercice -->
                            <?php if ($exercice_actif): ?>
                            <div class="col-12 mb-4">
                                <div class="card <?php echo $formulaire_soumis ? 'border-success' : 'border-primary'; ?>">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calendar-check me-2 text-primary"></i>حالة السنة المالية الحالية
                                        </h5>
                                        <span class="badge bg-<?php echo $formulaire_soumis ? 'success' : ($exercice_actif->statut == 'prolongation' ? 'warning' : 'primary'); ?>">
                                            <?php echo $formulaire_soumis ? 'تم التقديم' : ($exercice_actif->statut == 'prolongation' ? 'تمديد' : 'مفتوح'); ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-3">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">السنة المالية</h6>
                                                        <p class="text-muted mb-0"><?php echo $exercice_actif->annee; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle me-3">
                                                        <i class="fas fa-calendar-day"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">الفترة</h6>
                                                        <p class="text-muted mb-0">
                                                            <?php echo date('d/m/Y', strtotime($exercice_actif->date_debut)); ?> - 
                                                            <?php echo date('d/m/Y', strtotime($exercice_actif->date_fin)); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar avatar-sm <?php echo $formulaire_soumis ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning'; ?> rounded-circle me-3">
                                                        <i class="fas <?php echo $formulaire_soumis ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">حالة النموذج</h6>
                                                        <p class="text-muted mb-0">
                                                            
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Barre de progression -->
                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>مدة التقديم</span>
                                                <?php
                                                $date_debut = new DateTime($exercice_actif->date_debut);
                                                $date_fin = new DateTime($exercice_actif->date_fin);
                                                $aujourdhui = new DateTime();
                                                $total_jours = $date_debut->diff($date_fin)->days + 1;
                                                $jours_ecoules = $date_debut->diff($aujourdhui)->days;
                                                $pourcentage = min(100, max(0, ($jours_ecoules / $total_jours) * 100));
                                                ?>
                                                <span><?php echo round($pourcentage, 0); ?>%</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar <?php echo $pourcentage > 80 ? 'bg-danger' : 'bg-success'; ?>" 
                                                    role="progressbar" 
                                                    style="width: <?php echo $pourcentage; ?>%"
                                                    aria-valuenow="<?php echo $pourcentage; ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100"></div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <small>البداية: <?php echo date('d/m/Y', strtotime($exercice_actif->date_debut)); ?></small>
                                                <small>النهاية: <?php echo date('d/m/Y', strtotime($exercice_actif->date_fin)); ?></small>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="mt-4 d-flex justify-content-between">
                                            <div>
                                                <div class="mt-3 text-center">
                                                <div class="alert alert-info mb-0">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>ملاحظة:</strong> الرجاء تعبئة جميع النماذج المطلوبة قبل تاريخ 
                                                    <strong><?php echo date('d/m/Y', strtotime($exercice_actif->date_fin)); ?></strong>
                                                </div>
                                            </div>
                                            </div>
                                            
                                            <?php if (!$exercice_actif): ?>
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    <?php
                                                    $jours_restants = $aujourdhui->diff($date_fin)->days;
                                                    if ($jours_restants > 0) {
                                                        echo "متبقي $jours_restants يوم";
                                                    } else {
                                                        echo "انتهت المهلة";
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-12 mb-4">
                                <div class="card shadow-sm border-0">
                                    <div class="card-body text-center py-4">

                                        <div class="mb-3">
                                            <i class="fas fa-hourglass-end text-danger" style="font-size:48px;"></i>
                                        </div>

                                        <h5 class="fw-bold text-danger mb-2">
                                            انتهت مهلة تقديم السنة المالية
                                        </h5>

                                        <p class="text-muted mb-3">
                                            لم يعد بإمكانكم إرسال النماذج الخاصة بهذه السنة المالية.
                                        </p>

                                        <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                            السنة المالية مغلقة
                                        </span>

                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>



               
                <!-- Informations de la Société -->
                <div class="col-lg-8 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-building me-2 text-primary"></i>معلومات المؤسسة
                                </h5>
                                <button class="btn btn-sm btn-outline-primary" onclick="modifierSociete()">
                                    <i class="fas fa-edit me-1"></i> تعديل المعلومات
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-signature me-2"></i>الاسم بالعربية:</strong>
                                    <p class="mt-1 text-dark"><?php echo $societe->raison_ar; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-signature me-2"></i>الاسم بالفرنسية:</strong>
                                    <p class="mt-1 text-dark"><?php echo $societe->raison_fr; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-map-marker-alt me-2"></i>العنوان:</strong>
                                    <p class="mt-1 text-dark"><?php echo $societe->adresse_ar; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-flag me-2"></i>الولاية:</strong>
                                    <p class="mt-1 text-dark"><?php if (isset($societe->wilaya)){$ville = Wilayas::trouve_par_id($societe->wilaya); echo $ville->nom;} ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-barcode me-2"></i>الرمز البريدي:</strong>
                                    <p class="mt-1 text-dark"><?php echo $societe->postal ?: '---'; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-phone me-2"></i>الهاتف:</strong>
                                    <p class="mt-1 text-dark"><?php echo $societe->tel1; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-envelope me-2"></i>البريد الإلكتروني:</strong>
                                    <p class="mt-1 text-dark"><?php echo $societe->email ?: '---'; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong><i class="fas fa-calendar-alt me-2"></i>السنة المالية:</strong>
                                    <p class="mt-1 text-dark">من <?php echo $societe->exercice_debut; ?> إلى <?php echo $societe->exercice_fin; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Rapides -->
                <div class="col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2 text-warning"></i>إجراءات سريعة
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">                                
                                <button class="btn btn-outline-success text-start" onclick="window.location.href='ajouter_employe.php'">
                                    <i class="fas fa-user-plus me-2"></i> إضافة موظف جديد
                                </button>
                                
                                <button class="btn btn-outline-info text-start" onclick="window.location.href='documents.php'">
                                    <i class="fas fa-folder me-2"></i> المستندات والملفات
                                </button>
                                
                                <button class="btn btn-outline-warning text-start" onclick="modifierProfil()">
                                    <i class="fas fa-user-edit me-2"></i> تعديل الملف الشخصي
                                </button>
                                
                                <button class="btn btn-outline-secondary text-start" onclick="changerMotPasse()">
                                    <i class="fas fa-key me-2"></i> تغيير كلمة المرور
                                </button>
                                
                                <button class="btn btn-outline-danger text-start" onclick="window.location.href='../logout.php'">
                                    <i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations du Compte -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-circle me-2 text-success"></i>معلومات حسابك
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center mb-4">
                                    <?php if($current_user->photo): ?>
                                        <img src="../uploads/photos/<?php echo $current_user->photo; ?>" 
                                             alt="<?php echo $current_user->prenom . ' ' . $current_user->nom; ?>" 
                                             class="img-fluid rounded-circle border" 
                                             style="width: 120px; height: 120px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                             style="width: 120px; height: 120px;">
                                            <i class="fas fa-user fa-3x text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="mt-3 mb-0"><?php echo $current_user->prenom . ' ' . $current_user->nom; ?></h5>
                                    <p class="text-muted"><?php echo $current_user->user; ?></p>
                                </div>
                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-envelope me-2"></i>البريد الإلكتروني:</strong>
                                            <p class="mt-1 text-dark"><?php echo $current_user->email ?: '---'; ?></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-phone me-2"></i>رقم الهاتف:</strong>
                                            <p class="mt-1 text-dark"><?php echo $current_user->telephone ?: '---'; ?></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-mobile-alt me-2"></i>رقم الجوال:</strong>
                                            <p class="mt-1 text-dark"><?php echo $current_user->mobile ?: '---'; ?></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-map-marker-alt me-2"></i>العنوان:</strong>
                                            <p class="mt-1 text-dark"><?php echo $current_user->adresse ?: '---'; ?></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-calendar-alt me-2"></i>تاريخ التسجيل:</strong>
                                            <p class="mt-1 text-dark"><?php echo date('d/m/Y', strtotime($current_user->date_creation)); ?></p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="fas fa-history me-2"></i>آخر دخول:</strong>
                                            <p class="mt-1 text-dark">
                                                <?php echo $current_user->date_der ? date('d/m/Y H:i', strtotime($current_user->date_der)) : 'لم يسجل دخول من قبل'; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

<!-- Modal Changer Mot de Passe -->
<div class="modal fade" id="changerMotPasseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <form id="formChangerMotPasse" method="POST">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>تغيير كلمة المرور
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="messageAlertMotPasse" class="alert d-none"></div>
                    <div class="mb-3">
                        <label for="ancien_motpasse" class="form-label">كلمة المرور الحالية <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="ancien_motpasse" name="ancien_motpasse" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('ancien_motpasse')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nouveau_motpasse" class="form-label">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="nouveau_motpasse" name="nouveau_motpasse" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('nouveau_motpasse')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">يجب أن تتكون كلمة المرور من 8 أحرف على الأقل</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmer_motpasse" class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirmer_motpasse" name="confirmer_motpasse" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmer_motpasse')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">تغيير كلمة المرور</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Style CSS additionnel -->
<style>
    .card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .card.bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-xxl {
        width: 100px;
        height: 100px;
    }
    
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.8em;
    }
    
    .btn-outline-primary, .btn-outline-success, .btn-outline-info, 
    .btn-outline-warning, .btn-outline-secondary, .btn-outline-danger {
        border-width: 2px;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }
    
    .border-start {
        border-left-width: 4px !important;
    }
</style>

<!-- JavaScript pour la page -->
<script>
// Fonction pour modifier المؤسسة
function modifierSociete() {
    window.location.href = 'modifier_societe.php?id=<?php echo $societe->id_societe; ?>';
}

// Fonction pour تعديل الملف الشخصي
function modifierProfil() {
    window.location.href = 'profil.php';
}

// Fonction pour تغيير كلمة المرور
function changerMotPasse() {
    const modal = new bootstrap.Modal(document.getElementById('changerMotPasseModal'));
    modal.show();
}

// Afficher/Masquer mot de passe
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    initPasswordModal();
    updateClock();
    setInterval(updateClock, 60000); // Update every minute
});

// Initialiser le modal de changement de mot de passe
function initPasswordModal() {
    const form = document.getElementById('formChangerMotPasse');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const ancienMotPasse = document.getElementById('ancien_motpasse').value;
            const nouveauMotPasse = document.getElementById('nouveau_motpasse').value;
            const confirmerMotPasse = document.getElementById('confirmer_motpasse').value;
            
            // Validation
            if (!ancienMotPasse || !nouveauMotPasse || !confirmerMotPasse) {
                showMessage('جميع الحقول مطلوبة', 'danger', 'messageAlertMotPasse');
                return;
            }
            
            if (nouveauMotPasse !== confirmerMotPasse) {
                showMessage('كلمات المرور غير متطابقة', 'danger', 'messageAlertMotPasse');
                return;
            }
            
            if (nouveauMotPasse.length < 8) {
                showMessage('كلمة المرور يجب أن تكون 8 أحرف على الأقل', 'danger', 'messageAlertMotPasse');
                return;
            }
            
            // Soumettre via AJAX
            const formData = new FormData(form);
            formData.append('user_id', <?php echo $current_user->id; ?>);
            
            fetch('ajax/traitement_utilisateurs.php?action=changer_motpasse_personnel', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success', 'messageAlertMotPasse');
                    
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('changerMotPasseModal'));
                        if (modal) {
                            modal.hide();
                        }
                        form.reset();
                    }, 2000);
                } else {
                    showMessage(data.message, 'danger', 'messageAlertMotPasse');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ أثناء الاتصال بالخادم', 'danger', 'messageAlertMotPasse');
            });
        });
    }
}

// Mettre à jour l'horloge
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
    const dateString = now.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    
    const clockElement = document.getElementById('clock');
    if (clockElement) {
        clockElement.innerHTML = `<i class="fas fa-clock me-2"></i> ${timeString} - ${dateString}`;
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

// Réinitialiser le modal quand il est fermé
document.getElementById('changerMotPasseModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('formChangerMotPasse')?.reset();
    document.getElementById('messageAlertMotPasse')?.classList.add('d-none');
});
</script>

<!--begin::Footer-->
<?php require_once("composit/footer.php"); ?>