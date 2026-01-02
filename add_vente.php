<?php
session_start();
include 'config/database.php';

// Variables pour gérer les étapes
$etape = isset($_GET['etape']) ? $_GET['etape'] : 1;
$article_trouve = null;
$message = '';

// Étape 1: Recherche d'article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rechercher'])) {
    $code = $_POST['code_article'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM Article WHERE code = ?");
        $stmt->execute([$code]);
        $article = $stmt->fetch();
        
        if ($article) {
            if ($article['qteStock'] > 0) {
                $_SESSION['article_courant'] = $article;
                $etape = 2;
                $article_trouve = $article;
            } else {
                $message = "<div class='error'> Cet article n'est plus en stock !</div>";
            }
        } else {
            $message = "<div class='error'> Aucun article trouvé avec ce code</div>";
        }
    } catch(PDOException $e) {
        $message = "<div class='error'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// Étape 2: Saisie de la quantité
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_panier'])) {
    $qte = (int)$_POST['quantite'];
    $article = $_SESSION['article_courant'];
    
    if ($qte > 0 && $qte <= $article['qteStock']) {
        // Initialiser le panier s'il n'existe pas
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        
        // Ajouter l'article au panier
        $_SESSION['panier'][] = [
            'article' => $article,
            'quantite' => $qte
        ];
        
        $message = "<div class='success'> Article ajouté au panier !</div>";
        $etape = 1; // Retour à la recherche
        unset($_SESSION['article_courant']);
    } else {
        $message = "<div class='error'> Quantité invalide. Stock disponible : " . $article['qteStock'] . "</div>";
        $article_trouve = $article;
    }
}

// Vider le panier
if (isset($_GET['vider'])) {
    unset($_SESSION['panier']);
    $message = "<div class='info'> Panier vidé</div>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Vente</title>
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
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .etapes {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            counter-reset: etape;
        }
        .etape {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #e0e0e0;
            margin: 0 5px;
            border-radius: 5px;
            position: relative;
            font-weight: bold;
        }
        .etape.active {
            background: #4CAF50;
            color: white;
        }
        .etape:before {
            counter-increment: etape;
            content: counter(etape);
            background: white;
            color: #333;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        .etape.active:before {
            background: #2E7D32;
            color: white;
        }
        .form-section {
            display: <?php echo $etape == 1 ? 'block' : 'none'; ?>;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 20px 0;
        }
        .form-section.active {
            display: block;
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
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button, .btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover {
            background: #45a049;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #c62828;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #2e7d32;
        }
        .info {
            background: #e3f2fd;
            color: #1565c0;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #1565c0;
        }
        .article-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .panier {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f2f2f2;
        }
        .actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Nouvelle Vente</h1>
        
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="etapes">
            <div class="etape <?php echo $etape == 1 ? 'active' : ''; ?>">Recherche Article</div>
            <div class="etape <?php echo $etape == 2 ? 'active' : ''; ?>">Saisie Quantité</div>
            <div class="etape <?php echo ($etape == 3 || isset($_SESSION['panier'])) ? 'active' : ''; ?>">Informations Vente</div>
        </div>
        
        <!-- Étape 1: Recherche d'article -->
        <div id="etape1" class="form-section <?php echo $etape == 1 ? 'active' : ''; ?>">
            <h2> Rechercher un article</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="code_article">Code de l'article :</label>
                    <input type="text" id="code_article" name="code_article" 
                           required placeholder="Ex: ART001" value="<?php echo isset($_POST['code_article']) ? htmlspecialchars($_POST['code_article']) : ''; ?>">
                </div>
                <button type="submit" name="rechercher">Rechercher</button>
            </form>
        </div>
        
        <!-- Étape 2: Saisie quantité (visible si article trouvé) -->
        <?php if ($etape == 2 && $article_trouve): ?>
        <div id="etape2" class="form-section active">
            <h2> Saisie de la quantité</h2>
            
            <div class="article-info">
                <h3>Article trouvé :</h3>
                <p><strong>Code :</strong> <?php echo htmlspecialchars($article_trouve['code']); ?></p>
                <p><strong>Nom :</strong> <?php echo htmlspecialchars($article_trouve['nom']); ?></p>
                <p><strong>Description :</strong> <?php echo htmlspecialchars($article_trouve['description']); ?></p>
                <p><strong>Stock disponible :</strong> <span style="color: #4CAF50; font-weight: bold;">
                    <?php echo $article_trouve['qteStock']; ?> unités</span></p>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="quantite">Quantité à vendre :</label>
                    <input type="number" id="quantite" name="quantite" 
                           min="1" max="<?php echo $article_trouve['qteStock']; ?>" 
                           required value="1">
                    <small>Maximum : <?php echo $article_trouve['qteStock']; ?> unités</small>
                </div>
                
                <div class="actions">
                    <a href="add_vente.php?etape=1" class="btn" style="background: #757575;">← Retour</a>
                    <button type="submit" name="ajouter_panier">Ajouter au panier</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Étape 3: Panier et informations vente -->
        <?php if (isset($_SESSION['panier']) && count($_SESSION['panier']) > 0): ?>
        <div id="etape3" class="form-section active">
            <h2> Votre Panier</h2>
            
            <div class="panier">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_general = 0;
                        foreach ($_SESSION['panier'] as $item):
                            $sous_total = $item['quantite'] * 100; // Prix fictif
                            $total_general += $sous_total;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['article']['code']); ?></td>
                            <td><?php echo htmlspecialchars($item['article']['nom']); ?></td>
                            <td>100 €</td>
                            <td><?php echo $item['quantite']; ?></td>
                            <td><?php echo $sous_total; ?> €</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h3 style="text-align: right;">Total : <?php echo $total_general; ?> €</h3>
                
                <div style="text-align: center; margin: 20px 0;">
                    <a href="add_vente.php?etape=1&vider=1" class="btn" style="background: #f44336;">Vider le panier</a>
                    <a href="add_vente.php?etape=3" class="btn" style="background: #2196F3;">Continuer les achats</a>
                </div>
            </div>
            
            <h2> Informations de la vente</h2>
            <form method="POST" action="process_vente.php">
                <div class="form-group">
                    <label for="numero">Numéro de vente :</label>
                    <input type="text" id="numero" name="numero" 
                           value="VTE-<?php echo date('Ymd-His'); ?>" readonly required>
                </div>
                
                <div class="form-group">
                    <label for="date">Date :</label>
                    <input type="date" id="date" name="date" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nom">Nom du client :</label>
                    <input type="text" id="nom" name="nom" 
                           placeholder="Ex: Mor Khoudia" required>
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse :</label>
                    <textarea id="adresse" name="adresse" rows="3" required></textarea>
                </div>
                
                <input type="hidden" name="panier_data" value="<?php echo htmlspecialchars(json_encode($_SESSION['panier'])); ?>">
                
                <div class="actions">
                    <a href="add_vente.php?etape=1" class="btn" style="background: #757575;">← Retour</a>
                    <button type="submit" style="background: #2196F5;">Finaliser la vente</button>
                </div>
            </form>
        </div>
        <?php elseif ($etape == 3): ?>
            <div class="info">
                <p>Votre panier est vide. Veuillez d'abord ajouter des articles.</p>
                <a href="add_vente.php?etape=1" class="btn">Ajouter un article</a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php">← Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>