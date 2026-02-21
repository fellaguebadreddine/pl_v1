<?php
// ajax/traitement_tab4.php
require_once('../../includes/initialiser.php');
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') die(json_encode(['success'=>false,'message'=>'Accès non autorisé']));
if (!$session->is_logged_in()) { echo json_encode(['success'=>false,'message'=>'Session expirée']); exit; }
$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) { echo json_encode(['success'=>false,'message'=>'Utilisateur introuvable']); exit; }

$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$id_tableau = isset($_POST['id_tableau']) ? intval($_POST['id_tableau']) : 0;
$id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
$id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
$annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
$statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'brouillon';
$response = ['success'=>false,'message'=>''];

try {
    if ($current_user->id_societe != $id_societe && $current_user->type != 'administrateur' && $current_user->type != 'super_administrateur')
        throw new Exception('غير مصرح بالوصول');

    switch ($action) {
        case 'add_tab4':
        case 'update_tab4':
            if (!isset($_POST['details'])) throw new Exception('لا توجد بيانات');
            $details = $_POST['details'];
            $details_to_delete = isset($_POST['supprimer_details']) ? $_POST['supprimer_details'] : [];

            $data_tableau = [
                'id_societe' => $id_societe,
                'id_user' => $id_user,
                'annee' => $annee,
                'statut' => $statut,
                'date_creation' => date('Y-m-d H:i:s')
            ];
            if ($statut == 'validé') $data_tableau['date_valide'] = date('Y-m-d H:i:s');

            if ($action == 'add_tab4') {
                $existing = Tableau4::trouve_par_criteres(['id_societe'=>$id_societe, 'annee'=>$annee, 'statut'=>'validé']);
                if ($existing && $statut == 'validé') throw new Exception('يوجد جدول مصادق عليه لهذه السنة بالفعل');
                $tableau = new Tableau4();
                foreach ($data_tableau as $k=>$v) if (property_exists($tableau,$k)) $tableau->$k = $v;
                if ($tableau->save()) {
                    $id_tableau = $tableau->id;
                    $response['id_tableau'] = $id_tableau;
                    $response['message'] = 'تم إنشاء الجدول';
                } else throw new Exception('خطأ في حفظ الجدول');
            } else {
                $tableau = Tableau4::trouve_par_id($id_tableau);
                if (!$tableau) throw new Exception('الجدول غير موجود');
                if ($tableau->id_societe != $id_societe || ($tableau->id_user != $id_user && $current_user->type == 'utilisateur'))
                    throw new Exception('غير مصرح بالتعديل');
                foreach ($data_tableau as $k=>$v) if (property_exists($tableau,$k)) $tableau->$k = $v;
                if (!$tableau->save()) throw new Exception('خطأ في تحديث الجدول');
                $response['message'] = 'تم تحديث الجدول';
            }

            // Suppression des détails marqués
            foreach ($details_to_delete as $id_detail) {
                $id_detail = intval($id_detail);
                if ($id_detail > 0) {
                    $d = DetailTab4::trouve_par_id($id_detail);
                    if ($d && $d->id_tableau_4 == $id_tableau) $d->delete();
                }
            }

            // Traitement des détails
            foreach ($details as $i => $detail) {
                $id_grade = isset($detail['id_grade']) ? intval($detail['id_grade']) : (isset($detail['id_grade_select']) ? intval($detail['id_grade_select']) : 0);
                if ($id_grade == 0) continue;
                $grade = Grade::trouve_par_id($id_grade);
                if (!$grade) continue;

                $id_detail = isset($detail['id']) ? intval($detail['id']) : 0;
                $data_detail = [
                    'postes_vacants_externe' => isset($detail['postes_vacants_externe']) ? intval($detail['postes_vacants_externe']) : 0,
                    'produit_formation_paramedicale' => isset($detail['produit_formation_paramedicale']) ? intval($detail['produit_formation_paramedicale']) : 0,
                    'concours_sur_titre' => isset($detail['concours_sur_titre']) ? intval($detail['concours_sur_titre']) : 0,
                    'debutants_contractuels' => isset($detail['debutants_contractuels']) ? intval($detail['debutants_contractuels']) : 0,
                    'ouvriers_batiment_contractuels' => isset($detail['ouvriers_batiment_contractuels']) ? intval($detail['ouvriers_batiment_contractuels']) : 0,
                    'methode_sur_titre' => isset($detail['methode_sur_titre']) ? intval($detail['methode_sur_titre']) : 0,
                    'examen_mini' => isset($detail['examen_mini']) ? intval($detail['examen_mini']) : 0,
                    'test_mini_ouvriers' => isset($detail['test_mini_ouvriers']) ? intval($detail['test_mini_ouvriers']) : 0,
                    'postes_financiers_exploites' => isset($detail['postes_financiers_exploites']) ? intval($detail['postes_financiers_exploites']) : 0,
                    'nombre_postes_financiers_exploites' => isset($detail['nombre_postes_financiers_exploites']) ? intval($detail['nombre_postes_financiers_exploites']) : 0,
                    'observations' => isset($detail['observations']) ? trim($detail['observations']) : ''
                ];

                if ($id_detail > 0) {
                    $obj = DetailTab4::trouve_par_id($id_detail);
                    if ($obj && $obj->id_tableau_4 == $id_tableau) {
                        $obj->id_grade = $id_grade;
                        foreach ($data_detail as $k=>$v) $obj->$k = $v;
                        $obj->save();
                    }
                } else {
                    $obj = new DetailTab4();
                    $obj->id_tableau_4 = $id_tableau;
                    $obj->id_grade = $id_grade;
                    foreach ($data_detail as $k=>$v) $obj->$k = $v;
                    $obj->save();
                }
            }
            $response['success'] = true;
            break;

        case 'delete_tab4':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('معرف غير صالح');
            $tableau = Tableau4::trouve_par_id($id);
            if (!$tableau) throw new Exception('الجدول غير موجود');
            if ($tableau->id_societe != $current_user->id_societe && $current_user->type == 'utilisateur')
                throw new Exception('غير مصرح بالحذف');
            $details = DetailTab4::trouve_par_tableau($id);
            foreach ($details as $d) $d->supprime();
            if ($tableau->supprime()) {
                $response['success'] = true;
                $response['message'] = 'تم حذف الجدول';
            } else throw new Exception('خطأ في الحذف');
            break;

        default:
            throw new Exception('إجراء غير معروف');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>