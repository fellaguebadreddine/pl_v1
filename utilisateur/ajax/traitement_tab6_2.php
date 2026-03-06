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
$statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'validé';
$nbr_annee = isset($_POST['nbr_annee']) ? intval($_POST['nbr_annee']) : 0;

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier les droits
    if ($current_user->id_societe != $id_societe && $current_user->type != 'administrateur' && $current_user->type != 'super_admin') {
        throw new Exception('غير مصرح بالوصول');
    }

    switch ($action) {
        
case 'add_tab6_2':
    // Vérifier existence
    $existant = Tableau6_2::existe_pour_societe_annee($id_societe, $annee);
    if ($existant) {
        throw new Exception('يوجد جدول لهذه السنة بالفعل');
    }

    // Créer le tableau principal
    $tableau = new Tableau6_2();
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
    $id_tableau = $tableau->id;

    // Insérer les détails à partir des employés
    if (isset($_POST['employes']) && is_array($_POST['employes'])) {
        foreach ($_POST['employes'] as $emp_data) {
            $employe = Employees::trouve_par_id($emp_data['id_employee']);
            if (!$employe) continue;

            $detail = new DetailTab6_2();
            $detail->id_tab_6_2 = $id_tableau;
            $detail->id_grade = $emp_data['id_grade'];
            $detail->id_societe = $id_societe;
            $detail->id_user = $id_user;
            $detail->annee = $annee;
            $detail->nom = $employe->nom;
            $detail->prenom = $employe->prenom;
            $detail->date_naissance = $employe->date_naissance;
            $detail->nbr_annee = $emp_data['nbr_annee'];
            $detail->observations = $emp_data['observations'] ?? '';
            $detail->date_valide = date('Y-m-d H:i:s');
            $detail->save();
        }
    }

    $response['success'] = true;
    $response['message'] = 'تم إنشاء الجدول بنجاح';
    $response['id_tableau'] = $id_tableau;
    break;

        case 'update_tab6_2':
            $tableau = Tableau6_2::trouve_par_id($id_tableau);
            if (!$tableau) throw new Exception('الجدول غير موجود');
            if ($tableau->id_societe != $id_societe) throw new Exception('غير مصرح');

            $tableau->statut = $statut;
            if ($statut == 'validé' && !$tableau->date_valide) {
                $tableau->date_valide = date('Y-m-d H:i:s');
            }
            if (!$tableau->save()) throw new Exception('خطأ في تحديث الجدول');

            if (isset($_POST['details'])) {
                foreach ($_POST['details'] as $id_detail => $data) {
                    $detail = DetailTab6_2::trouve_par_id($id_detail);
                    if ($detail && $detail->id_tab_6_2 == $id_tableau) {
                        if (isset($data['nbr_annee'])) {
                            $detail->nbr_annee = $data['nbr_annee'];
                        }
                        if (isset($data['observations'])) {
                            $detail->observations = trim($data['observations']);
                        }
                        $detail->save();
                    }
                }
            }

            $response['success'] = true;
            $response['message'] = 'تم تحديث الجدول';
            break;

        case 'delete_tab6':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('معرف غير صالح');
            $tableau = Tableau6_2::trouve_par_id($id);
            if (!$tableau) throw new Exception('الجدول غير موجود');
            if ($tableau->id_societe != $current_user->id_societe && $current_user->type == 'utilisateur') {
                throw new Exception('غير مصرح بالحذف');
            }
            $details = DetailTab6_2::trouve_par_tableau($id);
            foreach ($details as $d) {
                $d->delete();
            }
            if ($tableau->supprime()) {
                $response['success'] = true;
                $response['message'] = 'تم حذف الجدول';
            } else {
                throw new Exception('خطأ في الحذف');
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