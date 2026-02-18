<?php
require_once('../../includes/initialiser.php');

// Vérification AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

// Vérification session
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || ($current_user->type !== 'administrateur' && $current_user->type !== 'super_administrateur')) {
    echo json_encode(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à effectuer cette action']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

$tableau = Tableau1::trouve_par_id($id);
if (!$tableau) {
    echo json_encode(['success' => false, 'message' => 'Tableau non trouvé']);
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
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>