<?php
namespace Labroots\Utility;

/**
 *
 * Austin Hammer (June 2015)
 * Converts odd integers to strings
 * and returns the sum of the common factors of even numbers.
 *
 *
 * Input must be integers, or decimals.
 * Even number's sum will ignore fractions.
 * Negative numbers also be ignored for even numbers
 *
 * Uses a modified Sieve of Erastonthenes algorithm
 * NOTE: REQUIRES PHP 5.4
 *
 */
class IntegerConverter
{

    static $dictionary = array(
        0 => 'zero',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'fourty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety',
        100 => 'hundred',
        1000 => 'thousand',
        1000000 => 'million',
        1000000000 => 'billion',
        1000000000000 => 'trillion',
        1000000000000000 => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );

    protected $evenInts = [];
    protected $oddInts = [];
    protected $integers = [];

    protected $translatedOdd = [];
    protected $translatedEven = [];

    public function __construct(array $integers)
    {

        // Make sure values are digits only (can be string digits and floats)
        $sanitizedInts = array_filter($integers, 'is_numeric');

        $smallest = min($sanitizedInts);
        $largest = max($sanitizedInts);

        if (abs($smallest) > 1000000000000000000 || abs($smallest) > 1000000000000000000) {
            throw new \Exception("Can only process numbers less than 1000000000000000000!");
        }
        if (abs($largest) > 1000000000000000000 || abs($largest) > 1000000000000000000) {
            throw new \Exception("Can only process numbers less than 1000000000000000000!");
        }

        $this->integers = $sanitizedInts;

        $this->execute();

    }

    public function execute()
    {
        // first split them up (odd  and  even)
        $this->splitOddAndEven();

        $this->processOdd();
        $this->processEven();
    }

    public function displayAsHTML() {
        echo "<b>Numbers Inputted:</b> ".implode(", ", $this->integers)."<br />".PHP_EOL;

        echo "<b>Odd Numbers:</b> <br />";

        echo nl2br(print_r($this->translatedOdd, true)).PHP_EOL;

        echo "<p></p><b>Even Number Sum:</b> ".$this->translatedEven.PHP_EOL;
    }

    public function getValues()
    {
        return
            [
                'odd_strings' => $this->translatedOdd,
                'even_sum' => $this->translatedEven
            ];
    }


    protected function splitOddAndEven()
    {
        foreach ($this->integers as $int) {
            if ($int & 1) {
                // odd integers
                $this->oddInts[] = floatval($int);
            } else {
                // @NOTICE: even integers that are not WHOLE NUMBERS will be discarded
                if(!is_int($int)) {
                    continue;
                }
                $this->evenInts[] = intval($int);
            }
        }
    }

    protected function translateIntToWord($integer)
    {

        switch (true) {
            // deal with negative numbers
            case $integer < 0:
                $string = 'negative ' . $this->translateIntToWord(abs($integer));

                return $string;
                break;
            // smaller numbers are easy
            case $integer < 21:
                $string = self::$dictionary[$integer];
                break;
            // hundreds get a little more complicated
            case $integer < 100:
                $tens = ((int)($integer / 10)) * 10;
                $units = $integer % 10;
                $string = self::$dictionary[$tens];
                if ($units) {
                    $string .= '-' . self::$dictionary[$units];
                }
                break;
            //
            case $integer < 1000:
                $hundreds = $integer / 100;
                $remainder = $integer % 100;
                $string = self::$dictionary[$hundreds] . ' ' . self::$dictionary[100];
                if ($remainder) {
                    $string .= ' and ' . $this->translateIntToWord($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($integer, 1000)));
                $numBaseUnits = (int)($integer / $baseUnit);
                $remainder = $integer % $baseUnit;
                $string = $this->translateIntToWord($numBaseUnits) . ' ' . self::$dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? ' and ' : ', ';
                    $string .= $this->translateIntToWord($remainder);
                }
                break;
        }


        // DEAL WITH DECIMALS/FRACTIONS
        if ($this->hasDecimal($integer)) {
            list($integer, $decimals) = explode('.', $integer);

            $decimals = strval($decimals);
            $integer = number_format($integer, 2, '.', ' ');  // Truncate if more than 3 decimals
            // if zero
            if ($decimals == 0) {
                $decimals = self::$dictionary[0];
            } // ones decimals
            elseif ($decimals > 0 && $decimals < 21) {
                $decimals = self::$dictionary[$decimals];
            } // tens decimals
            else {
                $tens = (intval($decimals / 10)) * 10;
                $units = $decimals % 10;
                if ($tens > 0) {
                    $decimals = '' . self::$dictionary[$tens];
                }
                if ($units) {
                    $decimals .= '-' . self::$dictionary[$units];
                }
            }

            $string .= ' point ' . $decimals;
        }

        return $string;

    }

    private function hasDecimal($val)
    {
        return ((float)$val !== floor($val));

    }

    private function processOdd()
    {
        // do odd numbers
        foreach ($this->oddInts as $int) {
            $this->translatedOdd[strval($int)] = $this->translateIntToWord($int);
        }
    }

    private function processEven()
    {
        $allFactors = [];

        // find all even common divsors(factors)
        foreach ($this->evenInts as $int) {
            if($int > 0) { $allFactors[] = $this->calcFactors($int); }
        }

        // remove unique values (only get common ones)
        $commonFactors = call_user_func_array('array_intersect', $allFactors);

        $this->translatedEven = array_sum($commonFactors);
    }


    // creates a simple Sieve_of_Eratosthenes
    function calcFactors($integer)
    {
        $limit = abs($integer);

        $factors = [];

        for ($i = 1; $i <= $limit; $i++)
        {
            if(0 === $limit % $i) {
                $factors[] = $i;
            }
        }

        return $factors;
    }


}

?>
