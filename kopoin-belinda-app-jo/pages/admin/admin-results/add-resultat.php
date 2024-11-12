<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $idAthlete = filter_input(INPUT_POST, 'idAthlete', FILTER_VALIDATE_INT);
    $idEpreuve = filter_input(INPUT_POST, 'idEpreuve', FILTER_VALIDATE_INT);
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

    // Vérifiez si les champs obligatoires sont vides
    if (empty($idAthlete) || empty($idEpreuve) || empty($resultat)) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: add-resultat.php");
        exit();
    }

    try {
        // Requête pour ajouter un résultat
        $queryAddResult = "INSERT INTO RESULTAT (id_athlete, id_epreuve, resultat) VALUES (:idAthlete, :idEpreuve, :resultat)";
        $statementAddResult = $connexion->prepare($queryAddResult);
        $statementAddResult->bindParam(":idAthlete", $idAthlete, PDO::PARAM_INT);
        $statementAddResult->bindParam(":idEpreuve", $idEpreuve, PDO::PARAM_INT);
        $statementAddResult->bindParam(":resultat", $resultat, PDO::PARAM_STR);

        // Exécutez la requête
        if ($statementAddResult->execute()) {
            // Redirigez ou effectuez d'autres actions si nécessaire
            header("Location: manage-results.php");
            exit();
        } else {
            // Gérez les erreurs d'insertion ici
            $_SESSION['error'] = "Erreur lors de l'ajout du résultat.";
            header("Location: add-resultat.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-resultat.php");
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
    <title>Ajouter un Résultat - Jeux Olympiques 2028</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
    <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-users/manage-users.php">Gestion Utilisateurs</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-genres/manage-genres.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Résultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-resultat.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat?')">
            <!-- Ajoutez les champs pour l'athlète, l'épreuve et le résultat -->
            <label for="idAthlete">Athlète :</label>
            <select name="idAthlete" id="idAthlete" required>
                <?php
                // Récupérer la liste des athlètes depuis la base de données
                $queryAthleteList = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE";
                $statementAthleteList = $connexion->query($queryAthleteList);

                // Afficher chaque athlète dans la liste déroulante
                while ($rowAthlete = $statementAthleteList->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $rowAthlete['id_athlete'] . '">' . $rowAthlete['nom_athlete'] . ' ' . $rowAthlete['prenom_athlete'] . '</option>';
                }
                ?>
            </select>

            <label for="idEpreuve">Épreuve :</label>
            <select name="idEpreuve" id="idEpreuve" required>
                <?php
                // Récupérer la liste des épreuves depuis la base de données
                $queryEpreuveList = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
                $statementEpreuveList = $connexion->query($queryEpreuveList);

                // Afficher chaque épreuve dans la liste déroulante
                while ($rowEpreuve = $statementEpreuveList->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $rowEpreuve['id_epreuve'] . '">' . $rowEpreuve['nom_epreuve'] . '</option>';
                }
                ?>
            </select>

            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat" required>

            <input type="submit" value="Ajouter le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des Résultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo jeux olympiques 2028">
        </figure>
    </footer>
</body>

</html>