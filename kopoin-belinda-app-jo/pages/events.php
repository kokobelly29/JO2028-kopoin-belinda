<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/styles-computer.css">
    <link rel="stylesheet" href="../css/styles-responsive.css">
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <title>Liste des Sports - Jeux Olympiques - Los Angeles 2028</title>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="sports.php">Sports</a></li>
                <li><a href="events.php">Calendrier des épreuves</a></li>
                <li><a href="results.php">Résultats</a></li>
                <li><a href="login.php">Accès administrateur</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Calendrier des épreuves</h1>

        <?php
        require_once("../database/database.php");

        try {
            // Requête pour récupérer la liste des sports depuis la base de données
            $query = "SELECT e.nom_epreuve, s.nom_sport, e.date_epreuve, e.heure_epreuve, l.nom_lieu, l.adresse_lieu, l.cp_lieu 
                        FROM EPREUVE e 
                        INNER JOIN SPORT s ON e.id_sport = s.id_sport 
                        INNER JOIN LIEU l ON e.id_lieu = l.id_lieu";
            $statement = $connexion->prepare($query);
            $statement->execute();
            
            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table border='0.5' cellpadding='5' cellspacing='0'>";
                echo "<tr><th class='color'>Epreuves</th><th>Catégories Sports</th><th>Date</th><th>Heure</th><th>Lieu</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    // Conversion de la date et de l'heure au format désiré
                    $date_formatee = date("d/m/Y", strtotime($row['date_epreuve']));
                    $heure_formatee = date("H:i", strtotime($row['heure_epreuve']));

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_sport'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($date_formatee, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($heure_formatee, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_lieu'] . ',  ' . $row['cp_lieu'] . ',  ' . $row['adresse_lieu'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun sport trouvé.</p>";
            }
        } catch (PDOException $e) {
            // Gestion d'erreur améliorée
            echo "<p style='color: red;'>Erreur : Impossible de récupérer les sports. Veuillez réessayer plus tard.</p>";
            error_log("Erreur PDO : " . $e->getMessage()); // Log de l'erreur dans un fichier serveur pour débogage
        }

        // Définir le niveau d'affichage des erreurs (utile en phase de développement)
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        ?>
        
        <p class="paragraph-link">
            <a class="link-home" href="../index.php">Retour Accueil</a>
        </p>

    </main>
    <footer>
        <figure>
            <img src="../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>

</html>
