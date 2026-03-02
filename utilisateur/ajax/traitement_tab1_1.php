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
        $id_grade = intval($_POST['id_grade'] ?? 0);
        $id_tableau = intval($_POST['id_tableau'] ?? 0); // RECUPRER ID TABLEAU

        $effectif_reel_31_dec  = intval($_POST['effectif_reel_31_dec'] ?? 0);
        $effectif_reel_annee_1 = intval($_POST['effectif_reel_annee_1'] ?? 0);
        $titulaires            = intval($_POST['titulaires'] ?? 0);
        $stagaires             = intval($_POST['stagaires'] ?? 0);

        $femmes                        = intval($_POST['femmes'] ?? 0);
        $titulaie_temps_complet        = intval($_POST['titulaie_temps_complet'] ?? 0);
        $titulaie_femmes_complet       = intval($_POST['titulaie_femmes_complet'] ?? 0);
        $titulaie_temps_partiel        = intval($_POST['titulaie_temps_partiel'] ?? 0);
        $titulaie_femmes_partiel       = intval($_POST['titulaie_femmes_partiel'] ?? 0);
        $contrat_temps_complet         = intval($_POST['contrat_temps_complet'] ?? 0);
        $contrat_femme_complet         = intval($_POST['contrat_femme_complet'] ?? 0);
        $contrat_temps_pratiel         = intval($_POST['contrat_temps_pratiel'] ?? 0);
        $contrat_femmes_pratiel        = intval($_POST['contrat_femmes_pratiel'] ?? 0);

        $observations = trim($_POST['observations'] ?? '');

        $annee = date('Y');

        // ==============================
        // Vérifier grade
        // ==============================
        $grade = Grade::trouve_par_id($id_grade);
        if (!$grade) {
            throw new Exception('الرتبة غير موجودة');
        }

        // ==============================
        // CALCUL SERVEUR OBLIGATOIRE
        // ==============================
        $tol_titu_stag = $titulaires + $stagaires;
        $difrence      = $tol_titu_stag - $effectif_reel_annee_1;

        // ==============================
        // Création objet
        // ==============================
        // SI TABLEAU EXISITE AJOUTER id_tableau_1_1 AVEC LE ID 
        if (!empty($id_tableau)){
             $detail =  new DetailTab1_1();
             $detail->id_tableau_1_1 = $id_tableau;
        }else{
            // SI NON CRER UN 
$detail = new DetailTab1_1();
        $detail->id_tableau_1_1 = 0;
        }
        
        $detail->annee = $annee;
        $detail->id_user = $current_user->id;
        $detail->id_societe = $current_user->id_societe;
        $detail->id_grade = $id_grade;

        $detail->effectif_reel_31_dec  = $effectif_reel_31_dec;
        $detail->effectif_reel_annee_1 = $effectif_reel_annee_1;
        $detail->titulaires            = $titulaires;
        $detail->stagaires             = $stagaires;
        $detail->tol_titu_stag         = $tol_titu_stag;
        $detail->femmes                = $femmes;
        $detail->difrence              = $difrence;

        $detail->titulaie_temps_complet  = $titulaie_temps_complet;
        $detail->titulaie_femmes_complet = $titulaie_femmes_complet;
        $detail->titulaie_temps_partiel  = $titulaie_temps_partiel;
        $detail->titulaie_femmes_partiel = $titulaie_femmes_partiel;

        $detail->contrat_temps_complet  = $contrat_temps_complet;
        $detail->contrat_femme_complet  = $contrat_femme_complet;
        $detail->contrat_temps_pratiel  = $contrat_temps_pratiel;
        $detail->contrat_femmes_pratiel = $contrat_femmes_pratiel;

        $detail->observations = $observations;

        // ==============================
        // Sauvegarde
        // ==============================
        if (!$detail->save()) {
            throw new Exception('فشل حفظ البيانات');
        }

        $response['success'] = true;
        $response['id'] = $detail->id;
        $response['grade_name'] = $grade->grade;
        $response['tol_titu_stag'] = $tol_titu_stag;
        $response['difrence'] = $difrence;
        $response['message'] = 'تمت الإضافة بنجاح';
    }elseif  ($action === 'delete_detail') {

    $id = intval($_POST['id'] ?? 0);

    $detail = DetailTab1_1::trouve_par_id($id);

    if (!$detail) {
        throw new Exception('السطر غير موجود');
    }

    if (!$detail->supprime()) {
        throw new Exception('فشل الحذف');
    }

    $response['success'] = true;
}elseif ($action === 'save_tableau') {

    $id_tableau = intval($_POST['id_tableau'] ?? 0);
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
    if ($id_tableau == 0){
    $details = DetailTab1_1::trouve_tableeu_vide($id_societe, $id_user);

    if (empty($details)) {
        throw new Exception('لا توجد بيانات للحفظ');
    }
    }else{
        $details = DetailTab1_1::trouve_tableeu_vide($id_tableau,$id_societe, $id_user);
    }

    // ==============================
    // TRANSACTION (IMPORTANT ERP)
    // ==============================
   

    try {

        // ==============================
        // 1️⃣ Créer tableau principal
        // ==============================
        $tableau = new Tableau1_1();
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
            $detail->id_tableau_1_1 = $last_id;

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