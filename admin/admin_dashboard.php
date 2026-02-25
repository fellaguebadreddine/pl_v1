<?php
// admin_dashboard.php
require_once('../includes/initialiser.php');

// Vérifier que l'utilisateur est admin ou super admin
if (!$session->is_logged_in()) {
    redirect_to('../login.php');
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || ($current_user->type !== 'administrateur' && $current_user->type !== 'super_administrateur')) {
    redirect_to('../login.php');
}

// Récupérer l'exercice actif
$exercice_actif = Exercice::get_exercice_actif();
$annee_courante = $exercice_actif ? $exercice_actif->annee : date('Y');

// Définir la liste des tables (code, nom, table_name)
$tables = [
    ['code' => 'tab1', 'nom' => 'الجدول 1 - المناصب العليا', 'table' => 'tableau1'],
    ['code' => 'tab1_1', 'nom' => 'الملحق 1/1', 'table' => 'tableau1_1'],
    ['code' => 'tab2', 'nom' => 'الجدول 2', 'table' => 'tableau2'],
    ['code' => 'tab3', 'nom' => 'الجدول 3 - حركة الموظفين', 'table' => 'tableau3'],
    ['code' => 'tab4', 'nom' => 'الجدول 4', 'table' => 'tableau4'],
    ['code' => 'tab4_1', 'nom' => 'الملحق 4/1', 'table' => 'tableau4_1'],
    ['code' => 'tab5', 'nom' => 'الجدول 5', 'table' => 'tableau5'],
    // Ajoutez ici les autres tables
];



// ==========================
// 1️⃣ Récupérer les sociétés
// ==========================

$societes = [];

// Si tu as une méthode trouve_tous() en MySQLi, garde-la.
// Sinon fallback direct :

$societes = Societe::trouve_par_societe();

// ==========================
// 2️⃣ Calcul des statistiques
// ==========================

$stats = [];

foreach ($societes as $soc) {

    $total = count($tables);
    $termines = 0;
    $en_attente = 0;
    $brouillons = 0;
    $non_commences = 0;
    $details = [];

    foreach ($tables as $t) {

        $table = $t['table'];

        // ⚠️ sécuriser le nom de table
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            continue;
        }

        $sql = "SELECT statut 
                FROM `$table` 
                WHERE id_societe = ? 
                AND annee = ? 
                LIMIT 1";

        if ($stmt = $bd->prepare($sql)) {

            $stmt->bind_param("ii", $soc->id_societe, $annee_courante);
            $stmt->execute();

            $result = $stmt->get_result();
            $record = $result->fetch_object();

            if ($record) {

                $statut = $record->statut;
                $details[$t['code']] = $statut;

                switch ($statut) {
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

            } else {
                $details[$t['code']] = null;
                $non_commences++;
            }

            $stmt->close();
        }
    }

    $stats[$soc->id_societe] = [
        'societe' => $soc,
        'total' => $total,
        'termines' => $termines,
        'en_attente' => $en_attente,
        'brouillons' => $brouillons,
        'non_commences' => $non_commences,
        'details' => $details,
        'pourcentage' => $total > 0 
            ? round(($termines / $total) * 100) 
            : 0
    ];
}

$titre = "لوحة القيادة - الإدارة";
$active_menu = "admin_dashboard";
require_once("composit/header.php");
?>

<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i> لوحة قيادة الإدارة</h3>
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
            <!-- Filtre année -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <form method="get" class="form-inline">
                                <label for="annee" class="me-2">السنة :</label>
                                <select name="annee" id="annee" class="form-control" onchange="this.form.submit()">
                                    <?php
                                    $annees = range($annee_courante - 5, $annee_courante + 1);
                                    foreach ($annees as $a):
                                    ?>
                                    <option value="<?php echo $a; ?>" <?php echo $a == $annee_courante ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Résumé global -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo count($societes); ?></h3>
                            <p>عدد المؤسسات</p>
                        </div>
                        <div class="icon"><i class="fas fa-building"></i></div>
                    </div>
                </div>
                <?php
                $total_tables_global = count($tables) * count($societes);
                $total_termines_global = array_sum(array_column($stats, 'termines'));
                $total_en_attente_global = array_sum(array_column($stats, 'en_attente'));
                $total_brouillons_global = array_sum(array_column($stats, 'brouillons'));
                $total_non_commences_global = array_sum(array_column($stats, 'non_commences'));
                $pourcentage_global = $total_tables_global > 0 ? round(($total_termines_global / $total_tables_global) * 100) : 0;
                ?>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $total_termines_global; ?> / <?php echo $total_tables_global; ?></h3>
                            <p>مكتملة</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $total_en_attente_global; ?></h3>
                            <p>في انتظار المراجعة</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $total_non_commences_global + $total_brouillons_global; ?></h3>
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
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $pourcentage_global; ?>%;" aria-valuenow="<?php echo $pourcentage_global; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $pourcentage_global; ?>% مكتمل
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-3"><span class="badge bg-success"><?php echo $total_termines_global; ?> مكتملة</span></div>
                        <div class="col-3"><span class="badge bg-warning"><?php echo $total_en_attente_global; ?> في المراجعة</span></div>
                        <div class="col-3"><span class="badge bg-info"><?php echo $total_brouillons_global; ?> مسودات</span></div>
                        <div class="col-3"><span class="badge bg-secondary"><?php echo $total_non_commences_global; ?> غير مبدوءة</span></div>
                    </div>
                </div>
            </div>

            <!-- Tableau des sociétés -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">تفاصيل التقدم حسب المؤسسة - السنة <?php echo $annee_courante; ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>المؤسسة</th>
                                    <th>ICE</th>
                                    <th>المسؤول</th>
                                    <th>التقدم</th>
                                    <th>مكتملة</th>
                                    <th>في المراجعة</th>
                                    <th>مسودة</th>
                                    <th>غير مبدوءة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($societes as $index => $soc): 
                                    $s = $stats[$soc->id_societe];
                                ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $soc->raison_ar; ?></td>
                                    <td><?php echo $soc->ice ?? '---'; ?></td>
                                    <td>
                                        <?php
                                        // Récupérer le responsable de la société (utilisateur de type utilisateur)
                                        $sql = "SELECT prenom, nom FROM accounts WHERE id_societe = ? AND type = 'utilisateur' LIMIT 1";
                                        $stmt = $bd->prepare($sql);
                                        $stmt->execute([$soc->id_societe]);
                                        $resp = $stmt->fetch(PDO::FETCH_OBJ);
                                        echo $resp ? $resp->prenom . ' ' . $resp->nom : '---';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $s['pourcentage']; ?>%;" aria-valuenow="<?php echo $s['pourcentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $s['pourcentage']; ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo $s['termines']; ?></td>
                                    <td class="text-center"><?php echo $s['en_attente']; ?></td>
                                    <td class="text-center"><?php echo $s['brouillons']; ?></td>
                                    <td class="text-center"><?php echo $s['non_commences']; ?></td>
                                    <td>
                                        <a href="admin_societe_details.php?id_societe=<?php echo $soc->id_societe; ?>&annee=<?php echo $annee_courante; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> تفاصيل
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="envoyerRappel(<?php echo $soc->id_societe; ?>)">
                                            <i class="fas fa-bell"></i> تذكير
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Détail par table (optionnel) -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">حالة كل جدول (cliquez sur une société pour voir le détail)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Pour voir le détail de chaque table pour une société, utilisez le bouton "تفاصيل" dans le tableau ci-dessus.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function envoyerRappel(idSociete) {
    if (confirm('هل أنت متأكد من إرسال تذكير إلى هذه المؤسسة؟')) {
        $.ajax({
            url: 'ajax/envoyer_rappel.php',
            type: 'POST',
            data: { id_societe: idSociete, annee: <?php echo $annee_courante; ?> },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    alert('تم إرسال التذكير بنجاح');
                } else {
                    alert('خطأ: ' + res.message);
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال');
            }
        });
    }
}
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