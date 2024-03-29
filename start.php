<?php
include "php/_index.php";

session_start();
if(isset($_SESSION["game"])) session_regenerate_id();
$_SESSION["game"] = new Game($_GET["white"], $_GET["black"]);

header("Location: /game.php?session=".session_id());
exit;
