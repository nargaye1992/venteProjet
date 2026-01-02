 <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Ventes - Accueil</title>
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
            text-align: center;
        }
        .menu {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .menu a {
            display: inline-block;
            padding: 15px 30px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .menu a:hover {
            background: #45a049;
        }
        .info-box {
            background: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Système de Gestion des Ventes</h1>
        
        <div class="info-box">
            <h3> Instructions pour acheter des produits :</h3>
            <ol>
                <li>Pour acheter un nouveau produit cliquer sur <strong>Nouvelle Vente.</strong></li>
                <li>Pour verifier une vente cliquer sur <strong>Rechercher Vente.</strong></li>
                <li>Consulter les informations des articles a patir de <strong>Statistiques Rapides .</strong>.</li>
            </ol>
        </div>

        <div class="menu">
            <a href="add_vente.php"> Nouvelle Vente</a>
            <a href="search_vente.php"> Rechercher Vente</a>
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <h3> Statistiques rapides</h3>
            <?php
            include 'config/database.php';
            
            try {
                // Nombre d'articles
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM Article");
                $article_count = $stmt->fetch()['total'];
                
                // Articles en stock
                $stmt = $pdo->query("SELECT COUNT(*) as dispo FROM Article WHERE qteStock > 0");
                $dispo_count = $stmt->fetch()['dispo'];
                
                // Nombre de ventes
                $stmt = $pdo->query("SELECT COUNT(*) as ventes FROM Vente");
                $vente_count = $stmt->fetch()['ventes'];
                
                echo "<p> Articles en base : <strong>$article_count</strong></p>";
                echo "<p> Articles disponibles : <strong>$dispo_count</strong></p>";
                echo "<p> Ventes enregistrées : <strong>$vente_count</strong></p>";
            } catch(PDOException $e) {
                echo "<p style='color: red;'> Connectez-vous à la base de données d'abord</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>