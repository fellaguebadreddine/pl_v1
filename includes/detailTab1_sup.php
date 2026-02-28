<?php

require_once('bd.php');
require_once('fonctions.php');
// Tableau1.php
class DetailTab1_sup {
    protected static $nom_table="detail_tab_1_sup";
	protected static $champs = array('id', 'id_societe', 'id_tab_1', 'annee', 'id_user', 'code','poste_sup','postes_total_sup','postes_reel_sup', 'poste_intirim_sup', 'poste_femme_sup', 'difference_sup', 'observations_sup', 'date_tabl', 'total_hf_postes_total_sup', 'total_hf_postes_reel_sup','total_hf_poste_intirim', 'total_hf_poste_femme_sup', 'total_hf_difference_sup');
	public $id;	
	public $id_societe;
	public $id_tab_1;
	public $annee;
	public $id_user;
	public $code;
	public $poste_sup;
	public $postes_total_sup;
	public $postes_reel_sup;
    public $poste_intirim_sup;
    public $poste_femme_sup;
    public $difference_sup;
    public $observations_sup;
    public $date_tabl_sup;
	public $total_hf_postes_total_sup;
	public $total_hf_postes_reel_sup;
	public $total_hf_poste_intirim_sup;
	public $total_hf_poste_femme_sup;
	public $total_hf_difference_sup;
   
public static function trouve_par_tableau($id_tab_1)
{
    global $bd;

    $sql = "SELECT *
            FROM " . self::$nom_table . "
            WHERE id_tab_1 = $id_tab_1
            ORDER BY id";

    $result = $bd->requete($sql, [$id_tab_1]);

    $details = [];

    if ($result) {
        while ($row = $result->fetch_object()) {
            $details[] = $row;
        }
    }

    return $details;
}

    
    public static function trouve_par_grade_tableau($id_grade, $id_tab_1) {
        global $bd;
        $sql = "SELECT * FROM ".self::$nom_table." WHERE id_grade = id_grade AND id_tab_1 = $id_tab_1";
        $result = $bd->requete($sql, array($id_grade, $id_tab_1));
        if ($row = $bd->objet($result)) {
            return $row;
        }
        return false;
    }
    
    public static function creer($data) {
        global $bd;
        $sql = "INSERT INTO ".self::$nom_table." (id_tab_1, annee, id_user, code, id_societe, id_grade, 
                postes_total, postes_reel, poste_intirim, poste_femme, difference, observations, date_tabl) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $bd->requete($sql, array(
            $data['id_tab_1'], $data['annee'], $data['id_user'], $data['code'], $data['id_societe'], 
            $data['id_grade'], $data['postes_total'], $data['postes_reel'], $data['poste_intirim'], 
            $data['poste_femme'], $data['difference'], $data['observations'], $data['date_tabl']
        ));
        return $result !== false;
    }
    
    public static function mettre_a_jour($id, $data) {
        global $bd;
        $sql = "UPDATE ".self::$nom_table." SET 
                id_grade = ?, postes_total = ?, postes_reel = ?, 
                poste_intirim = ?, poste_femme = ?, difference = ?, 
                observations = ? 
                WHERE id = ?";
        $result = $bd->requete($sql, array(
            $data['id_grade'], $data['postes_total'], $data['postes_reel'], 
            $data['poste_intirim'], $data['poste_femme'], $data['difference'], 
            $data['observations'], $id
        ));
        return $result !== false;
    }
    
	public function supprime(){
		global $bd;
		$sql = "DELETE FROM ".self::$nom_table;
		$sql .= " WHERE id =". $bd->escape_value($this->id) ;
		$sql .=" LIMIT 1";
		$bd->requete($sql);
		return($bd->affected_rows() == 1) ? true : false ;
			}



   
    public static function trouve_par_societe($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id}";
    return  self::trouve_par_sql($q);
	}


    public static function trouve_par_id($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE Id={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_tab_vide_par_admin($id,$id_societe) {
		return self::trouve_par_sql("SELECT * FROM ".self::$nom_table ." where id_tab_1 = 0 and id_societe = {$id_societe}   and id_user = {$id} ");
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


}



?>