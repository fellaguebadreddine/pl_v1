<?php
// super_admin_avancement.php
require_once('../includes/initialiser.php');

// Vérifier que l'utilisateur est super admin
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'super_admin') {
    $session->logout();
    redirect_to('../login.php');
}

// Récupérer l'exercice actif
$exercice_actif = Exercice::get_exercice_actif();
$annee_courante = $exercice_actif ? $exercice_actif->annee : date('Y');

// Gestion des filtres
$annee_filter = isset($_GET['annee']) ? intval($_GET['annee']) : $annee_courante;
$wilaya_filter = isset($_GET['wilaya']) ? intval($_GET['wilaya']) : 0; // 0 = toutes

// Récupérer toutes les wilayas disponibles (depuis la table societe)
$wilayas = Wilayas::trouve_tous(); // méthode à implémenter, retourne liste d'objets avec id et nom

// Récupérer les statistiques globales
$stats_globales = SuperAdmin::get_stats_globales($annee_filter);

// Récupérer les statistiques par wilaya (pour le graphique et le tableau)
if ($wilaya_filter == 0) {
    $stats_wilayas = SuperAdmin::get_stats_avancement_par_wilaya($annee_filter);
} else {
    // Si une wilaya est filtrée, on peut afficher les détails des sociétés de cette wilaya
    $stats_wilayas = []; // on ne les utilise pas pour le tableau principal
    $societes_wilaya = Societe::trouve_par_wilaya($wilaya_filter); // méthode à implémenter
    // On va plutôt afficher un tableau des sociétés de cette wilaya
}

$titre = "تتبع التقدم حسب الولايات";
$active_menu = "super_admin";
$active_submenu = "avancement";
$header = array('chart.js', 'select2');
require_once("composit/header.php");
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-chart-line me-2"></i> تتبع التقدم حسب الولايات</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="super_admin.php">الإدارة العامة</a></li>
                        <li class="breadcrumb-item active">تتبع التقدم حسب الولايات</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <!-- Filtres -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="fas fa-filter me-2"></i> تصفية النتائج</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" class="row g-3">
                                <div class="col-md-4">
                                    <label for="annee" class="form-label">السنة المالية</label>
                                    <select name="annee" id="annee" class="form-control select2">
                                        <?php 
                                        $annees = Exercice::trouve_tous(); // retourne tous les exercices
                                        foreach ($annees as $a): 
                                        ?>
                                            <option value="<?php echo $a->annee; ?>" <?php echo $a->annee == $annee_filter ? 'selected' : ''; ?>>
                                                <?php echo $a->annee; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="wilaya" class="form-label">الولاية</label>
                                    <select name="wilaya" id="wilaya" class="form-control select2">
                                        <option value="0">جميع الولايات</option>
                                        <?php foreach ($wilayas as $w): ?>
                                            <option value="<?php echo $w->id; ?>" <?php echo $w->id == $wilaya_filter ? 'selected' : ''; ?>>
                                                <?php echo $w->nom; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-1"></i> عرض
                                    </button>
                                    <a href="?" class="btn btn-secondary">
                                        <i class="fas fa-undo me-1"></i> إعادة تعيين
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($wilaya_filter == 0): ?>
                <!-- Vue globale par wilayas -->
                <!-- Cartes des indicateurs globaux -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $stats_globales['total_societes']; ?></h3>
                                <p>إجمالي المؤسسات</p>
                            </div>
                            <div class="icon"><i class="fas fa-building"></i></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?php echo $stats_globales['tableaux_valides']; ?> / <?php echo $stats_globales['total_tableaux']; ?></h3>
                                <p>جداول مصادق عليها</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                            <div class="small-box-footer">
                                <span class="badge bg-white text-success p-2 mt-1"><?php echo $stats_globales['taux_validation_global']; ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?php echo $stats_globales['societes_actives']; ?></h3>
                                <p>مؤسسات نشطة</p>
                            </div>
                            <div class="icon"><i class="fas fa-chart-pie"></i></div>
                            <div class="small-box-footer">
                                <span class="badge bg-white text-warning p-2 mt-1"><?php echo $stats_globales['taux_participation']; ?>%</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Graphique d'avancement par wilaya -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-gradient-dark text-white">
                                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i> معدل الإنجاز حسب الولايات</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="avancementChart" style="height: 400px; width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau détaillé par wilaya -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0"><i class="fas fa-table me-2"></i> تفاصيل التقدم حسب الولايات - سنة <?php echo $annee_filter; ?></h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>الولاية</th>
                                                <th>عدد المؤسسات</th>
                                                <th>المؤسسات النشطة</th>
                                                <th>الجداول المقدمة</th>
                                                <th>في الانتظار</th>
                                                <th>مصادق عليها</th>
                                                <th>مسودة</th>
                                                <th>معدل الإنجاز</th>
                                                <th>الإجراءات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats_wilayas as $wilaya): ?>
                                            <tr>
                                                <td><?php echo $wilaya['wilaya']; ?></td>
                                                <td><?php echo $wilaya['total_societes']; ?></td>
                                                <td><?php echo $wilaya['societes_actives']; ?></td>
                                                <td><?php echo $wilaya['tableaux_soumis']; ?></td>
                                                <td><?php echo $wilaya['en_attente']; ?></td>
                                                <td><?php echo $wilaya['valides']; ?></td>
                                                <td><?php echo $wilaya['brouillons']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?php echo $wilaya['taux_avancement'] >= 80 ? 'success' : ($wilaya['taux_avancement'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                             style="width: <?php echo $wilaya['taux_avancement']; ?>%;">
                                                            <?php echo $wilaya['taux_avancement']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="?wilaya=<?php echo $wilaya['id']; ?>&annee=<?php echo $annee_filter; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> تفاصيل
                                                    </a>
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

            <?php else: ?>
                <!-- Vue détaillée d'une wilaya spécifique -->
                <?php
                $wilaya_nom = ''; // à récupérer depuis $wilayas
                foreach ($wilayas as $w) {
                    if ($w->id == $wilaya_filter) {
                        $wilaya_nom = $w->nom;
                        break;
                    }
                }
                $societes = Societe::trouve_par_wilaya($wilaya_filter); // à implémenter
                ?>
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">تفاصيل ولاية <?php echo $wilaya_nom; ?> - سنة <?php echo $annee_filter; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>المؤسسة</th>
                                        <th>الجداول</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($societes as $soc): 
                                        // Récupérer le statut du tableau principal (par exemple tableau1) pour l'année
                                        $tableau = Tableau1::trouve_par_societe_annee($soc->id_societe, $annee_filter);
                                        $statut = $tableau ? $tableau->statut : 'non_commence';
                                        $responsable = Accounts::trouve_responsable_societe($soc->id_societe); // méthode à implémenter
                                    ?>
                                    <tr>
                                        <td><?php echo $soc->raison_ar; ?></td>
                                        <td>1/12</td> <!-- À remplacer par le nombre réel de tableaux remplis / total -->
                                        <td>
                                            <?php
                                            $badge = 'secondary';
                                            $texte = 'غير مبدوء';
                                            if ($statut == 'validé') { $badge = 'success'; $texte = 'مصادق عليه'; }
                                            elseif ($statut == 'en_attente') { $badge = 'warning'; $texte = 'في الانتظار'; }
                                            elseif ($statut == 'brouillon') { $badge = 'info'; $texte = 'مسودة'; }
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $texte; ?></span>
                                        </td>
                                        <td>
                                            <a href="admin_societe_details.php?id_societe=<?php echo $soc->id_societe; ?>&annee=<?php echo $annee_filter; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="?" class="btn btn-secondary"><i class="fas fa-arrow-right me-1"></i> رجوع إلى القائمة العامة</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>



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
.small-box > .inner { padding: 10px; }
.small-box h3 { font-size: 2.2rem; font-weight: 700; margin: 0 0 10px; }
.small-box .icon {
    color: rgba(0,0,0,0.15);
    position: absolute;
    top: -10px;
    left: 10px;
    z-index: 0;
    font-size: 70px;
    transform: rotate(-15deg);
}
.bg-gradient-dark { background: linear-gradient(45deg, #343a40, #1d2124); }
.progress { border-radius: 20px; }
.progress-bar { border-radius: 20px; }
</style>

<?php require_once("composit/footer.php"); ?>