<?php

  function cacul_pourcentage($nombre,$total,$pourcentage)
    { 
      $resultat = ($nombre/$total) * $pourcentage;
      return round($resultat); // Arrondi la valeur
    } 

	
function transformer($code) {
	$code = preg_replace("#é#", '&eacute;', $code);
	$code = preg_replace("#è#", '&egrave;', $code);
	$code = preg_replace("#ê#", '&ecirc;', $code);
	$code = preg_replace("#à#", '&agrave;', $code);
	$code = preg_replace("#ç#", '&ccedil;', $code);
	$code = preg_replace("#ù#", '&ugrave;', $code);
	$code = preg_replace("#û#", '&ucirc;', $code);
	$code = preg_replace("#©#", '&copy;', $code);
	

	return $code;
}

function find_all_files($dir) 
{ 
    $root = scandir($dir,0); 
    foreach($root as $value) 
    { 
        if($value === '.' || $value === '..' || $value === 'index.html') {continue;} 
        if(is_file("$dir/$value")) {$result[]="$dir/$value";continue;} 
        foreach(find_all_files("$dir/$value") as $value) 
        { 
            $result[]=$value; 
        } 
    } 
    return $result; 
}

function eleminer_zeros_de_date( $date="" ) {
  // first remove the marked zeros
  $sans_zeros = str_replace('*0', '', $date);
  // then remove any remaining marks
  $nettoye = str_replace('*', '', $sans_zeros);
  return $nettoye;
}
function redirect_to( $emplacement = NULL ) {
  if ($emplacement != NULL) {
    header("Location: {$emplacement}");
    exit;
  }
}
ob_start();
function readresser_a( $emplacement = NULL ) {
  if ($emplacement != NULL) {
    ob_clean();
    header("Location: {$emplacement}");
    exit;
  }
}


function afficher_message($message="") {
  if (!empty($message)) { 
    return "<p class=\"message\">{$message}</p>";
  } else {
    return "";
  }
}



function contenir_composition_template($template="") {
    global $user,$main_menu_sel,$sub_menu_sel;
	include(SITE_ROOT.DS.'composit'.DS.$template);
}
 //*******************************************************************************

//*********************************************************

function log_action($action, $message="") {
	$logfile = SITE_ROOT.DS.'logs'.DS.'log.txt';
	$new = file_exists($logfile) ? false : true;
  if($handle = fopen($logfile, 'a')) { // append
    $timestamp = strftime("%Y-%m-%d %H:%M:%S", time());
		$content = "{$timestamp} | {$action}: {$message}\n";
    fwrite($handle, $content);
    fclose($handle);
    if($new) { chmod($logfile, 0755); }
  } else {
    echo "pas de permition d'ecriture sur le ficher login ";
  }
}

function datetime_to_text($datetime=""){
  $unixdatetime = strtotime($datetime);
  return strftime("%d/%m/%Y a %I:%M %p",$unixdatetime);
}

function datetime_to_year($datetime=""){
  $unixdatetime = strtotime($datetime);
  return strftime("%Y ",$unixdatetime);
}

function mysql_datetime($datetime=''){
 if(empty($datetime)){
 return strftime("%Y-%m-%d %H:%M:%S",time());
 }else{
  return strftime("%Y-%m-%d %H:%M:%S",$datetime);
 }
 }
 
function mysql_date($date=''){
 if(empty($date)){
 return strftime("%Y-%m-%d",time());
 }else{
  return strftime("%Y-%m-%d",$date);
 }
 } 
 
function mysql_time($time=''){
 if(empty($time)){
 return strftime("%H:%M",time());
 }else{
  return strftime("%H:%M",$time);
 }
 }
 
 function fr_datetime($datetime=''){
 if(empty($datetime)){
 return strftime("%H:%M:%S | %d/%m/%Y ",time());
 }else{
  return strftime("%H:%M:%S | %d/%m/%Y ",$datetime);
 }
 }
 function fr_date($datetime){
 if(empty($datetime)){
 return strftime("%d/%m/%Y",time());
 }else{
  return strftime("%d/%m/%Y",$datetime);
 }
 }
 
  function fr_date2($datetime){
  $unixdatetime = strtotime($datetime);
  return strftime("%d-%m-%Y",$unixdatetime);

 }  function fr_date3($datetime){
  $unixdatetime = strtotime($datetime);
  return strftime("%d/%m/%Y",$unixdatetime);
 }
 
 
function frdate_mysqldate($frdate){
    $jj = substr($frdate,0,2);
	$mm = substr($frdate,3,2);
	$yy = substr($frdate,6,4);
	
	return  strftime("%Y-%m-%d %H:%M:%S",strtotime($yy."-".$mm."-".$jj));
}

function get_date($datemysql){
    $date[] = substr($datemysql,0,4);
	$date[] = substr($datemysql,5,2);
	$date[] = substr($datemysql,8,2);
	
	return  $date;
 }
 
 function ar_date($datetime=''){
 if(empty($datetime)){
 return strftime("%Y/%m/%d ",time());
 }else{
  return strftime("%Y/%m/%d ",$datetime);
 }
 }
 function fr_datetime2($datetime=''){
 if(empty($datetime)){
 return strftime(" %d.%m.%y | %Hh%M ",time());
 }else{
  return strftime(" %d.%m.%y | %Hh%M ",$datetime);
 }

}

								
						
								
function error_message($msg){
return '<div class="alert alert-danger alert-dismissable">'.$msg.' </div>';
}

function system_message($msg){

return '<div class="alert alert-info alert-dismissable">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> <br/>
			  '.$msg.'
            </div>';
}

function positif_message($msg){
return '<div class="alert alert-success ">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> <br/>
			  '.$msg.'
            </div>';


}

function session_message($msg){
return '<div class="alert alert-info">'.$msg.'</div>';
return '<div class="alert alert-info alert-dismissable">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> <br/>
			  '.$msg.'
            </div>';
}

function warning_message($msg){

return '<div class="alert alert-warning alert-dismissable">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button> <br/>
			  '.$msg.'
            </div>';
}
// Fonction pour vérifier si la période de saisie est ouverte
function periode_saisie_ouverte() {
    return Exercice::verifier_periode_saisie();
}

// Fonction pour les sociétés
function get_exercice_actif_info() {
    $verification = periode_saisie_ouverte();
    return array(
        'autorise' => $verification['autorise'],
        'message' => $verification['message'],
        'exercice' => $verification['exercice']
    );
}
function envoyer_alerte_exercice($societe_id, $type_alerte) {
    $societe = Societe::trouve_par_id($societe_id);
    $exercice_actif = Exercice::get_exercice_actif();
    
    if (!$societe || !$exercice_actif) return false;
    
    // Récupérer l'email de l'admin de la société
    $admin_societe = Accounts::trouve_admin_par_societe($societe_id);
    
    if ($admin_societe && $admin_societe->email) {
        $sujet = "";
        $message = "";
        
        switch ($type_alerte) {
            case 'rappel_7jours':
                $sujet = "تذكير: 7 أيام متبقية لتقديم النموذج";
                $message = "عزيزي/عزيزة " . $admin_societe->prenom . " " . $admin_societe->nom . "،\n\n";
                $message .= "تذكير بأنه متبقي 7 أيام فقط لتقديم النموذج الخاص بمؤسستكم " . $societe->raison_ar . ".\n";
                $message .= "الرجاء تقديم النموذج قبل: " . date('d/m/Y', strtotime($exercice_actif->date_fin)) . "\n\n";
                $message .= "رابط التقديم: " . SITE_URL . "utilisateur/formulaire.php?exercice=" . $exercice_actif->id . "\n\n";
                $message .= "شكراً لتعاونكم.";
                break;
                
            case 'prolongation':
                $sujet = "تم تمديد فترة التقديم";
                $message = "عزيزي/عزيزة " . $admin_societe->prenom . " " . $admin_societe->nom . "،\n\n";
                $message .= "تم تمديد فترة تقديم النماذج حتى: " . date('d/m/Y', strtotime($exercice_actif->periode_extension)) . "\n";
                $message .= "الرجاء تقديم النموذج في أقرب وقت ممكن.\n\n";
                $message .= "رابط التقديم: " . SITE_URL . "utilisateur/formulaire.php?exercice=" . $exercice_actif->id . "\n\n";
                $message .= "شكراً لتعاونكم.";
                break;
        }
        
        // Envoyer l'email
        return mail($admin_societe->email, $sujet, $message);
    }
    
    return false;
}
function chifre_en_lettre($montant, $devise1='', $devise2='')
{
    if(empty($devise1)) $dev1='Dinars';
    else $dev1=$devise1;
    if(empty($devise2)) $dev2='Centimes';
    else $dev2=$devise2;
    $valeur_entiere=intval($montant);
    $valeur_decimal=intval(round($montant-intval($montant), 2)*100);
    $dix_c=intval($valeur_decimal%100/10);
    $cent_c=intval($valeur_decimal%1000/100);
    $unite[1]=$valeur_entiere%10;
    $dix[1]=intval($valeur_entiere%100/10);
    $cent[1]=intval($valeur_entiere%1000/100);
    $unite[2]=intval($valeur_entiere%10000/1000);
    $dix[2]=intval($valeur_entiere%100000/10000);
    $cent[2]=intval($valeur_entiere%1000000/100000);
    $unite[3]=intval($valeur_entiere%10000000/1000000);
    $dix[3]=intval($valeur_entiere%100000000/10000000);
    $cent[3]=intval($valeur_entiere%1000000000/100000000);
    $chif=array('', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix sept', 'dix huit', 'dix neuf');
        $secon_c='';
        $trio_c='';
    for($i=1; $i<=3; $i++){
        $prim[$i]='';
        $secon[$i]='';
        $trio[$i]='';
        if($dix[$i]==0){
            $secon[$i]='';
            $prim[$i]=$chif[$unite[$i]];
        }
        else if($dix[$i]==1){
            $secon[$i]='';
            $prim[$i]=$chif[($unite[$i]+10)];
        }
        else if($dix[$i]==2){
            if($unite[$i]==1){
            $secon[$i]='vingt et';
            $prim[$i]=$chif[$unite[$i]];
            }
            else {
            $secon[$i]='vingt';
            $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==3){
            if($unite[$i]==1){
            $secon[$i]='trente et';
            $prim[$i]=$chif[$unite[$i]];
            }
            else {
            $secon[$i]='trente';
            $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==4){
            if($unite[$i]==1){
            $secon[$i]='quarante et';
            $prim[$i]=$chif[$unite[$i]];
            }
            else {
            $secon[$i]='quarante';
            $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==5){
            if($unite[$i]==1){
            $secon[$i]='cinquante et';
            $prim[$i]=$chif[$unite[$i]];
            }
            else {
            $secon[$i]='cinquante';
            $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==6){
            if($unite[$i]==1){
            $secon[$i]='soixante et';
            $prim[$i]=$chif[$unite[$i]];
            }
            else {
            $secon[$i]='soixante';
            $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==7){
            if($unite[$i]==1){
            $secon[$i]='soixante et';
            $prim[$i]=$chif[$unite[$i]+10];
            }
            else {
            $secon[$i]='soixante';
            $prim[$i]=$chif[$unite[$i]+10];
            }
        }
        else if($dix[$i]==8){
            if($unite[$i]==1){
            $secon[$i]='quatre-vingts et';
            $prim[$i]=$chif[$unite[$i]];
            }
            else {
            $secon[$i]='quatre-vingt';
            $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==9){
            if($unite[$i]==1){
            $secon[$i]='quatre-vingts et';
            $prim[$i]=$chif[$unite[$i]+10];
            }
            else {
            $secon[$i]='quatre-vingts';
            $prim[$i]=$chif[$unite[$i]+10];
            }
        }
        if($cent[$i]==1) $trio[$i]='cent';
        else if($cent[$i]!=0 || $cent[$i]!='') $trio[$i]=$chif[$cent[$i]] .' cents';
    }
     
     
$chif2=array('', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingts', 'quatre-vingts dix');
    $secon_c=$chif2[$dix_c];
    if($cent_c==1) $trio_c='cent';
    else if($cent_c!=0 || $cent_c!='') $trio_c=$chif[$cent_c] .' cents';
     
    if(($cent[3]==0 || $cent[3]=='') && ($dix[3]==0 || $dix[3]=='') && ($unite[3]==1))
        echo $trio[3]. '  ' .$secon[3]. ' ' . $prim[3]. ' million ';
    else if(($cent[3]!=0 && $cent[3]!='') || ($dix[3]!=0 && $dix[3]!='') || ($unite[3]!=0 && $unite[3]!=''))
        echo $trio[3]. ' ' .$secon[3]. ' ' . $prim[3]. ' millions ';
    else
        echo $trio[3]. ' ' .$secon[3]. ' ' . $prim[3];
     
    if(($cent[2]==0 || $cent[2]=='') && ($dix[2]==0 || $dix[2]=='') && ($unite[2]==1))
        echo ' mille ';
    else if(($cent[2]!=0 && $cent[2]!='') || ($dix[2]!=0 && $dix[2]!='') || ($unite[2]!=0 && $unite[2]!=''))
        echo $trio[2]. ' ' .$secon[2]. ' ' . $prim[2]. ' milles ';
    else
        echo $trio[2]. ' ' .$secon[2]. ' ' . $prim[2];
     
    echo $trio[1]. ' ' .$secon[1]. ' ' . $prim[1];
     
    echo ' '. $dev1 .' ' ;
     
    if(($cent_c=='0' || $cent_c=='') && ($dix_c=='0' || $dix_c==''))
        echo ' et z&eacute;ro '. $dev2;
    else
        echo $trio_c. ' ' .$secon_c. ' ' . $dev2;
}
?>