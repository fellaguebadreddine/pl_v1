<?php
// dashboard_societe.php
require_once('../includes/initialiser.php');

// Vérification de l'utilisateur
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    $session->logout();
    redirect_to('../login.php');
}
if ($current_user->type !== 'utilisateur') {
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

// Définir la liste des tables avec leurs classes, noms et liens
$tables = [
    ['code' => 'tab1', 'nom' => 'الجدول 1 - المناصب العليا', 'classe' => 'Tableau1', 'lien_edit' => 'tab1.php?action=edit_tab1&id=', 'lien_add' => 'tab1.php?action=add_tab1'],
    ['code' => 'tab1_1', 'nom' => 'الملحق 1/1', 'classe' => 'Tableau1_1', 'lien_edit' => 'tab1_1.php?action=edit_tab1_1&id=', 'lien_add' => 'tab1_1.php?action=add_tab1_1'],
    ['code' => 'tab2', 'nom' => 'الجدول 2', 'classe' => 'Tableau2', 'lien_edit' => 'tab2.php?action=edit_tab2&id=', 'lien_add' => 'tab2.php?action=add_tab2'],
    ['code' => 'tab3', 'nom' => 'الجدول 3 - حركة الموظفين', 'classe' => 'Tableau3', 'lien_edit' => 'tab3.php?action=edit_tab3&id=', 'lien_add' => 'tab3.php?action=add_tab3'],
    ['code' => 'tab4', 'nom' => 'الجدول 4', 'classe' => 'Tableau4', 'lien_edit' => 'tab4.php?action=edit_tab4&id=', 'lien_add' => 'tab4.php?action=add_tab4'],
    ['code' => 'tab4_1', 'nom' => 'الملحق 4/1', 'classe' => 'Tableau4_1', 'lien_edit' => 'tab4_1.php?action=edit_tab4_1&id=', 'lien_add' => 'tab4_1.php?action=add_tab4_1'],
    ['code' => 'tab5', 'nom' => 'الجدول 5', 'classe' => 'Tableau5', 'lien_edit' => 'tab5.php?action=edit_tab5&id=', 'lien_add' => 'tab5.php?action=add_tab5'],
    // Ajoutez ici les autres tables (tab2, tab6, etc.) selon votre besoin
];

// Récupérer le statut de chaque table pour l'année courante
$statuts = [];
foreach ($tables as $t) {
    $classe = $t['classe'];
    if (class_exists($classe)) {
        // On cherche s'il existe un enregistrement pour cette société et année
        // On suppose que chaque classe a une méthode trouve_par_societe_annee ou similaire
        // Sinon on peut faire une requête générique
        $method = 'trouve_par_societe_annee'; // à implémenter dans chaque classe
        if (method_exists($classe, $method)) {
            $obj = $classe::$method($societe->id_societe, $annee_courante);
        } else {
            // Fallback : on cherche le premier enregistrement pour cette société et année
            $obj = $classe::trouve_par_criteres(['id_societe' => $societe->id_societe, 'annee' => $annee_courante]);
            if (is_array($obj) && !empty($obj)) {
                $obj = $obj[0];
            } else {
                $obj = null;
            }
        }
        if ($obj) {
            $statuts[$t['code']] = [
                'id' => $obj->id,
                'statut' => $obj->statut,
                'date_modif' => $obj->date_creation ?? $obj->date_valide ?? '',
                'objet' => $obj
            ];
        } else {
            $statuts[$t['code']] = null;
        }
    } else {
        $statuts[$t['code']] = null;
    }
}

// Compteurs
$total_tables = count($tables);
$termines = 0;
$en_attente = 0;
$brouillons = 0;
$non_commences = 0;

foreach ($statuts as $s) {
    if (!$s) {
        $non_commences++;
    } else {
        switch ($s['statut']) {
            case 'validé':
                $termines++;
                break;
            case 'en_attente':
                $en_attente++;
                break;
            case 'brouillon':
                $brouillons++;
                break;
            default:
                $non_commences++;
        }
    }
}

$titre = "لوحة القيادة - " . $societe->raison_ar;
$active_menu = "dashboard";
$active_submenu = "dashboard";

require_once("composit/header.php");
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i> لوحة القيادة</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active">لوحة القيادة</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <!-- Informations société et année -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5><i class="fas fa-building me-2 text-primary"></i> <?php echo $societe->raison_ar; ?></h5>
                                    <?php if (!empty($societe->ice)): ?>
                                        <p class="mb-1"><strong>ICE :</strong> <?php echo $societe->ice; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="badge bg-success p-3" style="font-size: 1.2rem;">
                                        <i class="fas fa-calendar-alt me-1"></i> السنة المالية : <?php echo $annee_courante; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="card-title">مرحباً</h6>
                                <h4><?php echo $current_user->prenom . ' ' . $current_user->nom; ?></h4>
                                <p class="mb-0"><i class="fas fa-user-tie me-1"></i> مدير المؤسسة</p>
                            </div>
                            <div>
                                <i class="fas fa-user-circle fa-4x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cartes de progression -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $total_tables; ?></h3>
                            <p>إجمالي الجداول</p>
                        </div>
                        <div class="icon"><i class="fas fa-table"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $termines; ?></h3>
                            <p>مكتملة</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $en_attente; ?></h3>
                            <p>في انتظار المراجعة</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $brouillons + $non_commences; ?></h3>
                            <p>غير مكتملة</p>
                        </div>
                        <div class="icon"><i class="fas fa-pencil-alt"></i></div>
                    </div>
                </div>
            </div>

            <!-- Barre de progression globale -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">التقدم العام</h5>
                </div>
                <div class="card-body">
                    <?php $pourcentage = $total_tables > 0 ? round(($termines / $total_tables) * 100) : 0; ?>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $pourcentage; ?>%;" aria-valuenow="<?php echo $pourcentage; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $pourcentage; ?>% مكتمل
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-3"><span class="badge bg-success"><?php echo $termines; ?> مكتملة</span></div>
                        <div class="col-3"><span class="badge bg-warning"><?php echo $en_attente; ?> في المراجعة</span></div>
                        <div class="col-3"><span class="badge bg-info"><?php echo $brouillons; ?> مسودات</span></div>
                        <div class="col-3"><span class="badge bg-secondary"><?php echo $non_commences; ?> غير مبدوءة</span></div>
                    </div>
                </div>
            </div>

            <!-- Liste des tables -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">حالة الجداول للسنة <?php echo $annee_courante; ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>الجدول</th>
                                    <th>الحالة</th>
                                    <th>آخر تحديث</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables as $t): 
                                    $code = $t['code'];
                                    $stat = $statuts[$code] ?? null;
                                ?>
                                <tr>
                                    <td><?php echo $t['nom']; ?></td>
                                    <td>
                                        <?php if (!$stat): ?>
                                            <span class="badge bg-secondary">غير مبدوء</span>
                                        <?php else: 
                                            $s = $stat['statut'];
                                            $badge = 'secondary';
                                            $texte = $s;
                                            if ($s == 'validé') { $badge = 'success'; $texte = 'مصادق عليه'; }
                                            elseif ($s == 'en_attente') { $badge = 'warning'; $texte = 'في انتظار المراجعة'; }
                                            elseif ($s == 'brouillon') { $badge = 'info'; $texte = 'مسودة'; }
                                        ?>
                                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $texte; ?></span>
                                            <?php if (!empty($stat['objet']->commentaire_admin)): ?>
                                                <i class="fas fa-comment text-info ms-1" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($stat['objet']->commentaire_admin); ?>"></i>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($stat): ?>
                                            <?php echo date('d/m/Y', strtotime($stat['date_modif'])); ?>
                                        <?php else: ?>
                                            ---
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$stat): ?>
                                            <a href="<?php echo $t['lien_add']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> إضافة</a>
                                        <?php else: ?>
                                            <a href="<?php echo $t['lien_edit'] . $stat['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> تعديل</a>
                                            <a href="print_<?php echo $code; ?>.php?id=<?php echo $stat['id']; ?>" class="btn btn-sm btn-info" target="_blank"><i class="fas fa-print"></i> طباعة</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Activation des tooltips -->
<script>
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>

<style>
.small-box {
    border-radius: 0.5rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    display: block;
    margin-bottom: 20px;
    position: relative;
    text-decoration: none;
    color: #fff;
}
.small-box > .inner {
    padding: 10px;
}
.small-box h3 {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 10px;
    padding: 0;
    white-space: nowrap;
}
.small-box .icon {
    color: rgba(0,0,0,0.15);
    position: absolute;
    top: -10px;
    left: 10px;
    z-index: 0;
    font-size: 70px;
    transform: rotate(-15deg);
}
.bg-info { background-color: #17a2b8 !important; }
.bg-success { background-color: #28a745 !important; }
.bg-warning { background-color: #ffc107 !important; }
.bg-danger { background-color: #dc3545 !important; }
.opacity-50 { opacity: 0.5; }
</style>

<?php require_once("composit/footer.php"); ?>