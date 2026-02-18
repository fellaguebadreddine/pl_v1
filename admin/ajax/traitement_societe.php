<?php
session_start();
require_once("../../includes/initialiser.php");

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

//if ($action !== 'ajouter') {
 //   echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
//   exit;
//}


// Dans la vérification de l'action, ajoutez :
if ($action === 'ajouter') {
    ajouterSociete();
} elseif ($action === 'details') {
    detailsSociete();
} elseif ($action === 'modifier') {
    modifierSociete();
} elseif ($action === 'supprimer') {
    supprimerSociete();
} else {
    echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
    exit;
}

// Ajouter cette fonction pour les détails
function detailsSociete() {
    $id_societe = intval($_GET['id'] ?? 0);
    
    if ($id_societe <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف الشركة غير صالح']);
        exit;
    }
    
    $societe = Societe::trouve_par_id($id_societe);
    
    if (!$societe) {
        echo json_encode(['success' => false, 'message' => 'الشركة غير موجودة']);
        exit;
    }
    
    // Déterminer la classe CSS et le texte pour l'état
    $etatClasses = [
        'active' => 'success',
        'inactive' => 'danger',
        'pending' => 'warning',
        'suspended' => 'secondary'
    ];
    
    $etatTexts = [
        'active' => 'نشطة',
        'inactive' => 'غير نشطة',
        'pending' => 'قيد المراجعة',
        'suspended' => 'موقوفة مؤقتاً'
    ];
    
    $etatClass = isset($etatClasses[$societe->etat]) ? $etatClasses[$societe->etat] : 'secondary';
    $etatText = isset($etatTexts[$societe->etat]) ? $etatTexts[$societe->etat] : 'غير محدد';
    
    // Formater la date de création
   // $date_creation_formatted = date('d/m/Y', strtotime($societe->date_creation));
    
    // Récupérer le nom de la wilaya
    //$wilaya_nom = $societe->nom_wilaya();
    
    echo json_encode([
        'success' => true,
        'id_societe' => $societe->id_societe,
        'raison_ar' => $societe->raison_ar,
        'raison_fr' => $societe->raison_fr,
        'adresse_ar' => $societe->adresse_ar,
        'postal' => $societe->postal,
        'tel1' => $societe->tel1,
        'tel2' => $societe->tel2,
        'fax' => $societe->fax,
        'mob1' => $societe->mob1,
        'mob2' => $societe->mob2,
        'email' => $societe->email,
        'logo' => $societe->logo,
        'etat' => $societe->etat,
        'etat_class' => $etatClass,
        'etat_text' => $etatText,
        'wilaya_nom' => $societe->wilayas
    ]);
}
function ajouterSociete() {
try {

    // ========================
    // 1️⃣ Récupération & validation
    // ========================
    $raison_ar  = trim($_POST['raison_ar'] ?? '');
    $raison_fr  = trim($_POST['raison_fr'] ?? '');
    $adresse_ar = trim($_POST['adresse_ar'] ?? '');
    $postal     = trim($_POST['postal'] ?? '');
    $tel1       = trim($_POST['tel1'] ?? '');
    $tel2       = trim($_POST['tel2'] ?? '');
    $fax        = trim($_POST['fax'] ?? '');
    $mob1       = trim($_POST['mob1'] ?? '');
    $mob2       = trim($_POST['mob2'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $wilayas    = (int)($_POST['wilayas'] ?? 0);
    $etat       = trim($_POST['etat'] ?? 'active');

    if (!$raison_ar || !$raison_fr || !$adresse_ar || !$tel1 || !$wilayas) {
        echo json_encode(['success'=>false,'message'=>'جميع الحقول الإلزامية مطلوبة']);
        exit;
    }

    if (!preg_match('/^(05|06|07)[0-9]{8}$/', $tel1)) {
        echo json_encode(['success'=>false,'message'=>'رقم الهاتف غير صالح']);
        exit;
    }

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success'=>false,'message'=>'البريد الإلكتروني غير صالح']);
        exit;
    }

    // ========================
    // 2️⃣ Upload logo
    // ========================
    $logo = null;

    if (!empty($_FILES['logo']['name'])) {

        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success'=>false,'message'=>'نوع صورة غير مسموح']);
            exit;
        }

        if ($_FILES['logo']['size'] > 2*1024*1024) {
            echo json_encode(['success'=>false,'message'=>'حجم الصورة أكبر من 2MB']);
            exit;
        }

        $upload_dir = __DIR__ . '/../uploads/logos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $logo = uniqid('logo_', true).'.'.$ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir.$logo);
    }

    // ========================
    // 3️⃣ Save
    // ========================
    $societe = new Societe();
    $societe->raison_ar  = $raison_ar;
    $societe->raison_fr  = $raison_fr;
    $societe->adresse_ar = $adresse_ar;
    $societe->postal     = $postal;
    $societe->tel1       = $tel1;
    $societe->tel2       = $tel2;
    $societe->fax        = $fax;
    $societe->mob1       = $mob1;
    $societe->mob2       = $mob2;
    $societe->email      = $email;
    $societe->wilayas    = $wilayas;
    $societe->etat       = $etat;
    $societe->logo       = $logo;

    if ($societe->existe()) {
        echo json_encode(['success'=>false,'message'=>'المؤسسة موجودة مسبقاً']);
        exit;
    }

    if ($societe->save()) {
        echo json_encode([
            'success'=>true,
            'message'=>'تمت إضافة المؤسسة بنجاح',
            'id_societe'=>$societe->id_societe
        ]);
    } else {
        echo json_encode(['success'=>false,'message'=>'فشل الحفظ']);
    }

} catch (Throwable $e) {
    error_log($e->getMessage());
    echo json_encode(['success'=>false,'message'=>'خطأ داخلي في النظام']);
}
}
