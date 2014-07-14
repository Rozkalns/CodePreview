<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Exchange model
 *
 * Used for selling/buying portal. Very simple class, mainly used for quick
 * currency convertations from one currency to other. 
 * 
 * Here I am using fresh data that are fetched from European Central Bank.
 * 
 * @todo       Store data somewhere, in that case I don't need to trouble bank server all the time.
 * 
 * @copyright  2014 Roberts Rozkalns
 * @version    Release: @1@
 * @since      Class available since Release 0.1
 */

class Exchange_Model {

    public $rates = array();

    public function __construct() {
        parent::__construct();

        $this->rates['EUR'] = "1";
        if (!$this->lvlDead()) {
            $this->rates['LVL'] = "0.702804";
        }

        $XMLContent = file("http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml");

        foreach ($XMLContent as $line) {
            if (preg_match("/currency='([[:alpha:]]+)'/", $line, $currencyCode)) {
                if (preg_match("/rate='([[:graph:]]+)'/", $line, $rate)) {
                    $this->rates[$currencyCode[1]] = $rate[1];
                }
            }
        }
    }
    
    /** The time till Latvian Lats should be displayed.
     * 
     * @return bool Do we need to display Lats or not.
     */
    public function lvlDead() {
        if (time() > strtotime("06/30/2014 23:59")) {
            return true;
        }
        return false;
    }

    /** Prepare all currencies for AJAX call
     * 
     * @return string With all currencies
     */
    public function currencies() {
        $r = join(array_keys($this->rates), ",");
        return $r;
    }

    /** Calculate the value of given data
     * 
     * @param int $ammount Ammount of money you want to exchange
     * @param string $from From which currency you want to exchange
     * @param string $to To which currency you want to exchange
     * @return double With calculated value in format x.xx 
     */
    public function value($ammount, $from, $to) {
        $r['r'] = number_format($this->rates[$to] / $this->rates[$from] * $ammount, 2, ".", "");
        return $r;
    }

}
