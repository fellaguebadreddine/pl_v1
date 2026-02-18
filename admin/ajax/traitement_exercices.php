<?php
require_once('../../includes/initialiser.php');

// Vérifier l'authentification
if (!$session->is_logged_in()) {
    echo json_encode(array('success' => false, 'message' => 'غير مصرح'));
    exit;
}

$current_user = Accounts::trouve_par_id($session->id_utilisateur);
if (!$current_user || $current_user->type !== 'administrateur') {
    echo json_encode(array('success' => false, 'message' => 'غير مصرح'));
    exit;
}

// Traiter les requêtes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'ajouter':
            if (isset($_POST['annee'], $_POST['date_debut'], $_POST['date_fin'])) {
                $result = Exercice::ouvrir_exercice(
                    $_POST['annee'],
                    $_POST['date_debut'],
                    $_POST['date_fin'],
                    $_POST['commentaire'] ?? ''
                );
                echo json_encode($result);
            } else {
                echo json_encode(array('success' => false, 'message' => 'بيانات ناقصة'));
            }
            break;
            
        case 'fermer':
            if (isset($_POST['id'])) {
                $result = Exercice::fermer_exercice(
                    $_POST['id'],
                    $current_user->id,
                    $_POST['commentaire'] ?? ''
                );
                echo json_encode($result);
            } else {
                echo json_encode(array('success' => false, 'message' => 'معرف غير محدد'));
            }
            break;
            
        case 'prolonger':
            if (isset($_POST['exercice_id'], $_POST['nouvelle_date_fin'])) {
                $result = Exercice::prolonger_exercice(
                    $_POST['exercice_id'],
                    $_POST['nouvelle_date_fin'],
                    $current_user->id,
                    $_POST['commentaire'] ?? ''
                );
                echo json_encode($result);
            } else {
                echo json_encode(array('success' => false, 'message' => 'بيانات ناقصة'));
            }
            break;
            
        case 'supprimer':
            if (isset($_POST['id'])) {
                $result = Exercice::supprimer($_POST['id']);
                if ($result) {
                    echo json_encode(array('success' => true, 'message' => 'تم الحذف بنجاح'));
                } else {
                    echo json_encode(array('success' => false, 'message' => 'خطأ أثناء الحذف'));
                }
            }
            break;
            
        case 'reouvrir':
            if (isset($_POST['id'])) {
                $exercice = Exercice::trouve_par_id($_POST['id']);
                if ($exercice) {
                    $exercice->statut = 'ouvert';
                    if ($exercice->save()) {
                        echo json_encode(array('success' => true, 'message' => 'تم إعادة الفتح بنجاح'));
                    } else {
                        echo json_encode(array('success' => false, 'message' => 'خطأ أثناء إعادة الفتح'));
                    }
                } else {
                    echo json_encode(array('success' => false, 'message' => 'لم يتم العثور على السنة المالية'));
                }
            }
            break;
            
        default:
            echo json_encode(array('success' => false, 'message' => 'عملية غير معروفة'));
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'details') {
    if (isset($_GET['id'])) {
        $exercice = Exercice::trouve_par_id($_GET['id']);
        if ($exercice) {
            echo json_encode(array(
                'success' => true,
                'id' => $exercice->id,
                'annee' => $exercice->annee,
                'date_debut' => $exercice->date_debut,
                'date_fin' => $exercice->date_fin,
                'statut' => $exercice->statut,
                'commentaire' => $exercice->commentaire
            ));
        } else {
            echo json_encode(array('success' => false, 'message' => 'لم يتم العثور على السنة المالية'));
        }
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'طلب غير صالح'));
}
?>