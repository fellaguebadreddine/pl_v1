<?php
require 'includes/config.php';
require 'lib/SimpleXLSX.php';

		  $servername = "localhost";
		  $username = "softdz38_pl_user";
		  $password = "4sN*{7#ld9Gy0phs";
		 $dbname = "softdz38_pl";
use Shuchkin\SimpleXLSX;

$message = "";
// Create connection
			$conn = new mysqli($servername, $username, $password, $dbname);

        // Vérifier la connexion
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
if (isset($_POST['import'])) {

    if (!empty($_FILES['file']['tmp_name'])) {

        if ($xlsx = SimpleXLSX::parse($_FILES['file']['tmp_name'])) {

            $rows = $xlsx->rows();


           
$conn->set_charset("utf8mb4");

$stmt = $conn->prepare("
    INSERT INTO grades (
        annee, ministere, wilaya, institution,
        loi, filiere, corps, grade, classe,
        postes_ouverts, postes_pourvus, postes_vacants,
        indice_mensuel, salaire_mensuel, salaire_annuel
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Erreur préparation: " . $conn->error);
}

foreach ($rows as $index => $row) {

    if ($index == 0) continue;

    $annee       = trim($row[0] ?? '');
    $ministere   = trim($row[1] ?? '');
    $wilaya      = trim($row[2] ?? '');
    $institution = trim($row[3] ?? '');
    $loi  = trim($row[4] ?? '');
    $filiere     = trim($row[5] ?? '');
    $corps       = trim($row[6] ?? '');
    $grade       = trim($row[7] ?? '');
    $classe      = trim($row[8] ?? '');
    $ouverts     = intval($row[9] ?? 0);
    $pourvus     = intval($row[10] ?? 0);
    $vacants     = intval($row[11] ?? 0);
    $indice      = floatval($row[12] ?? 0);
    $mensuel     = floatval($row[13] ?? 0);
    $annuel      = floatval($row[14] ?? 0);

    if (!empty($annee)) {

        $stmt->bind_param(
            "sssssssssiiiddd",
            $annee,
            $ministere,
            $wilaya,
            $institution,
            $loi,
            $filiere,
            $corps,
            $grade,
            $classe,
            $ouverts,
            $pourvus,
            $vacants,
            $indice,
            $mensuel,
            $annuel
        );

        $stmt->execute();
    }
}

$stmt->close();
$message = "✅ Import réussi.";

           

        } else {
            $message = "Erreur lecture fichier : " . SimpleXLSX::parseError();
        }

    } else {
        $message = "Veuillez sélectionner un fichier.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>Import Grades</title>
</head>
<body>

<h3>تحميل ملف Excel</h3>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" accept=".xlsx" required>
    <button type="submit" name="import">استيراد</button>
</form>

<p><?= $message ?></p>

</body>
</html>