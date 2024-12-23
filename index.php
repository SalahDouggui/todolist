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

// Charger les données d'une tâche pour l'édition
$edit_tache = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $sql = "SELECT * FROM taches WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $edit_tache = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Mettre à jour une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tache'])) {
    $id = (int) $_POST['id'];
    $nom_tache = trim($_POST['nom_tache']);
    $categorie = $_POST['categorie'] ?? 'Général';
    $priorite = $_POST['priorite'] ?? 'Moyenne';

    $sql = "UPDATE taches SET nom = :nom, categorie = :categorie, priorite = :priorite WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nom' => $nom_tache,
        'categorie' => $categorie,
        'priorite' => $priorite,
        'id' => $id
    ]);

    header("Location: index.php?success=update");
    exit;
}

// Ajouter une tâche
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nom_tache']) && !isset($_POST['update_tache'])) {
    $nom_tache = trim($_POST['nom_tache']);
    $categorie = $_POST['categorie'] ?? 'Général';
    $priorite = $_POST['priorite'] ?? 'Moyenne';

    $sql = "INSERT INTO taches (nom, categorie, priorite) VALUES (:nom, :categorie, :priorite)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nom' => $nom_tache, 'categorie' => $categorie, 'priorite' => $priorite]);

    $lastId = $pdo->lastInsertId();
    header("Location: index.php?success=new");
    exit;
}
// Supprimer une tâche
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $sql = "DELETE FROM taches WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);

    header("Location: index.php?success=delete");
    exit;
}

// Récupérer toutes les tâches
$sql = "SELECT * FROM taches ORDER BY date_creation ASC";
$stmt = $pdo->query($sql);
$taches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <h1>ToDo List</h1>

        <!-- Afficher les messages de succès -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] === 'new'): ?>
                    La tâche a été ajoutée avec succès !
                <?php elseif ($_GET['success'] === 'update'): ?>
                    La tâche a été mise à jour avec succès !
                <?php elseif ($_GET['success'] === 'delete'): ?>
                    La tâche a été supprimée avec succès !
                <?php endif; ?>
            </div>
        <?php endif; ?>


        <!-- Formulaire pour ajouter ou modifier une tâche -->
        <?php if ($edit_tache): ?>
            <form action="index.php" method="POST" style="margin-bottom: 20px;">
                <input type="hidden" name="id" value="<?= $edit_tache['id'] ?>">
                <input type="text" name="nom_tache" value="<?= htmlspecialchars($edit_tache['nom']) ?>" required>
                <select name="categorie">
                    <option value="Général" <?= $edit_tache['categorie'] === 'Général' ? 'selected' : '' ?>>Général</option>
                    <option value="Travail" <?= $edit_tache['categorie'] === 'Travail' ? 'selected' : '' ?>>Travail</option>
                    <option value="Personnel" <?= $edit_tache['categorie'] === 'Personnel' ? 'selected' : '' ?>>Personnel</option>
                </select>
                <select name="priorite">
                    <option value="Haute" <?= $edit_tache['priorite'] === 'Haute' ? 'selected' : '' ?>>Haute</option>
                    <option value="Moyenne" <?= $edit_tache['priorite'] === 'Moyenne' ? 'selected' : '' ?>>Moyenne</option>
                    <option value="Basse" <?= $edit_tache['priorite'] === 'Basse' ? 'selected' : '' ?>>Basse</option>
                </select>
                <button type="submit" name="update_tache">Mettre à jour</button>
            </form>
        <?php else: ?>
            <form action="index.php" method="POST">
                <!-- Champ "Nouvelle tâche" -->
                <input type="text" name="nom_tache" placeholder="Nouvelle tâche" required>

                <!-- Sélecteur de catégorie -->
                <select name="categorie">
                    <option value="Général">Général</option>
                    <option value="Travail">Travail</option>
                    <option value="Personnel">Personnel</option>
                </select>

                <!-- Sélecteur de priorité -->
                <select name="priorite">
                    <option value="Haute">Haute</option>
                    <option value="Moyenne" selected>Moyenne</option>
                    <option value="Basse">Basse</option>
                </select>

                <!-- Bouton "Ajouter" -->
                <button type="submit">Ajouter</button>
            </form>

        <?php endif; ?>

        <!-- Formulaire de filtre -->
        <form id="filter-form" class="filter-form" style="margin-bottom: 20px;">
            <label for="filter-priorite">Filtrer par priorité :</label>
            <select id="filter-priorite" class="filter-select">
                <option value="">Toutes</option>
                <option value="Haute">Haute</option>
                <option value="Moyenne">Moyenne</option>
                <option value="Basse">Basse</option>
            </select>

            <label for="filter-categorie">Filtrer par catégorie :</label>
            <select id="filter-categorie" class="filter-select">
                <option value="">Toutes</option>
                <option value="Travail">Travail</option>
                <option value="Personnel">Personnel</option>
                <option value="Général">Général</option>
            </select>
        </form>

        <!-- Liste des tâches -->
        <ul>
            <?php foreach ($taches as $tache) : ?>
                <li class="tache <?= isset($_GET['new']) && $_GET['new'] == $tache['id'] ? 'new' : '' ?>"
                    data-priorite="<?= htmlspecialchars($tache['priorite']) ?>"
                    data-categorie="<?= htmlspecialchars($tache['categorie']) ?>">

                    <!-- Nom et détails de la tâche -->
                    <div>
                        <?= htmlspecialchars($tache['nom']) ?>
                        <span>(<?= htmlspecialchars($tache['categorie']) ?>)</span>
                        <span style="color: <?= $tache['priorite'] === 'Haute' ? 'red' : ($tache['priorite'] === 'Basse' ? 'green' : 'orange') ?>;">
                            [<?= htmlspecialchars($tache['priorite']) ?>]
                        </span>
                    </div>

                    <!-- Actions (boutons) -->
                    <div class="actions">
                        <a href="index.php?edit=<?= $tache['id'] ?>" class="edit">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="index.php?delete=<?= $tache['id'] ?>" class="delete" onclick="return confirmDeletion(this);">
                            <i class="fas fa-trash-alt"></i> Supprimer
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <script src="js/script.js"></script>
</body>

</html>