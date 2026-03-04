<?php
require_once('bd.php');
require_once('fonctions.php');


	class Exercice {
    
		protected static $nom_table = "exercices";
		protected static $champs = array('id', 'annee', 'date_debut', 'date_fin', 'statut', 'commentaire');
		
		public $id;
		public $annee;
		public $date_debut;
		public $date_fin;
		public $statut;
		public $commentaire;

		
    public static function ouvrir_exercice($annee, $date_debut, $date_fin, $commentaire = '') {
        global $bd;
        
        // Vérifier si un exercice existe déjà pour cette année
        $existing = self::trouve_par_annee($annee);
        if ($existing) {
            return array('success' => false, 'message' => 'Un exercice existe déjà pour cette année');
        }
        
        // Vérifier si les dates sont valides
        if (strtotime($date_debut) >= strtotime($date_fin)) {
            return array('success' => false, 'message' => 'La date de début doit être antérieure à la date de fin');
        }
        
        // Vérifier si un exercice est déjà ouvert
        $exercice_actif = self::get_exercice_actif();
        if ($exercice_actif) {
            return array('success' => false, 'message' => 'Un exercice est déjà ouvert (' . $exercice_actif->annee . ')');
        }
        
        $sql = "INSERT INTO " . self::$nom_table . " 
                (annee, date_debut, date_fin, statut, commentaire) 
                VALUES ($annee, '{$date_debut}', '{$date_fin}', 'ouvert', '$commentaire')";
        
        $params = array(
            'annee' => $annee,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'commentaire' => $commentaire,
        );
        
        if ($bd->requete($sql, $params)) {
            return array('success' => true, 'message' => 'Exercice ouvert avec succès pour toutes les sociétés');
        }
        
        return array('success' => false, 'message' => 'Erreur lors de l\'ouverture de l\'exercice');
    }
	/*=====================================================
	fermer exercice
	===================================================*/
	  public static function fermer_exercice($id_exercice, $admin_id, $commentaire = '') {
        global $bd;
        
        // Récupérer l'exercice
        $exercice = self::trouve_par_id($id_exercice);
        if (!$exercice) {
            return array('success' => false, 'message' => 'Exercice non trouvé');
        }
        
        if ($exercice->statut == 'ferme') {
            return array('success' => false, 'message' => 'Cet exercice est déjà fermé');
        }
        
        // Vérifier si toutes les sociétés ont soumis leurs formulaires
       // $toutes_soumissions = self::verifier_soumissions_societes($id_exercice);
        
        if ($exercice->statut == 'ouvert' || $exercice->statut == 'prolongation' ) {
            // Fermer l'exercice
            $sql = "UPDATE " . self::$nom_table . " 
                    SET statut = 'ferme', 
                    commentaire = '{$commentaire}'
					WHERE id = {$id_exercice}";
            
            $params = array(
                'id' => $id_exercice,
                'commentaire' => $commentaire
            );
            
            if ($bd->requete($sql, $params)) {
                return array(
                    'success' => true, 
                    'message' => 'تم غلق السنة المالية'
                );
            }
        } else {
            // Retourner les détails des sociétés manquantes
            return array(
                'success' => false, 
                'message' => 'auccun exercice trouvé',
                'need_extension' => true
            );
        }
        
        return array('success' => false, 'message' => 'Erreur lors de la fermeture');
    }
	/* ===============================================================================
	trouve annee exrcice 
	=============================================================================*/
	
	public static function trouve_par_annee($annee) {
        global $bd;
        
        $sql = "SELECT * FROM " . self::$nom_table . " WHERE annee = $annee LIMIT 1";
       $result_array = self::trouve_par_sql($sql);

		return !empty($result_array) ? array_shift($result_array) : false;
    }

 /* ================================
       Récupérer l'exercice actif
    ================================= */
    public static function get_exercice_actif() {
        global $bd;

      

        $sql = "
            SELECT *
            FROM " . self::$nom_table . "
            WHERE statut IN ('ouvert', 'prolongation')
           
            LIMIT 1
        ";

        $result = $bd->requete($sql);
        $row = $bd->fetch_array($result);

        return $row ? self::instantiate($row) : false;
    }

	public static function trouve_active_exercice(){
		 $result_array = self::trouve_par_sql("SELECT * FROM ".self::$nom_table." ");
		return !empty($result_array) ? array_shift($result_array) : false;
	}

	public static function get_exercices_avec_progression() {
        global $bd;
        
        $sql = "SELECT 
                e.*,
                (SELECT COUNT(*) FROM societes WHERE actif = 1) as total_societes,
                (SELECT COUNT(DISTINCT f.id_societe) FROM formulaires f 
                 WHERE f.id_exercice = e.id AND f.statut = 'soumis') as societes_soumises
                FROM " . self::$nom_table . " e
                ORDER BY e.annee DESC";
        
        return $bd->requete($sql);
    }

    /**
 * Prolonge un exercice en modifiant sa date de fin
 * @param int $id_exercice ID de l'exercice à prolonger
 * @param string $nouvelle_date_fin Nouvelle date de fin (format Y-m-d)
 * @param int $id_user ID de l'utilisateur qui effectue la prolongation
 * @param string $commentaire Commentaire optionnel
 * @return array ['success' => bool, 'message' => string]
 */
public static function prolonger_exercice($id_exercice, $nouvelle_date_fin, $id_user, $commentaire = '') {
    // Validation de base
    if (empty($id_exercice) || empty($nouvelle_date_fin)) {
        return ['success' => false, 'message' => 'بيانات ناقصة'];
    }

    // Récupérer l'exercice
    $exercice = self::trouve_par_id($id_exercice);
    if (!$exercice) {
        return ['success' => false, 'message' => 'التمرين غير موجود'];
    }

    // Vérifier que l'exercice est ouvert ou déjà en prolongation
    if ($exercice->statut != 'ouvert' && $exercice->statut != 'prolongation') {
        return ['success' => false, 'message' => 'لا يمكن تمديد تمرين مغلق'];
    }

    // Vérifier que la nouvelle date est postérieure à la date de fin actuelle
    $date_fin_actuelle = strtotime($exercice->date_fin);
    $nouvelle_date = strtotime($nouvelle_date_fin);
    if ($nouvelle_date <= $date_fin_actuelle) {
        return ['success' => false, 'message' => 'التاريخ الجديد يجب أن يكون بعد التاريخ الحالي'];
    }

    // Mettre à jour les champs
    $exercice->date_fin = $nouvelle_date_fin;
    $exercice->statut = 'prolongation';
    
    // Si vous avez un champ pour le commentaire, vous pouvez l'ajouter ou le concaténer
    if (!empty($commentaire)) {
        $exercice->commentaire = (!empty($exercice->commentaire) ? $exercice->commentaire . "\n" : '') 
                                 . 'تمديد بواسطة المستخدم ' . $id_user . ' في ' . date('Y-m-d H:i:s') . ' : ' . $commentaire;
    } else {
        $exercice->commentaire = (!empty($exercice->commentaire) ? $exercice->commentaire . "\n" : '') 
                                 . 'تمديد بواسطة المستخدم ' . $id_user . ' في ' . date('Y-m-d H:i:s');
    }

    // Si vous avez un champ id_admin_validateur ou last_modified_by, mettez-le à jour
    // $exercice->last_modified_by = $id_user;

    // Sauvegarder
    if ($exercice->save()) {
        return ['success' => true, 'message' => 'تم تمديد السنة المالية بنجاح'];
    } else {
        return ['success' => false, 'message' => 'خطأ أثناء التمديد'];
    }
}
	 public static function verifier_periode_saisie() {
        $exercice_actif = self::get_exercice_actif();
        
        if (!$exercice_actif) {
            return array(
                'autorise' => false,
                'message' => 'Aucun exercice actif en cours. La période de saisie est fermée.',
                'exercice' => null
            );
        }
        
        return array(
            'autorise' => true,
            'message' => 'Période de saisie ouverte jusqu\'au ' . date('d/m/Y', strtotime($exercice_actif->date_fin)),
            'exercice' => $exercice_actif
        );
    }
	
	public static function trouve_par_societe_and_annee( $annee){

		$sql = "SELECT * FROM ".self::$nom_table." WHERE annee = {$annee};";

		$result_array = self::trouve_par_sql($sql);

		return !empty($result_array) ? array_shift($result_array) : false;

	}

	public static function trouve_par_societe($id_societe){

		$sql = "SELECT * FROM ".self::$nom_table." WHERE id_societe = {$id_societe} ORDER BY id DESC;";

		$result_array = self::trouve_par_sql($sql);

		return $result_array;

	}

	public static function trouver_exercice_courant($id_societe) {
        global $bd;
        
        $sql = "SELECT * FROM " . self::$nom_table . " 
                WHERE id_societe = $id_societe 
                AND statut = 'ouvert'
                AND CURDATE() BETWEEN date_debut AND date_fin
                LIMIT 1";
        
		$result_array = $bd->requete($sql);
		return !empty($result_array) ? $bd->num_rows($result_array): false;
    }
	public static function statistiques_societe($id_societe) {
        global $bd;
        
        $sql = "SELECT 
                COUNT(*) as total_exercices,
                SUM(CASE WHEN statut = 'ouvert' THEN 1 ELSE 0 END) as exercices_ouverts,
                SUM(CASE WHEN statut = 'ferme' THEN 1 ELSE 0 END) as exercices_fermes,
                SUM(CASE WHEN statut = 'cloture' THEN 1 ELSE 0 END) as exercices_clotures,
                MIN(date_debut) as premier_exercice,
                MAX(date_fin) as dernier_exercice
                FROM " . self::$nom_table . " 
                WHERE id_societe = $id_societe";
        
$result_array = $bd->requete($sql);
		return !empty($result_array) ? $bd->num_rows($result_array): false;
}

	public static function trouve_last_par_societe($id_societe){

		$sql = "SELECT * FROM ".self::$nom_table." WHERE id_societe = {$id_societe} ORDER BY id DESC LIMIT 1;";

		$result_array = self::trouve_par_sql($sql);

		return !empty($result_array) ? array_shift($result_array) : false;

	}

	public static function trouve_par_id($id){

		$sql = "SELECT * FROM ".self::$nom_table." WHERE id = {$id};";

		$result_array = self::trouve_par_sql($sql);

		return !empty($result_array) ? array_shift($result_array) : false;

	}

	public static function trouve_tous(){
		return self::trouve_par_sql("SELECT * FROM " . self::$nom_table);		
	}

	public static function trouve_exercice_actif_par_societe($id=0) {
		$result_array = self::trouve_par_sql("SELECT actif FROM ".self::$nom_table." WHERE id_societe ={$id} LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
	  }


	// pour que ne tompa dans des erreurs foux qu'on selection tous "SELECT * FROM" 

	public static function trouve_par_sql($sql = "")

	{

		global $bd;

		$result_set = $bd->requete($sql);

		$object_array = array();

		while ($row = $bd->fetch_array($result_set)) {

			$object_array[] = self::instantiate($row);

		}

		/* // on peu utiliser la fonction predefinit mysqli_fetch_object

	   // mais dans le cas o il y a de jointure dans la requete.... 

	while ($object = $bd->fetch_object($result_set)){

	  $object_array[] = $object;

	}

	*/

		return $object_array;

	}


/* ================================
       Hydratation objet sécurisée
    ================================= */
    private static function instantiate($record) {
        $object = new self;

        foreach ($record as $attribute => $value) {
            $attribute = strtolower($attribute); // 🔥 clé
            if (property_exists($object, $attribute)) {
                $object->$attribute = $value;
            }
        }

        return $object;
    }


/* ================================
       Vérifier si l'attribut existe
    ================================= */
    private function has_attribute($attribute) {
        $attribute = strtolower($attribute);
        $object_vars = $this->attributes();

        return array_key_exists($attribute, $object_vars);
    }

    /* ================================
       Liste des attributs autorisés
    ================================= */
    protected function attributes() {
        $attributes = array();

        foreach (self::$champs as $field) {
            if (property_exists($this, $field)) {
                $attributes[$field] = $this->$field;
            }
        }

        return $attributes;
    }

	protected function sanitized_attributes()

	{

		global $bd;

		$clean_attributes = array();

		// sanitize the values before submitting

		// note : does not alter the actual value of each attribute

		foreach ($this->attributes() as $key => $value) {

			$clean_attributes[$key] = $bd->escape_value($value);

		}

		return $clean_attributes;

	}



	public function ajouter()

	{

		global $bd;

		$attributes = $this->sanitized_attributes();

		$sql = "INSERT INTO " . self::$nom_table . "(";

		$sql .= join(", ", array_keys($attributes));

		$sql .= ") VALUES (' ";

		$sql .= join("', '", array_values($attributes));

		$sql .= "')";

		if ($bd->requete($sql)) {

			$this->id = $bd->insert_id();

			return true;

		} else {

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

		//return($bd->affected_rows() == 1) ? true : false ;

		return true;

		}

		



	public function save(){

		// A new record won't have an id yet.

		return isset($this->id)? $this->modifier() : $this->ajouter();

	   }



	public function supprime()

	{

		global $bd;

		$sql = "DELETE FROM " . self::$nom_table;

		$sql .= " WHERE id =" . $bd->escape_value($this->id);

		$sql .= " LIMIT 1";

		$bd->requete($sql);

		return ($bd->affected_rows() == 1) ? true : false;

	}

}

