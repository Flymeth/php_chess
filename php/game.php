<?php
class Game {
    /**
     * @var Coup[]
     */
    public $coups = array();
    /**
     * @var Player[]
     */
    public $joueurs = array();
    readonly Plateau $plate;
    private Player | null $winner;

    public function __construct($player1Name, $player2Name) {
        array_push($this->joueurs, 
            new Player($player1Name ? $player1Name : "Whites", Player::$ColorWhites, $this),
            new Player($player2Name ? $player2Name : "Blacks", Player::$ColorBlacks, $this)
        );
        
        $this->plate = new Plateau($this);
        $this->winner= null;
    }

    public function updateWinner() {
        $this->winner= null;

        $last_key = array_key_last($this->coups);
        if(!$last_key) return;
        $player = $this->coups[$last_key]->piece->joueur;
        if(
            $player->getOpponent()->isCheckMate()
        ) $this->winner= $player;
    }

    public function getWinner() {
        return $this->winner;
    }

    public function nextPlayingPlayer(): Player {
        $p1_moves_len = sizeof($this->joueurs[0]->moves);
        $p2_moves_len = sizeof($this->joueurs[1]->moves);

        return $p1_moves_len > $p2_moves_len ? $this->joueurs[1] : $this->joueurs[0];
    }
}