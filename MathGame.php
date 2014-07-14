<?php
/**
 * Math memory game
 *
 * This memory game I made for my doughter that started to attend the school.
 * She had some difficulties with elementary counting and substracting, so
 * I decided to make a memory game for her to play. You can randomly generate 
 * tiles, print them out, slice up, and start playing. The idea is to match 
 * formula with the result. The result of this game is suspected to be that 
 * doughter could remember these easy formulas by haert and she doesn't need to
 * calculate them in hand as adults do. Who does still counting on fingers, how 
 * many is 4 + 3 ? Of course, it's 7.
 *
 * @copyright  2013 Roberts Rozkalns
 * @version    Release: @1@
 * @since      Class available since Release 1.0
 */
class Game {

    /**
     * Number of examples, two tiles per example
     * @var int
     */
    public $examples;

    /**
     * Smallest number to make calculations
     * @var int
     */
    public $nlow;

    /**
     * Largest number to make calculations
     * @var int
     */
    public $nhig;

    /**
     * Smallest result
     * @var int
     */
    public $rlow;

    /**
     * Larges result 
     * @var int
     */
    public $rhig;

    /**
     * Store generated cards
     * @var array
     */
    public $cards = array();

    /**
     * Store generated results to avoid result overlaping 
     * @var array
     */
    public $usedResults = array();

    /**
     * Fill array with signs you want to use
     * 
     * @todo make available multiplication and division 
     * @var array
     */
    public $signs;
    
    public function __construct() {

        $this->examples = 12;
        $this->nlow = 1;
        $this->nhig = 10;
        $this->rlow = 0;
        $this->rhig = 12;
        $this->signs = array('+', '-');

        $this->generateCards();
        $this->display();
    }

    /**
     * Generate array of cards
     *
     */
    public function generateCards() {
        $n = 0;

        while ($n < $this->examples) {

            // Generate random set of numbers
            $r1 = rand($this->nlow, $this->nhig);
            $r2 = rand($this->nlow, $this->nhig);

            // Randomly pick the sign
            $sign = rand(0,1);
            $s = $this->signs[$sign];
            
            // Make result of generated set of numbers
            switch ($s) {
                case "+" : $r = $r1 + $r2;
                    break;
                case "-" : $r = $r1 - $r2;
                    break;
            }

            // Check if the number fits in bounds and if already this result exist
            if ($r >= $this->rlow && $r <= $this->rhig && !in_array($r, $this->usedResults)) {
                $this->cards[] = array("formula" => $r1 . $s . $r2, "result" => $r);
                $n++;
            }
            
            // Fill array with used results
            $this->usedResults[] = $r;
        }

        return;
    }

    /**
     * Display cards for printing
     *
     */
    public function display() {

        $r = "<style type = \"text/css\">";
        $r .= $this->style();
        $r .= "</style>";

        foreach ($this->cards as $card) {
            $r .= "<div class=\"card\"><div class=\"center\">" . $card['formula'] . "</div></div>";
            $r .= "<div class=\"card\"><div class=\"center\">" . $card['result'] . "</div></div>";
        }

        echo $r;
    }

    /**
     * Style of cards
     *
     */
    public function style() {
        $r = ".card {";
        $r .= "font-size: 3em;";
        $r .= "text-align: center;";
        $r .= "border: 1px solid #fab;";
        $r .= "width: 145px;";
        $r .= "height: 145px;";
        $r .= "display: inline-block;";
        $r .= "}";

        $r .= ".card:before {";
        $r .= "content: '';";
        $r .= "display: inline-block;";
        $r .= "height: 100%;";
        $r .= "vertical-align: middle;";
        $r .= "}";

        $r .= ".center {";
        $r .= "display: inline-block;";
        $r .= "vertical-align: middle;";
        $r .= "}";

        return $r;
    }
}

$game = new Game();