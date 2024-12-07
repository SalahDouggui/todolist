<?php
// Inclure la configuration
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

// Initialiser les filtres
$filter_priorite = $_GET['filter_priorite'] ?? '';
$filter_categorie = $_GET['filter_categorie'] ?? '';

// Construire la requête SQL avec les filtres
$sql = "SELECT * FROM taches WHERE 1=1";

// Ajouter un filtre pour la priorité
if (!empty($filter_priorite)) {
    $sql .= " AND priorite = :priorite";
}

// Ajouter un filtre pour la catégorie
if (!empty($filter_categorie)) {
    $sql .= " AND categorie = :categorie";
}

// Ajouter un tri par défaut
$sql .= " ORDER BY date_creation ASC";

// Préparer et exécuter la requête
$stmt = $pdo->prepare($sql);

// Associer les valeurs des filtres, si nécessaires
$params = [];
if (!empty($filter_priorite)) {
    $params['priorite'] = $filter_priorite;
}
if (!empty($filter_categorie)) {
    $params['categorie'] = $filter_categorie;
}

$stmt->execute($params);
$taches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter une tâche
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
    <div class="container">
        <h1>ToDo List</h1>

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

        <!-- Formulaire pour filtrer les tâches -->
        <form action="index.php" method="GET" style="margin-bottom: 20px;">
            <label for="filter_priorite">Filtrer par priorité :</label>
            <select name="filter_priorite" id="filter_priorite">
                <option value="">Toutes</option>
                <option value="Haute" <?= $filter_priorite === 'Haute' ? 'selected' : '' ?>>Haute</option>
                <option value="Moyenne" <?= $filter_priorite === 'Moyenne' ? 'selected' : '' ?>>Moyenne</option>
                <option value="Basse" <?= $filter_priorite === 'Basse' ? 'selected' : '' ?>>Basse</option>
            </select>

            <label for="filter_categorie">Filtrer par catégorie :</label>
            <select name="filter_categorie" id="filter_categorie">
                <option value="">Toutes</option>
                <option value="Travail" <?= $filter_categorie === 'Travail' ? 'selected' : '' ?>>Travail</option>
                <option value="Personnel" <?= $filter_categorie === 'Personnel' ? 'selected' : '' ?>>Personnel</option>
                <option value="Général" <?= $filter_categorie === 'Général' ? 'selected' : '' ?>>Général</option>
            </select>

            <button type="submit">Appliquer</button>
        </form>

        <!-- Liste des tâches -->
        <h2>Mes tâches</h2>
        <p>
            <strong>Filtres actifs :</strong>
            Priorité : <?= !empty($filter_priorite) ? htmlspecialchars($filter_priorite) : 'Toutes' ?>,
            Catégorie : <?= !empty($filter_categorie) ? htmlspecialchars($filter_categorie) : 'Toutes' ?>
        </p>
        <ul>
            <?php foreach ($taches as $tache) : ?>
                <li class="<?= $tache['terminee'] ? 'terminee' : '' ?>">
                    <?= htmlspecialchars($tache['nom']) ?>
                    <span>(<?= htmlspecialchars($tache['categorie']) ?>)</span>
                    <span style="color: <?= $tache['priorite'] === 'Haute' ? 'red' : ($tache['priorite'] === 'Basse' ? 'green' : 'orange') ?>;">
                        [<?= htmlspecialchars($tache['priorite']) ?>]
                    </span>
                    <a href="index.php?delete=<?= $tache['id'] ?>" style="color: red;">Supprimer</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>

</html>