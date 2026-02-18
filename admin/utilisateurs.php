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

// Récupérer tous les utilisateurs
$utilisateurs = Accounts::trouve_tous();
?>

<?php

$titre = "إدارة المستخدمين";

$active_menu = "utilisateurs";

$active_submenu = "utilisateurs";

$header = array('todo');

if ($current_user->type =='administrateur' or $current_user->type =='utilisateur'){

    require_once("composit/header.php");
}
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
                    <h3 class="mb-0">إدارة المستخدمين</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">المستخدمين</li>
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
                    <!-- Section des Utilisateurs -->
                    <section class="py-3">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="fw-bold text-primary mb-2">
                                            <i class="fas fa-users me-3"></i>قائمة المستخدمين
                                        </h2>
                                        <p class="text-muted">إدارة وعرض جميع المستخدمين المسجلين في النظام</p>
                                    </div>
                                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#ajouterUtilisateurModal">
                                        <i class="fas fa-plus me-2"></i>إضافة مستخدم جديد
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
                                                    <i class="fas fa-users fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0"><?php echo count($utilisateurs); ?></h5>
                                                <p class="text-muted mb-0">إجمالي المستخدمين</p>
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
                                                    <i class="fas fa-user-shield fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0">
                                                    <?php 
                                                    $admin_count = 0;
                                                    foreach($utilisateurs as $user) {
                                                        if($user->type == 'administrateur') $admin_count++;
                                                    }
                                                    echo $admin_count;
                                                    ?>
                                                </h5>
                                                <p class="text-muted mb-0">المسؤولون</p>
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
                                                    <i class="fas fa-user fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0">
                                                    <?php 
                                                    $user_count = 0;
                                                    foreach($utilisateurs as $user) {
                                                        if($user->type == 'utilisateur') $user_count++;
                                                    }
                                                    echo $user_count;
                                                    ?>
                                                </h5>
                                                <p class="text-muted mb-0">المستخدمون</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card border-start border-danger border-4 bg-white h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-sm bg-danger bg-opacity-10 text-danger rounded-circle">
                                                    <i class="fas fa-user-times fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0">
                                                    <?php 
                                                    $inactive_count = 0;
                                                    foreach($utilisateurs as $user) {
                                                        if($user->active == '0') $inactive_count++;
                                                    }
                                                    echo $inactive_count;
                                                    ?>
                                                </h5>
                                                <p class="text-muted mb-0">غير مفعلين</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table des Utilisateurs -->
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent border-end-0">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="ابحث عن مستخدم...">
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active" data-filter="all">الكل</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="administrateur">المسؤولون</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="utilisateur">المستخدمون</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="active">نشط</button>
                                            <button type="button" class="btn btn-outline-primary" data-filter="inactive">غير نشط</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="utilisateursTable" class="table table-hover mb-0" dir="rtl">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" width="50">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                                    </div>
                                                </th>
                                                <th>المستخدم</th>
                                                <th>الاسم الكامل</th>
                                                <th>البريد الإلكتروني</th>
                                                <th>الهاتف</th>
                                                <th>نوع الحساب</th>
                                                <th>الحالة</th>
                                                <th>تاريخ التسجيل</th>
                                                <th class="text-center">الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach ($utilisateurs as $utilisateur):
                                                // Déterminer les classes pour le type
                                                $typeClass = $utilisateur->type == 'administrateur' ? 'primary' : 'success';
                                                $typeText = $utilisateur->type == 'administrateur' ? 'مسؤول' : 'مستخدم';
                                                
                                                // Déterminer les classes pour الحالة
                                                $activeClass = $utilisateur->active == '1' ? 'success' : 'danger';
                                                $activeText = $utilisateur->active == '1' ? 'نشط' : 'غير نشط';
                                                
                                                // Formater التاريخ
                                                $date_creation = date('d/m/Y', strtotime($utilisateur->date_creation));
                                            ?>
                                            <tr class="align-middle" data-type="<?php echo $utilisateur->type; ?>" data-status="<?php echo $utilisateur->active == '1' ? 'active' : 'inactive'; ?>">
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input row-checkbox" type="checkbox" value="<?php echo $utilisateur->id; ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm me-3">
                                                            <?php if($utilisateur->photo): ?>
                                                                <img src="../uploads/photos/<?php echo $utilisateur->photo; ?>" 
                                                                     alt="<?php echo $utilisateur->prenom . ' ' . $utilisateur->nom; ?>" 
                                                                     class="rounded-circle" 
                                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                            <?php else: ?>
                                                                <div class="avatar-initial bg-<?php echo $typeClass; ?> bg-opacity-10 text-<?php echo $typeClass; ?> rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                    <i class="fas fa-user fs-5"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-semibold"><?php echo $utilisateur->user; ?></h6>
                                                            <small class="text-muted">ID: #<?php echo str_pad($utilisateur->id, 4, '0', STR_PAD_LEFT); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo $utilisateur->prenom . ' ' . $utilisateur->nom; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope me-2 text-muted"></i>
                                                    <?php echo $utilisateur->email ?: '---'; ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-phone me-2 text-muted"></i>
                                                    <?php echo $utilisateur->telephone ?: '---'; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $typeClass; ?> bg-opacity-10 text-<?php echo $typeClass; ?>">
                                                        <i class="fas fa-user-tag me-1"></i>
                                                        <?php echo $typeText; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $activeClass; ?> bg-opacity-10 text-<?php echo $activeClass; ?>">
                                                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                                        <?php echo $activeText; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $date_creation; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" 
                                                                class="btn btn-outline-primary" 
                                                                onclick="afficherDetailsUtilisateur(<?php echo $utilisateur->id; ?>)"
                                                                data-bs-toggle="tooltip" 
                                                                title="عرض التفاصيل">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-outline-success" 
                                                                onclick="modifierUtilisateur(<?php echo $utilisateur->id; ?>)"
                                                                data-bs-toggle="tooltip" 
                                                                title="تعديل">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                onclick="supprimerUtilisateur(<?php echo $utilisateur->id; ?>)"
                                                                data-bs-toggle="tooltip" 
                                                                title="حذف">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-outline-warning" 
                                                                onclick="changerMotPasse(<?php echo $utilisateur->id; ?>)"
                                                                data-bs-toggle="tooltip" 
                                                                title="تغيير كلمة المرور">
                                                            <i class="fas fa-key"></i>
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
                                            <span class="text-muted">مستخدم محدد</span>
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

<!-- Modal Ajouter Utilisateur -->
<div class="modal fade" id="ajouterUtilisateurModal" tabindex="-1" aria-labelledby="ajouterUtilisateurModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" dir="rtl">
            <form id="formAjouterUtilisateur" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="ajouterUtilisateurModalLabel">
                        <i class="fas fa-user-plus me-2"></i>إضافة مستخدم جديد
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Messages d'erreur/succès -->
                    <div id="messageAlertUtilisateur" class="alert d-none"></div>
                    
                    <!-- Onglets -->
                    <ul class="nav nav-tabs mb-4" id="utilisateurTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-basiques-tab" data-bs-toggle="tab" data-bs-target="#info-basiques" type="button" role="tab">
                                <i class="fas fa-user me-1"></i> المعلومات الأساسية
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="compte-tab" data-bs-toggle="tab" data-bs-target="#compte" type="button" role="tab">
                                <i class="fas fa-key me-1"></i> معلومات الحساب
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                <i class="fas fa-address-book me-1"></i> معلومات الاتصال
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="utilisateurTabContent">
                        <!-- Informations Basiques -->
                        <div class="tab-pane fade show active" id="info-basiques" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">الاسم الشخصي <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required placeholder="أدخل الاسم الشخصي">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">الاسم العائلي <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" required placeholder="أدخل الاسم العائلي">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="photo" class="form-label">الصورة الشخصية</label>
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <img id="photoPreview" src="../assets/img/default-user.png" alt="معاينة الصورة" class="img-fluid rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                            </div>
                                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*" onchange="previewPhoto(event)">
                                            <div class="form-text">الصور المسموح بها: JPG, PNG, GIF. الحجم الأقصى: 2MB</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="wilaya" class="form-label">الولاية</label>
                                    <select class="form-select" id="wilaya" name="wilaya">
                                        <option value="">اختر الولاية</option>
                                        <option value="1">أدرار</option>
                                        <option value="2">الشلف</option>
                                        <!-- ... autres wilayas ... -->
                                        <option value="48">غليزان</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations du Compte -->
                        <div class="tab-pane fade" id="compte" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">اسم المستخدم <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" required placeholder="أدخل اسم المستخدم">
                                    <div class="form-text">يجب أن يكون اسم المستخدم فريداً</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="example@domain.com">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password" class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required placeholder="أدخل كلمة المرور">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">يجب أن تتكون كلمة المرور من 8 أحرف على الأقل</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="تأكيد كلمة المرور">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="type" class="form-label">نوع الحساب <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">اختر نوع الحساب</option>
                                        <option value="administrateur">مسؤول النظام</option>
                                        <option value="utilisateur">مستخدم عادي</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="active" class="form-label">حالة الحساب</label>
                                    <select class="form-select" id="active" name="active">
                                        <option value="1" selected>نشط</option>
                                        <option value="0">غير نشط</option>
                                    </select>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                                        <label class="form-check-label" for="send_welcome_email">
                                            إرسال بريد ترحيبي بالمستخدم الجديد
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informations de Contact -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">رقم الهاتف</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="أدخل رقم الهاتف">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label">رقم الجوال</label>
                                    <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="أدخل رقم الجوال">
                                </div>
                                
                                <div class="col-12">
                                    <label for="adresse" class="form-label">العنوان</label>
                                    <textarea class="form-control" id="adresse" name="adresse" rows="3" placeholder="أدخل العنوان الكامل"></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="id_societe" name="id_societe">
                                        <label class="form-check-label" for="id_societe">
                                            ربط المستخدم بمؤسسة معينة
                                        </label>
                                    </div>
                                    
                                    <div id="societeSelection" class="mt-3 d-none">
                                        <label for="societe_id" class="form-label">اختر المؤسسة</label>
                                        <select class="form-select" id="societe_id" name="societe_id">
                                            <option value="">اختر المؤسسة</option>
                                            <?php
                                            $societes = Societe::trouve_tous();
                                            foreach($societes as $societe):
                                            ?>
                                            <option value="<?php echo $societe->id_societe; ?>"><?php echo $societe->raison_ar; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnResetFormUtilisateur">
                        <i class="fas fa-redo me-1"></i> مسح النموذج
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitUtilisateur">
                        <i class="fas fa-save me-1"></i> حفظ المستخدم
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    <input type="hidden" id="user_id_motpasse" name="user_id">
                    <div class="mb-3">
                        <label for="nouveau_motpasse" class="form-label">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="nouveau_motpasse" name="nouveau_motpasse" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('nouveau_motpasse')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
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
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="forcer_changement" name="forcer_changement">
                        <label class="form-check-label" for="forcer_changement">
                            إجبار المستخدم على تغيير كلمة المرور عند تسجيل الدخول التالي
                        </label>
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
    
    .badge.bg-opacity-10 {
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
    
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        padding: 10px 20px;
        border-radius: 8px 8px 0 0;
        transition: all 0.3s;
    }
    
    .nav-tabs .nav-link:hover {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
        border-bottom: 3px solid #0d6efd;
        font-weight: 600;
    }
</style>

<!-- JavaScript pour la page -->
<script>
// Variables globales
let selectedUsers = [];

// Prévisualisation de la photo
function previewPhoto(event) {
    const reader = new FileReader();
    const preview = document.getElementById('photoPreview');
    
    reader.onload = function() {
        preview.src = reader.result;
    }
    
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
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
    initTableFunctions();
    initUserModal();
    initPasswordModal();
    initCheckboxSelection();
});

// Fonctions pour la table
function initTableFunctions() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('[data-filter]');
    const rowsPerPage = document.getElementById('rowsPerPage');
    
    // Recherche
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#utilisateursTable tbody tr');
            
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
            
            const rows = document.querySelectorAll('#utilisateursTable tbody tr');
            
            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'active' || filter === 'inactive') {
                    const status = row.getAttribute('data-status');
                    row.style.display = (status === filter) ? '' : 'none';
                } else {
                    const type = row.getAttribute('data-type');
                    row.style.display = (type === filter) ? '' : 'none';
                }
            });
        });
    });
    
    // Pagination
    if (rowsPerPage) {
        rowsPerPage.addEventListener('change', function() {
            // Implémenter la pagination si nécessaire
            console.log('Nombre de lignes par page:', this.value);
        });
    }
    
    // Initialiser les tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialiser le modal d'ajout utilisateur
function initUserModal() {
    const form = document.getElementById('formAjouterUtilisateur');
    const btnReset = document.getElementById('btnResetFormUtilisateur');
    const messageAlert = document.getElementById('messageAlertUtilisateur');
    const idSocieteCheckbox = document.getElementById('id_societe');
    const societeSelection = document.getElementById('societeSelection');
    
    // Afficher/masquer la sélection de société
    if (idSocieteCheckbox) {
        idSocieteCheckbox.addEventListener('change', function() {
            if (this.checked) {
                societeSelection.classList.remove('d-none');
            } else {
                societeSelection.classList.add('d-none');
                document.getElementById('societe_id').value = '';
            }
        });
    }
    
    // Réinitialiser le formulaire
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            form.reset();
            document.getElementById('photoPreview').src = '../assets/images/default-user.png';
            societeSelection.classList.add('d-none');
            messageAlert.classList.add('d-none');
            
            // Réinitialiser les onglets
            const firstTab = document.querySelector('#utilisateurTabs button:first-child');
            if (firstTab) {
                firstTab.click();
            }
        });
    }
    
    // Soumission du formulaire
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validation
            if (password !== confirmPassword) {
                showMessage('كلمات المرور غير متطابقة', 'danger', 'messageAlertUtilisateur');
                return;
            }
            
            if (password.length < 8) {
                showMessage('كلمة المرور يجب أن تكون 8 أحرف على الأقل', 'danger', 'messageAlertUtilisateur');
                return;
            }
            
            // Désactiver le bouton
            const submitBtn = document.getElementById('btnSubmitUtilisateur');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...';
            submitBtn.disabled = true;
            
            // Soumettre via AJAX
            const formData = new FormData(form);
            
            fetch('ajax/traitement_utilisateurs.php?action=ajouter', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success', 'messageAlertUtilisateur');
                    
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('ajouterUtilisateurModal'));
                        if (modal) {
                            modal.hide();
                        }
                        location.reload();
                    }, 2000);
                } else {
                    showMessage(data.message, 'danger', 'messageAlertUtilisateur');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ أثناء حفظ البيانات', 'danger', 'messageAlertUtilisateur');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
}

// Initialiser le modal de changement de mot de passe
function initPasswordModal() {
    const form = document.getElementById('formChangerMotPasse');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nouveauMotPasse = document.getElementById('nouveau_motpasse').value;
            const confirmerMotPasse = document.getElementById('confirmer_motpasse').value;
            const userId = document.getElementById('user_id_motpasse').value;
            
            if (nouveauMotPasse !== confirmerMotPasse) {
                showMessage('كلمات المرور غير متطابقة', 'danger', 'messageAlertMotPasse');
                return;
            }
            
            if (nouveauMotPasse.length < 8) {
                showMessage('كلمة المرور يجب أن تكون 8 أحرف على الأقل', 'danger', 'messageAlertMotPasse');
                return;
            }
            
            const formData = new FormData(form);
            
            fetch('ajax/traitement_utilisateurs.php?action=changer_motpasse', {
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
                    }, 2000);
                } else {
                    showMessage(data.message, 'danger', 'messageAlertMotPasse');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('حدث خطأ أثناء تغيير كلمة المرور', 'danger', 'messageAlertMotPasse');
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
        
        selectedUsers = Array.from(checkedBoxes).map(cb => cb.value);
        
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
function afficherDetailsUtilisateur(id) {
    // Implémenter l'affichage des détails
    fetch(`ajax/traitement_utilisateurs.php?action=details&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Afficher les détails dans un modal
                alert(`تفاصيل المستخدم: ${data.prenom} ${data.nom}`);
            } else {
                alert(data.message);
            }
        });
}

function modifierUtilisateur(id) {
    window.location.href = `modifier_utilisateur.php?id=${id}`;
}

function supprimerUtilisateur(id) {
    if (confirm('هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذه العملية.')) {
        const formData = new FormData();
        formData.append('id', id);
        
        fetch('ajax/traitement_utilisateurs.php?action=supprimer', {
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

function changerMotPasse(id) {
    document.getElementById('user_id_motpasse').value = id;
    const modal = new bootstrap.Modal(document.getElementById('changerMotPasseModal'));
    modal.show();
}

// Actions en masse
function activerSelection() {
    if (selectedUsers.length === 0) return;
    
    if (confirm(`هل تريد تفعيل ${selectedUsers.length} مستخدم؟`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(selectedUsers));
        
        fetch('ajax/traitement_utilisateurs.php?action=activer_selection', {
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
    if (selectedUsers.length === 0) return;
    
    if (confirm(`هل تريد تعطيل ${selectedUsers.length} مستخدم؟`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(selectedUsers));
        
        fetch('ajax/traitement_utilisateurs.php?action=desactiver_selection', {
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
    if (selectedUsers.length === 0) return;
    
    if (confirm(`هل أنت متأكد من حذف ${selectedUsers.length} مستخدم؟ لا يمكن التراجع عن هذه العملية.`)) {
        const formData = new FormData();
        formData.append('ids', JSON.stringify(selectedUsers));
        
        fetch('ajax/traitement_utilisateurs.php?action=supprimer_selection', {
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
document.getElementById('ajouterUtilisateurModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('formAjouterUtilisateur')?.reset();
    document.getElementById('photoPreview').src = '../assets/images/default-user.png';
    document.getElementById('messageAlertUtilisateur')?.classList.add('d-none');
    document.getElementById('societeSelection')?.classList.add('d-none');
    
    const firstTab = document.querySelector('#utilisateurTabs button:first-child');
    if (firstTab) {
        firstTab.click();
    }
});

document.getElementById('changerMotPasseModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('formChangerMotPasse')?.reset();
    document.getElementById('messageAlertMotPasse')?.classList.add('d-none');
});
</script>

<!--begin::Footer-->
<?php require_once("composit/footer.php"); ?>