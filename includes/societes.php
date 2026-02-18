<?php

require_once('bd.php');
require_once('fonctions.php');

class Societe {
	
	protected static $nom_table="societe";
	protected static $champs = array('id_societe','raison_fr', 'raison_ar', 'adresse_ar', 'postal','tel1','tel2','fax','mob1','mob2','email','exercice_debut','exercice_fin','logo','etat','wilayas');
	public $id_societe;
	public $raison_ar;
	public $raison_fr;
	public $adresse_ar;
	public $postal;
	public $tel1;
	public $tel2;
	public $fax;
	public $mob1;
	public $mob2;
	public $email;
	public $exercice_debut;
	public $exercice_fin;
	public $logo;
	public $etat;
	public $wilayas;
	




	public static function count_util(){
		global $bd;
		$q =  "SELECT count(*) FROM ".self::$nom_table;
		$q .= " WHERE type !='administrateur' "; 
		
		$result_array = $bd->requete($q);
		return !empty($result_array) ? $bd->num_rows($result_array): false;
	}
	
	public static function valider($login="", $mot_passe="") {
    global $bd;

    $sql  = "SELECT * FROM ".self::$nom_table." ";
    $sql .= "WHERE login = '{$login}' ";
    $sql .= "AND mot_passe = '".SHA1($mot_passe)."' ";
    $sql .= "LIMIT 1";
    $result_array = self::trouve_par_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	
	// public function date_der(){
	// global $bd;
    //  $sql  = "UPDATE ".self::$nom_table." SET ";
    //  $sql .= "date_der  = '".mysql_datetime()."' ";
	//  $sql .= " WHERE id =".$this->id." ";
	//  $sql .= "LIMIT 1 ";
	
	//  $result_array = self::trouve_par_sql($sql);
	// 	return !empty($result_array) ? array_shift($result_array) : false;
	// }
	
	public  function  existe(){
	 global $bd;
	 $sql  = "SELECT * FROM ".self::$nom_table." ";
    $sql .= "WHERE raison_ar = '".$this->raison_ar."'   ";
	//$sql .= "OR email = '".$this->email."' ";
    $sql .= "LIMIT 1";
    $result_array = self::trouve_par_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	// public  function  existe_client_libre(){
	//  global $bd;
	//  $sql  = "SELECT * FROM ".self::$nom_table." ";
    // $sql .= "WHERE ref_pro = '".$this->ref_pro."' and num_fac = '0' and num_clie = 0  ";
	// //$sql .= "OR email = '".$this->email."' ";
    // $sql .= "LIMIT 1";
    // $result_array = self::trouve_par_sql($sql);
	// 	return !empty($result_array) ? array_shift($result_array) : false;
	// }
	// public  function  login_email_existe(){
	//  global $bd;
	//  $sql  = "SELECT * FROM ".self::$nom_table." ";
    // $sql .= "WHERE login = '".$this->login."' ";
	// $sql .= "AND email = '".$this->email."' ";
    // $sql .= "LIMIT 1";
    // $result_array = self::trouve_par_sql($sql);
	// 	return !empty($result_array) ? array_shift($result_array) : false;
	// }
	
	// public  function  login_existe(){
	//  global $bd;
	//  $sql  = "SELECT * FROM ".self::$nom_table." ";
    // $sql .= "WHERE login = '".$this->login."' ";
    // $sql .= "LIMIT 1";
    // $result_array = self::trouve_par_sql($sql);
	// 	return !empty($result_array) ? array_shift($result_array) : false;
	// }
     
   
	
	// public  function  mot_passe_existe(){
	//  global $bd;
	//  $sql  = "SELECT * FROM ".self::$nom_table." ";
    // $sql .= "WHERE mot_passe = '".$this->mot_passe."' ";
    // $sql .= "LIMIT 1";
    // $result_array = self::trouve_par_sql($sql);
	// 	return !empty($result_array) ? array_shift($result_array) : false;
	// }
	
	// public  function  email_existe(){
	//  global $bd;
	//  $sql  = "SELECT * FROM ".self::$nom_table." ";
    // $sql .= "WHERE email = '".$this->email."' ";
    // $sql .= "LIMIT 1";
    // $result_array = self::trouve_par_sql($sql);
	// 	return !empty($result_array) ? array_shift($result_array) : false;
	// }
	

	public static function count(){
	
	$users = self::not_admin();
	return count($users);
	}
	
	public static function not_sup_admin(){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE type !='super_administrateur'";
    return  self::trouve_par_sql($q);
	}
  public static function trouve_type_par_id_societe($id_societe) {
    global $bd;
    $sql = "SELECT type FROM " . self::$nom_table . " WHERE id_societe = {$id_societe} LIMIT 1";
    $result = $bd->requete($sql);
    
    if ($result && $row = $bd->fetch_array($result)) {
        return $row['type'];
    } else {
        return false;
    }
}

	
	
	
	public static function select_par_ordre1($order,$crois,$start,$display){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE type !='administrateur'";
	$q .= " ORDER BY {$order} {$crois} ";
	$q .= " LIMIT {$start}, {$display} "; 
	return  self::trouve_par_sql($q);
	}
	
	public static function not_admin(){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE type !='administrateur'";
    return  self::trouve_par_sql($q);
	}
	
	public static function trouve_par_type($type){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE type ='{$type}'";
    return  self::trouve_par_sql($q);
	}
	
	
	public static function select_par_ordre($order,$crois,$start,$display){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE type !='administrateur'";
	$q .= " AND type !='super_administrateur'";
	$q .= " ORDER BY {$order} {$crois} ";
	$q .= " LIMIT {$start}, {$display} "; 
	return  self::trouve_par_sql($q);
	}
	
	public static function select_par_ordre_type($order,$crois,$start,$display,$type){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE type ='{$type}'";
	$q .= " ORDER BY {$order} {$crois} ";
	$q .= " LIMIT {$start}, {$display} "; 
	return  self::trouve_par_sql($q);
	}
	
	public static function select_par_ordre_ens($order,$crois,$start,$display){
	$q =  "SELECT personne.* FROM personne,enseignant";
	$q .= " WHERE personne.id =enseignant.id_personne";
	$q .= " ORDER BY {$order} {$crois} ";
	$q .= " LIMIT {$start}, {$display} "; 
	return  self::trouve_par_sql($q);
	}
	
	
	// les fonction commun entre les classe
	public static function trouve_tous() {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table);
  }
  	public static function trouve_limit() {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table."  ORDER BY id_societe DESC LIMIT  5");
  }
  public static function trouve_fact_vide_client_libre() {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table ." where num_fac = 0 and num_clie = 0 ");
  }
  
  public static function trouve_fact_vide_non_client_libre() {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table ." where num_fac = 0 and num_clie != 0 ");
  }
  
  public static function trouve_par_id($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE id_societe ={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_par_random($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE random = {$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
public static function trouve_fact_num_clie($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE num_fac= {$id} and num_clie = 0 LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
    
  public static function trouve_fact_vide() {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table ." where num_fac = 0 ");
  }
    public static function trouve_par_last($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." where num_fac != '0'  ORDER BY `id_vent` DESC LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_client_par_facture($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." where num_fac = {$id} and num_clie != 0   ORDER BY `id` DESC LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
   public static function trouve_par_last_client_not_fact($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." where num_fac  != '0'  ORDER BY `id` and num_clie != 0 ORDER BY `id`  DESC LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
     public static function trouve_par_last_client($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." where num_fac  = '0' and num_clie != 0 ORDER BY `id_vent` DESC LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_par_classee($classe=0){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE classe ='{$classe}'";
	$q .= " AND type ='eleve'";
    return  self::trouve_par_sql($q);
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
	 return isset($this->id_societe)? $this->modifier() : $this->ajouter();
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
	     $this->id_societe = $bd->insert_id();
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
$sql .= " WHERE id_societe =". $bd->escape_value($this->id_societe) ;
$bd->requete($sql);
//return($bd->affected_rows() == 1) ? true : false ;
return true;
}
	    public function modifier_num(){
global $bd;
$attributes = $this->sanitized_attributes();
$attribute_pairs = array();
foreach($attributes as $key => $value){
 $attribute_pairs[] = "{$key}='{$value}'";
}
$sql = "update ".self::$nom_table." SET ";
$sql .= " WHERE id_societe =". $bd->escape_value($this->id_societe) ;
$bd->requete($sql);
return($bd->affected_rows() == 1) ? true : false ;
}
	
public function supprime(){
global $bd;
$sql = "DELETE FROM ".self::$nom_table;
$sql .= " WHERE id_societe =". $bd->escape_value($this->id_societe) ;
$sql .=" LIMIT 1";
$bd->requete($sql);
return($bd->affected_rows() == 1) ? true : false ;
	}

	}


?>