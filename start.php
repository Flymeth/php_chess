<?php
include "php/_index.php";

session_start();
if(isset($_SESSION["game"])) session_regenerate_id();
$_SESSION["game"] = new Game($_POST["white"], $_POST["black"], $_POST["starters"] == "1", $_POST["pieceDirection"]);

header("Location: /game.php?session=".session_id());
exit;
