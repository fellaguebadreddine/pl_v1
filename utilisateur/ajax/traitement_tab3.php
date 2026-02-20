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

    if (!isset($_POST['details'])) {
        throw new Exception('لا توجد بيانات للحفظ');
    }

    $details = $_POST['details'];
    $details_to_delete = isset($_POST['supprimer_details']) ? $_POST['supprimer_details'] : [];

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

    // ===============================
    // CREATION OU UPDATE TABLEAU
    // ===============================

    if ($action == 'add_tab3') {

        $existing = Tableau3::trouve_par_criteres([
            'id_societe' => $id_societe,
            'annee' => $annee,
            'statut' => 'validé'
        ]);

        if ($existing && $statut == 'validé') {
            throw new Exception('يوجد جدول مصادق عليه لهذه السنة بالفعل');
        }

        $tableau = new Tableau3();

    } else {

        $tableau = Tableau3::trouve_par_id($id_tableau);

        if (!$tableau) {
            throw new Exception('الجدول غير موجود');
        }
    }

    foreach ($data_tableau as $key => $value) {
        if (property_exists($tableau, $key)) {
            $tableau->$key = $value;
        }
    }

    if (!$tableau->save()) {
        throw new Exception('خطأ أثناء حفظ الجدول');
    }

    // 🔥 IMPORTANT : récupérer ID une seule fois
    $id_tableau = $tableau->id;

    if (empty($id_tableau)) {
        throw new Exception('لم يتم توليد رقم الجدول');
    }

    $response['id_tableau'] = $id_tableau;
    $response['message'] = ($action == 'add_tab3')
        ? 'تم إنشاء الجدول بنجاح'
        : 'تم تحديث الجدول بنجاح';

    // ===============================
    // SUPPRESSION DETAILS
    // ===============================

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

    // ===============================
    // INSERT / UPDATE DETAILS
    // ===============================

    foreach ($details as $detail_data) {

        $id_grade = intval($detail_data['id_grade'] ?? 0);
        if ($id_grade == 0) continue;

        $id_detail = intval($detail_data['id'] ?? 0);

        if ($id_detail > 0) {

            $detail = DetailTab3::trouve_par_id($id_detail);

        } else {

            $detail = new DetailTab3();
            $detail->id_tableau_3 = $id_tableau;
        }

        $detail->id_grade = $id_grade;
        $detail->annee = $annee;
        $detail->id_user = $id_user;
        $detail->id_societe = $id_societe;
        $detail->code = trim($detail_data['code'] ?? '');
        $detail->interne = isset($detail_data['interne']) ? 1 : 0;
        $detail->externe = isset($detail_data['externe']) ? 1 : 0;
        $detail->diplome = isset($detail_data['diplome']) ? 1 : 0;
        $detail->concour = isset($detail_data['concour']) ? 1 : 0;
        $detail->examen_pro = isset($detail_data['examen_pro']) ? 1 : 0;
        $detail->test_pro = isset($detail_data['test_pro']) ? 1 : 0;
        $detail->nomination = intval($detail_data['nomination'] ?? 0);
        $detail->loi = trim($detail_data['loi'] ?? '');
        $detail->observation = trim($detail_data['observation'] ?? '');

        $detail->save();
    }

    $response['success'] = true;

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