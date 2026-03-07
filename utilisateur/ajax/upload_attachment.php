<?php
// ajax/upload_attachment.php
header('Content-Type: application/json');
require_once('../../includes/initialiser.php');

// Vérification de la requête AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

// Vérification de la session
if (!$session->is_logged_in()) {
    die(json_encode(['success' => false, 'message' => 'Session expirée']));
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'utilisateur') {
    die(json_encode(['success' => false, 'message' => 'غير مصرح بهذا الإجراء']));
}

// Récupération des paramètres
$table_type = $_POST['table_type'] ?? '';
$record_id = intval($_POST['record_id'] ?? 0);
if (empty($table_type) || $record_id <= 0) {
    die(json_encode(['success' => false, 'message' => 'Paramètres manquants']));
}

// Mapping des types de tableaux vers les classes correspondantes
$class_map = [
    'tab1'    => 'Tableau1',
    'tabl_1_1'  => 'Tableau1_1',
    'tab2'    => 'Tableau2',
    'tab2_1'    => 'Tableau2_1',
    'tab2_2'    => 'Tableau2_2',
    'tab3'    => 'Tableau3',
    'tab4'    => 'Tableau4',
    'tab4_1'  => 'Tableau4_1',
    'tab5'    => 'Tableau5',
    'tab6'    => 'Tableau6',
];

if (!isset($class_map[$table_type])) {
    die(json_encode(['success' => false, 'message' => 'Type de tableau inconnu']));
}
$classe = $class_map[$table_type];

// Vérifier que la classe existe
if (!class_exists($classe)) {
    die(json_encode(['success' => false, 'message' => 'Classe introuvable pour ce tableau']));
}

// Récupérer l'enregistrement
$record = $classe::trouve_par_id($record_id);
if (!$record) {
    die(json_encode(['success' => false, 'message' => 'Enregistrement introuvable']));
}

// Vérifier que l'enregistrement appartient bien à la société de l'utilisateur
if ($record->id_societe != $current_user->id_societe) {
    die(json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à modifier cet enregistrement']));
}

// Vérifier que l'enregistrement a une année (champ 'annee')
if (!property_exists($record, 'annee') || empty($record->annee)) {
    die(json_encode(['success' => false, 'message' => 'L\'enregistrement ne possède pas d\'année']));
}

// Traitement du fichier uploadé
if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'message' => 'Aucun fichier reçu ou erreur lors du transfert']));
}

$allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
$file_info = pathinfo($_FILES['file']['name']);
$extension = strtolower($file_info['extension'] ?? '');
if (!in_array($extension, $allowed_extensions)) {
    die(json_encode(['success' => false, 'message' => 'Type de fichier non autorisé (autorisé : pdf, doc, docx, xls, xlsx, jpg, jpeg, png)']));
}

// Construction du chemin de destination
$base_upload_dir = '../../uploads/attachments/';
$societe_dir = $base_upload_dir . $record->id_societe . '/';
$annee_dir = $societe_dir . $record->annee . '/';
$table_dir = $annee_dir . $table_type . '/';

// Création récursive du dossier si nécessaire
if (!is_dir($table_dir)) {
    if (!mkdir($table_dir, 0755, true)) {
        die(json_encode(['success' => false, 'message' => 'Impossible de créer le dossier de destination']));
    }
}

// Générer un nom de fichier unique
$filename = time() . '_' . $record_id . '.' . $extension;
$full_path = $table_dir . $filename;

// Déplacer le fichier uploadé
if (!move_uploaded_file($_FILES['file']['tmp_name'], $full_path)) {
    die(json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde du fichier']));
}

// Supprimer l'ancien fichier s'il existe
if (!empty($record->attachment)) {
    $old_full_path = '../../' . $record->attachment; // on reconstitue le chemin absolu
    if (file_exists($old_full_path)) {
        unlink($old_full_path);
    }
}

// Calculer le chemin relatif à la racine du site (à partir du dossier web)
// On suppose que la racine web correspond au dossier parent de 'ajax' et 'uploads'
// Dans notre structure, '../../' ramène à la racine du site, donc le chemin relatif à la racine est 'uploads/attachments/...'
$relative_path = str_replace('../../', '', $full_path);

// Mettre à jour l'attribut attachment de l'enregistrement
$record->attachment = $relative_path;
if (!$record->save()) {
    // En cas d'échec, on supprime le fichier pour ne pas laisser d'orphelin
    unlink($full_path);
    die(json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la base de données']));
}

// Réponse de succès
echo json_encode(['success' => true, 'message' => 'Fichier uploadé avec succès']);
?>