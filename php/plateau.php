<?php
class Plateau {
    /**
     * @var Piece[]
     */
    public $pions = array();

    public function __construct(public Game $game, private string $pieceDir = "top") {
        for($i = 0; $i <= 1; $i++) {
            $joueur = $game->getPlayer([
                Player::$ColorWhites, Player::$ColorBlacks
            ][$i]);

            // Positionnement des pions
            foreach(Position::$STARTING_POSITIONS["pion"][$i] as $pos) array_push($this->pions, new Pion($joueur, $pos, $this));
            // Positionnement des tours
            foreach(Position::$STARTING_POSITIONS["tour"][$i] as $pos) array_push($this->pions, new Tour($joueur, $pos, $this));
            // Positionnement des cavaliers
            foreach(Position::$STARTING_POSITIONS["cavalier"][$i] as $pos) array_push($this->pions, new Cavalier($joueur, $pos, $this));
            // Positionnement des fous
            foreach(Position::$STARTING_POSITIONS["fou"][$i] as $pos) array_push($this->pions, new Fou($joueur, $pos, $this));
            // Positionnement de la dame
            foreach(Position::$STARTING_POSITIONS["dame"][$i] as $pos) array_push($this->pions, new Dame($joueur, $pos, $this));
            // Positionnement du roi
            foreach(Position::$STARTING_POSITIONS["roi"][$i] as $pos) array_push($this->pions, new Roi($joueur, $pos, $this));   
        }
    }

    public function getPieceAt(Position | String $position): Piece | null {
        foreach($this->pions as $piece) {
            if($piece->position->isSame($position) && $piece->state == "alive") return $piece;
        };
        return null;
    }

    public function coupMakesCheck(Coup $coup) {
        $move = $coup->movement;
        $piece= $coup->piece;
        $eaten_piece = $coup->eaten;

        // We pre-execute the move
        if($eaten_piece) $eaten_piece->state = "eaten";
        $piece->position->move(...$move->get_directions());

        // We're checking if this move makes check
        $will_be_check = $piece->joueur->isCheck();

        // We cancel the move
        $piece->position->move(...$move->reversed()->get_directions());
        if($eaten_piece) $eaten_piece->state = "alive";
        
        // We return the result
        return $will_be_check;
    }

    /**
     * Checks if a case or a piece is contested
     * If you give a piece, the function will check if the opponent can contests this piece.
     * Else, it will check for both players if the case is contested
     * 
     * @param $forceByPlayer If you include this parameter, this function will check if the case/piece is contested by only this player
     * @return Coup[]
     */
    public function isContested(Position | Piece $contester, Player | null $forceByPlayer = null) {
        /**
         * @var Coup[]
         */
        $coups = [];

        $contestedPiece = $contester instanceof Piece ? $contester : null;
        $contestedByPlayer = $forceByPlayer ? $forceByPlayer : (
            $contestedPiece ? $contestedPiece->joueur->getOpponent() : null
        );

        $contestedPosition= $contester instanceof Piece ? $contester->position->__clone() : $contester;

        // Checking everything exept bichops
        for($dir_x = -1; $dir_x <= 1; $dir_x++) {
            for($dir_y = -1; $dir_y <= 1; $dir_y++) {
                if(!($dir_x || $dir_y)) continue;
                
                /**
                 * @var Piece|null
                 */
                $piece = null;
                $decalage = 0;
                while(!$piece) {
                    $decalage++;
                    try {
                        $mouvement = Mouvement::create($contestedPosition, $dir_x * $decalage, $dir_y * $decalage);
                    } catch (\Throwable $th) { break; }
                    
                    $piece = $this->game->plate->getPieceAt($mouvement->end_position);
                }

                if($piece && (
                    !$contestedByPlayer
                    || $piece->joueur->color == $contestedByPlayer->color
                )) {
                    $mouvement= $mouvement->reversed();
                    $coup = new Coup($mouvement, $piece);

                    if(
                        (
                            $piece instanceof Dame
                        ) || (
                            $piece instanceof Tour
                            && Tour::validerMouvement($mouvement)
                        ) || (
                            $piece instanceof Fou
                            && Fou::validerMouvement($mouvement)
                        ) || (
                            $piece instanceof Pion
                            && Pion::validerMouvement($mouvement, $piece)
                            && $piece->deplacementPossible($mouvement)
                        ) || (
                            $piece instanceof Roi
                            && Roi::validerMouvement($mouvement)
                        )
                    ) array_push($coups, $coup);
                }
            }
        }

        // Checking for bichops
        for($dir_x = -2; $dir_x <= 2; $dir_x++) {
            for($dir_y = -2; $dir_y <= 2; $dir_y++) {
                if(abs($dir_x) == abs($dir_y) || !(
                    $dir_x && $dir_y
                )) continue;
                
                try {
                    $mouvement = Mouvement::create($contestedPosition, $dir_x, $dir_y);
                } catch (\Throwable $th) { continue; }
                
                $piece = $this->game->plate->getPieceAt($mouvement->end_position);
                if(
                    $piece
                    && $piece instanceof Cavalier
                    && (
                        !$contestedByPlayer
                        || $piece->joueur->color == $contestedByPlayer->color
                    )
                ) array_push($coups, new Coup($mouvement->reversed(), $piece));
            }
        }

        return $coups;
    }

    public function __write() {
        echo "<table><tbody>";
        
        $rows = str_split(Position::$vertical_axis);

        $reverse_rows = $this->pieceDir == "white_bottom" ? false : $this->pieceDir == "white_top" || (
            (
                $this->game->nextPlayingPlayer()->color == Player::$ColorWhites
            ) == (
                $this->pieceDir == "top"
            )
        );
        if($reverse_rows) $rows = array_reverse($rows);

        $last_key = array_key_last($this->game->coups);
        $last_coup = is_null($last_key) ? null : $this->game->coups[$last_key];

        foreach($rows as $y) {
            echo "<tr data-row='{$y}'>";

            $colomns = str_split(Position::$horizontal_axis);
            if(!$reverse_rows) $colomns = array_reverse($colomns);

            foreach($colomns as $x) {
                $case = $x.$y;
                $piece= $this->getPieceAt($case);
                echo 
                "<td 
                    data-moved='".((
                        $last_coup
                        && (
                            $last_coup->movement->start_position->isSame($case)
                            || $last_coup->movement->end_position->isSame($case)
                        )
                    ) ? "yes" : "no")."'
                    data-column='$x'
                    data-line='$y'
                    data-case='$case'".
                    ($piece ? 
                        "
                        data-piece='{$piece->type}'
                        data-color='{$piece->joueur->color}'
                        "
                    : "").
                ">".(
                    $piece ? $piece->__toString() : ""
                )."</td>";
            }

            echo "</tr>";
        }
        echo "</tbody></table>";
    }

}