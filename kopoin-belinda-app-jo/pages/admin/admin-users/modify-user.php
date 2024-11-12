<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL
if (!isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-users.php");
    exit();
}

$idUtilisateur = filter_input(INPUT_GET, 'id_utilisateur', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'utilisateur est un entier valide
if (!$idUtilisateur && $idUtilisateur !== 0) {
    $_SESSION['error'] = "ID de l'utilisateur invalide.";
    header("Location: manage-users.php");
    exit();
}

// Vider les messages de succès précédents
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}

// Récupérez les informations de l'utilisateur pour affichage dans le formulaire
try {
    $queryUtilisateur = "SELECT id_utilisateur, nom_utilisateur, prenom_utilisateur, login FROM UTILISATEUR WHERE id_utilisateur = :idUtilisateur";
    $statementUtilisateur = $connexion->prepare($queryUtilisateur);
    $statementUtilisateur->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
    $statementUtilisateur->execute();

    if ($statementUtilisateur->rowCount() > 0) {
        $user = $statementUtilisateur->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header("Location: manage-users.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-users.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomUtilisateur = filter_input(INPUT_POST, 'nomUtilisateur', FILTER_SANITIZE_SPECIAL_CHARS);
    $prenomUtilisateur = filter_input(INPUT_POST, 'prenomUtilisateur', FILTER_SANITIZE_SPECIAL_CHARS);
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password']; // Si le mot de passe est modifié, on le hashera

    // Vérification des champs
    if (empty(trim($nomUtilisateur)) || empty(trim($prenomUtilisateur)) || empty(trim($login))) {
        $_SESSION['error'] = "Tous les champs doivent être remplis.";
        header("Location: modify-user.php?id_utilisateur=$idUtilisateur");
        exit();
    }

    try {
        // Vérifiez si le login existe déjà (en excluant l'utilisateur actuel)
        $queryCheck = "SELECT id_utilisateur FROM UTILISATEUR WHERE login = :login AND id_utilisateur <> :idUtilisateur";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":login", $login, PDO::PARAM_STR);
        $statementCheck->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "Ce login existe déjà.";
            header("Location: modify-user.php?id_utilisateur=$idUtilisateur");
            exit();
        }

        // Hashage du mot de passe uniquement s'il a été modifié
        if (!empty($password)) {
            if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $_SESSION['error'] = "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.";
                header("Location: modify-user.php?id_utilisateur=$idUtilisateur");
                exit();
            }
            $passwordHashed = password_hash($password, PASSWORD_BCRYPT);
            $query = "UPDATE UTILISATEUR SET nom_utilisateur = :nomUtilisateur, prenom_utilisateur = :prenomUtilisateur, login = :login, password = :password WHERE id_utilisateur = :idUtilisateur";
        } else {
            $query = "UPDATE UTILISATEUR SET nom_utilisateur = :nomUtilisateur, prenom_utilisateur = :prenomUtilisateur, login = :login WHERE id_utilisateur = :idUtilisateur";
        }

        // Exécuter la mise à jour
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomUtilisateur", $nomUtilisateur, PDO::PARAM_STR);
        $statement->bindParam(":prenomUtilisateur", $prenomUtilisateur, PDO::PARAM_STR);
        $statement->bindParam(":login", $login, PDO::PARAM_STR);
        if (!empty($password)) {
            $statement->bindParam(":password", $passwordHashed, PDO::PARAM_STR);
        }
        $statement->bindParam(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'utilisateur a été modifié avec succès.";
            header("Location: manage-users.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'utilisateur.";
            header("Location: modify-user.php?id_utilisateur=$idUtilisateur");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-user.php?id_utilisateur=$idUtilisateur");
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
    <title>Modifier un Utilisateur - Jeux Olympiques - Los Angeles 2028</title>
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
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Modifier un Utilisateur</h1>

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

        <form action="modify-user.php?id_utilisateur=<?php echo $idUtilisateur; ?>" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur ?')">
            <label for="nomUtilisateur">Nom de l'utilisateur :</label>
            <input type="text" name="nomUtilisateur" id="nomUtilisateur" value="<?php echo htmlspecialchars($user['nom_utilisateur']); ?>" required>

            <label for="prenomUtilisateur">Prénom de l'utilisateur :</label>
            <input type="text" name="prenomUtilisateur" id="prenomUtilisateur" value="<?php echo htmlspecialchars($user['prenom_utilisateur']); ?>" required>

            <label for="login">Login de l'utilisateur :</label>
            <input type="text" name="login" id="login" value="<?php echo htmlspecialchars($user['login']); ?>" required>

            <label for="password">Mot de passe de l'utilisateur <span class='color_mpd' >(laissez vide pour ne pas changer)</span>:</label>
            <input type="password" name="password" id="password">

            <input type="submit" value="Modifier l'Utilisateur">
        </form>

        <p class="paragraph-link">
            <a class="link-home" href="manage-users.php">Retour à la gestion des Utilisateurs</a>
        </p>
    </main>

    <footer>
        <figure>
            <img src="../../../img/logo-jo.png" alt="logo Jeux Olympiques - Los Angeles 2028">
        </figure>
    </footer>
</body>
<style>
.color_mpd{
    color: #d7c378;;
  font-size: italic;
}

</style>
</html>
