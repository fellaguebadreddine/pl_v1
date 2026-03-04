<?php

require_once('bd.php');
require_once('fonctions.php');

class SuperAdmin {

    /**
     * Retourne les statistiques globales pour une année donnée
     * @param int $annee
     * @return array
     */
    public static function get_stats_globales($annee) {
        global $bd;

        // Liste des tables à prendre en compte (12 tables)
        $tables = [
            'tableau_1', 'tableau_1_1', 'tableau_2', 'tableau_3', 
            'tableau_4', 'tableau_4_1', 'tableau_5'
        ]; // adaptez selon vos noms réels

        // Total sociétés
        $sql = "SELECT COUNT(*) FROM societe";
        $result = $bd->requete($sql);
        $row = $bd->fetch_array($result);
        $total_societes = $row[0];

        // Sociétés actives (au moins un utilisateur de type 'utilisateur')
        $sql = "SELECT COUNT(DISTINCT id_societe) FROM accounts WHERE type = 'utilisateur'";
        $result = $bd->requete($sql);
        $row = $bd->fetch_array($result);
        $societes_actives = $row[0];

        // Total tableaux tous types confondus pour l'année
        $total_tableaux = 0;
        $tableaux_valides = 0;
        $total_femmes = 0;

        foreach ($tables as $table) {
            // Vérifier si la table existe (optionnel, on peut ignorer si erreur)
            // On fait les requêtes avec précaution
            $sql = "SELECT COUNT(*) FROM `$table` WHERE annee = " . intval($annee);
            $result = $bd->requete($sql);
            if ($result) {
                $row = $bd->fetch_array($result);
                $total_tableaux += $row[0];
            }

            $sql = "SELECT COUNT(*) FROM `$table` WHERE annee = " . intval($annee) . " AND statut = 'validé'";
            $result = $bd->requete($sql);
            if ($result) {
                $row = $bd->fetch_array($result);
                $tableaux_valides += $row[0];
            }

            
        }

        // Taux de validation global
        $taux_validation_global = $total_tableaux > 0 ? round(($tableaux_valides / $total_tableaux) * 100, 1) : 0;

        // Sociétés ayant au moins un tableau soumis (tout statut sauf peut-être brouillon ? On prend tout sauf brouillon ou on prend tous les existants)
        // Ici on prend toutes les sociétés ayant au moins un enregistrement dans l'une des tables pour l'année
        $unions = [];
        foreach ($tables as $table) {
            $unions[] = "SELECT id_societe FROM `$table` WHERE annee = " . intval($annee);
        }
        $sql_union = implode(" UNION ", $unions);
        $sql = "SELECT COUNT(DISTINCT id_societe) FROM ($sql_union) AS tmp";
        $result = $bd->requete($sql);
        $row = $bd->fetch_array($result);
        $societes_avec_tableau = $row[0];

        $taux_participation = $total_societes > 0 ? round(($societes_avec_tableau / $total_societes) * 100, 1) : 0;

        return [
            'total_societes' => $total_societes,
            'societes_actives' => $societes_actives,
            'total_tableaux' => $total_tableaux,
            'tableaux_valides' => $tableaux_valides,
            'taux_validation_global' => $taux_validation_global,
            'taux_participation' => $taux_participation,
            'total_femmes' => $total_femmes
        ];
    }

    /**
     * Retourne les statistiques par wilaya pour une année donnée
     * @param int $annee
     * @return array
     */
    public static function get_stats_avancement_par_wilaya($annee) {
        global $bd;

        // Liste des tables
        $tables = [
            'tableau_1', 'tableau_1_1', 'tableau_2', 'tableau_3', 
            'tableau_4', 'tableau_4_1', 'tableau_5'
        ];

        // Récupérer les wilayas (on suppose une table wilaya avec id et nom)
        // Si vous n'avez pas de table wilaya, adaptez pour récupérer les valeurs distinctes du champ wilaya dans societe
        $sql = "SELECT id, nom FROM wilayas ORDER BY id";
        $result = $bd->requete($sql);
        $wilayas = [];
        while ($row = $bd->fetch_object($result)) {
            $wilayas[] = $row;
        }

        $stats = [];

        foreach ($wilayas as $wil) {
            $wil_id = $wil->id;
            $wil_nom = $wil->nom;

            // Nombre de sociétés dans cette wilaya
            $sql = "SELECT COUNT(*) FROM societe WHERE wilayas = " . intval($wil_id);
            $res = $bd->requete($sql);
            $row = $bd->fetch_array($res);
            $total_societes = $row[0];

            // Sociétés actives (avec au moins un utilisateur) dans cette wilaya
            $sql = "SELECT COUNT(DISTINCT s.id_societe) 
                    FROM societe s 
                    JOIN accounts a ON a.id_societe = s.id_societe 
                    WHERE s.wilayas = " . intval($wil_id) . " AND a.type = 'utilisateur'";
            $res = $bd->requete($sql);
            $row = $bd->fetch_array($res);
            $societes_actives = $row[0];

            // Compter les tableaux pour cette wilaya
            $tableaux_soumis = 0;
            $en_attente = 0;
            $valides = 0;
            $brouillons = 0;

            foreach ($tables as $table) {
                // Tableaux soumis (statut != 'brouillon') ou simplement existants ? On prend existants
                $sql = "SELECT COUNT(*) FROM `$table` t 
                        JOIN societe s ON t.id_societe = s.id_societe 
                        WHERE s.wilayas = " . intval($wil_id) . " AND t.annee = " . intval($annee) . " AND t.statut != 'brouillon'";
                $res = $bd->requete($sql);
                if ($res) {
                    $row = $bd->fetch_array($res);
                    $tableaux_soumis += $row[0];
                }

                // En attente
                $sql = "SELECT COUNT(*) FROM `$table` t 
                        JOIN societe s ON t.id_societe = s.id_societe 
                        WHERE s.wilayas = " . intval($wil_id) . " AND t.annee = " . intval($annee) . " AND t.statut = 'en_attente'";
                $res = $bd->requete($sql);
                if ($res) {
                    $row = $bd->fetch_array($res);
                    $en_attente += $row[0];
                }

                // Validés
                $sql = "SELECT COUNT(*) FROM `$table` t 
                        JOIN societe s ON t.id_societe = s.id_societe 
                        WHERE s.wilayas = " . intval($wil_id) . " AND t.annee = " . intval($annee) . " AND t.statut = 'validé'";
                $res = $bd->requete($sql);
                if ($res) {
                    $row = $bd->fetch_array($res);
                    $valides += $row[0];
                }

                // Brouillons
                $sql = "SELECT COUNT(*) FROM `$table` t 
                        JOIN societe s ON t.id_societe = s.id_societe 
                        WHERE s.wilayas = " . intval($wil_id) . " AND t.annee = " . intval($annee) . " AND t.statut = 'brouillon'";
                $res = $bd->requete($sql);
                if ($res) {
                    $row = $bd->fetch_array($res);
                    $brouillons += $row[0];
                }
            }

            // Nombre total de tableaux possibles pour cette wilaya : total_societes * nombre_de_tables
            $total_possible = $total_societes * count($tables);
            $taux_avancement = $total_possible > 0 ? round(($valides / $total_possible) * 100, 1) : 0;

            $stats[] = [
                'id' => $wil_id,
                'wilaya' => $wil_nom,
                'total_societes' => $total_societes,
                'societes_actives' => $societes_actives,
                'tableaux_soumis' => $tableaux_soumis,
                'en_attente' => $en_attente,
                'valides' => $valides,
                'brouillons' => $brouillons,
                'taux_avancement' => $taux_avancement
            ];
        }

        return $stats;
    }
}