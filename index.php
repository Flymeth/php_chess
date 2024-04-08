<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChessGuez.com</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="padding: 0 15px">

    <h1>Bienvenue sur ChessGuez.com</h1>
    <p>Concu avec ❤️ par <a href="https://johan-janin.com" target="_blank">Johan</a>.</p>
    <p>Voir le projet sur <a href="https://github.com/Flymeth/php_chess" target="_blank">Github</a>.</p>
    <hr>

    <form action="/start.php" method="post">
        <h2>Setup de la partie</h2>
        <label for="white">
            Nom des blancs
            <input type="text" name="white" id="white" placeholder="Whites">
        </label>
        <label for="black">
            Nom des noirs
            <input type="text" name="black" id="black" placeholder="Blacks">
        </label>

        <label for="starters">
            Qui commecera la partie ?
            <select name="starters" id="starters">
                <option value="0">Blancs</option>
                <option value="1">Noirs</option>
            </select>
        </label>

        <label for="plate_direction">
            Direction du plateau
            <select name="pieceDirection" id="plate_direction">
                <option value="white_bottom">Blancs en haut</option>
                <option value="top" selected>Jouer vers le haut</option>
                <option value="bottom">Jouer vers le bas</option>
                <option value="white_top">Blancs en bas</option>
            </select>
        </label>

        <div>
            <button type="submit">Commencer la partie</button>
        </div>
    </form>
    <?php if(!isset($_GET["clean"])): ?>
        <a href="/reset.php">Supprimer les données de parties</a>
    <?php endif ?>
</body>
</html>