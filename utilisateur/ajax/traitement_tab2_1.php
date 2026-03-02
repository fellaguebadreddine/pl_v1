<?php
require_once('../../includes/initialiser.php');

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => ''];

try {

    // ==============================
    // Vérification AJAX
    // ==============================
    if (
        empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
    ) {
        throw new Exception('Accès non autorisé');
    }

    // ==============================
    // Vérification session
    // ==============================
    if (!$session->is_logged_in()) {
        throw new Exception('انتهت الجلسة');
    }

    $current_user = Accounts::trouve_par_id($session->id_utilisateur);
    if (!$current_user) {
        throw new Exception('المستخدم غير موجود');
    }

    $action = $_POST['action'] ?? '';

    // ======================================================
    // =================== ADD DETAIL =======================
    // ======================================================
    if ($action === 'add_detail') {

        // ==============================
        // Nettoyage sécurisé
        // ==============================      

        $observations = trim($_POST['observations'] ?? '');

        $annee = date('Y');

        // ==============================
        // Vérifier grade
        // ==============================
       

       

        // ==============================
        // Création objet
        // ==============================
        $detail = new DetailTab2_1();
        $detail->id_tab_2_1 = 0;
        $detail->annee = $annee;
        $detail->id_user = $current_user->id;
        $detail->id_societe = $current_user->id_societe;        
        $detail->observations = $observations;

        // ==============================
        // Sauvegarde
        // ==============================
        if (!$detail->save()) {
            throw new Exception('فشل حفظ البيانات');
        }

        $response['success'] = true;
        $response['id'] = $detail->id;
        $response['message'] = 'تمت الإضافة بنجاح';
    }elseif  ($action === 'delete_detail') {

    $id = intval($_POST['id'] ?? 0);

    $detail = DetailTab2_1::trouve_par_id($id);

    if (!$detail) {
        throw new Exception('السطر غير موجود');
    }

    if (!$detail->supprime()) {
        throw new Exception('فشل الحذف');
    }

    $response['success'] = true;
}elseif ($action === 'save_tableau') {

    
    // ==============================
    // Sécurité : toujours utiliser session
    // ==============================
    $id_user    = $current_user->id;
    $id_societe = $current_user->id_societe;

    $statut = isset($_POST['statut']) ? trim($_POST['statut']) : 'en attente';
    $annee  = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');

    // ==============================
    // Vérifier qu'il existe des détails brouillon
    // ==============================
    $details = DetailTab2_1::trouve_tableeu_vide($id_societe, $id_user);

    if (empty($details)) {
        throw new Exception('لا توجد بيانات للحفظ');
    }

    // ==============================
    // TRANSACTION (IMPORTANT ERP)
    // ==============================
   

    try {

        // ==============================
        // 1️⃣ Créer tableau principal
        // ==============================
        $tableau = new Tableau2_1();
        $tableau->statut = $statut;
        $tableau->annee = $annee;
        $tableau->id_user = $id_user;
        $tableau->id_societe = $id_societe;
        $tableau->date_creation = date('Y-m-d H:i:s');

        if ($statut === 'en_attente') {
            $tableau->date_valide = date('Y-m-d H:i:s');
        } else {
            $tableau->date_valide = null;
        }

        if (!$tableau->save()) {
            throw new Exception('خطأ أثناء حفظ الجدول الرئيسي');
        }

        $last_id = $tableau->id;

        // ==============================
        // 2️⃣ Update detail_tab_1_1
        // ==============================
        foreach ($details as $detail) {
            $detail->id_tab_2_1 = $last_id;

            if (!$detail->save()) {
                throw new Exception('فشل تحديث تفاصيل الجدول');
            }
        }
        $response['success'] = true;
        $response['message'] = ($statut === 'en_attente')
            ? 'تم تقديم الجدول بنجاح'
            : 'تم حفظ المسودة بنجاح';

    } catch (Exception $e) {

        $database->connection->rollback();
        throw $e;
    }
} 


} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;

?>