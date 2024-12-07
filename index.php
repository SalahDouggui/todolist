<?php
$config = require 'config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Ajouter une nouvelle tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nom_tache'])) {
    $nom_tache = trim($_POST['nom_tache']);
    $categorie = $_POST['categorie'] ?? 'Général';
    $priorite = $_POST['priorite'] ?? 'Moyenne';

    $sql = "INSERT INTO taches (nom, categorie, priorite) VALUES (:nom, :categorie, :priorite)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nom' => $nom_tache, 'categorie' => $categorie, 'priorite' => $priorite]);

    header("Location: index.php");
    exit;
}


// Supprimer une tâche
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $sql = "DELETE FROM taches WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: index.php");
    exit;
}
// Marquer une tâche comme terminée ou non terminée
if (isset($_GET['toggle'])) {
    $id = (int) $_GET['toggle'];

    // Récupérer l'état actuel de la tâche
    $sql = "SELECT terminee FROM taches WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $tache = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($tache) {
        // Inverser l'état de la tâche
        $nouvelEtat = !$tache['terminee'];
        $sql = "UPDATE taches SET terminee = :terminee WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['terminee' => $nouvelEtat, 'id' => $id]);
    }

    // Rediriger pour éviter les soumissions multiples
    header("Location: index.php");
    exit;
}

// Récupérer les tâches
$sql = "SELECT * FROM taches ORDER BY date_creation DESC";
$stmt = $pdo->query($sql);
$taches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo List</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <h1>ToDo List</h1>
    <div class="container">
        <!-- Formulaire pour ajouter une tâche -->
        <form action="index.php" method="POST">
            <input type="text" name="nom_tache" placeholder="Nouvelle tâche" required>
            <select name="categorie">
                <option value="Général">Général</option>
                <option value="Travail">Travail</option>
                <option value="Personnel">Personnel</option>
            </select>
            <select name="priorite">
                <option value="Haute">Haute</option>
                <option value="Moyenne" selected>Moyenne</option>
                <option value="Basse">Basse</option>
            </select>
            <button type="submit">Ajouter</button>
        </form>
        <!-- Affichage des tâches -->
        <h2>Mes tâches</h2>
        <ul>
            <?php foreach ($taches as $tache) : ?>
                <li class="<?= $tache['terminee'] ? 'terminee' : '' ?>">
                    <?= htmlspecialchars($tache['nom']) ?>
                    <span>(<?= htmlspecialchars($tache['categorie']) ?>)</span>
                    <span style="color: <?= $tache['priorite'] === 'Haute' ? 'red' : ($tache['priorite'] === 'Basse' ? 'blue' : 'black') ?>;">
                        [<?= htmlspecialchars($tache['priorite']) ?>]
                    </span>
                    <?= $tache['terminee'] ? '<span style="color: green;">(terminée)</span>' : '' ?>
                    <a href="index.php?toggle=<?= $tache['id'] ?>" style="color: blue;">
                        <?= $tache['terminee'] ? 'Marquer comme non terminée' : 'Marquer comme terminée' ?>
                    </a>
                    <a href="index.php?delete=<?= $tache['id'] ?>" style="color: red;">Supprimer</a>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
</body>

</html>