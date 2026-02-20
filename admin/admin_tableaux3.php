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

$active_menu = "tab_3";

$active_submenu = "tab_3";

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
                    <h3 class="mb-0"><i class="fas fa-table me-2"></i>    الجدول 3 </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                        <li class="breadcrumb-item active">  جدول  3 </li>
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
                                        <th width="10%" class="text-center">الملاحظة</th>
                                        <th width="15%" class="text-center">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $tabls = Tableau3::trouve_tableau_3_par_id($nav_societe->id_societe);
                                    ?>
                                    <?php 
                                   
                                    if (!empty($tabls)): 
                                       
                                            $statut_badge = $tabls->statut == 'validé' ? 'success' : 
                                                          ($tabls->statut == 'brouillon' ? 'warning' : 'secondary');
                                    ?>
                                    <tr>
                                       <td class="text-center">
                                            <a href="../utilisateur/print_tab3.php?id=<?php echo $tabls->id; ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fa fa-print"></i> <?php echo $tabls->id; ?>
                                            </a>
                                        </td>
                                        <td class="text-center"><?php echo $tabls->annee; ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-<?php echo $statut_badge; ?>">
                                                <?php echo $tabls->statut; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $tabls->date_creation ? date('d/m/Y', strtotime($tabls->date_creation)) : '---'; ?>
                                        </td>
                                        <td><?php echo $tabls->commentaire_admin; ?></td>
                                        <td class="text-center">
                                      
                                            <a href="details_tableau3.php?action=affchier_detail&id=<?php echo $tabls->id; ?>" 
                                               class="btn btn-sm btn-warning me-1" title="التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                        </td>
                                    </tr>
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

