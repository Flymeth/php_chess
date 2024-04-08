<?php
include "php/_index.php";

$session_id = $_GET["session"];
session_id($session_id);

session_start();
if(!isset($_SESSION["game"])) die("Invalid game id provided.");
/**
 * @var Game
 */
$game = $_SESSION["game"];
$winner = $game->getWinner();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partie <?php echo $session_id ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/game.css">
</head>
<body data-winner="<?php echo $winner?->pseudo ?>">
    <div id="loader"></div>
    <main>
        <div class="left">
            <section>
                <h1>Echecs - <?php echo $game->joueurs[0]->__toHtml()." contre ".$game->joueurs[1]->__toHtml() ?></h1>
                <?php if($winner): ?>
                    <div class="game_ended">
                        <h2>Partie terminé!</h2>

                        <p><?php echo $winner->__toHtml() ?> remporte la partie par echec et mat!</p>
                    </div>
                <?php endif ?>

                <button id="invite">Invite ton ami</button>
                <a href="/">
                    <button id="quit">Quitter la partie</button>
                </a>
            </section>
            <section>
                <h2>Liste des coups:</h2>
                <ol class="coups">
                    <?php
                        foreach(array_reverse($game->coups) as $coup) {
                            echo "<li>".$coup->__toString()."</li>";
                        }
                    ?>
                </ol>
            </section>
            <span id="message"></span>
        </div>
        <div class="right">
            <section id="game">
                <section class="eaten_piece" id="eaten_white_pieces">
                    <ul>
                        <?php 
                            foreach($game->getPlayer(Player::$ColorWhites)->getPiecesWithState("eaten") as $piece) {
                                echo "<li>".$piece->__toString()."</li>";
                            }
                        ?>
                    </ul>
                </section>

                <?php echo $game->plate->__write() ?>

                <section class="eaten_piece" id="eaten_black_pieces">
                    <ul>
                        <?php 
                            foreach($game->getPlayer(Player::$ColorBlacks)->getPiecesWithState("eaten") as $piece) {
                                echo "<li>".$piece->__toString()."</li>";
                            }
                        ?>
                    </ul>
                </section>
            </section>
        </div>
    </main>

    <?php if(!$winner): ?>
        <script src="./js/game.js"></script>
    <?php endif ?>
</body>
</html>
