<?php

require_once('bd.php');
require_once('fonctions.php');

class Employees{

	protected static $nom_table="employees"; 
	protected static $champs = array('id', 'nom','prenom','date_naissance','id_grade', 'id_societe', 'nbr_annee', 'date_debut_emplois');
	public $id;
	public $nom;
	public $prenom;
	public $date_naissance;	
	public $id_grade;
    public $id_societe;
	public $nbr_annee;
	public $date_debut_emplois;
	
public static function trouve_retraite_par_societe($id_societe, $annee) {    

    $sql = "SELECT * FROM  employees
                WHERE id_societe = {$id_societe} 
                AND YEAR(date_naissance) <= $annee - 60 
                ORDER BY nom, prenom";

    return self::trouve_par_sql($sql);
}

public static function trouve_retraite_par_age_par_societe($id_societe, $annee) {    

    $sql = "SELECT 
    id,
    nom,
    prenom,
    date_naissance,
	id_grade,
     ($annee - YEAR(date_debut_emplois)) AS nbr_annee
FROM employees
WHERE id_societe = {$id_societe}
AND YEAR(date_naissance) <= $annee - 60
ORDER BY nom, prenom";

    return self::trouve_par_sql($sql);
}

 public static function trouve_responsable_societe($id_societe) {
        $q = "SELECT * FROM accounts WHERE id_societe = $id_societe AND type = 'utilisateur' LIMIT 1";
        return  self::trouve_par_sql($q);
    }
// Ajouter ces méthodes à la classe Accounts

public static function compter_par_societe($id_societe) {
    global $bd;
    
    $sql = "SELECT COUNT(*) FROM " . self::$nom_table . " WHERE id_societe = {$id_societe}";
    
    
		$result_array = $bd->requete($sql);
		return !empty($result_array) ? $bd->num_rows($result_array): false;
}

public static function compter_par_societe_actifs($id_societe) {
    global $bd;
    
    $sql = "SELECT COUNT(*) FROM " . self::$nom_table . " WHERE id_societe = {$id_societe} AND active = 1";
    
    $result_array = $bd->requete($sql);
		return !empty($result_array) ? $bd->num_rows($result_array): false;
}

public static function redirection_par_role($user) {
    switch ($user->type) {
        case 'administrateur':
            redirect_to("admin/index.php");
            break;
		case 'super_admin':
			redirect_to("super_admin/index.php");
			break;
        case 'utilisateur':
            redirect_to("utilisateur/index.php");
            break;
       
        default:
            redirect_to("login.php");
            break;
    }
}
// Ajoutez ces méthodes à votre classe Accounts existante


  public function nom_compler() {
    if(isset($this->nom) && isset($this->prenom)) {
      return $this->nom . " " . $this->prenom;
    } else {
      return "";
    }
  }

	


	
		public  function  existe(){
	 global $bd;
	 $sql  = "SELECT * FROM ".self::$nom_table." ";
    $sql .= "WHERE user = '".$this->user."' ";
	
    $sql .= "LIMIT 1";
    $result_array = self::trouve_par_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
	}


	public static function recherche($nom,$euser,$tel){
	global $bd ;
	
	$q =  "SELECT * FROM ".self::$nom_table."   WHERE nom_clie like '%{$nom}%' and user_clie like '{$euser}%' and tel_clie like '{$tel}%' ;" ;
	return  self::trouve_par_sql($q);
		
	}
  


	public static function count(){
	
	$users = self::not_admin();
	return count($users);
	}
	

	
	

	public static function trouve_par_societe($societe){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ='{$societe}'";
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
	
	public static function trouve_par_agence($id=0){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_agence ={$id}";
    return  self::trouve_par_sql($q);
	}
	
	// les fonction commun entre les classe
	public static function trouve_tous() {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table);
  }
 

  public static function trouve_par_id($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE id ={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }

 
  public static function trouve_not_active(){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE active ='0'";
    return  self::trouve_par_sql($q);
	}
  public static function is_active($login){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE active ='1'";
    $q .= "AND ( mobile= '{$login}' ";
    $q.= "OR user = '".$login."') ";
    return  self::trouve_par_sql($q);
	}
	  public static function is_block($login){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE active ='0'";
	$q .= "AND ( mobile= '{$login}' ";
    $q.= "OR user = '".$login."') ";
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