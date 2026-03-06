<?php
// ajax/traitement_tab6.php
require_once('../../includes/initialiser.php');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    exit;
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$id_tableau = isset($_POST['id_tableau']) ? intval($_POST['id_tableau']) : 0;
$id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
$id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
$annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
$statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'brouillon';

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier les droits
    if ($current_user->id_societe != $id_societe && $current_user->type != 'administrateur' && $current_user->type != 'super_admin') {
        throw new Exception('غير مصرح بالوصول');
    }

    switch ($action) {
        
case 'add_tab6_1':
    // Vérifier existence
    $existant = Tableau6_1::existe_pour_societe_annee($id_societe, $annee);
    if ($existant) {
        throw new Exception('يوجد جدول لهذه السنة بالفعل');
    }

    // Créer le tableau principal
    $tableau = new Tableau6_1();
    $tableau->id_societe = $id_societe;
    $tableau->id_user = $id_user;
    $tableau->annee = $annee;
    $tableau->statut = $statut;
    $tableau->date_creation = date('Y-m-d H:i:s');
    if ($statut == 'validé') {
        $tableau->date_valide = date('Y-m-d H:i:s');
    }
    if (!$tableau->save()) {
        throw new Exception('خطأ في إنشاء الجدول');
    }
  

    $response['success'] = true;
    $response['message'] = 'تم إنشاء الجدول بنجاح';
    $response['id_tableau'] = $id_tableau;
    break;

        case 'update_tab6_1':
            $tableau = Tableau6_1::trouve_par_id($id_tableau);
            if (!$tableau) throw new Exception('الجدول غير موجود');
            if ($tableau->id_societe != $id_societe) throw new Exception('غير مصرح');

            $tableau->statut = $statut;
            if ($statut == 'validé' && !$tableau->date_valide) {
                $tableau->date_valide = date('Y-m-d H:i:s');
            }
            if (!$tableau->save()) throw new Exception('خطأ في تحديث الجدول');

            

            $response['success'] = true;
            $response['message'] = 'تم تحديث الجدول';
            break;

        case 'delete_tab6_1':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('معرف غير صالح');
            $tableau = Tableau6_1::trouve_par_id($id);
            if (!$tableau) throw new Exception('الجدول غير موجود');
            if ($tableau->id_societe != $current_user->id_societe && $current_user->type == 'utilisateur') {
                throw new Exception('غير مصرح بالحذف');
            }
           
            break;

        default:
            throw new Exception('إجراء غير معروف');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>