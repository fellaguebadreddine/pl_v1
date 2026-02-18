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
if (!$current_user || $current_user->type !== 'administrateur') {
    echo json_encode(['success' => false, 'message' => 'غير مسموح بالوصول']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'ajouter':
        ajouterGrade();
        break;
    case 'details':
        detailsGrade();
        break;
    case 'modifier':
        modifierGrade();
        break;
    case 'supprimer':
        supprimerGrade();
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

function ajouterGrade() {
    // Récupérer les données
    $grade = trim($_POST['grade'] ?? '');
    $lois = trim($_POST['lois'] ?? '');
    $actif = intval($_POST['actif'] ?? 1);
    
    // Validation
    if (empty($grade)) {
        echo json_encode(['success' => false, 'message' => 'اسم الرتبة مطلوب']);
        exit;
    }
   
    
    try {
        // Créer la grade
        $gradeObj = new Grade();
        $gradeObj->grade = htmlentities($grade);
        $gradeObj->lois = htmlentities($lois);
        $gradeObj->actif = $actif;
        
        // Vérifier si la grade existe déjà
        if ($gradeObj->existe()) {
            echo json_encode(['success' => false, 'message' => 'الرتبة موجودة مسبقاً']);
            exit;
        }
        
        if ($gradeObj->save()) {
           
            
            echo json_encode([
                'success' => true,
                'message' => 'تمت إضافة الرتبة بنجاح',
                'id' => $gradeObj->id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
        }
    } catch (Exception $e) {
        error_log("Erreur d'ajout grade: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function detailsGrade() {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف الرتبة غير صالح']);
        exit;
    }
    
    $grade = Grade::trouve_par_id($id);
    
    if (!$grade) {
        echo json_encode(['success' => false, 'message' => 'الرتبة غير موجودة']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'id' => $grade->id,
        'grade' => $grade->grade,
        'lois' => $grade->lois,
        'actif' => $grade->actif
    ]);
}

function modifierGrade() {
    $id = intval($_POST['grade_id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف الرتبة غير صالح']);
        exit;
    }
    
    $grade = Grade::trouve_par_id($id);
    
    if (!$grade) {
        echo json_encode(['success' => false, 'message' => 'الرتبة غير موجودة']);
        exit;
    }
    
    // Mettre à jour les champs
    $grade->grade = htmlentities(trim($_POST['grade'] ?? ''));
    $grade->lois = htmlentities(trim($_POST['lois'] ?? ''));
    $grade->actif = intval($_POST['actif'] ?? 1);
    
    // Validation
    if (empty($grade->grade)) {
        echo json_encode(['success' => false, 'message' => 'اسم الرتبة مطلوب']);
        exit;
    }
    
    try {
        if ($grade->save()) {
           
            
            echo json_encode(['success' => true, 'message' => 'تم تعديل الرتبة بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ في تحديث البيانات']);
        }
    } catch (Exception $e) {
        error_log("Erreur modification grade: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function supprimerGrade() {
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف الرتبة غير صالح']);
        exit;
    }
    
    $grade = Grade::trouve_par_id($id);
    
    if (!$grade) {
        echo json_encode(['success' => false, 'message' => 'الرتبة غير موجودة']);
        exit;
    }
    
    try {
        if ($grade->supprimer()) {
           
            
            echo json_encode(['success' => true, 'message' => 'تم حذف الرتبة بنجاح']);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطأ في حذف البيانات']);
        }
    } catch (Exception $e) {
        error_log("Erreur suppression grade: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function activerSelection() {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي رتب']);
        exit;
    }
    
    try {
        $count = 0;
        foreach ($ids as $id) {
            $grade = Grade::trouve_par_id($id);
            if ($grade) {
                $grade->actif = 1;
                $grade->save();
                $count++;
            }
        }
        
        
        echo json_encode(['success' => true, 'message' => 'تم تفعيل ' . $count . ' رتبة بنجاح']);
    } catch (Exception $e) {
        error_log("Erreur activation grades: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function desactiverSelection() {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي رتب']);
        exit;
    }
    
    try {
        $count = 0;
        foreach ($ids as $id) {
            $grade = Grade::trouve_par_id($id);
            if ($grade) {
                $grade->actif = 0;
                $grade->save();
                $count++;
            }
        }
        
        
        echo json_encode(['success' => true, 'message' => 'تم تعطيل ' . $count . ' رتبة بنجاح']);
    } catch (Exception $e) {
        error_log("Erreur désactivation grades: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}

function supprimerSelection() {
    $ids = json_decode($_POST['ids'] ?? '[]', true);
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'لم يتم تحديد أي رتب']);
        exit;
    }
    
    try {
        $count = 0;
        foreach ($ids as $id) {
            $grade = Grade::trouve_par_id($id);
            if ($grade && $grade->supprimer()) {
                $count++;
            }
        }
        
        
        echo json_encode(['success' => true, 'message' => 'تم حذف ' . $count . ' رتبة بنجاح']);
    } catch (Exception $e) {
        error_log("Erreur suppression grades: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'خطأ في النظام: ' . $e->getMessage()]);
    }
}


?>