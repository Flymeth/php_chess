<?php
class Plateau {
    /**
     * @var Piece[]
     */
    public $pions = array();

    public function __construct(public Game $game) {
        for($i = 0; $i <= 1; $i++) {
            $joueur = $game->joueurs[$i];

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
        $eaten_piece = $coup->ate;

        // We pre-execute the move
        if($eaten_piece) $eaten_piece->state = "ate";
        $piece->position->move(...$move->get_directions());

        // We're checking if this move does a check
        $will_be_check = $piece->joueur->isCheck();

        // We cancel the move
        $piece->position->move(...$move->reversed()->get_directions());
        if($eaten_piece) $eaten_piece->state = "alive";
        
        // We return the result
        return $will_be_check;
    }

    private function isCaseExcluded(Position $case, Position ...$excludedCases) {
        if(!$excludedCases) return false;

        foreach ($excludedCases as $excluded) {
            if($case->isSame($excluded)) return true;
        };
        return false;
    }

    public function isCaseContested(Position $case, Player $byPlayer, Position ...$excludedCases) {
        // Checking everything exept bichops
        for($dir_x = -1; $dir_x <= 1; $dir_x++) {
            for($dir_y = -1; $dir_y <= 1; $dir_y++) {
                if(!($dir_x || $dir_y)) continue;
                
                $piece = null;
                $decalage = 0;
                while(!$piece) {
                    $decalage++;
                    try {
                        $mouvement = Mouvement::create($case, $dir_x * $decalage, $dir_y * $decalage);
                    } catch (\Throwable $th) { break; }
                    
                    if(
                        !$this->isCaseExcluded($mouvement->end_position, ...$excludedCases)
                    ) $piece = $this->game->plate->getPieceAt($mouvement->end_position);
                }

                if($piece && $piece->joueur->color == $byPlayer->color) {
                    $coup = new Coup($mouvement->reversed(), $piece);
                    
                    if($piece->type == "D") return $coup;
                    else if(
                        $piece->type == "T"
                        && ($dir_x == 0 || $dir_y == 0)
                    ) return $coup;
                    else if(
                        $piece->type == "F"
                        && $dir_x && $dir_y
                    ) return $coup;
                    else if(
                        $piece->type == "P"
                        && $decalage == 1
                        && $dir_x
                        && $dir_y == (
                            $byPlayer->color == "Black" ? -1 : 1
                        )
                    ) return $coup;
                    else if(
                        $piece->type == "R"
                        && $decalage == 1
                    ) return $coup;
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
                    $mouvement = Mouvement::create($case, $dir_x, $dir_y);
                } catch (\Throwable $th) { continue; }
                
                $piece = $this->game->plate->getPieceAt($mouvement->end_position);
                if(
                    $piece
                    && !$this->isCaseExcluded($piece->position, ...$excludedCases)
                    && $piece->type == "C"
                    && $piece->joueur->color == $byPlayer->color
                ) return new Coup($mouvement->reversed(), $piece);
            }
        }

        return null;
    }

    public function __toHtml() {
        echo "<table><tbody>";
        
        foreach(array_reverse(str_split(Position::$vertical_axis)) as $y) {
            echo "<tr data-row='{$y}'>";

            foreach(str_split(Position::$horizontal_axis) as $x) {
                $case = $x.$y;
                $piece= $this->getPieceAt($case);
                echo 
                "<td 
                    data-colomn='{$x}' 
                    data-case='{$case}'".
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