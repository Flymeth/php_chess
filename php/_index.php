<?php
include "game.php";
include "piece.php";
include "plateau.php";
include "player.php";
include "position.php";
include "mouvement.php";

// Utilities functions
function sign($n) {
    return ($n > 0) - ($n < 0);
}