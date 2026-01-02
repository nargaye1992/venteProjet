
<?php
session_start();
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['panier'])) {
    header('Location: add_vente.php');
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Récupérer les données du formulaire
    $numero = $_POST['numero'];
    $date = $_POST['date'];
    $nom_client = $_POST['nom'];
    $adresse = $_POST['adresse'];
    $panier = json_decode($_POST['panier_data'], true);
    
    // 1. Créer la vente
    $stmt = $pdo->prepare("INSERT INTO Vente (numero, date, nom, adresse) VALUES (?, ?, ?, ?)");
    $stmt->execute([$numero, $date, $nom_client, $adresse]);
    $id_vente = $pdo->lastInsertId();
    
    // 2. Ajouter les articles à la vente et mettre à jour les stocks
    foreach ($panier as $item) {
        $id_article = $item['article']['id'];
        $quantite = $item['quantite'];
        
        // Insérer dans VenteArticle
        $stmt = $pdo->prepare("INSERT INTO VenteArticle (idArt, idVen, qteVendue) VALUES (?, ?, ?)");
        $stmt->execute([$id_article, $id_vente, $quantite]);
        
        // Mettre à jour le stock
        $stmt = $pdo->prepare("UPDATE Article SET qteStock = qteStock - ? WHERE id = ?");
        $stmt->execute([$quantite, $id_article]);
    }
    
    $pdo->commit();
    
    // Vider le panier
    unset($_SESSION['panier']);
    
    // Message de succès
    $message = "Vente enregistrée avec succès ! Numéro : $numero";
    
} catch(PDOException $e) {
    $pdo->rollBack();
    $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vente Enregistrée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        .result {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #4CAF50;
            font-size: 24px;
            margin: 20px 0;
        }
        .error {
            color: #f44336;
            font-size: 24px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="result">
        <?php if (strpos($message, 'succès') !== false): ?>
            <div class="success"> <?php echo $message; ?></div>
            <p>La vente a été enregistrée dans la base de données.</p>
        <?php else: ?>
            <div class="error"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <a href="add_vente.php" class="btn">Nouvelle vente</a>
            <a href="index.php" class="btn">Accueil</a>
            <a href="search_vente.php" class="btn">Voir les ventes</a>
        </div>
    </div>
</body>
</html>