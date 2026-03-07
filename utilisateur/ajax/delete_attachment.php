// ajax/delete_attachment.php
<?php
require_once('../../includes/initialiser.php');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}
if (!$session->is_logged_in()) {
    die(json_encode(['success' => false, 'message' => 'Session expirée']));
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'utilisateur') {
    die(json_encode(['success' => false, 'message' => 'غير مصرح']));
}

$table_type = $_POST['table_type'] ?? '';
$record_id = intval($_POST['record_id'] ?? 0);
if (empty($table_type) || $record_id <= 0) {
    die(json_encode(['success' => false, 'message' => 'Paramètres manquants']));
}

$class_map = [
    'tab1' => 'Tableau1',
    'tab1_1' => 'Tableau1_1',
    'tab2' => 'Tableau2',
    'tab3' => 'Tableau3',
    'tab4' => 'Tableau4',
    'tab4_1' => 'Tableau4_1',
    'tab5' => 'Tableau5',
    'tab6' => 'Tableau6'
];
if (!isset($class_map[$table_type])) {
    die(json_encode(['success' => false, 'message' => 'Type de tableau inconnu']));
}
$classe = $class_map[$table_type];

$record = $classe::trouve_par_id($record_id);
if (!$record || $record->id_societe != $current_user->id_societe) {
    die(json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé']));
}

if (!empty($record->attachment)) {
    $file_path = $record->attachment;
    $record->attachment = null;
    if ($record->save()) {
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        echo json_encode(['success' => true, 'message' => 'مرفق محذوف']);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في الحذف']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'لا يوجد مرفق']);
}