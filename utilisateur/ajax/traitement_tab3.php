<?php
require_once('../../includes/initialiser.php');

// Vérifie si la requête est AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

// Vérifie si l'utilisateur est connecté
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}

// Récupère les données POST
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$id_tableau = isset($_POST['id_tableau']) ? intval($_POST['id_tableau']) : 0;
$id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
$id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
$annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
$statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'brouillon';

$response = ['success' => false, 'message' => ''];

try {
    // Vérifier l'utilisateur
    $current_user = Accounts::trouve_par_id($session->id_utilisateur);
    if (!$current_user) {
        throw new Exception('المستخدم غير موجود');
    }

    // Vérifier que l'utilisateur a accès à cette société
    if ($current_user->id_societe != $id_societe && $current_user->type != 'administrateur' && $current_user->type != 'super_administrateur') {
        throw new Exception('غير مصرح بالوصول إلى هذه المؤسسة');
    }

    switch ($action) {
        case 'add_tab3':
        case 'update_tab3':
            // Vérifier les détails
            if (!isset($_POST['details'])) {
                throw new Exception('لا توجد بيانات للحفظ');
            }

            $details = $_POST['details'];
            $details_to_delete = isset($_POST['supprimer_details']) ? $_POST['supprimer_details'] : [];

            // Préparer les données du tableau
            $data_tableau = [
                'id_societe' => $id_societe,
                'id_user' => $id_user,
                'annee' => $annee,
                'statut' => $statut,
                'date_creation' => date('Y-m-d H:i:s')
            ];

            if ($statut == 'validé') {
                $data_tableau['date_valide'] = date('Y-m-d H:i:s');
            }

            // Sauvegarder ou mettre à jour le tableau principal
            if ($action == 'add_tab3') {
                // Vérifier si un tableau existe déjà pour cette année et société
                $existing = Tableau3::trouve_par_criteres(['id_societe' => $id_societe, 'annee' => $annee, 'statut' => 'validé']);
                if ($existing && $statut == 'validé') {
                    throw new Exception('يوجد جدول مصادق عليه لهذه السنة بالفعل');
                }

                $tableau = new Tableau3();
                foreach ($data_tableau as $key => $value) {
                    if (property_exists($tableau, $key)) {
                        $tableau->$key = $value;
                    }
                }

                if ($tableau->save()) {
                    $id_tableau = $tableau->id;
                    $response['id_tableau'] = $id_tableau;
                    $response['message'] = 'تم إنشاء الجدول بنجاح';
                } else {
                    throw new Exception('خطأ أثناء حفظ الجدول');
                }
            } else {
                $tableau = Tableau3::trouve_par_id($id_tableau);
                if (!$tableau) {
                    throw new Exception('الجدول غير موجود');
                }

                // Vérifier les permissions
                if ($tableau->id_societe != $id_societe || ($tableau->id_user != $id_user && $current_user->type == 'utilisateur')) {
                    throw new Exception('غير مصرح بتعديل هذا الجدول');
                }

                foreach ($data_tableau as $key => $value) {
                    if (property_exists($tableau, $key)) {
                        $tableau->$key = $value;
                    }
                }

                if (!$tableau->save()) {
                    throw new Exception('خطأ أثناء تحديث الجدول');
                }

                $response['message'] = 'تم تحديث الجدول بنجاح';
            }

            // Supprimer les détails marqués pour suppression
            if (!empty($details_to_delete)) {
                foreach ($details_to_delete as $id_detail) {
                    $id_detail = intval($id_detail);
                    if ($id_detail > 0) {
                        $detail = DetailTab3::trouve_par_id($id_detail);
                        if ($detail && $detail->id_tableau_3 == $id_tableau) {
                            $detail->supprime();
                        }
                    }
                }
            }

            // Traiter les détails
            foreach ($details as $index => $detail_data) {
                // Ignorer les lignes sans grade
                $id_grade = 0;
                if (isset($detail_data['id_grade']) && !empty($detail_data['id_grade'])) {
                    $id_grade = intval($detail_data['id_grade']);
                } elseif (isset($detail_data['id_grade_select']) && !empty($detail_data['id_grade_select'])) {
                    $id_grade = intval($detail_data['id_grade_select']);
                }

                if ($id_grade == 0) {
                    continue;
                }

                // Vérifier que le grade existe
                $grade = Grade::trouve_par_id($id_grade);
                if (!$grade) {
                    continue;
                }

                // Récupérer les données
                $id_detail = isset($detail_data['id']) ? intval($detail_data['id']) : 0;
                $interne = isset($detail_data['interne']) ? 1 : 0;
                $externe = isset($detail_data['externe']) ? 1 : 0;
                $diplome = isset($detail_data['diplome']) ? 1 : 0;
                $concour = isset($detail_data['concour']) ? 1 : 0;
                $examen_pro = isset($detail_data['examen_pro']) ? 1 : 0;
                $test_pro = isset($detail_data['test_pro']) ? 1 : 0;
                $nomination = isset($detail_data['nomination']) ? intval($detail_data['nomination']) : 0;
                $loi = isset($detail_data['loi']) ? trim($detail_data['loi']) : '';
                $observation = isset($detail_data['observation']) ? trim($detail_data['observation']) : '';
                $code = isset($detail_data['code']) ? trim($detail_data['code']) : $grade->id; // ou laisser vide

                if ($id_detail > 0) {
                    // Mettre à jour le détail existant
                    $detail = DetailTab3::trouve_par_id($id_detail);
                    if ($detail && $detail->id_tableau_3 == $id_tableau) {
                        $detail->id_grade = $id_grade;
                        $detail->interne = $interne;
                        $detail->externe = $externe;
                        $detail->diplome = $diplome;
                        $detail->concour = $concour;
                        $detail->examen_pro = $examen_pro;
                        $detail->test_pro = $test_pro;
                        $detail->nomination = $nomination;
                        $detail->loi = $loi;
                        $detail->observation = $observation;
                        $detail->code = $code;
                        $detail->save();
                    }
                } else {
                     $id_tableau = $tableau->id;
                    $response['id_tableau'] = $id_tableau;
                    $response['message'] = 'تم إنشاء الجدول بنجاح';
                } else {
                    throw new Exception('خطأ أثناء حفظ الجدول');
                }
                    // Créer un nouveau détail
                    $detail = new DetailTab3();
                    $detail->id_tableau_3 = $id_tableau;
                    $detail->id_grade = $id_grade;
                    $detail->annee = $annee;
                    $detail->id_user = $id_user;
                    $detail->id_societe = $id_societe;
                    $detail->code = $code;
                    $detail->interne = $interne;
                    $detail->externe = $externe;
                    $detail->diplome = $diplome;
                    $detail->concour = $concour;
                    $detail->examen_pro = $examen_pro;
                    $detail->test_pro = $test_pro;
                    $detail->nomination = $nomination;
                    $detail->loi = $loi;
                    $detail->observation = $observation;
                    $detail->save();
                }
            }

            $response['success'] = true;
            break;

        case 'delete_tab3':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

            if ($id <= 0) {
                throw new Exception('معرف غير صالح');
            }

            $tableau = Tableau3::trouve_par_id($id);
            if (!$tableau) {
                throw new Exception('الجدول غير موجود');
            }

            // Vérifier les permissions
            if ($tableau->id_societe != $current_user->id_societe && $current_user->type == 'utilisateur') {
                throw new Exception('غير مصرح بحذف هذا الجدول');
            }

            // Vérifier que c'est un brouillon (ou permettre la suppression si administrateur)
            if ($tableau->statut != 'brouillon' && $current_user->type == 'utilisateur') {
                throw new Exception('لا يمكن حذف الجدول إلا إذا كان مسودة');
            }

            // Supprimer les détails
            $details = DetailTab3::trouve_par_tableau($id);
            foreach ($details as $detail) {
                $detail->supprime();
            }

            // Supprimer le tableau
            if ($tableau->supprime()) {
                $response['success'] = true;
                $response['message'] = 'تم حذف الجدول بنجاح';
            } else {
                throw new Exception('خطأ أثناء حذف الجدول');
            }
            break;

        default:
            throw new Exception('إجراء غير معروف');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Retourner la réponse en JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>