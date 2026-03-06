<?php
// employee.php
require_once('../includes/initialiser.php');

// Vérification de l'utilisateur
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'utilisateur') {
    $session->logout();
    redirect_to('../login.php');
}
if (!$current_user->id_societe) {
    redirect_to('../login.php');
}
$societe = Societe::trouve_par_id($current_user->id_societe);
if (!$societe) {
    redirect_to('../login.php');
}
$exercice_actif = Exercice::get_exercice_actif();
$annee_courante = $exercice_actif ? $exercice_actif->annee : date('Y');

// Déterminer l'action
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $date_naissance = $_POST['date_naissance'];
                $date_debut_emplois = $_POST['date_debut_emplois'];
                $id_grade = intval($_POST['id_grade']);

                // Validation simple
                if (empty($nom) || empty($prenom) || empty($date_naissance) || empty($date_debut_emplois) || empty($id_grade)) {
                    $error = "جميع الحقول مطلوبة";
                } else {
                    $employee = new Employees();
                    $employee->nom = $nom;
                    $employee->prenom = $prenom;
                    $employee->date_naissance = $date_naissance;
                    $employee->date_debut_emplois = $date_debut_emplois;
                    $employee->id_grade = $id_grade;
                    $employee->id_societe = $societe->id_societe;
                    $employee->nbr_annee = $annee_courante - date('Y', strtotime($date_debut_emplois));
                    if ($employee->save()) {
                        redirect_to('employee.php?action=list&success=1');
                    } else {
                        $error = "خطأ أثناء الحفظ";
                    }
                }
                break;
            case 'edit':
                $id_emp = intval($_POST['id']);
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $date_naissance = $_POST['date_naissance'];
                $date_debut_emplois = $_POST['date_debut_emplois'];
                $id_grade = intval($_POST['id_grade']);

                $employee = Employees::trouve_par_id($id_emp);
                if ($employee && $employee->id_societe == $societe->id_societe) {
                    $employee->nom = $nom;
                    $employee->prenom = $prenom;
                    $employee->date_naissance = $date_naissance;
                    $employee->date_debut_emplois = $date_debut_emplois;
                    $employee->id_grade = $id_grade;
                    $employee->nbr_annee = $annee_courante - date('Y', strtotime($date_debut_emplois));
                    if ($employee->save()) {
                        redirect_to('employee.php?action=list&success=2');
                    } else {
                        $error = "خطأ أثناء التحديث";
                    }
                } else {
                    $error = "غير مصرح بهذا الإجراء";
                }
                break;
            case 'delete':
                $id_emp = intval($_POST['id']);
                $employee = Employees::trouve_par_id($id_emp);
                if ($employee && $employee->id_societe == $societe->id_societe) {
                    if ($employee->delete()) {
                        redirect_to('employee.php?action=list&success=3');
                    } else {
                        $error = "خطأ أثناء الحذف";
                    }
                } else {
                    $error = "غير مصرح بهذا الإجراء";
                }
                break;
        }
    }
}

// Récupérer tous les grades pour le select
$grades = Grade::trouve_tous();

$titre = "إدارة الموظفين";
$active_menu = "employee";
$active_submenu = "";
$header = array('select2'); // pour le select
require_once("composit/header.php");
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-users me-2"></i> إدارة الموظفين</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active">الموظفين</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                    $msg = [
                        1 => "تمت إضافة الموظف بنجاح",
                        2 => "تم تحديث بيانات الموظف بنجاح",
                        3 => "تم حذف الموظف بنجاح"
                    ];
                    echo $msg[$_GET['success']] ?? "تمت العملية بنجاح";
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action == 'list'): ?>
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i> قائمة الموظفين</h5>
                        <a href="?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> إضافة موظف</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم</th>
                                        <th>اللقب</th>
                                        <th>تاريخ الميلاد</th>
                                        <th>السلك أو الرتبة</th>
                                        <th> تاريخ التنصيب </th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $employes = Employees::trouve_par_societe($societe->id_societe);
                                    if (!empty($employes)):
                                        foreach ($employes as $emp):
                                            $grade = Grade::trouve_par_id($emp->id_grade);
                                    ?>
                                    <tr>
                                        <td><?php echo $emp->id; ?></td>
                                        <td><?php echo htmlspecialchars($emp->prenom); ?></td>
                                        <td><?php echo htmlspecialchars($emp->nom); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($emp->date_naissance)); ?></td>
                                        <td><?php echo $grade ? $grade->grade : '---'; ?></td>
                                         <td><?php echo date('d/m/Y', strtotime($emp->date_debut_emplois)); ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $emp->id; ?>" class="btn btn-sm btn-warning me-1" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $emp->id; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-users fa-2x text-muted mb-3 d-block"></i>
                                            لا يوجد موظفون مسجلون
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($action == 'add' || $action == 'edit'): ?>
                <?php
                $employee = null;
                if ($action == 'edit' && $id > 0) {
                    $employee = Employees::trouve_par_id($id);
                    if (!$employee || $employee->id_societe != $societe->id_societe) {
                        redirect_to('employee.php?action=list');
                    }
                }
                ?>
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas <?php echo $action == 'add' ? 'fa-plus' : 'fa-edit'; ?> me-2"></i>
                            <?php echo $action == 'add' ? 'إضافة موظف جديد' : 'تعديل بيانات الموظف'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="post" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="<?php echo $action; ?>">
                            <?php if ($action == 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $employee->id; ?>">
                            <?php endif; ?>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">الاسم <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" required
                                           value="<?php echo $employee ? htmlspecialchars($employee->prenom) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">اللقب <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" required
                                           value="<?php echo $employee ? htmlspecialchars($employee->nom) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_naissance" class="form-label">تاريخ الميلاد <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" required
                                           value="<?php echo $employee ? $employee->date_naissance : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="id_grade" class="form-label">الرتبة <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="id_grade" name="id_grade" required>
                                        <option value="">اختر الدرجة</option>
                                        <?php foreach ($grades as $g): ?>
                                            <option value="<?php echo $g->id; ?>" <?php echo ($employee && $employee->id_grade == $g->id) ? 'selected' : ''; ?>>
                                                <?php echo $g->grade; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="date_debut_emplois" class="form-label">تاريخ التنصيب <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_debut_emplois" name="date_debut_emplois" required
                                           value="<?php echo $employee ? $employee->date_debut_emplois : ''; ?>">
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="?action=list" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-1"></i> رجوع
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> 
                                    <?php echo $action == 'add' ? 'إضافة' : 'تحديث'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Activation de Select2
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        dir: 'rtl'
    });
});
</script>

<?php require_once("composit/footer.php"); ?>