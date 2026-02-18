<?php
session_start();
require_once("../../includes/initialiser.php");

header('Content-Type: application/json');

// Vérifier l'authentification
if (!isset($session->id_utilisateur)) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user) {
    echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'changer_motpasse_personnel') {
    changerMotPassePersonnel();
} else {
    echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
    exit;
}

function changerMotPassePersonnel() {
    global $current_user;
    
    $ancien_motpasse = trim($_POST['ancien_motpasse'] ?? '');
    $nouveau_motpasse = trim($_POST['nouveau_motpasse'] ?? '');
    $user_id = intval($_POST['user_id'] ?? 0);
    
    // Vérifier que l'utilisateur modifie son propre mot de passe
    if ($user_id != $current_user->id) {
        echo json_encode(['success' => false, 'message' => 'غير مسموح بتغيير كلمة مرور مستخدم آخر']);
        exit;
    }
    
    // Vérifier l'ancien mot de passe
    if (!password_verify($ancien_motpasse, $current_user->mot_passe)) {
        echo json_encode(['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة']);
        exit;
    }
    
    if (strlen($nouveau_motpasse) < 8) {
        echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل']);
        exit;
    }
    
    try {
        $current_user->mot_passe = password_hash($nouveau_motpasse, PASSWORD_DEFAULT);
        $current_user->phash = md5(uniqid());
        $current_user->date_modif = date('Y-m-d H:i:s');
        
        if ($current_user->save()) {
                       
            echo json_encode(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ في تحديث كلمة المرور']);
        }
    } catch (Exception $e) {
        error_log("Erreur changement mot de passe personnel: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

?>