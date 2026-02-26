<?php
require_once('../../includes/config.php');

global $bd;
$servername = "localhost"; $username = "softdz38_pl_user"; $password = "4sN*{7#ld9Gy0phs"; $dbname = "softdz38_pl";
$conn = new mysqli($servername, $username, $password, $dbname);
 // Vérifier la connexion
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

header('Content-Type: application/json');

if ($_POST['action'] === 'add_sup') {

    // Récupération et sécurisation des données
    $id_societe = intval($_POST['id_societe']);
    $id_tab_1   =  0 ;
    $annee      = intval($_POST['annee']);
    $id_user    = intval($_POST['id_user']);
    $code       =  0;
    $poste_sup  = intval($_POST['poste_sup']);
    $postes_total_sup = intval($_POST['postes_total_sup']);
    $postes_reel_sup  = intval($_POST['postes_reel_sup']);
    $poste_intirim_sup = intval($_POST['poste_intirim_sup']);
    $poste_femme_sup   = intval($_POST['poste_femme_sup']);
    $difference_sup    = intval($_POST['difference_sup']);
    $observations_sup  = trim($_POST['observations_sup']);

    $stmt = $conn->prepare("
        INSERT INTO detail_tab_1_sup 
        (id_societe, id_tab_1, annee, id_user, code, poste_sup,
         postes_total_sup, postes_reel_sup, poste_intirim_sup,
         poste_femme_sup, difference_sup, observations_sup)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Types : i=integer, s=string
    $stmt->bind_param(
        "iisiiiiiiiis",
        $id_societe,
        $id_tab_1,
        $annee,
        $id_user,
        $code,
        $poste_sup,
        $postes_total_sup,
        $postes_reel_sup,
        $poste_intirim_sup,
        $poste_femme_sup,
        $difference_sup,
        $observations_sup
    );

    if ($stmt->execute()) {
        $id = $stmt->insert_id;

        // Construction de la ligne HTML à insérer dynamiquement
        $html = '
        <tr data-id="' . $id . '">
            <td>' . htmlspecialchars($code) . '</td>
            <td>' . $poste_sup . '</td>
            <td>' . $postes_total_sup . '</td>
            <td>' . $postes_reel_sup . '</td>
            <td>' . $poste_intirim_sup . '</td>
            <td>' . $poste_femme_sup . '</td>
            <td>' . $difference_sup . '</td>
            <td>' . htmlspecialchars($observations_sup) . '</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm"
                    onclick="deleteDetailSup(' . $id . ')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>';

        echo json_encode([
            'status' => 'success',
            'html'   => $html
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Erreur insertion : ' . $bd->error
        ]);
    }
    exit;
}

if ($_POST['action'] === 'delete_sup') {

    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM detail_tab_1_sup WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Erreur suppression'
        ]);
    }
    exit;
}
?>