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

// Récupérer l'exercice actif
$exercice_actif = Exercice::get_exercice_actif();

if (!$exercice_actif) {
    $session->set_message("لا توجد سنة مالية مفتوحة حالياً", "danger");
    redirect_to('index.php');
}

// Vérifier si le formulaire a déjà été soumis pour cet exercice
$formulaire_soumis = 0;

// Récupérer les données existantes si elles existent
$donnees_existantes = array();
if ($formulaire_soumis) {
    $donnees_existantes = FormulairePostes::get_donnees_par_societe_exercice($societe->id_societe, $exercice_actif->id);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérifier la période de saisie
        if (!$exercice_actif) {
            throw new Exception("فترة التقديم مغلقة لهذه السنة المالية");
        }
        
        // Récupérer les données du formulaire
        $donnees_formulaire = array(
            'id_societe' => $societe->id_societe,
            'id_exercice' => $exercice_actif->id,
            'date_soumission' => date('Y-m-d H:i:s'),
            'statut' => 'soumis'
        );
        
        // Données des hauts fonctionnaires
        $hauts_fonctionnaires = array();
        if (isset($_POST['hauts_fonctionnaires'])) {
            foreach ($_POST['hauts_fonctionnaires'] as $index => $ligne) {
                if (!empty($ligne['code']) && !empty($ligne['fonction'])) {
                    $hauts_fonctionnaires[] = array(
                        'code' => $ligne['code'],
                        'fonction' => $ligne['fonction'],
                        'postes_jusque' => intval($ligne['postes_jusque']),
                        'postes_annee' => intval($ligne['postes_annee']),
                        'difference' => intval($ligne['difference']),
                        'observations' => $ligne['observations'],
                        'ordre' => $index
                    );
                }
            }
        }
        
        // Données des hauts postes
        $hauts_postes = array();
        if (isset($_POST['hauts_postes'])) {
            foreach ($_POST['hauts_postes'] as $index => $ligne) {
                if (!empty($ligne['code']) && !empty($ligne['fonction'])) {
                    $hauts_postes[] = array(
                        'code' => $ligne['code'],
                        'fonction' => $ligne['fonction'],
                        'postes_jusque' => intval($ligne['postes_jusque']),
                        'postes_annee' => intval($ligne['postes_annee']),
                        'difference' => intval($ligne['difference']),
                        'observations' => $ligne['observations'],
                        'ordre' => $index
                    );
                }
            }
        }
        
        // Calculer les totaux
        $totaux = array(
            'total_jusque_hf' => 0,
            'total_annee_hf' => 0,
            'total_diff_hf' => 0,
            'total_jusque_hp' => 0,
            'total_annee_hp' => 0,
            'total_diff_hp' => 0,
            'total_general_jusque' => 0,
            'total_general_annee' => 0,
            'total_general_diff' => 0
        );
        
        foreach ($hauts_fonctionnaires as $ligne) {
            $totaux['total_jusque_hf'] += $ligne['postes_jusque'];
            $totaux['total_annee_hf'] += $ligne['postes_annee'];
            $totaux['total_diff_hf'] += $ligne['difference'];
        }
        
        foreach ($hauts_postes as $ligne) {
            $totaux['total_jusque_hp'] += $ligne['postes_jusque'];
            $totaux['total_annee_hp'] += $ligne['postes_annee'];
            $totaux['total_diff_hp'] += $ligne['difference'];
        }
        
        $totaux['total_general_jusque'] = $totaux['total_jusque_hf'] + $totaux['total_jusque_hp'];
        $totaux['total_general_annee'] = $totaux['total_annee_hf'] + $totaux['total_annee_hp'];
        $totaux['total_general_diff'] = $totaux['total_diff_hf'] + $totaux['total_diff_hp'];
        
        // Sauvegarder les données
        $formulaire_id = FormulairePostes::save(
            $donnees_formulaire,
            $hauts_fonctionnaires,
            $hauts_postes,
            $totaux
        );
        
        if ($formulaire_id) {
            $session->set_message("تم حفظ البيانات بنجاح", "success");
            redirect_to('formulaire_postes.php?success=1');
        } else {
            throw new Exception("حدث خطأ أثناء حفظ البيانات");
        }
        
    } catch (Exception $e) {
        $session->set_message($e->getMessage(), "danger");
    }
}

$titre = "نموذج المناصب العليا - " . $societe->raison_ar;
$active_menu = "formulaires";
$active_submenu = "formulaire_postes";
$header = array('select2');

require_once("composit/header.php");
?>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">نموذج المناصب العليا</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">نموذج المناصب العليا</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!--end::App Content Header-->
    
    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    تم حفظ النموذج بنجاح!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Carte d'information sur l'exercice -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2 text-primary"></i>معلومات السنة المالية
                    </h5>
                    <span class="badge bg-<?php echo $formulaire_soumis ? 'success' : 'primary'; ?>">
                        <?php echo $formulaire_soumis ? 'تم التقديم' : 'قيد التعبئة'; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
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
                        
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">فترة التقديم</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo date('d/m/Y', strtotime($exercice_actif->date_debut)); ?> - 
                                        <?php echo date('d/m/Y', strtotime($exercice_actif->date_fin)); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle me-3">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">المؤسسة</h6>
                                    <p class="text-muted mb-0"><?php echo $societe->raison_ar; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-sm <?php echo $formulaire_soumis ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning'; ?> rounded-circle me-3">
                                    <i class="fas <?php echo $formulaire_soumis ? 'fa-check-circle' : 'fa-edit'; ?>"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">حالة النموذج</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo $formulaire_soumis ? 'تم التقديم' : 'غير مكتمل'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!$formulaire_soumis ): ?>
                    <div class="alert alert-info mb-0 mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>ملاحظة:</strong> الرجاء تعبئة النموذج كاملاً قبل تاريخ 
                        <strong><?php echo date('d/m/Y', strtotime($exercice_actif->date_fin)); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Formulaire principal -->
            <form id="formulairePostes" method="POST" action="">
                <input type="hidden" name="exercice_id" value="<?php echo $exercice_actif->id; ?>">
                <input type="hidden" name="societe_id" value="<?php echo $societe->id_societe; ?>">
                
                <!-- Section الوظائف العليا -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>الوظائف العليا
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne('hauts_fonctionnaires')">
                            <i class="fas fa-plus me-1"></i> إضافة سطر
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="table_hauts_fonctionnaires">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">الرمز</th>
                                        <th width="20%" class="text-center">الوظائف العليا</th>
                                        <th width="15%" class="text-center">عدد المناصب المالية الحقيقية إلى غاية <?php echo ($exercice_actif->annee - 1); ?>/12/31</th>
                                        <th width="15%" class="text-center">عدد المناصب المالية الحقيقية في السنة <?php echo $exercice_actif->annee; ?></th>
                                        <th width="10%" class="text-center">الفارق</th>
                                        <th width="25%" class="text-center">الملاحظات</th>
                                        <th width="10%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_hauts_fonctionnaires">
                                    <?php if ($formulaire_soumis && isset($donnees_existantes['hauts_fonctionnaires'])): ?>
                                        <?php foreach ($donnees_existantes['hauts_fonctionnaires'] as $index => $ligne): ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="hauts_fonctionnaires[<?php echo $index; ?>][code]" 
                                                       class="form-control text-center" value="<?php echo htmlspecialchars($ligne['code']); ?>" required readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="hauts_fonctionnaires[<?php echo $index; ?>][fonction]" 
                                                       class="form-control" value="<?php echo htmlspecialchars($ligne['fonction']); ?>" required readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_fonctionnaires[<?php echo $index; ?>][postes_jusque]" 
                                                       class="form-control text-center postes-jusque" 
                                                       value="<?php echo $ligne['postes_jusque']; ?>" min="0" onchange="calculerDifference(this)" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_fonctionnaires[<?php echo $index; ?>][postes_annee]" 
                                                       class="form-control text-center postes-annee" 
                                                       value="<?php echo $ligne['postes_annee']; ?>" min="0" onchange="calculerDifference(this)" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_fonctionnaires[<?php echo $index; ?>][difference]" 
                                                       class="form-control text-center difference" value="<?php echo $ligne['difference']; ?>" readonly>
                                            </td>
                                            <td>
                                                <textarea name="hauts_fonctionnaires[<?php echo $index; ?>][observations]" 
                                                          class="form-control" rows="1" readonly><?php echo htmlspecialchars($ligne['observations']); ?></textarea>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Ligne vide par défaut -->
                                        <tr>
                                            <td>
                                                <input type="text" name="hauts_fonctionnaires[0][code]" 
                                                       class="form-control text-center" placeholder="001" required>
                                            </td>
                                            <td>
                                                <select name="hauts_fonctionnaires[0][fonction]" class="form-control select-fonction" required>
                                                    <option value="">اختر الوظيفة</option>
                                                    <option value="المدير العام">المدير العام</option>
                                                    <option value="المدير التنفيذي">المدير التنفيذي</option>
                                                    <option value="المدير المالي">المدير المالي</option>
                                                    <option value="المدير التقني">المدير التقني</option>
                                                    <option value="المدير الإداري">المدير الإداري</option>
                                                    <option value="المدير التجاري">المدير التجاري</option>
                                                    <option value="المسؤول الأول">المسؤول الأول</option>
                                                    <option value="رئيس القسم">رئيس القسم</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_fonctionnaires[0][postes_jusque]" 
                                                       class="form-control text-center postes-jusque" value="0" min="0" onchange="calculerDifference(this)">
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_fonctionnaires[0][postes_annee]" 
                                                       class="form-control text-center postes-annee" value="0" min="0" onchange="calculerDifference(this)">
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_fonctionnaires[0][difference]" 
                                                       class="form-control text-center difference" value="0" readonly>
                                            </td>
                                            <td>
                                                <textarea name="hauts_fonctionnaires[0][observations]" 
                                                          class="form-control" rows="1" placeholder="ملاحظات..."></textarea>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">المجموع الفرعي:</td>
                                        <td class="text-center fw-bold" id="total_jusque_hf">0</td>
                                        <td class="text-center fw-bold" id="total_annee_hf">0</td>
                                        <td class="text-center fw-bold" id="total_diff_hf">0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section المناصب العليا -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-briefcase me-2"></i>المناصب العليا
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" onclick="ajouterLigne('hauts_postes')">
                            <i class="fas fa-plus me-1"></i> إضافة سطر
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="table_hauts_postes">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">الرمز</th>
                                        <th width="20%" class="text-center">المناصب العليا</th>
                                        <th width="15%" class="text-center">عدد المناصب المالية الحقيقية إلى غاية <?php echo ($exercice_actif->annee - 1); ?>/12/31</th>
                                        <th width="15%" class="text-center">عدد المناصب المالية الحقيقية في السنة <?php echo $exercice_actif->annee; ?></th>
                                        <th width="10%" class="text-center">الفارق</th>
                                        <th width="25%" class="text-center">الملاحظات</th>
                                        <th width="10%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_hauts_postes">
                                    <?php if ($formulaire_soumis && isset($donnees_existantes['hauts_postes'])): ?>
                                        <?php foreach ($donnees_existantes['hauts_postes'] as $index => $ligne): ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="hauts_postes[<?php echo $index; ?>][code]" 
                                                       class="form-control text-center" value="<?php echo htmlspecialchars($ligne['code']); ?>" required readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="hauts_postes[<?php echo $index; ?>][fonction]" 
                                                       class="form-control" value="<?php echo htmlspecialchars($ligne['fonction']); ?>" required readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_postes[<?php echo $index; ?>][postes_jusque]" 
                                                       class="form-control text-center postes-jusque" 
                                                       value="<?php echo $ligne['postes_jusque']; ?>" min="0" onchange="calculerDifference(this)" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_postes[<?php echo $index; ?>][postes_annee]" 
                                                       class="form-control text-center postes-annee" 
                                                       value="<?php echo $ligne['postes_annee']; ?>" min="0" onchange="calculerDifference(this)" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_postes[<?php echo $index; ?>][difference]" 
                                                       class="form-control text-center difference" value="<?php echo $ligne['difference']; ?>" readonly>
                                            </td>
                                            <td>
                                                <textarea name="hauts_postes[<?php echo $index; ?>][observations]" 
                                                          class="form-control" rows="1" readonly><?php echo htmlspecialchars($ligne['observations']); ?></textarea>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Ligne vide par défaut -->
                                        <tr>
                                            <td>
                                                <input type="text" name="hauts_postes[0][code]" 
                                                       class="form-control text-center" placeholder="A01" required>
                                            </td>
                                            <td>
                                                <select name="hauts_postes[0][fonction]" class="form-control select-poste" required>
                                                    <option value="">اختر المنصب</option>
                                                    <option value="رئيس المجلس الإداري">رئيس المجلس الإداري</option>
                                                    <option value="نائب الرئيس">نائب الرئيس</option>
                                                    <option value="عضو مجلس الإدارة">عضو مجلس الإدارة</option>
                                                    <option value="المسير العام">المسير العام</option>
                                                    <option value="المسير المساعد">المسير المساعد</option>
                                                    <option value="المستشار المالي">المستشار المالي</option>
                                                    <option value="المستشار القانوني">المستشار القانوني</option>
                                                    <option value="المسؤول عن المراقبة">المسؤول عن المراقبة</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_postes[0][postes_jusque]" 
                                                       class="form-control text-center postes-jusque" value="0" min="0" onchange="calculerDifference(this)">
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_postes[0][postes_annee]" 
                                                       class="form-control text-center postes-annee" value="0" min="0" onchange="calculerDifference(this)">
                                            </td>
                                            <td>
                                                <input type="number" name="hauts_postes[0][difference]" 
                                                       class="form-control text-center difference" value="0" readonly>
                                            </td>
                                            <td>
                                                <textarea name="hauts_postes[0][observations]" 
                                                          class="form-control" rows="1" placeholder="ملاحظات..."></textarea>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">المجموع الفرعي:</td>
                                        <td class="text-center fw-bold" id="total_jusque_hp">0</td>
                                        <td class="text-center fw-bold" id="total_annee_hp">0</td>
                                        <td class="text-center fw-bold" id="total_diff_hp">0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Total Général -->
                <div class="card mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calculator me-2"></i>المجموع العام
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-success">
                                    <tr>
                                        <th class="text-center">المجموع العام</th>
                                        <th class="text-center">عدد المناصب المالية الحقيقية إلى غاية <?php echo ($exercice_actif->annee - 1); ?>/12/31</th>
                                        <th class="text-center">عدد المناصب المالية الحقيقية في السنة <?php echo $exercice_actif->annee; ?></th>
                                        <th class="text-center">الفارق</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center fw-bold">المجموع العام</td>
                                        <td class="text-center fw-bold" id="total_general_jusque">0</td>
                                        <td class="text-center fw-bold" id="total_general_annee">0</td>
                                        <td class="text-center fw-bold" id="total_general_diff">0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i class="fas fa-arrow-right me-1"></i> رجوع
                                </button>
                            </div>
                            <div>
                                <?php if (!$formulaire_soumis ): ?>
                                    <button type="button" class="btn btn-warning me-2" onclick="enregistrerBrouillon()">
                                        <i class="fas fa-save me-1"></i> حفظ كمسودة
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-paper-plane me-1"></i> تقديم النموذج
                                    </button>
                                <?php elseif ($formulaire_soumis): ?>
                                    <button type="button" class="btn btn-primary" onclick="imprimerFormulaire()">
                                        <i class="fas fa-print me-1"></i> طباعة النموذج
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-danger" disabled>
                                        <i class="fas fa-times me-1"></i> فترة التقديم مغلقة
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->

<script>
// Variables globales
let compteurHF = <?php echo $formulaire_soumis ? count($donnees_existantes['hauts_fonctionnaires']) : 1; ?>;
let compteurHP = <?php echo $formulaire_soumis ? count($donnees_existantes['hauts_postes']) : 1; ?>;

// Initialiser les sélecteurs
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Select2
    $('.select-fonction, .select-poste').select2({
        placeholder: "اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl"
    });
    
    // Calculer les totaux initiaux
    calculerTotaux();
});

// Ajouter une ligne
function ajouterLigne(type) {
    const tbodyId = type === 'hauts_fonctionnaires' ? 'tbody_hauts_fonctionnaires' : 'tbody_hauts_postes';
    const compteur = type === 'hauts_fonctionnaires' ? compteurHF : compteurHP;
    const index = compteur;
    
    const newRow = document.createElement('tr');
    
    if (type === 'hauts_fonctionnaires') {
        newRow.innerHTML = `
            <td>
                <input type="text" name="hauts_fonctionnaires[${index}][code]" 
                       class="form-control text-center" placeholder="${String(index + 1).padStart(3, '0')}" required>
            </td>
            <td>
                <select name="hauts_fonctionnaires[${index}][fonction]" class="form-control select-fonction" required>
                    <option value="">اختر الوظيفة</option>
                    <option value="المدير العام">المدير العام</option>
                    <option value="المدير التنفيذي">المدير التنفيذي</option>
                    <option value="المدير المالي">المدير المالي</option>
                    <option value="المدير التقني">المدير التقني</option>
                    <option value="المدير الإداري">المدير الإداري</option>
                    <option value="المدير التجاري">المدير التجاري</option>
                    <option value="المسؤول الأول">المسؤول الأول</option>
                    <option value="رئيس القسم">رئيس القسم</option>
                </select>
            </td>
            <td>
                <input type="number" name="hauts_fonctionnaires[${index}][postes_jusque]" 
                       class="form-control text-center postes-jusque" value="0" min="0" onchange="calculerDifference(this)">
            </td>
            <td>
                <input type="number" name="hauts_fonctionnaires[${index}][postes_annee]" 
                       class="form-control text-center postes-annee" value="0" min="0" onchange="calculerDifference(this)">
            </td>
            <td>
                <input type="number" name="hauts_fonctionnaires[${index}][difference]" 
                       class="form-control text-center difference" value="0" readonly>
            </td>
            <td>
                <textarea name="hauts_fonctionnaires[${index}][observations]" 
                          class="form-control" rows="1" placeholder="ملاحظات..."></textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        compteurHF++;
    } else {
        newRow.innerHTML = `
            <td>
                <input type="text" name="hauts_postes[${index}][code]" 
                       class="form-control text-center" placeholder="A${String(index + 1).padStart(2, '0')}" required>
            </td>
            <td>
                <select name="hauts_postes[${index}][fonction]" class="form-control select-poste" required>
                    <option value="">اختر المنصب</option>
                    <option value="رئيس المجلس الإداري">رئيس المجلس الإداري</option>
                    <option value="نائب الرئيس">نائب الرئيس</option>
                    <option value="عضو مجلس الإدارة">عضو مجلس الإدارة</option>
                    <option value="المسير العام">المسير العام</option>
                    <option value="المسير المساعد">المسير المساعد</option>
                    <option value="المستشار المالي">المستشار المالي</option>
                    <option value="المستشار القانوني">المستشار القانوني</option>
                    <option value="المسؤول عن المراقبة">المسؤول عن المراقبة</option>
                </select>
            </td>
            <td>
                <input type="number" name="hauts_postes[${index}][postes_jusque]" 
                       class="form-control text-center postes-jusque" value="0" min="0" onchange="calculerDifference(this)">
            </td>
            <td>
                <input type="number" name="hauts_postes[${index}][postes_annee]" 
                       class="form-control text-center postes-annee" value="0" min="0" onchange="calculerDifference(this)">
            </td>
            <td>
                <input type="number" name="hauts_postes[${index}][difference]" 
                       class="form-control text-center difference" value="0" readonly>
            </td>
            <td>
                <textarea name="hauts_postes[${index}][observations]" 
                          class="form-control" rows="1" placeholder="ملاحظات..."></textarea>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="supprimerLigne(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        compteurHP++;
    }
    
    document.getElementById(tbodyId).appendChild(newRow);
    
    // Réinitialiser Select2 pour la nouvelle ligne
    $(newRow).find('.select-fonction, .select-poste').select2({
        placeholder: "اختر...",
        allowClear: true,
        width: '100%',
        dir: "rtl"
    });
}

// Supprimer une ligne
function supprimerLigne(button) {
    const row = button.closest('tr');
    const tbody = row.parentNode;
    
    // Ne pas supprimer la dernière ligne
    if (tbody.children.length > 1) {
        row.remove();
        calculerTotaux();
    }
}

// Calculer la différence pour une ligne
function calculerDifference(input) {
    const row = input.closest('tr');
    const postesJusque = parseInt(row.querySelector('.postes-jusque').value) || 0;
    const postesAnnee = parseInt(row.querySelector('.postes-annee').value) || 0;
    const difference = postesAnnee - postesJusque;
    
    row.querySelector('.difference').value = difference;
    calculerTotaux();
}

// Calculer les totaux
function calculerTotaux() {
    let totalJusqueHF = 0, totalAnneeHF = 0, totalDiffHF = 0;
    let totalJusqueHP = 0, totalAnneeHP = 0, totalDiffHP = 0;
    
    // Totaux hauts fonctionnaires
    document.querySelectorAll('#tbody_hauts_fonctionnaires tr').forEach(row => {
        const postesJusque = parseInt(row.querySelector('.postes-jusque').value) || 0;
        const postesAnnee = parseInt(row.querySelector('.postes-annee').value) || 0;
        const diff = parseInt(row.querySelector('.difference').value) || 0;
        
        totalJusqueHF += postesJusque;
        totalAnneeHF += postesAnnee;
        totalDiffHF += diff;
    });
    
    // Totaux hauts postes
    document.querySelectorAll('#tbody_hauts_postes tr').forEach(row => {
        const postesJusque = parseInt(row.querySelector('.postes-jusque').value) || 0;
        const postesAnnee = parseInt(row.querySelector('.postes-annee').value) || 0;
        const diff = parseInt(row.querySelector('.difference').value) || 0;
        
        totalJusqueHP += postesJusque;
        totalAnneeHP += postesAnnee;
        totalDiffHP += diff;
    });
    
    // Mettre à jour les totaux affichés
    document.getElementById('total_jusque_hf').textContent = totalJusqueHF;
    document.getElementById('total_annee_hf').textContent = totalAnneeHF;
    document.getElementById('total_diff_hf').textContent = totalDiffHF;
    
    document.getElementById('total_jusque_hp').textContent = totalJusqueHP;
    document.getElementById('total_annee_hp').textContent = totalAnneeHP;
    document.getElementById('total_diff_hp').textContent = totalDiffHP;
    
    // Totaux généraux
    const totalGeneralJusque = totalJusqueHF + totalJusqueHP;
    const totalGeneralAnnee = totalAnneeHF + totalAnneeHP;
    const totalGeneralDiff = totalDiffHF + totalDiffHP;
    
    document.getElementById('total_general_jusque').textContent = totalGeneralJusque;
    document.getElementById('total_general_annee').textContent = totalGeneralAnnee;
    document.getElementById('total_general_diff').textContent = totalGeneralDiff;
}

// Enregistrer en tant que brouillon
function enregistrerBrouillon() {
    // Ici vous pouvez implémenter la logique d'enregistrement en brouillon
    // Par exemple, envoyer une requête AJAX avec statut = 'brouillon'
    alert('سيتم تطبيق خاصية الحفظ كمسودة في وقت لاحق');
}

// Imprimer le formulaire
function imprimerFormulaire() {
    window.print();
}

// Validation du formulaire
document.getElementById('formulairePostes').addEventListener('submit', function(e) {
    if (!<?php echo $exercice_actif ? 'true' : 'false'; ?>) {
        e.preventDefault();
        alert('فترة التقديم مغلقة لهذه السنة المالية');
        return false;
    }
    
    // Vérifier que tous les champs requis sont remplis
    const champsRequis = this.querySelectorAll('[required]');
    let valide = true;
    
    champsRequis.forEach(champ => {
        if (!champ.value.trim()) {
            champ.style.borderColor = 'red';
            valide = false;
        } else {
            champ.style.borderColor = '';
        }
    });
    
    if (!valide) {
        e.preventDefault();
        alert('الرجاء تعبئة جميع الحقول المطلوبة');
        return false;
    }
    
    // Demander confirmation
    if (!confirm('هل أنت متأكد من رغبتك في تقديم النموذج؟')) {
        e.preventDefault();
        return false;
    }
});
</script>

<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
    text-align: center;
    vertical-align: middle;
}

.table td {
    vertical-align: middle;
}

.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.card-header {
    border-bottom: 2px solid rgba(0,0,0,.125);
}

.difference {
    background-color: #f8f9fa;
    font-weight: bold;
}

@media print {
    .app-content-header, .card-header button, .btn, .select2-container {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .card-header {
        background-color: #fff !important;
        color: #000 !important;
        border-bottom: 2px solid #000 !important;
    }
    
    input, select, textarea {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    
    input[readonly], textarea[readonly] {
        background: transparent !important;
    }
}
</style>

<?php require_once("composit/footer.php"); ?>