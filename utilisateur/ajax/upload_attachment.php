// ajax/upload_attachment.php
<?php
session_start();
require_once("../../includes/initialiser.php");

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

// Déterminer la classe du tableau selon le type
$class_map = [
    'tab1' => 'Tableau1',
    'tabl_1_1' => 'Tableau1_1',
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

// Traitement du fichier
if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'message' => 'Aucun fichier reçu ou erreur d\'upload']));
}

$allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
$file_info = pathinfo($_FILES['file']['name']);
$extension = strtolower($file_info['extension']);
if (!in_array($extension, $allowed_extensions)) {
    die(json_encode(['success' => false, 'message' => 'Type de fichier non autorisé']));
}

$upload_dir = '../uploads/attachments/' . $table_type . '/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Générer un nom unique
$filename = uniqid() . '_' . $record_id . '.' . $extension;
$file_path = $upload_dir . $filename;

if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
    // Mettre à jour la colonne attachment de l'enregistrement
    $record->attachment = $file_path;
    if ($record->save()) {
        echo json_encode(['success' => true, 'message' => 'Fichier uploadé avec succès']);
    } else {
        unlink($file_path);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement du fichier']);
}