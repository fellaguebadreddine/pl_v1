<?php
require_once('../../includes/initialiser.php');

// Vérification AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['success' => false, 'message' => 'Accès non autorisé']));
}

// Vérification session
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'administrateur') {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
$type = isset($_POST['type']) ? $_POST['type'] : 'tab1'; // Par défaut tab1

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
    exit;
}

// Déterminer la classe à utiliser
switch ($type) {
    case 'tab1':
        $classe = 'Tableau1';
        break;
    case 'tab3':
        $classe = 'Tableau3';
        break;
    case 'tab4':
        $classe = 'Tableau4';
        break;
    // Ajouter d'autres cas si nécessaire
    default:
        echo json_encode(['success' => false, 'message' => 'نوع الجدول غير معروف']);
        exit;
}

// Vérifier que la classe existe
if (!class_exists($classe)) {
    echo json_encode(['success' => false, 'message' => 'فئة الجدول غير موجودة']);
    exit;
}

$tableau = $classe::trouve_par_id($id);
if (!$tableau) {
    echo json_encode(['success' => false, 'message' => 'لايوجد جدول']);
    exit;
}

$tableau->commentaire_admin = $commentaire;
if ($tableau->save()) {
    echo json_encode(['success' => true, 'message' => 'تم حفظ الملاحظة']);
} else {
    echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
}
?>