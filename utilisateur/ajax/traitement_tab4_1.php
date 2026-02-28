<?php
// ajax/traitement_tab4_1.php
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
$id_tableau_4 = isset($_POST['id_tableau_4']) ? intval($_POST['id_tableau_4']) : 0;
$id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
$id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
$annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
$statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'brouillon';
$response = ['success' => false, 'message' => ''];

// Liste des champs numériques pour faciliter le traitement
$numeric_fields = [
    'temps_complete_contrat_annee',
    'temps_partiel_contrat_annee',
    'temps_complete_permanente_annee',
    'temps_partiel_permanente_annee',
    'temps_complete_contrat_annee_1',
    'temps_partiel_contrat_annee_1',
    'temps_complete_permanente_annee_1',
    'temps_partiel_permanente_annee_1',
    'temps_complete_contrat_vacant',
    'temps_partiel_contrat_vacant',
    'temps_complete_permanente_vacant',
    'temps_partiel_permanente_vacant'
];

try {
    // Vérifier les droits d'accès à la société
    if ($current_user->id_societe != $id_societe && $current_user->type != 'administrateur' && $current_user->type != 'super_administrateur') {
        throw new Exception('غير مصرح بالوصول إلى هذه المؤسسة');
    }

    switch ($action) {
        case 'add_tab4_1':
        case 'update_tab4_1':
            // Vérifier la présence des détails
            if (!isset($_POST['details'])) {
                throw new Exception('لا توجد بيانات للحفظ');
            }

            $details = $_POST['details'];
            $details_to_delete = isset($_POST['supprimer_details']) ? $_POST['supprimer_details'] : [];

            // Préparer les données du tableau principal (annexe)
            $data_tableau = [
                'id_tableau_4' => $id_tableau_4,
                'id_societe'   => $id_societe,
                'id_user'      => $id_user,
                'annee'        => $annee,
                'statut'       => $statut,
                'date_creation'=> date('Y-m-d H:i:s')
            ];
            if ($statut == 'validé') {
                $data_tableau['date_valide'] = date('Y-m-d H:i:s');
            }

            if ($action == 'add_tab4_1') {
                // Création d'un nouvel enregistrement annexe
                $tableau = new Tableau4_1();
                foreach ($data_tableau as $k => $v) {
                    if (property_exists($tableau, $k)) {
                        $tableau->$k = $v;
                    }
                }
                if ($tableau->save()) {
                    $id_tableau = $tableau->id;
                    $response['id_tableau'] = $id_tableau;
                    $response['message'] = 'تم إنشاء الملحق بنجاح';
                } else {
                    throw new Exception('خطأ أثناء إنشاء الملحق');
                }
            } else {
                // Mise à jour d'un annexe existant
                $tableau = Tableau4_1::trouve_par_id($id_tableau);
                if (!$tableau) {
                    throw new Exception('الملحق غير موجود');
                }
                // Vérifier les permissions
                if ($tableau->id_societe != $id_societe || ($tableau->id_user != $id_user && $current_user->type == 'utilisateur')) {
                    throw new Exception('غير مصرح بتعديل هذا الملحق');
                }
                foreach ($data_tableau as $k => $v) {
                    if (property_exists($tableau, $k)) {
                        $tableau->$k = $v;
                    }
                }
                if ($tableau->save()) {
                    $response['message'] = 'تم تحديث الملحق بنجاح';
                } else {
                    throw new Exception('خطأ أثناء تحديث الملحق');
                }
            }

            // Supprimer les détails marqués
            foreach ($details_to_delete as $id_detail) {
                $id_detail = intval($id_detail);
                if ($id_detail > 0) {
                    $det = DetailTab4_1::trouve_par_id($id_detail);
                    if ($det && $det->id_tableau_4_1 == $id_tableau) {
                        $det->delete();
                    }
                }
            }

            // Traiter les détails (lignes)
            foreach ($details as $index => $detail_data) {
                // Récupérer l'id_grade (soit depuis id_grade, soit depuis le select)
                $id_grade = 0;
                if (isset($detail_data['id_grade']) && !empty($detail_data['id_grade'])) {
                    $id_grade = intval($detail_data['id_grade']);
                } elseif (isset($detail_data['id_grade_select']) && !empty($detail_data['id_grade_select'])) {
                    $id_grade = intval($detail_data['id_grade_select']);
                }
                if ($id_grade == 0) {
                    continue; // ignorer les lignes sans grade
                }
                // Vérifier que le grade existe (optionnel)
                $grade = Grade::trouve_par_id($id_grade);
                if (!$grade) {
                    continue;
                }

                $id_detail = isset($detail_data['id']) ? intval($detail_data['id']) : 0;
                $categorie = isset($detail_data['categorie']) ? trim($detail_data['categorie']) : '';
                $num_categorie = isset($detail_data['num_categorie']) ? intval($detail_data['num_categorie']) : 0;
                $observation = isset($detail_data['observation']) ? trim($detail_data['observation']) : '';

                // Récupérer les valeurs des champs numériques
                $values = [];
                foreach ($numeric_fields as $field) {
                    $values[$field] = isset($detail_data[$field]) ? intval($detail_data[$field]) : 0;
                }

                if ($id_detail > 0) {
                    // Mise à jour d'une ligne existante
                    $det = DetailTab4_1::trouve_par_id($id_detail);
                    if ($det && $det->id_tableau_4_1 == $id_tableau) {
                        $det->id_grade = $id_grade;
                        $det->categorie = $categorie;
                        $det->num_categorie = $num_categorie;
                        foreach ($values as $field => $val) {
                            $det->$field = $val;
                        }
                        $det->observation = $observation;
                        $det->save();
                    }
                } else {
                    // Création d'une nouvelle ligne
                    $det = new DetailTab4_1();
                    $det->id_tableau_4_1 = $id_tableau;
                    $det->id_grade = $id_grade;
                    $det->categorie = $categorie;
                    $det->num_categorie = $num_categorie;
                    foreach ($values as $field => $val) {
                        $det->$field = $val;
                    }
                    $det->observation = $observation;
                    $det->save();
                }
            }

            $response['success'] = true;
            break;

        case 'delete_tab4_1':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if ($id <= 0) {
                throw new Exception('معرف غير صالح');
            }
            $tableau = Tableau4_1::trouve_par_id($id);
            if (!$tableau) {
                throw new Exception('الملحق غير موجود');
            }
            // Vérifier les droits
            if ($tableau->id_societe != $current_user->id_societe && $current_user->type == 'utilisateur') {
                throw new Exception('غير مصرح بحذف هذا الملحق');
            }
            // Supprimer les détails
            $details = DetailTab4_1::trouve_par_tableau($id);
            foreach ($details as $det) {
                $det->delete();
            }
            if ($tableau->delete()) {
                $response['success'] = true;
                $response['message'] = 'تم حذف الملحق بنجاح';
            } else {
                throw new Exception('خطأ أثناء حذف الملحق');
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
exit;
?>