<?php

require_once('bd.php');
require_once('fonctions.php');

class Situation {
	
	protected static $nom_table="situation";
	protected static $champs = array('id','id_client','id_societe', 'date_fac','ttc','ht','tva','tol_tva','retenu','tol_retenu','somme_ttc', 'id_commune','id_wilaya', 'id_banque','date_encs','facture_scan','id_g50','id_g50A','random','date_valide','id_user');
	public $id;
	public $id_client;
	public $id_societe;
	public $date_fac;
	public $ttc;
	public $ht;
	public $tva;
	public $tol_tva;
	public $retenu;
	public $tol_retenu;
	public $somme_ttc;
	public $id_commune;
	public $id_wilaya;
	public $id_banque;
	public $date_encs;
	public $facture_scan;
	public $id_g50;
	public $id_g50A;
	public $random;
	public $date_valide;
	public $id_user;
	
	public static function trouve_facture_par_societe_avant_g50($id,$d1,$d2){
		$q =  "SELECT * FROM ".self::$nom_table;
		$q .= " WHERE id_societe = {$id}";	
		$q .= " AND  id_g50=0";
		$q .= " AND date_fac BETWEEN  '{$d1}' and '{$d2}'";
		return  self::trouve_par_sql($q);
		}
		public static function trouve_vente_par_facture_tva_9($id){
			$q =  "SELECT * FROM ".self::$nom_table;
			$q .= " WHERE id ={$id}";
			$q .= " AND tva = '0.09'";
			return  self::trouve_par_sql($q);
			}
			public static function trouve_vente_par_facture_tva_19($id){
			$q =  "SELECT * FROM ".self::$nom_table;
			$q .= " WHERE id ={$id}";
			$q .= " AND tva = '0.19'";
			return  self::trouve_par_sql($q);
			}
			public static function trouve_vente_par_facture_tva_0($id){
			$q =  "SELECT * FROM ".self::$nom_table;
			$q .= " WHERE id ={$id}";
			$q .= " AND tva = '0.00'";
			return  self::trouve_par_sql($q);
			}
	public static function recherche($d1,$d2){
	global $bd ;
	
	$q =  "SELECT * FROM ".self::$nom_table."   WHERE date_valid BETWEEN  '{$d1}' and '{$d2}'  ;" ;
	return  self::trouve_par_sql($q);		

	}
	public static function trouve_etat_104_par_societe($id,$date_debut,$date_fin){
	$q =  "SELECT id_facture , id_client, SUM (somme_ht) as somme_ht , SUM (somme_tva) as somme_tva FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id}";
	$q .= " AND timbre =0";
	$q .= " AND date_fac BETWEEN  '{$date_debut}' and '{$date_fin}'";
	$q .= " GROUP BY id_client";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_etat_104_jointure($id,$date_debut,$date_fin){
	$q =  "SELECT facture_vente.id_client, SUM (somme_ht) as somme_ht , SUM (somme_tva) as somme_tva from facture_vente,reglement_client,mode_paiement_societe Where facture_vente.id_facture = reglement_client.id_facture and mode_paiement_societe.id=reglement_client.mode_paiment and mode_paiement_societe.mode_paiement!=2 and facture_vente.id_societe={$id} AND reglement_client.type_fact =0 AND facture_vente.date_fac BETWEEN  '{$date_debut}' and '{$date_fin}' GROUP BY facture_vente.id_client";
	
	
    return  self::trouve_par_sql($q);
	}
	public static function trouve_situation_par_id($id){
		$q =  "SELECT * FROM ".self::$nom_table;
		$q .= " WHERE id ={$id}";
		return  self::trouve_par_sql($q);
		}
	public static function trouve_facture_par_id_client($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_client ={$id}";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_par_id_client_etat_facture_non_valide($id){
		$q =  "SELECT facture_vente.*, mode_paiement_societe.mode_paiement FROM reglement_client INNER JOIN mode_paiement_societe ON reglement_client.mode_paiment = mode_paiement_societe.id INNER JOIN facture_vente ON facture_vente.id_facture = reglement_client.id_facture";
		$q .= " WHERE reglement_client.id_client ={$id}";
		$q .= " AND mode_paiement_societe.mode_paiement=3";
		$q .= " AND reglement_client.type_fact =0";
		return  self::trouve_par_sql($q);
		}
	public static function trouve_facture_par_societe($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id}";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_par_societe_and_id_g50($id_societe,$id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id_societe}";
	$q .= " AND id_g50 ={$id}";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_par_societe_and_Exercice($id,$date_debut,$date_fin){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id}";
		$q .= " AND date_fac BETWEEN  '{$date_debut}' and '{$date_fin}'";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_par_societe_and_Exercice_by_id($id,$date_debut,$date_fin){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id}";
	$q .= " AND date_fac BETWEEN  '{$date_debut}' and '{$date_fin}' ORDER BY id_facture ASC ";
    return  self::trouve_par_sql($q);
	}		
	public static function trouve_facture_par_societe_and_Exercice_and_id_client($id,$id_client,$date_debut,$date_fin){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_societe ={$id}";
	$q .= " AND id_client ={$id_client}";
	$q .= " AND date_fac BETWEEN  '{$date_debut}' and '{$date_fin}'";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_par_societe_etat_g50($id,$d1,$d2){
		$q =  " SELECT facture_vente.*, mode_paiement_societe.mode_paiement FROM reglement_client INNER JOIN mode_paiement_societe ON reglement_client.mode_paiment = mode_paiement_societe.id INNER JOIN facture_vente ON facture_vente.id_facture = reglement_client.id_facture";
		$q .= " WHERE reglement_client.id_societe ={$id}";
		$q .= " AND  facture_vente.id_g50 =0 ";
		$q .= " AND reglement_client.type_fact =0 ";
		$q .= " AND mode_paiement_societe.mode_paiement !=3";
		$q .= " AND date_fac BETWEEN  '{$d1}' and '{$d2}'";
		return  self::trouve_par_sql($q);
		}
	public static function trouve_facture_par_societe_avant($id,$annexe,$d1,$d2){
		$q =  "SELECT * FROM ".self::$nom_table;
		$q .= " WHERE id_societe = {$id}";
		$q .= " AND  id_commune={$annexe}";		
		$q .= " AND  id_g50A=0";
		$q .= " AND date_encs BETWEEN  '{$d1}' and '{$d2}'";
		return  self::trouve_par_sql($q);
		}
	public static function trouve_facture_par_societe_par_month($id,$annexe,$d1,$d2){
		$q =  "SELECT * FROM ".self::$nom_table;
		$q .= " WHERE id_societe = {$id}";
		$q .= " AND  id_commune={$annexe}";			
		$q .= " AND  id_g50A=0";
		$q .= " AND date_encs BETWEEN  '{$d1}' and '{$d2}'";
		return  self::trouve_par_sql($q);
		}
	public static function trouve_facture_vente_autre_espece($id){
			$q =  "SELECT * FROM ".self::$nom_table;
			$q .= " WHERE id_facture = {$id}";
			$q .= " AND  timbre=0";
			
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_vente_espece($id){
	$q =  "SELECT * FROM ".self::$nom_table;
			$q .= " WHERE id_facture = {$id}";
			$q .= " AND timbre !=0";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_vente_par_facture_timbre($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_facture ={$id}";
	$q .= " AND id_g50 = 0 ";
	$q .= " AND timbre !=0";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_par_societe_NoEspece_month($id,$d1,$d2){
		$q =  " SELECT facture_vente.*, mode_paiement_societe.mode_paiement FROM reglement_client INNER JOIN mode_paiement_societe ON reglement_client.mode_paiment = mode_paiement_societe.id INNER JOIN facture_vente ON facture_vente.id_facture = reglement_client.id_facture";
			$q .= " WHERE reglement_client.id_societe ={$id}";
			$q .= " AND  facture_vente.id_g50 =0 ";
			$q .= " AND reglement_client.type_fact =0 ";
			$q .= " AND mode_paiement_societe.mode_paiement =1";
			$q .= " AND reglement_client.date BETWEEN  '{$d1}' and '{$d2}'";
		return  self::trouve_par_sql($q);
		}
	public static function trouve_facture_par_societe_especes($id,$d1,$d2){
			$q =  "SELECT * FROM ".self::$nom_table;
			$q .= " WHERE id_societe ={$id}";
			$q .= " AND id_g50 = 0 ";
			$q .= " AND timbre > 0";
			$q .= " AND date_fac BETWEEN  '{$d1}' and '{$d2}'";
			return  self::trouve_par_sql($q);
		}
	public static function trouve_facture_par_societe_aTerme($id,$d1,$d2){
			$q =  " SELECT facture_vente.*, mode_paiement_societe.mode_paiement FROM reglement_client INNER JOIN mode_paiement_societe ON reglement_client.mode_paiment = mode_paiement_societe.id INNER JOIN facture_vente ON facture_vente.id_facture = reglement_client.id_facture";
			$q .= " WHERE reglement_client.id_societe ={$id}";
			$q .= " AND  facture_vente.id_g50 =0 ";
			$q .= " AND reglement_client.type_fact =0 ";
			$q .= " AND mode_paiement_societe.mode_paiement=3";
			$q .= " AND reglement_client.date BETWEEN  '{$d1}' and '{$d2}'";
			return  self::trouve_par_sql($q);
		}

	public static function trouve_facture_non_valide_par_societe($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE Etat =0";
	$q .= " and id_societe ={$id}";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_valide_par_societe($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE Etat =1";
	$q .= " and id_societe ={$id}";
    return  self::trouve_par_sql($q);
	}
	public static function trouve_facture_valide_par_societe_client($id){
		$q =  "SELECT * FROM ".self::$nom_table;
		$q .= " WHERE Etat =1";
		$q .= " and id_societe ={$id}";
		$q .= " Group by id_client";
		return  self::trouve_par_sql($q);
		}
	
  public function nom_compler() {
    if(isset($this->nom) && isset($this->prenom)) {
      return $this->nom . " " . $this->prenom;
    } else {
      return "";
    }
  }
	public static function count_util(){
		global $bd;
		$q =  "SELECT count(*) FROM ".self::$nom_table;
		$q .= " WHERE type !='administrateur' "; 
		
		$result_array = $bd->requete($q);
		return !empty($result_array) ? $bd->num_rows($result_array): false;
	}
	
	
	
	public  function  existe(){
	 global $bd;
	 $sql  = "SELECT * FROM ".self::$nom_table." ";
    $sql .= "WHERE id = '".$this->id."' ";
	//$sql .= "OR email = '".$this->email."' ";
    $sql .= "LIMIT 1";
    $result_array = self::trouve_par_sql($sql);
		return !empty($result_array) ? array_shift($result_array) : false;
	}
	public static function trouve_somme_ht_par_facture($id){
	$q =  "SELECT * FROM ".self::$nom_table;
	$q .= " WHERE id_facture ={$id}";
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
  public static function trouve_par_id_09($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE id ={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_par_id_00($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE id ={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_par_id_client($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE id_client ={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_somme_ht_id($id) {
    $result_array = self::trouve_par_sql("SELECT somme_ht  FROM ".self::$nom_table." WHERE id ={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_Remise_id($id) {
    $result_array = self::trouve_par_sql("SELECT Remise  FROM ".self::$nom_table." WHERE id ={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }
  public static function trouve_par_random($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." WHERE random = {$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
  }  
  public static function somme_ht_non_espece($id) {
    $result_array = self::trouve_par_sql("SELECT SUM(somme_ht) as som FROM ".self::$nom_table." where id IN ('{$id}') AND mode_paiment != 'Espèces'");
		return !empty($result_array) ? array_shift($result_array) : false;
  } 
  public static function somme_remise_par_id($id) {
    $result_array = self::trouve_par_sql("SELECT SUM(Remise) AS remise_total FROM ".self::$nom_table." where id IN ({$id}) ");
	return !empty($result_array) ? array_shift($result_array) : false;
  } 

   public static function trouve_last_par_societe($id=0) {
    $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." where id_societe ={$id} ORDER BY `id` DESC LIMIT 1");
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