<?php
require_once('../../includes/initialiser.php');

// Vérifie si la requête est AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit;
}

// Vérifie si l'utilisateur est connecté
if (!$session->is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}

// Récupère les données POST
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$response = ['success' => false, 'message' => ''];

try {
    $current_user = Accounts::trouve_par_id($session->id_utilisateur);
    if (!$current_user) {
        throw new Exception('المستخدم غير موجود');
    }
    
    switch ($action) {
        case 'add_detail':
            $type = isset($_POST['type']) ? $_POST['type'] : 'hf';
           
            
            if ($type === 'hf') {
                // Récupérer les données
           
            $id_grade = isset($_POST['id_grade']) ? intval($_POST['id_grade']) : 0;
            $code = isset($_POST['code']) ? intval($_POST['code']) : 0;
            $postes_total = isset($_POST['postes_total']) ? intval($_POST['postes_total']) : 0;
            $postes_reel = isset($_POST['postes_reel']) ? intval($_POST['postes_reel']) : 0;
            $poste_intirim = isset($_POST['poste_intirim']) ? intval($_POST['poste_intirim']) : 0;
            $poste_femme = isset($_POST['poste_femme']) ? intval($_POST['poste_femme']) : 0;
            $difference = isset($_POST['difference']) ? intval($_POST['difference']) : 0;
            $observations = isset($_POST['observations']) ? trim($_POST['observations']) : '';
            $id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
            $id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
            $annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
            $total_hf_postes_total = isset($_POST['total_hf_postes_total']) ? intval($_POST['total_hf_postes_total']) : 0;
            $total_hf_postes_reel = isset($_POST['total_hf_postes_reel']) ? intval($_POST['total_hf_postes_reel']) : 0;
            $total_hf_poste_intirim = isset($_POST['total_hf_poste_intirim']) ? intval($_POST['total_hf_poste_intirim']) : 0;
            $total_hf_poste_femme = isset($_POST['total_hf_poste_femme']) ? intval($_POST['total_hf_poste_femme']) : 0;
            $total_hf_difference = isset($_POST['total_hf_difference']) ? intval($_POST['total_hf_difference']) : 0;

             // Vérifier si le grade existe
             $grade = Grade::trouve_par_id($id_grade);
             if (!$grade) {
                 throw new Exception('الوظيفة غير موجودة');
             }
            
                
                // Créer le détail HF
                $detail = new DetailTab1();
                $detail->id_tab_1 = 0;
                $detail->annee = $annee;
                $detail->code = $code;
                $detail->id_user = $id_user;
                $detail->id_societe = $id_societe;
                $detail->id_grade = $id_grade;
                $detail->code = $code;
                $detail->postes_total = $postes_total;
                $detail->postes_reel = $postes_reel;
                $detail->poste_intirim = $poste_intirim;
                $detail->poste_femme = $poste_femme;
                $detail->difference = $difference;
                $detail->observations = $observations;
                $detail->date_tabl = date('Y-m-d');
                $detail->total_hf_postes_total = $total_hf_postes_total;
                $detail->total_hf_postes_reel = $total_hf_postes_reel;
                $detail->total_hf_poste_intirim = $total_hf_poste_intirim;
                $detail->total_hf_poste_femme = $total_hf_poste_femme;
                $detail->total_hf_difference = $total_hf_difference;
                
                if ($detail->save()) {
                                                         
                    $response['success'] = true;
                    $response['id'] = $detail->id;
                    $response['code'] = $grade->id;
                    $response['grade_name'] = $grade->grade;
                    $response['message'] = 'تمت الإضافة بنجاح';
                   
                } else {
                    throw new Exception('خطأ أثناء حفظ البيانات');
                }
            } else {
            $type = isset($_POST['type']) ? $_POST['type'] : 'hp';
            $id_grade_hp = isset($_POST['id_grade_hp']) ? intval($_POST['id_grade_hp']) : 0;
            $code_hp = isset($_POST['code_hp']) ? intval($_POST['code_hp']) : 0;
            $postes_total_hp = isset($_POST['postes_total_hp']) ? intval($_POST['postes_total_hp']) : 0;
            $postes_reel_hp = isset($_POST['postes_reel_hp']) ? intval($_POST['postes_reel_hp']) : 0;
            $poste_intirim_hp = isset($_POST['poste_intirim_hp']) ? intval($_POST['poste_intirim_hp']) : 0;
            $poste_femme_hp = isset($_POST['poste_femme_hp']) ? intval($_POST['poste_femme_hp']) : 0;
            $difference_hp = isset($_POST['difference_hp']) ? intval($_POST['difference_hp']) : 0;
            $observations_hp = isset($_POST['observations_hp']) ? trim($_POST['observations_hp']) : '';
            $id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
            $id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
            $annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
            $total_hp_postes_total = isset($_POST['total_hp_postes_total']) ? intval($_POST['total_hp_postes_total']) : 0;
            $total_hp_postes_reel = isset($_POST['total_hp_postes_reel']) ? intval($_POST['total_hp_postes_reel']) : 0;
            $total_hp_poste_intirim = isset($_POST['total_hp_poste_intirim']) ? intval($_POST['total_hp_poste_intirim']) : 0;
            $total_hp_poste_femme = isset($_POST['total_hp_poste_femme']) ? intval($_POST['total_hp_poste_femme']) : 0;
            $total_hp_difference = isset($_POST['total_hp_difference']) ? intval($_POST['total_hp_difference']) : 0;
            
                
                // Créer le détail HP
                $detail = new DetailTab1_hp();
                $detail->id_tab_1 = 0;
                $detail->annee = $annee;
                $detail->code = $code_hp;
                $detail->id_user = $id_user;
                $detail->id_societe = $id_societe;
                $detail->id_grade_hp = $id_grade_hp;
                $detail->postes_total_hp = $postes_total_hp;
                $detail->postes_reel_hp = $postes_reel_hp;
                $detail->poste_intirim_hp = $poste_intirim_hp;
                $detail->poste_femme_hp = $poste_femme_hp;
                $detail->difference_hp = $difference_hp;
                $detail->observations_hp = $observations_hp;
                $detail->total_hp_postes_total = $total_hp_postes_total;
                $detail->total_hp_postes_reel = $total_hp_postes_reel;
                $detail->total_hp_poste_intirim = $total_hp_poste_intirim;
                $detail->total_hp_poste_femme = $total_hp_poste_femme;
                $detail->total_hp_difference = $total_hp_difference;
                $detail->date_tabl_hp = date('Y-m-d');
                 // Vérifier si le grade existe
             $grade = Grade::trouve_par_id($id_grade_hp);
             if (!$grade) {
                 throw new Exception('الوظيفة غير موجودة');
             }
                
                if ($detail->save()) {
                                        
                    $response['success'] = true;
                    $response['id'] = $detail->id;
                    $response['code'] = $grade->id;
                    $response['grade_name'] = $grade->grade;
                    $response['message'] = 'تمت الإضافة بنجاح';
                    
                } else {
                    throw new Exception('خطأ أثناء حفظ البيانات');
                }
            }
            break;
            
        case 'delete_detail':
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $type = isset($_POST['type']) ? $_POST['type'] : 'hf';
            
            if ($type === 'hf') {
                $detail = DetailTab1::trouve_par_id($id);
                $tableau_id = $detail ? $detail->id_tab_1 : 0;
                
                if ($detail && $detail->supprime()) {
                    // Recalculer les totaux
                    $details = DetailTab1::trouve_par_tableau($tableau_id);
                    $total = 0;
                    $total_reel = 0;
                    $total_intrim = 0;
                    $total_femmes = 0;
                    
                    foreach ($details as $d) {
                        $total += $d->postes_total;
                        $total_reel += $d->postes_reel;
                        $total_intrim += $d->poste_intirim;
                        $total_femmes += $d->poste_femme;
                    }
                    
                    // Mettre à jour le tableau
                    $tableau = Tableau1::trouve_par_id($tableau_id);
                    if ($tableau) {
                        $tableau->total = $total;
                        $tableau->total_reel = $total_reel;
                        $tableau->total_intrim = $total_intrim;
                        $tableau->total_femmes = $total_femmes;
                        $tableau->save();
                    }
                    
                    $response['success'] = true;
                    $response['message'] = 'تم الحذف بنجاح';
                    $response['totals'] = [
                        'total' => $total,
                        'total_reel' => $total_reel,
                        'total_intrim' => $total_intrim,
                        'total_femmes' => $total_femmes
                    ];
                } else {
                    throw new Exception('خطأ أثناء الحذف');
                }
            } else {
                $detail = DetailTab1_hp::trouve_par_id($id);
                $tableau_id = $detail ? $detail->id_tableau : 0;
                
                if ($detail && $detail->supprime()) {
                    // Recalculer les totaux HP
                    $details_hp = DetailTab1_hp::trouve_par_tableau($tableau_id);
                    $total_hp = 0;
                    $total_hp_reel = 0;
                    $total_hp_intirim = 0;
                    $total_hp_femme = 0;
                    
                    foreach ($details_hp as $d) {
                        $total_hp += $d->postes_total_hp;
                        $total_hp_reel += $d->postes_reel_hp;
                        $total_hp_intirim += $d->poste_intirim_hp;
                        $total_hp_femme += $d->poste_femme_hp;
                    }
                    
                    // Mettre à jour le tableau
                    $tableau = Tableau1::trouve_par_id($tableau_id);
                    if ($tableau) {
                        // Soustraire les anciens totaux HP
                        $tableau->total -= $total_hp;
                        $tableau->total_reel -= $total_hp_reel;
                        $tableau->total_intrim -= $total_hp_intirim;
                        $tableau->total_femmes -= $total_hp_femme;
                        $tableau->save();
                    }
                    
                    $response['success'] = true;
                    $response['message'] = 'تم الحذف بنجاح';
                    $response['totals'] = [
                        'total_hp' => $total_hp,
                        'total_hp_reel' => $total_hp_reel,
                        'total_hp_intirim' => $total_hp_intirim,
                        'total_hp_femme' => $total_hp_femme
                    ];
                } else {
                    throw new Exception('خطأ أثناء الحذف');
                }
            }
            break;
            
        case 'save_tableau':
            $statut = isset($_POST['statut']) ? $_POST['statut'] : 'en attente';
            $annee = isset($_POST['annee']) ? intval($_POST['annee']) : date('Y');
            $id_user = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
            $id_societe = isset($_POST['id_societe']) ? intval($_POST['id_societe']) : 0;
           
            
            // Vérifier les permissions
            if ($current_user->id != $id_user || $current_user->id_societe != $id_societe) {
                throw new Exception('غير مصرح بهذا الإجراء');
            }           
            
            $details_hf = DetailTab1::trouve_tab_vide_par_admin($id_user,$id_societe);
            $details_hp = DetailTab1_hp::trouve_tab_vide_par_admin($id_user,$id_societe);
             
            $total = 0;
            $total_reel = 0;
            $total_intrim = 0;
            $total_femmes = 0;
            
            foreach ($details_hf as $detail) {
                $total += $detail->postes_total;
                $total_reel += $detail->postes_reel;
                $total_intrim += $detail->poste_intirim;
                $total_femmes += $detail->poste_femme;
            }
            
            foreach ($details_hp as $detail) {
                $total += $detail->postes_total_hp;
                $total_reel += $detail->postes_reel_hp;
                $total_intrim += $detail->poste_intirim_hp;
                $total_femmes += $detail->poste_femme_hp;
            }
            
            // Mettre à jour le tableau
            $tableau =  New Tableau1();
            $tableau->statut = $statut;
            $tableau->annee = $annee;
            $tableau->id_user = $id_user;
            $tableau->id_societe = $id_societe;
            $tableau->total = $total;
            $tableau->total_reel = $total_reel;
            $tableau->total_intrim = $total_intrim;
            $tableau->total_femmes = $total_femmes;
            
            if ($statut == 'en_attente') {
                $tableau->date_valide = date('Y-m-d H:i:s');
            }
            
            if ($tableau->save()) {
                $detail_tab1s= DetailTab1::trouve_tab_vide_par_admin($id_user,$id_societe);
                foreach ($detail_tab1s as $detail_tab1){
                    $detail_tab1->id_tab_1 = $tableau->id;
                    $detail_tab1->save();
                }
                $detail_hp_tab1s= DetailTab1_hp::trouve_tab_vide_par_admin($id_user,$id_societe);
                foreach ($detail_hp_tab1s as $detail_hp_tab1){
                    $detail_hp_tab1->id_tab_1 = $tableau->id;
                    $detail_hp_tab1->save();
                }
                // Calculer les totaux finaux
           



                $response['success'] = true;
                $response['message'] = $statut == 'en_attente' ? 'تم تقديم الجدول بنجاح' : 'تم حفظ المسودة بنجاح';
            } else {
                throw new Exception('خطأ أثناء حفظ الجدول');
            }
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