<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChessGuez.com</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="padding: 0 15px">

    <h1>Bienvenue sur ChessGuez.com</h1>
    <p>Concu avec 🍵 par <a href="https://johan-janin.com" target="_blank">Johan</a>.</p>

    <hr>

    <form action="/start.php" method="get">
        <h2>Setup de la partie</h2>
        <label for="white">
            Nom des blancs (commenceront la partie)
            <input type="text" name="white" id="white" placeholder="Whites">
        </label>
        <label for="black">
            Nom des noirs
            <input type="text" name="black" id="black" placeholder="Blacks">
        </label>

        <div>
            <button type="submit">Commencer la partie</button>
        </div>
    </form>

</body>
</html>