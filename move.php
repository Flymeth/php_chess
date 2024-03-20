<?php
include ("./php/_index.php");

$session_id = $_GET["session"];
session_id($session_id);

session_start();
$game = $_SESSION["game"];
if(empty($game)) die("Invalid game id provided.");

$data = json_decode($_GET["data"]);
$case_from = new Position($data->from);
$case_to = new Position($data->to);
$mouvement = new Mouvement($case_from, $case_to);

$piece= $game->plate->getPieceAt($case_from);
if(empty($piece)) die("Invalid piece selected.");

$expectedPieceColor = $game->nextPlayingPlayer()->color;
if($piece->joueur->color != $expectedPieceColor) die("This is not this player's turn.");
$piece->execMouvement($mouvement);

die("true");