<?php
require_once('../../includes/initialiser.php');

// Vérification AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

// Vérification session
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'administrateur') {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
$type = isset($_POST['type_tableau']) ? $_POST['type_tableau'] : ''; // 'tab1' ou 'tab3' etc.

if ($id <= 0 || empty($type)) {
    echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
    exit;
}

// Mapping des types vers les classes
$classMap = [
    'tab1' => 'Tableau1',
    'tab3' => 'Tableau3',
    // Ajouter d'autres types si nécessaire
];

if (!isset($classMap[$type])) {
    echo json_encode(['success' => false, 'message' => 'نوع الجدول غير معروف']);
    exit;
}

$className = $classMap[$type];

if (!class_exists($className)) {
    echo json_encode(['success' => false, 'message' => 'Classe introuvable']);
    exit;
}

$tableau = $className::trouve_par_id($id);
if (!$tableau) {
    echo json_encode(['success' => false, 'message' => 'لايوجد جدول']);
    exit;
}

try {
    if ($action == 'valider') {
        // Valider le tableau
        $tableau->statut = 'validé';
        $tableau->date_valide = date('Y-m-d H:i:s');
        $tableau->id_admin_validateur = $current_user->id;
        
        if ($tableau->save()) {
            echo json_encode(['success' => true, 'message' => 'Tableau validé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation']);
        }
    } elseif ($action == 'demander_modification') {
        // Demander une modification (rejeter)
        if (empty($commentaire)) {
            echo json_encode(['success' => false, 'message' => 'Veuillez fournir une raison pour la demande de modification']);
            exit;
        }
        
        $tableau->statut = 'brouillon';
        $tableau->commentaire_admin = $commentaire;
        $tableau->id_admin_validateur = $current_user->id;
        
        if ($tableau->save()) {
            echo json_encode(['success' => true, 'message' => 'Demande de modification envoyée']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi de la demande']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }
} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success'=>false,'message'=>'خطأ داخلي في النظام']);
}
?>