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
?>
<?php

$action = isset($_GET['action']) ? $_GET['action'] : 'liste';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "إدارة الجداول - الإدارة العامة";

$active_menu = "tab_1";

$active_submenu = "tab_1";

$header = array('todo');

if ($current_user->type =='administrateur' or $current_user->type =='utilisateur'){

	require_once("composit/header.php");
}


// Exercices
$exercice_actif = Exercice::get_exercice_actif();
$annee_courante = $exercice_actif ? $exercice_actif->annee : date('Y');



?>


<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="fas fa-table me-2"></i>  جدول هيكلة التعدادات </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active">  جدول هيكلة التعدادات </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <?php if ($action == 'liste'): ?>
                <!-- Onglets de navigation -->
                 <div class="card">
               <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                     <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">ID</th>
                                        <th width="10%" class="text-center">السنة</th>
                                        <th width="10%" class="text-center">الحالة</th>
                                        <th width="15%" class="text-center">تاريخ التقديم</th>
                                        <th width="10%" class="text-center">إجمالي المناصب</th>
                                        <th width="10%" class="text-center">المناصب الحقيقية</th>
                                        <th width="10%" class="text-center">بالنيابة</th>
                                        <th width="10%" class="text-center">النساء</th>
                                        <th width="10%" class="text-center">الملاحظة</th>
                                        <th width="15%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $tabls = Tableau1::trouve_par_societe($nav_societe->id_societe);
                                    if (!empty($tabls)): 
                                        foreach ($tabls as $row): 
                                            $statut_badge = $row->statut == 'validé' ? 'success' : 
                                                          ($row->statut == 'en_attente' ? 'warning' : 'secondary');
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            
                                            <a href="print_tab_1.php?id=<?php echo $row->id; ?>" class="btn btn-sm btn-primary text-white" target="_blank">
                                            <i class="fa fa-print "></i> <?php echo $row->id; ?>
                                            </a>
                                        </td>
                                        <td class="text-center"><?php echo $row->annee; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $statut_badge; ?>">
                                                <?php echo $row->statut; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $row->date_valide ? date('d/m/Y', strtotime($row->date_valide)) : '---'; ?>
                                        </td>
                                        <td class="text-center"><?php echo $row->total; ?></td>
                                        <td class="text-center"><?php echo $row->total_reel; ?></td>
                                        <td class="text-center"><?php echo $row->total_intrim; ?></td>
                                        <td class="text-center"><?php echo $row->total_femmes; ?></td>
                                        <td><?php echo $row->commentaire_admin; ?></td>
                                        <td class="text-center">
                                      
                                            <a href="details_tableau.php?action=affchier_detail&id=<?php echo $row->id; ?>" 
                                               class="btn btn-sm btn-warning me-1" title="التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="commentTableau1(<?php echo $row->id; ?>)" 
                                                    class="btn btn-sm btn-danger" title="ملاحظة">
                                                <i class="fas fa-commenting"></i>
                                            </button>
                                            
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fas fa-table fa-2x text-muted mb-3 d-block"></i>
                                            لا توجد جداول مسجلة
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                        </tbody>
                    </table>
                                    </div>
                                    </div>
                                    </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once("composit/footer.php"); ?>

