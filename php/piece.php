<?php
class Piece {
    public $state= "alive";
    public function __construct(readonly String $type = "", readonly Player $joueur, readonly Position $position, readonly Plateau $plateau) {
        array_push($joueur->pions, $this);
    }

    protected function move(Mouvement $mouvement) {
        $this->plateau->game->updateWinner();
        if($this->plateau->game->getWinner()) die("The game has ended.");

        $piece_at_final_case = $this->plateau->getPieceAt($mouvement->end_position);
        if($piece_at_final_case) {
            if($piece_at_final_case->joueur->color == $this->joueur->color) die("You cannot eat one of your piece.");
            $piece_at_final_case->state = "ate";
        }
        $this->position->move(...$mouvement->get_directions());

        if($this->joueur->isCheck()) {
            $this->position->move(...$mouvement->reversed()->get_directions());
            if($piece_at_final_case) $piece_at_final_case->state = "alive";

            $this->plateau->game->updateWinner();
            die("Invalid move: you're in check!");
        }

        array_push($this->joueur->moves, $mouvement);
        array_push(
            $this->plateau->game->coups, 
            new Coup($mouvement, $this)
        );
        $this->plateau->game->updateWinner();
    }

    protected function isJumpingOverPieces(Mouvement $movement) {
        $dirs = $movement->get_directions();
        
        $dir_x = sign($dirs[0]);
        $dir_y = sign($dirs[1]);
        $current_position = $movement->start_position->__clone();
        $current_position->move($dir_x, $dir_y);

        while(!$current_position->isSame($movement->end_position)) {
            if($this->plateau->getPieceAt($current_position)) return true;
            $current_position->move($dir_x, $dir_y);
        };

        return false;
    }

    public function __toString() {
        return "".$this->state."".$this->joueur;
    }
}

class Pion extends Piece {
    private bool $hasMoved = false;
    public function __construct(Player $joueur, Position | String $position, Plateau $plateau) {
        parent::__construct(
            "P",
            $joueur, is_string($position) ? new Position($position) : $position,
            $plateau
        );
    }

    static function validerMouvement(Mouvement $mouvement, Pion $pion) {
        $dir = $mouvement->get_directions();
        $y_direction = $pion->joueur->color == "Black" ? -1 : 1;
        if($dir[1] == 2 * $y_direction && !$pion->hasMoved) return $dir[0] == 0;
        if($dir[1] != $y_direction) return false;

        return (
            $dir[0] >= -1 && $dir[0] <= 1
        );
    }
    public function execMouvement(Mouvement $mouvement) {
        if(!Pion::validerMouvement($mouvement, $this)) die("Invalid movement");
        $dirs = $mouvement->get_directions();
        $piece = $this->plateau->getPieceAt($mouvement->end_position);
        if(!$dirs[0] && $piece) die("Cannot move forward for now.");
        if($dirs[0] && !$piece) die("Invalid movement: there is no piece to eat.");
        $this->move($mouvement);
        $this->hasMoved = true;

        // Check if the piece as to transform to queen
        $transform_y = $this->joueur->color == "Black" ? 0 : strlen(Position::$vertical_axis) -1;
        if($mouvement->end_position->get_y() == $transform_y) {
            $new_queen = new Dame($this->joueur, $this->position, $this->plateau, $this);

            $index_plateau = array_search($this, $this->plateau->pions, true);
            array_splice($this->plateau->pions, $index_plateau, 1);
            $index_joueur = array_search($this, $this->joueur->pions, true);
            array_splice($this->joueur->pions, $index_joueur, 1);


            array_push($this->plateau->pions, $new_queen);
        }
    }

    public function __toString() {
        return $this->joueur->color == "White" ? "♙" : "♟";
    }
}
class Tour extends Piece {
    public function __construct(Player $joueur, Position | String $position, Plateau $plateau) {
        parent::__construct(
            "T", 
            $joueur, 
            is_string($position) ? new Position($position) : $position,
            $plateau
        );
    }

    static function validerMouvement(Mouvement $mouvement) {
        $dir = $mouvement->get_directions();
        return $dir[0] == 0 || $dir[1] == 0;
    }

    public function execMouvement(Mouvement $mouvement) {
        if(!Tour::validerMouvement($mouvement)) die("Invalid mouvement");
        if($this->isJumpingOverPieces($mouvement)) die("Cannot jump over pieces!");
        $this->move($mouvement);
    }

    public function __toString() {
        return $this->joueur->color == "White" ? "♖" : "♜";
    }
}
class Cavalier extends Piece {
    public function __construct(Player $joueur, Position | String $position, Plateau $plateau) {
        parent::__construct(
            "C", 
            $joueur, 
            is_string($position) ? new Position($position) : $position, 
            $plateau
        );
    }

    static function validerMouvement(Mouvement $mouvement) {
        $dir = $mouvement->get_directions();
        if($dir[1] == 0) return false;

        $normalized = abs($dir[0] / $dir[1]);
        return $normalized == .5 || $normalized == 2;
    }

    public function execMouvement(Mouvement $mouvement) {
        if(!Cavalier::validerMouvement($mouvement)) die("Invalid mouvement");
        $this->move($mouvement);
    }

    public function __toString() {
        return $this->joueur->color == "White" ? "♘" : "♞";
    }
}
class Fou extends Piece {
    public function __construct(Player $joueur, Position | String $position, Plateau $plateau) {
        parent::__construct(
            "F", 
            $joueur, 
            is_string($position) ? new Position($position) : $position,
            $plateau
        );
    }
    static function validerMouvement(Mouvement $mouvement) {
        $dir = $mouvement->get_directions();
        if($dir[1] == 0) return false;

        $normalized = abs($dir[0] / $dir[1]);
        return $normalized == 1;
    }

    public function execMouvement(Mouvement $mouvement) {
        if(!Fou::validerMouvement($mouvement)) die("Invalid mouvement");
        if($this->isJumpingOverPieces($mouvement)) die("Cannot jump over pieces!");
        $this->move($mouvement);
    }

    public function __toString() {
        return $this->joueur->color == "White" ? "♗" : "♝";
    }
}
class Dame extends Piece {
    public function __construct(Player $joueur, Position | String $position, Plateau $plateau, readonly Pion | null $from_pion = null) {
        parent::__construct(
            "D", 
            $joueur, 
            is_string($position) ? new Position($position) : $position,
            $plateau
        );
    }

    static function validerMouvement(Mouvement $mouvement) {
        $dir = $mouvement->get_directions();
        if(
            $dir[0] == 0
            || $dir[1] == 0
        ) return true;

        $normalized = abs($dir[0] / $dir[1]);
        return $normalized == 1;
    }

    public function execMouvement(Mouvement $mouvement) {
        if(!Dame::validerMouvement($mouvement)) die("Invalid mouvement");
        if($this->isJumpingOverPieces($mouvement)) die("Cannot jump over pieces!");
        $this->move($mouvement);
    }

    public function __toString() {
        return $this->joueur->color == "White" ? "♕" : "♛";
    }
}
class Roi extends Piece {
    public function __construct(Player $joueur, Position | String $position, Plateau $plateau) {
        parent::__construct(
            "R", 
            $joueur, 
            is_string($position) ? new Position($position) : $position,
            $plateau
        );
    }

    static function validerMouvement(Mouvement $mouvement) {
        $dir = $mouvement->get_directions();
        return (
            $dir[0] >= -1 && $dir[0] <= 1
            && $dir[1] >= -1 && $dir[1] <= 1
        );
    }

    public function execMouvement(Mouvement $mouvement) {
        if(!Roi::validerMouvement($mouvement)) die("Invalid mouvement");
        $this->move($mouvement);
    }

    public function __toString() {
        return $this->joueur->color == "White" ? "♔" : "♚";
    }
}