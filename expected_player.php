<?php
include ("./php/_index.php");

$session_id = $_GET["session"];
session_id($session_id);

session_start();
$game = $_SESSION["game"];
if(empty($game)) die("Invalid game id provided.");

die($game->nextPlayingPlayer()->color);