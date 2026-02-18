<?php

require_once('bd.php');
require_once('fonctions.php');
// Tableau1.php
class Tableau1 {
    protected static $nom_table="tableau_1";
	protected static $champs = array('id', 'id_societe', 'statut', 'annee', 'date_valide', 'id_user', 'total','total_reel','total_intrim','total_femmes', 'date_creation', 'commentaire_admin','id_admin_validateur');
	public $id;	
	public $id_societe;
	public $statut;
	public $annee;
	public $date_valide;
	public $id_user;
	public $total;
	public $total_reel;
	public $total_intrim;
	public $total_femmes;
    public $date_creation;
	public $commentaire_admin;
	public $id_admin_validateur;
   
	public static function existe_pour_societe_annee($id_societe, $annee) {
		global $bd;
	
		$sql = "SELECT id FROM tableau_1 
				WHERE id_societe = $id_societe 
				AND annee = $annee 
				LIMIT 1";
	
		$result = $bd->requete($sql, [$id_societe, $annee]);
	
		if ($result && $row = $bd->fetch_array($result)) {
			return $row['id'];
		}
	
		return false;
	}
	    public static function trouver_en_attente() {
        $q = "SELECT * FROM " . self::$nom_table . " 
                WHERE statut = 'en_attente' 
                ORDER BY date_soumission DESC";
        return  self::trouve_par_sql($q);
    }
    
    // Récupérer les tableaux par statut pour toutes les sociétés
    public static function trouver_par_statut_global($statut) {
        $q = "SELECT * FROM " . self::$nom_table . " 
                WHERE statut = '{$statut}'
               ";
      return  self::trouve_par_sql($q);
    }
	

    public static function calculerTotalParType($id_tableau, $champ) {
        global $bd;
        $sql = "SELECT SUM(dt.$champ) as total 
                FROM detail_tab_1 dt 
                INNER JOIN grades g ON dt.id_grade = g.id 
                WHERE dt.id_tab_1 = $id_tableau ";
       $result_set = $bd->requete($sql);
    
    if ($result_set && $row = $bd->fetch_array($result_set)) {
        return (float)$row['total'];
    }
        return 0;
    }
    

    public static function trouve_par_societe_annee($id_societe, $annee) {
        global $bd;
        $sql = "SELECT * FROM tableau_1 WHERE id_societe = $id_societe AND annee = '{$annee}'";
       $result_array = self::trouve_par_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
    }
    
    public static function get_annees_par_societe($id_societe) {
        global $bd;
        $sql = "SELECT DISTINCT annee FROM tableau_1 WHERE id_societe = $id_societe ORDER BY annee DESC";
        $result_array = self::trouve_par_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
    }
    


        public static function creer($data) {
        global $bd;
        $sql = "INSERT INTO ".self::$nom_table." (id_societe, annee, statut, date_valide, id_user, total, `total_reel`, total_intrim, total_femmes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $bd->requete($sql, array(
            $data['id_societe'], $data['annee'], $data['statut'], $data['date_valide'], 
            $data['id_user'], $data['total'], $data['total_reel'], $data['total_intrim'], $data['total_femmes']
        ));
        return $bd->dernierId();
    }

    
    public static function trouve_par_societe($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id}";
    return  self::trouve_par_sql($q);
	}
	public static function derniers_par_societe($id_societe, $limit = 5) {
    $q = "SELECT * FROM " . self::$nom_table . " 
            WHERE id_societe = $id_societe
            ORDER BY date_creation DESC 
            LIMIT $limit";
   return  self::trouve_par_sql($q);
}

	public static function trouve_tableau_1_par_id($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE id_societe={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }

    public static function trouve_par_id($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE Id={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
    public static function trouve_tab_vide_par_admin($id,$id_societe) {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table ." where  id_societe = {$id_societe}   and id_user = {$id} ");
  }


/////////////////end update ////////////////////////////////
/////////////////////////////////////////////////////////

  // pour que ne tompa dans des erreurs foux qu'on selection tous "SELECT * FROM" 
  public static function trouve_par_sql($sql="") {
    global $bd;
    $result_set = $bd->requete($sql);
    $object_array = array();
    while ($row = $bd->fetch_array($result_set)) {
      $object_array[] = self::instantiate($row);
    }
	/* // on peu utiliser la fonction predefinit mysqli_fetch_object
	   // mais dans le cas où il y a de jointure dans la requete.... 
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
$sql .= " WHERE id =". $bd->escape_value($this->id) ;
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

}



?>