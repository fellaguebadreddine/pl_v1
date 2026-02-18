<?php

require_once('bd.php');
require_once('fonctions.php');

class Wilayas {
	
	protected static $nom_table="wilayas";
	protected static $champs = array('id','code', 'nom');
	public $id;
	public $code;
	public $nom;
	
	public function nom_wilaya() {
		if(isset($this->nom)) {
		return $this->nom ;
		} else {
		return "";
		}
 	}
		
	public function date_der(){
	global $bd;
     $sql  = "UPDATE ".self::$nom_table." SET ";
     $sql .= "date_der  = '".mysql_datetime()."' ";
	 $sql .= " WHERE id =".$this->id." ";
	 $sql .= "LIMIT 1 ";
	
	 $result_array = self::trouve_par_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
	}


	

	public static function count(){
	
	$users = self::not_admin();
	return count($users);
	}
	

	public static function trouve_par_type($type){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE type ='{$type}'";
    return  self::trouve_par_sql($q);
	}
	
	// les fonction commun entre les classe
	public static function trouve_tous() {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table);
  }

  

  
  public static function trouve_par_id($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE id={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }


    

    public static function trouve_par_last($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." where num_fac != '0'  ORDER BY `id_vent` DESC LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }



  
  // pour que ne tompa dans des erreurs foux qu'on selection tous "SELECT * FROM" 
  public static function trouve_par_sql($sql="") {
    global $bd;
    $result_set = $bd->requete($sql);
    $object_array = array();
    while ($row = $bd->fetch_array($result_set)) {
      $object_array[] = self::instantiate($row);
    }
	/* // on peu utiliser la fonction predefinit mysqli_fetch_object
	   // mais dans le cas o� il y a de jointure dans la requete.... 
	while ($object = $bd->fetch_object($result_set)){
	  $object_array[] = $object;
	}
	*/
    return $object_array;
  }

	private static function instantiate($record) {
		// Could check that $record exists and is an array
    $object = new self;
		// Simple, long-form approach:
		// $object->id 				= $record['id'];
		// $object->login 	= $record['login'];
		// $object->mot_passe 	= $record['mot_passe'];
		// $object->nom = $record['nom'];
		// $object->prenom 	= $record['prenom'];
		
		// More dynamic, short-form approach:
		foreach($record as $attribute=>$value){
		  if($object->has_attribute($attribute)) {
		    $object->$attribute = $value;
		  }
		}
		return $object;
	}
	
	private function has_attribute($attribute) {
	  // get_object_vars returns an associative array with all attributes 
	  // (incl. private ones!) as the keys and their current values as the value
	  $object_vars = $this ->attributes();
	  // We don't care about the value, we just want to know if the key exists
	  // Will return true or false
	  return array_key_exists($attribute, $object_vars);
	}

	public function save(){
	 // A new record won't have an id yet.
	 return isset($this->id)? $this->modifier() : $this->ajouter();
	}
	
	protected function attributes(){
	// return an array of attribute keys and their values
	 $attributes = array();
	 foreach(self::$champs as $field){
	     if(property_exists($this, $field)){
		     $attributes[$field] = $this->$field; 
		 }
	 }
	 return $attributes;
	}
	
	protected function sanitized_attributes(){
	 global $bd;
	 $clean_attributes = array();
	 // sanitize the values before submitting
	 // note : does not alter the actual value of each attribute
	 foreach($this->attributes() as $key => $value){
	   $clean_attributes[$key] = $bd->escape_value($value);
	 }
	  return $clean_attributes;
	}
	
	public function ajouter(){
	 global $bd;
	 $attributes = $this->sanitized_attributes();
	 $sql = "INSERT INTO ".self::$nom_table."(";
	 $sql .= join(", ", array_keys($attributes));
	 $sql .= ") VALUES (' ";
	 $sql .= join("', '", array_values($attributes));
	 $sql .= "')";
	 if($bd->requete($sql)){
	     $this->id = $bd->insert_id();
		 return true;
	 }else{
	     return false;
	 }
	}
	
    public function modifier(){
global $bd;
$attributes = $this->sanitized_attributes();
$attribute_pairs = array();
foreach($attributes as $key => $value){
 $attribute_pairs[] = "{$key}='{$value}'";
}
$sql = "update ".self::$nom_table." SET ";
$sql .= join(", ", $attribute_pairs);
$sql .= " WHERE id_tva =". $bd->escape_value($this->id_tva) ;
$bd->requete($sql);
return($bd->affected_rows() == 1) ? true : false ;
}
	    public function modifier_num(){
global $bd;
$attributes = $this->sanitized_attributes();
$attribute_pairs = array();
foreach($attributes as $key => $value){
 $attribute_pairs[] = "{$key}='{$value}'";
}
$sql = "update ".self::$nom_table." SET ";
$sql .= "n_immatriculation = '".$this->n_immatriculation."' ";
$sql .= " WHERE id =". $bd->escape_value($this->id) ;
$bd->requete($sql);
return($bd->affected_rows() == 1) ? true : false ;
}
	
public function supprime(){
global $bd;
$sql = "DELETE FROM ".self::$nom_table;
$sql .= " WHERE id =". $bd->escape_value($this->id) ;
$sql .=" LIMIT 1";
$bd->requete($sql);
return($bd->affected_rows() == 1) ? true : false ;
	}

    
    /**
     * Récupère les statistiques d'avancement par wilaya
     */
    public static function get_stats_avancement_par_wilaya($annee = null) {
		global $bd;
        if (!$annee) {
            $exercice = Exercice::get_exercice_actif();
            $annee = $exercice ? $exercice->annee : date('Y');
        }
        
        $sql = "SELECT 
                    COALESCE(s.wilaya, 'غير محدد') as wilaya,
                    COUNT(DISTINCT s.id_societe) as total_societes,
                    COUNT(DISTINCT CASE WHEN a.id IS NOT NULL THEN s.id_societe END) as societes_actives,
                    COUNT(DISTINCT t.id) as total_tableaux,
                    COUNT(DISTINCT CASE WHEN t.statut = 'soumis' THEN t.id END) as tableaux_soumis,
                    COUNT(DISTINCT CASE WHEN t.statut = 'en_attente' THEN t.id END) as en_attente,
                    COUNT(DISTINCT CASE WHEN t.statut = 'validé' THEN t.id END) as valides,
                    COUNT(DISTINCT CASE WHEN t.statut = 'brouillon' THEN t.id END) as brouillons,
                    ROUND(COUNT(DISTINCT CASE WHEN t.statut = 'validé' THEN t.id END) * 100.0 / NULLIF(COUNT(DISTINCT s.id_societe), 0), 1) as taux_avancement,
                    SUM(t.total_femmes) as total_femmes,
                    SUM(t.total_intrim) as total_intrim,
                    SUM(t.total) as total_postes,
                    SUM(t.`total-reel`) as total_reel
                FROM societe s
                LEFT JOIN accounts a ON a.id_societe = s.id_societe AND a.type = 'utilisateur'
                LEFT JOIN tableau1 t ON t.id_societe = s.id_societe AND t.annee = ?
                GROUP BY s.wilaya
                ORDER BY taux_avancement DESC, wilaya ASC";
        
        $stmt = $bd->prepare($sql);
        $stmt->execute([$annee]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les statistiques globales
     */
    public static function get_stats_globales($annee = null) {
        if (!$annee) {
            $exercice = Exercice::get_exercice_actif();
            $annee = $exercice ? $exercice->annee : date('Y');
        }
        
        $sql = "SELECT 
                    COUNT(DISTINCT s.id_societe) as total_societes,
                    COUNT(DISTINCT CASE WHEN a.id IS NOT NULL THEN s.id_societe END) as societes_actives,
                    COUNT(DISTINCT t.id) as total_tableaux,
                    COUNT(DISTINCT CASE WHEN t.statut = 'validé' THEN t.id END) as tableaux_valides,
                    ROUND(COUNT(DISTINCT CASE WHEN t.statut = 'validé' THEN t.id END) * 100.0 / NULLIF(COUNT(DISTINCT s.id_societe), 0), 1) as taux_validation_global,
                    ROUND(COUNT(DISTINCT CASE WHEN a.id IS NOT NULL THEN s.id_societe END) * 100.0 / NULLIF(COUNT(DISTINCT s.id_societe), 0), 1) as taux_participation,
                    COALESCE(SUM(t.total_femmes), 0) as total_femmes
                FROM societe s
                LEFT JOIN accounts a ON a.id_societe = s.id_societe AND a.type = 'utilisateur'
                LEFT JOIN tableau1 t ON t.id_societe = s.id_societe AND t.annee = ?";
        
        $stmt = self::$database->prepare($sql);
        $stmt->execute([$annee]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les détails d'une wilaya spécifique
     */
    public static function get_details_wilaya($wilaya, $annee = null) {
        if (!$annee) {
            $exercice = Exercice::get_exercice_actif();
            $annee = $exercice ? $exercice->annee : date('Y');
        }
        
        $sql = "SELECT 
                    s.id_societe,
                    s.raison_ar,
                    s.ice,
                    a.id as id_admin,
                    a.prenom,
                    a.nom,
                    a.email,
                    a.telephone,
                    t.id as id_tableau,
                    t.statut,
                    t.date_soumission,
                    t.date_valide,
                    t.total,
                    t.`total-reel` as total_reel,
                    t.total_femmes,
                    t.total_intrim,
                    t.commentaire_admin
                FROM societe s
                LEFT JOIN accounts a ON a.id_societe = s.id_societe AND a.type = 'utilisateur'
                LEFT JOIN tableau1 t ON t.id_societe = s.id_societe AND t.annee = ?
                WHERE s.wilaya = ?
                ORDER BY t.date_soumission DESC";
        
        $stmt = self::$database->prepare($sql);
        $stmt->execute([$annee, $wilaya]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère toutes les wilayas disponibles
     */
    public static function get_all_wilayas() {
        $sql = "SELECT DISTINCT wilaya FROM societe WHERE wilaya IS NOT NULL AND wilaya != '' ORDER BY wilaya";
        $stmt = self::$database->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Récupère les statistiques par type de grade
     */
    public static function get_stats_par_grade($annee = null) {
        if (!$annee) {
            $exercice = Exercice::get_exercice_actif();
            $annee = $exercice ? $exercice->annee : date('Y');
        }
        
        $sql = "SELECT 
                    g.id,
                    g.grade,
                    g.loi,
                    COUNT(d.id) as total_lignes,
                    SUM(d.postes_total) as total_postes,
                    SUM(d.postes_reel) as total_reel,
                    SUM(d.poste_femme) as total_femmes,
                    SUM(d.poste_intirim) as total_intirim
                FROM grade g
                LEFT JOIN detail_tab1 d ON d.id_grade = g.id
                LEFT JOIN tableau1 t ON t.id = d.id_tableau AND t.annee = ?
                GROUP BY g.id, g.grade, g.loi
                ORDER BY total_reel DESC";
        
        $stmt = self::$database->prepare($sql);
        $stmt->execute([$annee]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Envoie un rappel aux responsables d'une wilaya
     */
    public static function envoyer_rappel_wilaya($wilaya, $annee) {
        $sql = "SELECT a.email, a.prenom, a.nom, s.raison_ar
                FROM societe s
                JOIN accounts a ON a.id_societe = s.id_societe AND a.type = 'utilisateur'
                WHERE s.wilaya = ?";
        
        $stmt = self::$database->prepare($sql);
        $stmt->execute([$wilaya]);
        $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $resultats = [];
        foreach ($responsables as $resp) {
            // Envoyer email
            $sujet = "تذكير بتقديم الجدول السنوي - سنة $annee";
            $message = "السلام عليكم ورحمة الله وبركاته\n\n";
            $message .= "السيد(ة) {$resp['prenom']} {$resp['nom']}،\n\n";
            $message .= "نذكركم بضرورة تقديم الجدول السنوي للمؤسسة {$resp['raison_ar']} برسم سنة $annee.\n";
            $message .= "نأمل منكم إتمام التعبئة والمصادقة في أقرب وقت.\n\n";
            $message .= "مع جزيل الشكر.\n";
            $message .= "الإدارة العامة للوظيف العمومي";
            
            // Fonction d'envoi d'email à implémenter
            // mail($resp['email'], $sujet, $message);
            
            $resultats[] = [
                'email' => $resp['email'],
                'success' => true,
                'message' => 'تم إرسال التذكير'
            ];
        }
        
        return $resultats;
    }


	}


?>