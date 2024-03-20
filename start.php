<?php
include "php/_index.php";

session_start();
$_SESSION["game"] = new Game($_GET["white"], $_GET["black"]);

header("Location: /game.php?session=".session_id());
exit;