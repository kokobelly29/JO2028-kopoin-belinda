<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: manage-athletes.php");
    exit();
}

$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'athlète est un entier valide
if (!$id_athlete && $id_athlete !== 0) {
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: manage-athletes.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations de l'athlète pour affichage dans le formulaire
try {
    $queryAthlete = "SELECT a.nom_athlete, a.prenom_athlete, a.id_pays, p.nom_pays, a.id_genre, g.nom_genre
                     FROM ATHLETE a 
                     INNER JOIN Pays p ON a.id_pays = p.id_pays
                     INNER JOIN GENRE g ON a.id_genre = g.id_genre
                     WHERE a.id_athlete = :idAthlete";

    // Préparation de la requête
    $statementAthlete = $connexion->prepare($queryAthlete);
    // Lier les paramètres avec les variables PHP
    $statementAthlete->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    // Exécution de la requête
    $statementAthlete->execute();

    // Vérifier si un athlète a été trouvé
    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlète non trouvé.";
        header("Location: manage-athletes.php");
        exit();
    }
} catch (PDOException $e) {
    // En cas d'erreur avec la base de données
    $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    header("Location: manage-athletes.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_SPECIAL_CHARS);
    $idPays = filter_input(INPUT_POST, 'idPays', FILTER_VALIDATE_INT);
    $idGenre = filter_input(INPUT_POST, 'idGenre', FILTER_VALIDATE_INT);

    // Vérifiez si tous les champs sont remplis
    if (empty($nomAthlete) || empty($prenomAthlete) || !$idPays || !$idGenre) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: modify-athlete.php?id_athlete=$id_athlete");
        exit();
    }

    try {
        // Requête pour mettre à jour les informations de l'athlète
        $query = "UPDATE ATHLETE SET nom_athlete = :nomAthlete, prenom_athlete = :prenomAthlete, 
                  id_pays = :idPays, id_genre = :idGenre 
                  WHERE id_athlete = :idAthlete";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":idPays", $idPays, PDO::PARAM_INT);
        $statement->bindParam(":idGenre", $idGenre, PDO::PARAM_INT);
        $statement->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'athlète a été modifié avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'athlète.";
            header("Location: modify-athlete.php?id_athlete=$id_athlete");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: modify-athlete.php?id_athlete=$id_athlete");
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
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon.ico" type="image/x-icon">
    <title>Modifier un Athlète - Jeux Olympiques - Los Angeles 2028</title>
</head>
<body>
    <header>
        <nav>
            <ul class="menu">
                <li><a href="../admin-users/manage-users.php">Gestion Administrateurs</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-genres/manage-genres.php">Gestion Genres</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Modifier un Athlète</h1>

        <!-- Affichage des messages d'erreur ou de succès -->
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') . '</p>';
            unset($_SESSION['success']);
        }
        ?>

        <form action="modify-athlete.php?id_athlete=<?php echo $id_athlete; ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet athlète ?')">
            <label for="nomAthlete">Nom de l'Athlète :</label>
            <input type="text" name="nomAthlete" id="nomAthlete" value="<?php echo htmlspecialchars($athlete['nom_athlete'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

            <label for="prenomAthlete">Prénom de l'athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" value="<?php echo htmlspecialchars($athlete['prenom_athlete'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>


            <label for="idPays">Pays de l'Athlète :</label>
            <select name="idPays" id="idPays" required>
                <option value="" disabled selected>Choisissez un pays</option>
                <?php
                try {
                    $queryCountry = "SELECT id_pays, nom_pays FROM PAYS ORDER BY nom_pays";
                    $statementCountry = $connexion->prepare($queryCountry);
                    $statementCountry->execute();
                    $countries = $statementCountry->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($countries as $country) {
                        $selected = ($country['id_pays'] == $athlete['id_pays']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($country['id_pays'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($country['nom_pays'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<option value="" disabled>Erreur de chargement des pays</option>';
                }
                ?>
            </select>

            <label for="idGenre">Genre de l'Athlète :</label>
            <select name="idGenre" id="idGenre" required>
                <option value="" disabled>Choisissez un genre</option>
                <?php 
                // Récupérer la liste des pays pour le menu déroulant
                try {
                    $queryGenres = "SELECT id_genre, nom_genre FROM GENRE ORDER BY nom_genre";
                    $statementGenres = $connexion->prepare($queryGenres);
                    $statementGenres->execute();
                    $genres = $statementGenres->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($genres as $genre) {
                        $selected = ($genre['id_genre'] == $athlete['id_genre']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($genre['id_genre'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($genre['nom_genre'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<option value="" disabled>Erreur de chargement des genres</option>';
                }
                ?>
            </select>

            <input type="submit" value="Modifier l'athlète">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athlètes</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
</html>
