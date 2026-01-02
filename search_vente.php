<?php
include 'config/database.php';

$ventes = [];
$critere = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $critere = $_POST['critere'];
    $type = $_POST['type_recherche'];
    
    try {
        if ($type === 'numero') {
            $stmt = $pdo->prepare("
                SELECT v.*, 
                       GROUP_CONCAT(CONCAT(a.nom, ' (', va.qteVendue, ' unités)') SEPARATOR ', ') as articles
                FROM Vente v
                LEFT JOIN VenteArticle va ON v.id = va.idVen
                LEFT JOIN Article a ON va.idArt = a.id
                WHERE v.numero LIKE ?
                GROUP BY v.id
                ORDER BY v.date DESC
            ");
            $stmt->execute(["%$critere%"]);
        } else {
            $stmt = $pdo->prepare("
                SELECT v.*, 
                       GROUP_CONCAT(CONCAT(a.nom, ' (', va.qteVendue, ' unités)') SEPARATOR ', ') as articles
                FROM Vente v
                LEFT JOIN VenteArticle va ON v.id = va.idVen
                LEFT JOIN Article a ON va.idArt = a.id
                WHERE v.nom LIKE ? OR v.adresse LIKE ?
                GROUP BY v.id
                ORDER BY v.date DESC
            ");
            $stmt->execute(["%$critere%", "%$critere%"]);
        }
        
        $ventes = $stmt->fetchAll();
    } catch(PDOException $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de Ventes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #2196F3;
            padding-bottom: 10px;
        }
        .search-form {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            max-width: 400px;
        }
        button {
            background: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #1976D2;
        }
        .results {
            margin-top: 30px;
        }
        .vente-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
        }
        .vente-header {
            background: #2196F3;
            color: white;
            padding: 10px;
            border-radius: 3px;
            margin: -15px -15px 15px -15px;
        }
        .vente-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        .info-item {
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        .btn-secondary {
            background: #757575;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Recherche de Ventes</h1>
        
        <div class="search-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="type_recherche">Rechercher par :</label>
                    <select id="type_recherche" name="type_recherche">
                        <option value="numero" <?php echo (isset($_POST['type_recherche']) && $_POST['type_recherche'] == 'numero') ? 'selected' : ''; ?>>Numéro de vente</option>
                        <option value="client" <?php echo (isset($_POST['type_recherche']) && $_POST['type_recherche'] == 'client') ? 'selected' : ''; ?>>Nom du client</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="critere">Critère de recherche :</label>
                    <input type="text" id="critere" name="critere" 
                           value="<?php echo htmlspecialchars($critere); ?>" 
                           placeholder="Saisir votre recherche..." required>
                </div>
                
                <button type="submit">Rechercher</button>
                <a href="search_vente.php" class="btn btn-secondary">Réinitialiser</a>
            </form>
        </div>
        
        <div class="results">
            <?php if (isset($message)): ?>
                <div class="error"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($ventes)): ?>
                <h2> Résultats (<?php echo count($ventes); ?> vente(s) trouvée(s))</h2>
                
                <?php foreach ($ventes as $vente): ?>
                <div class="vente-card">
                    <div class="vente-header">
                        <strong>Vente N°: <?php echo htmlspecialchars($vente['numero']); ?></strong>
                    </div>
                    
                    <div class="vente-info">
                        <div class="info-item">
                            <span class="info-label">Date :</span><br>
                            <?php echo date('d/m/Y', strtotime($vente['date'])); ?>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Client :</span><br>
                            <?php echo htmlspecialchars($vente['nom']); ?>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Adresse :</span><br>
                            <?php echo htmlspecialchars($vente['adresse']); ?>
                        </div>
                    </div>
                    
                    <?php if ($vente['articles']): ?>
                    <div class="info-item">
                        <span class="info-label">Articles vendus :</span><br>
                        <?php echo htmlspecialchars($vente['articles']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="no-results">
                    <h3> Aucune vente trouvée</h3>
                    <p>Aucune vente ne correspond à votre recherche.</p>
                    <a href="search_vente.php" class="btn">Nouvelle recherche</a>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h3> Entrez vos critères de recherche</h3>
                    <p>Utilisez le formulaire ci-dessus pour rechercher une vente.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" class="btn">← Retour à l'accueil</a>
            <a href="add_vente.php" class="btn">Nouvelle vente</a>
        </div>
    </div>
</body>
</html>