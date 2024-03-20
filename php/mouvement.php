<?php
class Mouvement {
    public static function create(Position $current_position, Int $dx, Int $dy) {
        $p2 = new Position($current_position->__toString());
        $p2->move($dx, $dy);
        return new Mouvement($current_position, $p2);
    }
    public function __construct(readonly Position $start_position, readonly Position $end_position) {
            $dir = $this->get_directions();
        if($dir[0] == 0 && $dir[1] == 0) die("Empty movement given");
    }

    public function get_directions() {
        $p1_x = $this->start_position->get_x();
        $p1_y = $this->start_position->get_y();

        $p2_x = $this->end_position->get_x();
        $p2_y = $this->end_position->get_y();

        return array($p2_x - $p1_x, $p2_y - $p1_y);
    }

    /**
     * Does "-1 * <direction>"
     */
    public function inverted() {
        $dirs = $this->get_directions();
        return Mouvement::create(
            $this->start_position,
            -$dirs[0], -$dirs[1]
        );
    }

    /**
     * Does the movement from the end to the start
     */
    public function reversed() {
        return new Mouvement($this->end_position, $this->start_position);
    }

    public function __toString() {
        return $this->start_position->__toString()."-".$this->end_position->__toString();
    }
}
class Coup {
    readonly Piece | null $ate;
    public function __construct(readonly Mouvement $movement, readonly Piece $piece) {
        $this->ate = $piece->plateau->getPieceAt($movement->end_position);
    }

    public function __toString() {
        return $this->piece.$this->movement;
    }
}