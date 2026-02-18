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

// Récupérer l'exercice actif
$exercice_actif = Exercice::get_exercice_actif();
$annee_courante = $exercice_actif ? $exercice_actif->annee : date('Y');

// Filtres
$wilaya_filter = isset($_GET['wilaya']) ? $_GET['wilaya'] : '';
$annee_filter = isset($_GET['annee']) ? intval($_GET['annee']) : $annee_courante;

// Récupérer toutes les wilayas disponibles
$wilayas = Wilayas::trouve_tous();

// Récupérer les statistiques par wilaya
$stats_wilayas = array();

// Statistiques globales
$stats_globales = array();

// Récupérer les wilayas avec leurs détails
$wilayas_data = [];
foreach ($stats_wilayas as $stat) {
    $wilayas_data[$stat['nom']] = $stat;
}

$titre = "تتبع التقدم حسب الولايات - الإدارة العامة للوظيف العمومي";
$active_menu = "avancement_wilayas";
$active_submenu = "avancement_wilayas";

if ($current_user->type =='super_admin' ){

	require_once("composit/header.php");
}
?>
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
    .small-box > .small-box-footer {
        background: rgba(0,0,0,0.1);
        color: rgba(255,255,255,0.8);
        display: block;
        padding: 3px 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 10;
        border-radius: 0 0 0.5rem 0.5rem;
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
    .bg-gradient-dark {
        background: linear-gradient(45deg, #343a40, #1d2124);
    }
    .opacity-50 {
        opacity: 0.5;
    }
    .progress {
        border-radius: 20px;
        background-color: #e9ecef;
    }
    .progress-bar {
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    @media print {
        .no-print, .small-box-footer, .btn, .card-tools {
            display: none !important;
        }
    }
</style>

<!--begin::App Main-->
<main class="app-main">
    <!--begin::App Content Header-->
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
    <!--end::App Content Header-->

    <!--begin::App Content-->
    <div class="app-content">
        <div class="container-fluid">
            
            <!-- En-tête avec informations de l'exercice -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <h4><i class="fas fa-calendar-alt me-2"></i> السنة المالية: <?php echo $annee_courante; ?></h4>
                                <?php if ($exercice_actif): ?>
                                    <p class="mb-0">
                                        <?php echo "من " . date('d/m/Y', strtotime($exercice_actif->date_debut)) . " إلى " . date('d/m/Y', strtotime($exercice_actif->date_fin)); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <i class="fas fa-flag fa-4x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                                        $annees = Exercice::trouve_tous();
                                        foreach ($annees as $a): 
                                        ?>
                                            <option value="<?php echo $a->id; ?>" <?php echo $a->id == $annee_filter ? 'selected' : ''; ?>>
                                                <?php echo $a->annee; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="wilaya" class="form-label">الولاية</label>
                                    <select name="wilaya" id="wilaya" class="form-control select2">
                                        <option value="">جميع الولايات</option>
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
                                    <a href="?annee=<?php echo $annee_courante; ?>" class="btn btn-secondary">
                                        <i class="fas fa-undo me-1"></i> إعادة تعيين
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cartes des indicateurs globaux -->
           

            <!-- Graphique d'avancement par wilaya -->
           
            <!-- Tableau détaillé par wilaya -->
            

            <!-- Top 5 wilayas les plus avancées -->
            

        </div>
    </div>
    <!--end::App Content-->
</main>
<!--end::App Main-->




<?php require_once("composit/footer.php"); ?>