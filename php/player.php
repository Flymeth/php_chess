<?php
class Player {
    public $pions= array();
    public $moves= array();

    public function __construct(readonly string $pseudo, readonly string $color, readonly Game $game) {

    }

    /**
     * @return Piece[]
     */
    public function getPiecesWithType(string $type): array {
        $pieces = array();
        foreach($this->pions as $pion) {
            if($pion->type == $type) {
                array_push($pieces, $pion);
            }
        }
        return $pieces;
    }

    public function getOpponent() {
        return $this->game->joueurs[
            $this->color == $this->game->joueurs[0]->color
        ];
    }

    public function isCheck() {
        $king = $this->getPiecesWithType("R")[0];
        if(!$king) die("Error: The king is missing!");
        return !!sizeof($this->game->plate->isContested($king));
    }

    public function isCheckMate() {
        if(!$this->isCheck()) return false;

        // We check if the king can move
        $king = $this->getPiecesWithType("R")[0];
        if(!$king) die("Error: The king is missing!");
        for($dir_x = -1; $dir_x <= 1; $dir_x++) {
            for($dir_y = -1; $dir_y <= 1; $dir_y++) {
                if($dir_x == 0 && $dir_y == 0) continue;

                $case = $king->position->__clone();
                try {
                    $case->move($dir_x, $dir_y);
                } catch (\Throwable $th) { continue; }
                
                $pieceOnTheCase = $this->game->plate->getPieceAt($case);
                if(
                    $pieceOnTheCase
                    && $pieceOnTheCase->joueur->color == $this->color
                ) continue;

                $caseContested= $this->game->plate->isContested($case, $king->joueur->getOpponent());
                if(!sizeof($caseContested)) return false;
            }
        }

        // Checking if the moved piece can be eaten
        $last_key = array_key_last($this->game->coups);
        if(!$last_key) die("There is no last move");
        $piece = $this->game->coups[$last_key]->piece;
        // We assume that the moved piece is the other player than this one
        // We assume that if this is false, the game is valid bc it has already checked the check mate
        if($piece->joueur->color == $this->color) return false;
        
        /**
         * @var Position[]
         */
        $casesToCheck = [];
        if($piece->type == "C" || $piece->type == "P") {
            array_push($casesToCheck, $piece->position);
        }else {
            $movement = new Mouvement($piece->position, $king->position);
            [$dir_x, $dir_y] = $movement->get_directions();
            [$sign_x, $sign_y]= [sign($dir_x), sign($dir_y)];

            $current_position= $piece->position->__clone();
            while(!$current_position->isSame($king->position)) {
                array_push($casesToCheck, $current_position->__clone());
                $current_position->move($sign_x, $sign_y);
            }
        }

        foreach ($casesToCheck as $case_position) {
            $coups = $this->game->plate->isContested($case_position, $king->joueur);
            foreach ($coups as $coup) {
                if(
                    !$this->game->plate->coupMakesCheck($coup)
                ) return false;
            }
        }
        
        return true;
    }

    public function __toHtml() {
        $winner = $this->game->getWinner();
        $underline = !$winner && $this->color == $this->game->nextPlayingPlayer()->color;
        
        $king_piece = $this->getPiecesWithType("R")[0];
        $name = $king_piece." ".$this->pseudo;
        
        if($underline) return "<u>$name</u>";
        else return $name;
    }
}