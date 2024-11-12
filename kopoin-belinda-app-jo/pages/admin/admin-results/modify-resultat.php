<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si les IDs d'athlète et d'épreuve sont fournis dans l'URL
if (!isset($_GET['id_athlete']) || !isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'athlète ou de l'épreuve manquant.";
    header("Location: manage-results.php");
    exit();
}

$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);
$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si les IDs sont valides
if (!$id_athlete || !$id_epreuve) {
    $_SESSION['error'] = "ID de l'athlète ou de l'épreuve invalide.";
    header("Location: manage-results.php");
    exit();
}

// Récupération des informations actuelles du résultat
try {
    $queryResult = "SELECT resultat, id_pays, id_genre FROM RESULTAT 
                    JOIN ATHLETE ON RESULTAT.id_athlete = ATHLETE.id_athlete 
                    WHERE RESULTAT.id_athlete = :idAthlete AND id_epreuve = :idEpreuve";
    $statementResult = $connexion->prepare($queryResult);
    $statementResult->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementResult->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
    $statementResult->execute();

    if ($statementResult->rowCount() > 0) {
        $result = $statementResult->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Résultat non trouvé.";
        header("Location: manage-results.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-results.php");
    exit();
}

// Requête pour récupérer les pays
$queryCountries = "SELECT id_pays, nom_pays FROM PAYS ORDER BY nom_pays";
$countries = $connexion->query($queryCountries)->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer les épreuves
$queryEvents = "SELECT id_epreuve, nom_epreuve FROM EPREUVE ORDER BY nom_epreuve";
$events = $connexion->query($queryEvents)->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer les genres
$queryGenres = "SELECT id_genre, nom_genre FROM GENRE ORDER BY nom_genre";
$genres = $connexion->query($queryGenres)->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer les athlètes
$queryAthletes = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE ORDER BY nom_athlete, prenom_athlete";
$athletes = $connexion->query($queryAthletes)->fetchAll(PDO::FETCH_ASSOC);

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newResult = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_SPECIAL_CHARS);
    $selectedCountry = filter_input(INPUT_POST, 'id_pays', FILTER_VALIDATE_INT);
    $selectedEvent = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);
    $selectedAthlete = filter_input(INPUT_POST, 'id_athlete', FILTER_VALIDATE_INT);
    $selectedGenre = filter_input(INPUT_POST, 'id_genre', FILTER_VALIDATE_INT);

    if (empty($newResult) || !$selectedCountry || !$selectedEvent || !$selectedAthlete || !$selectedGenre) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header("Location: modify-resultat.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
        exit();
    }

    try {
        $queryUpdate = "UPDATE RESULTAT SET resultat = :resultat WHERE id_athlete = :idAthlete AND id_epreuve = :idEpreuve";
        $statementUpdate = $connexion->prepare($queryUpdate);
        $statementUpdate->bindParam(":resultat", $newResult, PDO::PARAM_STR);
        $statementUpdate->bindParam(":idAthlete", $selectedAthlete, PDO::PARAM_INT);
        $statementUpdate->bindParam(":idEpreuve", $selectedEvent, PDO::PARAM_INT);

        if ($statementUpdate->execute()) {
            $_SESSION['success'] = "Le résultat a été modifié avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du résultat.";
            header("Location: modify-resultat.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-resultat.php?id_athlete=$id_athlete&id_epreuve=$id_epreuve");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <title>Modifier un Résultat - Jeux Olympiques</title>
</head>
<body>
    <main>
        <h1>Modifier le Résultat</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="modify-resultat.php?id_athlete=<?php echo $id_athlete; ?>&id_epreuve=<?php echo $id_epreuve; ?>" method="post">
            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat" value="<?php echo htmlspecialchars($result['resultat']); ?>" required>

            <label for="id_pays">Pays :</label>
            <select name="id_pays" id="id_pays" required>
                <?php foreach ($countries as $country): ?>
                    <option value="<?= $country['id_pays'] ?>" <?= ($result['id_pays'] == $country['id_pays']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($country['nom_pays']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_epreuve">Épreuve :</label>
            <select name="id_epreuve" id="id_epreuve" required>
                <?php foreach ($events as $event): ?>
                    <option value="<?= $event['id_epreuve'] ?>" <?= ($id_epreuve == $event['id_epreuve']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($event['nom_epreuve']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_athlete">Athlète :</label>
            <select name="id_athlete" id="id_athlete" required>
                <?php foreach ($athletes as $athlete): ?>
                    <option value="<?= $athlete['id_athlete'] ?>" <?= ($id_athlete == $athlete['id_athlete']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($athlete['nom_athlete'] . ' ' . $athlete['prenom_athlete']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="id_genre">Genre :</label>
            <select name="id_genre" id="id_genre" required>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?= $genre['id_genre'] ?>" <?= ($result['id_genre'] == $genre['id_genre']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($genre['nom_genre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" value="Modifier le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des Résultats</a>
        </p>
    </main>
</body>
</html>
