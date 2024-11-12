<?php
session_start();
require_once("../../../database/database.php");

// Check if the user is logged in
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Generate CSRF token if not already generated
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $nomEpreuve = filter_input(INPUT_POST, 'nomEpreuve', FILTER_SANITIZE_SPECIAL_CHARS);
    $idSport = filter_input(INPUT_POST, 'nomSport', FILTER_SANITIZE_NUMBER_INT);
    $dateEpreuve = filter_input(INPUT_POST, 'dateEpreuve', FILTER_SANITIZE_SPECIAL_CHARS);
    $heureEpreuve = filter_input(INPUT_POST, 'heureEpreuve', FILTER_SANITIZE_SPECIAL_CHARS);
    $idLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_NUMBER_INT);

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: add-events.php");
        exit();
    }

    // Check required fields
    if (empty($nomEpreuve) || empty($idSport) || empty($dateEpreuve) || empty($heureEpreuve) || empty($idLieu)) {
        $_SESSION['error'] = "All fields must be filled out.";
        header("Location: add-events.php");
        exit();
    }

    try {
        // Check if the event already exists
        $queryCheck = "SELECT EPREUVE.id_epreuve FROM EPREUVE 
                       INNER JOIN SPORT ON EPREUVE.id_sport = SPORT.id_sport
                       INNER JOIN LIEU ON EPREUVE.id_lieu = LIEU.id_lieu
                       WHERE EPREUVE.nom_epreuve = :nomEpreuve 
                       AND SPORT.id_sport = :idSport
                       AND EPREUVE.date_epreuve = :dateEpreuve
                       AND EPREUVE.heure_epreuve = :heureEpreuve
                       AND LIEU.id_lieu = :idLieu";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":idSport", $idSport, PDO::PARAM_INT);
        $statementCheck->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":idLieu", $idLieu, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Event already exists.";
            header("Location: add-events.php");
            exit();
        } else {
            // Insert the event
            $query = "INSERT INTO EPREUVE (nom_epreuve, id_sport, date_epreuve, heure_epreuve, id_lieu) 
                      VALUES (:nomEpreuve, :idSport, :dateEpreuve, :heureEpreuve, :idLieu)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":idSport", $idSport, PDO::PARAM_INT);
            $statement->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":idLieu", $idLieu, PDO::PARAM_INT);

            if ($statement->execute()) {
                $_SESSION['success'] = "Event successfully added.";
                header("Location: manage-events.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to add event.";
                header("Location: add-events.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        header("Location: add-events.php");
        exit();
    }
}

// Error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);
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
    <title>Ajouter une Epreuve - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages events, events, et results -->
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
        <h1>Ajouter une Epreuve</h1>
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
        <form action="add-events.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cette épreuve ?')">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <label for="nomEpreuve">Nom de l'Epreuve :</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" required>
            
            <label for="nomSport">Catégorie du Sport :</label>
            <select name="nomSport" id="nomSport" required>
                <option value="" disabled selected>Choisissez un sport</option>
                <?php
                try {
                    $queryEvent = "SELECT id_sport, nom_sport FROM SPORT ORDER BY nom_sport";
                    $statementEvent = $connexion->prepare($queryEvent);
                    $statementEvent->execute();
                    $events = $statementEvent->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($events as $event) {
                        echo '<option value="' . htmlspecialchars($event['id_sport'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($event['nom_sport'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<option value="" disabled>Erreur de chargement des events</option>';
                }
                ?>
            </select>

            <label for="dateEpreuve">Date de l'épreuve :</label>
            <input type="date" name="dateEpreuve" id="dateEpreuve" required>
            
            <label for="heureEpreuve">Heure de l'épreuve :</label>
            <input type="time" name="heureEpreuve" id="heureEpreuve" required>
            
            <label for="nomLieu">Lieu de l'Epreuve :</label>
            <select name="nomLieu" id="nomLieu" required>
                <option value="" disabled selected>Choisissez un lieu</option>
                <?php
                try {
                    $queryLieu = "SELECT id_lieu, nom_lieu FROM LIEU ORDER BY nom_lieu";
                    $statementLieu = $connexion->prepare($queryLieu);
                    $statementLieu->execute();
                    $lieux = $statementLieu->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($lieux as $lieu) {
                        echo '<option value="' . htmlspecialchars($lieu['id_lieu'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($lieu['nom_lieu'], ENT_QUOTES, 'UTF-8') . '</option>';
                    }
                } catch (PDOException $e) {
                    echo '<option value="" disabled>Erreur de chargement des genres</option>';
                }
                ?>
            </select>
            
            <input type="submit" value="Ajouter l'épreuve">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion du Calendrier</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>

</html>
