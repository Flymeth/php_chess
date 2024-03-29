<?php
class Position {
    static $horizontal_axis = "abcdefgh";
    static $vertical_axis = "12345678";
    static $STARTING_POSITIONS = array(
        "roi" => array(array("e1"), array("e8")),
        "dame" => array(array("d1"), array("d8")),
        "fou" => array(array("c1", "f1"), array("c8", "f8")),
        "cavalier" => array(array("b1", "g1"), array("b8", "g8")),
        "tour" => array(array("a1", "h1"), array("a8", "h8")),
        "pion" => array(
            array("a2", "b2", "c2", "d2", "e2", "f2", "g2", "h2"),
            array("a7", "b7", "c7", "d7", "e7", "f7", "g7", "h7"),
        ),
    );
    public function __construct(private $position = "a1") {
        if(
            $this->get_x() === false
            || $this->get_y() === false
        ) throw new ValueError("$position is not a valid position.");
    }
    static function fromCoordonate(int $x, int $y) {
        $horizontal = Position::$horizontal_axis[$x];
        $vertical = Position::$vertical_axis[$y];
        return new Position($horizontal.$vertical);
    }


    public function get_x() { 
        return strpos(
            $this::$horizontal_axis,
            substr($this->position, 0, 1)
        );
    }
    public function get_y() {
        return strpos(
            $this::$vertical_axis,
            substr($this->position, 1, 1)
        );
    }

    public function move(Int $dir_x, Int $dir_y) {
        $new_x = $this->get_x() + $dir_x;
        $new_y = $this->get_y() + $dir_y;

        if(
            $new_x < 0
            || $new_x >= strlen($this::$horizontal_axis)
        ) throw new OverflowException("New x is out of bounds.");
        if(
            $new_y < 0
            || $new_y >= strlen($this::$vertical_axis)
        ) throw new OverflowException("New y is out of bounds.");

        $this->position=
            substr($this::$horizontal_axis, $new_x, 1)
            . substr($this::$vertical_axis, $new_y, 1)
        ;
    }
    public function __clone() {
        return new Position($this->position);
    }
    public function __toString() {
        return $this->position;
    }
    public function isSame(String | Position $position) {
        if(!is_string($position)) $position = $position->__toString();

        return $this->position == $position;
    }
}