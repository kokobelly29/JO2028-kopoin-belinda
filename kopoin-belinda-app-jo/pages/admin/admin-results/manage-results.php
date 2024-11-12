<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$login = $_SESSION['login'];
$nom_utilisateur = $_SESSION['prenom_utilisateur'];
$prenom_utilisateur = $_SESSION['nom_utilisateur'];

// Fonction pour vérifier le token CSRF
function checkCSRFToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Token CSRF invalide.');
        }
    }
}

// Générer un token CSRF si ce n'est pas déjà fait
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Génère un token CSRF
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
    <title>Liste des Résultats - Jeux Olympiques - Los Angeles 2028</title>
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
        <h1>Liste des Résultats</h1>
        <div class="action-buttons">
            <button onclick="openAddResultatForm()">Ajouter un résultat</button>
        </div>
        
        <!-- Tableau des résultats -->
        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des lieux depuis la base de données
            $query = "SELECT r.id_epreuve, e.nom_epreuve, r.id_athlete, a.nom_athlete, a.prenom_athlete, r.resultat, p.id_pays, p.nom_pays, g.id_genre, g.nom_genre   
                    FROM RESULTAT r
                    INNER JOIN EPREUVE e ON e.id_epreuve = r.id_epreuve
                    INNER JOIN ATHLETE a ON a.id_athlete = r.id_athlete
                    INNER JOIN PAYS p ON p.id_pays = a.id_pays
                    INNER JOIN GENRE g ON g.id_genre = a.id_genre
                    ORDER BY id_athlete";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Nom Athlète</th><th>Prénom Athlète</th><th>Genre</th><th>Pays</th><th>Epreuve</th><th>Résultat</th><th>Modifier</th><th>Supprimer</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['prenom_athlete'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_genre'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_pays'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($row['resultat'], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td><button onclick='openModifyResultatForm({$row['id_athlete']}, {$row['id_epreuve']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteResultatConfirmation({$row['id_athlete']}, {$row['id_epreuve']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun résultat trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
        ?>
        
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>

    <script>
        function openAddResultatForm() {
            window.location.href = 'add-resultat.php';
        }

        function openModifyResultatForm(id_athlete, id_epreuve) {
            window.location.href = 'modify-resultat.php?id_athlete=' + encodeURIComponent(id_athlete) + '&id_epreuve=' + encodeURIComponent(id_epreuve);
        }

        function deleteResultatConfirmation(id_athlete, id_epreuve) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce résultat?")) {
                window.location.href = 'delete-resultat.php?id_athlete=' + encodeURIComponent(id_athlete) + '&id_epreuve=' + encodeURIComponent(id_epreuve);
            }
        }
    </script>
</body>

</html>