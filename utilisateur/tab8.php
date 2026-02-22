<?php
// tab4.php - Page principale pour les utilisateurs société
require_once('../includes/initialiser.php');

if (!$session->is_logged_in()) redirect_to('../login.php');
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) { $session->logout(); redirect_to('../login.php'); }
if ($current_user->type !== 'utilisateur') { $session->logout(); redirect_to('../login.php'); }
if (!$current_user->id_societe) redirect_to('../login.php');
$societe = Societe::trouve_par_id($current_user->id_societe);
if (!$societe) redirect_to('../login.php');

$exercice_actif = Exercice::get_exercice_actif();
$action = isset($_GET['action']) ? $_GET['action'] : 'list_tab4';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$titre = "الجدول 8";
$active_menu = "tab_8";
$active_submenu = "tab_8";
$header = array('select2');

require_once("composit/header.php");
?>
<?php
$annee = $exercice_actif ? $exercice_actif->annee : date('Y');
$existe = Tableau4::existe_pour_societe_annee($current_user->id_societe, $annee);
$tabls = Tableau4::trouve_tableau_1_par_id($societe->id_societe);


if ($action == "add_tab8") {

$annee = $exercice_actif ? $exercice_actif->annee : date('Y');

$existe = Tableau4::existe_pour_societe_annee(
    $current_user->id_societe,
    $annee
);

if ($existe) {
    redirect_to("?action=list_tab8");
    exit;
}
}
?>