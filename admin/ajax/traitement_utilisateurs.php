<?php
session_start();
require_once("../../includes/initialiser.php");

header('Content-Type: application/json');

// Vérifier l'authentification
//if (!isset($_SESSION['user_id'])) {
  //  echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
  //  exit;
//}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'administrateur') {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'ajouter':
        ajouterUtilisateur();
        break;
    case 'details':
        detailsUtilisateur();
        break;
    case 'modifier':
        modifierUtilisateur();
        break;
    case 'supprimer':
        supprimerUtilisateur();
        break;
    case 'changer_motpasse':
        changerMotPasse();
        break;
    case 'activer_selection':
        activerSelection();
        break;
    case 'desactiver_selection':
        desactiverSelection();
        break;
    case 'supprimer_selection':
        supprimerSelection();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'عملية غير معروفة']);
        exit;
}

function ajouterUtilisateur() {
    // Récupérer les données
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $type = trim($_POST['type'] ?? 'utilisateur');
    $active = intval($_POST['active'] ?? 1);
    $telephone = trim($_POST['telephone'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $wilaya = intval($_POST['wilaya'] ?? 0);
    $id_societe = intval($_POST['societe_id'] ?? 0);
    
    // Validation
    if (empty($username) || empty($password) || empty($email) || empty($nom) || empty($prenom)) {
        echo json_encode(['success' => false, 'message' => 'جميع الحقول الإلزامية مطلوبة']);
        exit;
    }
    
    // Vérifier si l'utilisateur existe déjà
    $existing = Accounts::trouver_par_login_simple($username);
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'اسم المستخدم موجود مسبقاً']);
        exit;
    }
    
    // Vérifier l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صالح']);
        exit;
    }
    
    // Traitement de la photo
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 2 * 1024 * 1024;
        
        $file_name = $_FILES['photo']['name'];
        $file_size = $_FILES['photo']['size'];
        $file_tmp = $_FILES['photo']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'نوع الملف غير مسموح به']);
            exit;
        }
        
        if ($file_size > $max_size) {
            echo json_encode(['success' => false, 'message' => 'حجم الملف كبير جداً (الحد الأقصى 2MB)']);
            exit;
        }
        
        $new_file_name = uniqid('user_', true) . '.' . $file_ext;
        $upload_dir = '../../uploads/photos/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $destination = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($file_tmp, $destination)) {
            $photo = $new_file_name;
        }
    }
    
    try {
        // Créer l'utilisateur
        $utilisateur = new Accounts();
        $utilisateur->user = htmlentities($username);
        $utilisateur->mot_passe = password_hash($password, PASSWORD_DEFAULT);
        $utilisateur->phash = md5(uniqid());
        $utilisateur->nom = htmlentities($nom);
        $utilisateur->prenom = htmlentities($prenom);
        $utilisateur->type = htmlentities($type);
        $utilisateur->email = htmlentities($email);
        $utilisateur->telephone = htmlentities($telephone);
        $utilisateur->mobile = htmlentities($mobile);
        $utilisateur->adresse = htmlentities($adresse);
        $utilisateur->wilaya = $wilaya;
        $utilisateur->photo = $photo;
        $utilisateur->active = $active;
        $utilisateur->id_societe = $id_societe;
        $utilisateur->date_creation = date('Y-m-d H:i:s');
        $utilisateur->date_modif = date('Y-m-d H:i:s');
        
        if ($utilisateur->save()) {
            
            echo json_encode([
                'success' => true,
                'message' => 'تمت إضافة المستخدم بنجاح',
                'id' => $utilisateur->id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
        }
    } catch (Exception $e) {
        error_log("Erreur d'ajout utilisateur: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function detailsUtilisateur() {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف المستخدم غير صالح']);
        exit;
    }
    
    $utilisateur = Accounts::trouve_par_id($id);
    
    if (!$utilisateur) {
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'id' => $utilisateur->id,
        'user' => $utilisateur->user,
        'nom' => $utilisateur->nom,
        'prenom' => $utilisateur->prenom,
        'type' => $utilisateur->type,
        'email' => $utilisateur->email,
        'telephone' => $utilisateur->telephone,
        'mobile' => $utilisateur->mobile,
        'adresse' => $utilisateur->adresse,
        'wilaya' => $utilisateur->wilaya,
        'photo' => $utilisateur->photo,
        'active' => $utilisateur->active,
        'date_creation' => $utilisateur->date_creation,
        'date_modif' => $utilisateur->date_modif
    ]);
}

function supprimerUtilisateur() {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف المستخدم غير صالح']);
        exit;
    }
    
    // Ne pas permettre de supprimer son propre compte
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'لا يمكن حذف حسابك الخاص']);
        exit;
    }
    
    $utilisateur = Accounts::trouve_par_id($id);
    
    if (!$utilisateur) {
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
        exit;
    }
    
    try {
        // Supprimer la photo si elle existe
        if (!empty($utilisateur->photo) && file_exists('../../uploads/photos/' . $utilisateur->photo)) {
            unlink('../../uploads/photos/' . $utilisateur->photo);
        }
        
        if ($utilisateur->supprimer()) {
            
            echo json_encode(['success' => true, 'message' => 'تم حذف المستخدم بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ في حذف البيانات']);
        }
    } catch (Exception $e) {
        error_log("Erreur suppression utilisateur: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function changerMotPasse() {
    $id = intval($_POST['user_id'] ?? 0);
    $nouveau_motpasse = trim($_POST['nouveau_motpasse'] ?? '');
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف المستخدم غير صالح']);
        exit;
    }
    
    if (strlen($nouveau_motpasse) < 8) {
        echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل']);
        exit;
    }
    
    $utilisateur = Accounts::trouve_par_id($id);
    
    if (!$utilisateur) {
        echo json_encode(['success' => false, 'message' => 'المستخدم غير موجود']);
        exit;
    }
    
    try {
        $utilisateur->mot_passe = password_hash($nouveau_motpasse, PASSWORD_DEFAULT);
        $utilisateur->phash = md5(uniqid()); // Régénérer le hash
        $utilisateur->date_modif = date('Y-m-d H:i:s');
        
        if ($utilisateur->save()) {
            
            echo json_encode(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ في تحديث كلمة المرور']);
        }
    } catch (Exception $e) {
        error_log("Erreur changement mot de passe: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function activerSelection() {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي مستخدمين']);
        exit;
    }
    
    try {
        $count = 0;
        foreach ($ids as $id) {
            $utilisateur = Accounts::trouve_par_id($id);
            if ($utilisateur) {
                $utilisateur->active = 1;
                $utilisateur->date_modif = date('Y-m-d H:i:s');
                $utilisateur->save();
                $count++;
            }
        }
        
        
        echo json_encode(['success' => true, 'message' => 'تم تفعيل ' . $count . ' مستخدم بنجاح']);
    } catch (Exception $e) {
        error_log("Erreur activation utilisateurs: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function desactiverSelection() {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي مستخدمين']);
        exit;
    }
    
    try {
        $count = 0;
        foreach ($ids as $id) {
            // Ne pas permettre de désactiver son propre compte
            if ($id == $_SESSION['user_id']) {
                continue;
            }
            
            $utilisateur = Accounts::trouve_par_id($id);
            if ($utilisateur) {
                $utilisateur->active = 0;
                $utilisateur->date_modif = date('Y-m-d H:i:s');
                $utilisateur->save();
                $count++;
            }
        }
       
        
        echo json_encode(['success' => true, 'message' => 'تم تعطيل ' . $count . ' مستخدم بنجاح']);
    } catch (Exception $e) {
        error_log("Erreur désactivation utilisateurs: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function supprimerSelection() {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي مستخدمين']);
        exit;
    }
    
    try {
        $count = 0;
        foreach ($ids as $id) {
            // Ne pas permettre de supprimer son propre compte
            if ($id == $_SESSION['user_id']) {
                continue;
            }
            
            $utilisateur = Accounts::trouve_par_id($id);
            if ($utilisateur) {
                // Supprimer la photo si elle existe
                if (!empty($utilisateur->photo) && file_exists('../../uploads/photos/' . $utilisateur->photo)) {
                    unlink('../../uploads/photos/' . $utilisateur->photo);
                }
                
                if ($utilisateur->supprimer()) {
                    $count++;
                }
            }
        }
        
        
        
        echo json_encode(['success' => true, 'message' => 'تم حذف ' . $count . ' مستخدم بنجاح']);
    } catch (Exception $e) {
        error_log("Erreur suppression utilisateurs: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}


?>