<?php
class Game {
    /**
     * @var Coup[]
     */
    public $coups = array();
    /**
     * @var Player[]
     */
    readonly array $joueurs;
    readonly Plateau $plate;
    private Player | null $winner;

    public function __construct(string $player1Name, string $player2Name, int $blacksStart = 0, string $piecesDir) {
        $joueurs = [
            new Player($player1Name ? $player1Name : "Whites", Player::$ColorWhites, $this),
            new Player($player2Name ? $player2Name : "Blacks", Player::$ColorBlacks, $this)
        ];
        if($blacksStart) $joueurs = array_reverse($joueurs);
        $this->joueurs = $joueurs;
        
        $this->plate = new Plateau($this, $piecesDir);
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
        $p1_moves_len = sizeof($this->joueurs[0]->getMoves());
        $p2_moves_len = sizeof($this->joueurs[1]->getMoves());

        return $this->joueurs[$p1_moves_len > $p2_moves_len];
    }
    public function getPlayer(string $color) {
        if(!(
            $color == Player::$ColorBlacks
            || $color == Player::$ColorWhites
        )) die("Invalid color.");
        
        return $this->joueurs[$color != $this->joueurs[0]->color];
    }
}