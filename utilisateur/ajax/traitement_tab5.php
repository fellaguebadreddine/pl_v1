<?php
// ajax/traitement_tab5.php (version adaptée avec dates)
require_once('../../includes/initialiser.php');

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}
if (!$session->is_logged_in()) die(json_encode(['success' => false, 'message' => 'Session expirée']));
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) die(json_encode(['success' => false, 'message' => 'Utilisateur introuvable']));

$action = $_POST['action'] ?? '';
$id_tableau = isset($_POST['id_tableau']) ? intval($_POST['id_tableau']) : 0;
$id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
$id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
$annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
$statut = $_POST['statut'] ?? 'brouillon';

$response = ['success' => false, 'message' => ''];

try {
    if ($current_user->id_societe != $id_societe && $current_user->type != 'administrateur' && $current_user->type != 'super_admin') {
        throw new Exception('غير مصرح بالوصول');
    }

    switch ($action) {
        case 'add_tab5':
        case 'update_tab5':
            if (!isset($_POST['details'])) throw new Exception('لا توجد بيانات');
            $details = $_POST['details'];
            $details_to_delete = $_POST['supprimer_details'] ?? [];

            $data_tableau = [
                'id_societe' => $id_societe,
                'id_user' => $id_user,
                'annee' => $annee,
                'statut' => $statut,
                'date_creation' => date('Y-m-d H:i:s')
            ];
            if ($statut == 'validé') $data_tableau['date_valide'] = date('Y-m-d H:i:s');

            if ($action == 'add_tab5') {
                $existing = Tableau5::existe_pour_societe_annee($id_societe, $annee);
                if ($existing && $statut == 'validé') throw new Exception('يوجد جدول مصادق عليه لهذه السنة');
                $tableau = new Tableau5();
                foreach ($data_tableau as $k=>$v) if (property_exists($tableau,$k)) $tableau->$k = $v;
                if ($tableau->save()) {
                    $id_tableau = $tableau->id;
                    $response['id_tableau'] = $id_tableau;
                    $response['message'] = 'تم إنشاء الجدول';
                } else throw new Exception('خطأ في إنشاء الجدول');
            } else {
                $tableau = Tableau5::trouve_par_id($id_tableau);
                if (!$tableau) throw new Exception('الجدول غير موجود');
                if ($tableau->id_societe != $id_societe) throw new Exception('غير مصرح');
                foreach ($data_tableau as $k=>$v) if (property_exists($tableau,$k)) $tableau->$k = $v;
                if (!$tableau->save()) throw new Exception('خطأ في تحديث الجدول');
                $response['message'] = 'تم تحديث الجدول';
            }

            // Supprimer les détails marqués
            foreach ($details_to_delete as $id_det) {
                $det = DetailTab5::trouve_par_id(intval($id_det));
                if ($det && $det->id_tableau_5 == $id_tableau) $det->supprime();
            }

            // Traitement des détails
            foreach ($details as $idx => $det_data) {
                $id_grade = isset($det_data['id_grade']) ? intval($det_data['id_grade']) : (isset($det_data['id_grade_select']) ? intval($det_data['id_grade_select']) : 0);
                if ($id_grade == 0) continue;

                $id_detail = isset($det_data['id']) ? intval($det_data['id']) : 0;
                $fields = [
                    'date_externe_concour_examen',
                    'date_externe_concour_diplome',
                    'date_externe_concour_recyclage',
                    'date_interne_concours_profi',
                    'date_interne_examen_profi',
                    'date_interne_preparation_list',
                    'date_concour_qualification',
                    'tabl_mise_niveau',
                    'comite_installation',
                    'date_concour_formation',
                    'autre',
                    'observations'
                ];
                $data_detail = [];
                foreach ($fields as $f) {
                    if ($f == 'observations') {
                        $data_detail[$f] = isset($det_data[$f]) ? trim($det_data[$f]) : '';
                    } else {
                        // Pour les dates, on garde la valeur si elle est non vide, sinon NULL
                        $data_detail[$f] = isset($det_data[$f]) && !empty($det_data[$f]) ? $det_data[$f] : null;
                    }
                }

                if ($id_detail > 0) {
                    $obj = DetailTab5::trouve_par_id($id_detail);
                    if ($obj && $obj->id_tableau_5 == $id_tableau) {
                        $obj->id_grade = $id_grade;
                        foreach ($data_detail as $k=>$v) $obj->$k = $v;
                        $obj->save();
                    }
                } else {
                    $obj = new DetailTab5();
                    $obj->id_tableau_5 = $id_tableau;
                    $obj->id_grade = $id_grade;
                    $obj->id_societe = $id_societe;
                    $obj->id_user = $id_user;
                    $obj->annee = $annee;
                    foreach ($data_detail as $k=>$v) $obj->$k = $v;
                    $obj->save();
                }
            }
            $response['success'] = true;
            break;

        case 'delete_tab5':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID invalide');
            $tableau = Tableau5::trouve_par_id($id);
            if (!$tableau) throw new Exception('الجدول غير موجود');
            if ($tableau->id_societe != $current_user->id_societe && $current_user->type == 'utilisateur')
                throw new Exception('غير مصرح');
            $details = DetailTab5::trouve_par_tableau($id);
            foreach ($details as $d) $d->delete();
            if ($tableau->supprime()) {
                $response['success'] = true;
                $response['message'] = 'تم حذف الجدول';
            } else throw new Exception('خطأ في الحذف');
            break;

        default:
            throw new Exception('Action inconnue');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>