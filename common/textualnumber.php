<?php
/**
* TextualNumber is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* TextualNumber is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with TextualNumber; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
* © Copyright 2004 Richard Heyes
*/

/**
* Converts a given number to a textual representation of it
*/
class TextualNumber
{
    private static $units  = array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');
    private static $teens  = array('ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen');
    private static $tens   = array(2 => 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety');
    private static $suffix = array('thousand', 'million', 'billion', 'trillion', 'quadrillion');

    /**
    * Returns appropriate text for given number. Despite
    * appearances the number should (though isn't required) be
    * passed in as a string. This allows large numbers to be
    * converted. Integers are handled just fine though.
    * Negative numbers are handled fine.
    *
    * The billion/trillion etc suffixes are done using the American
    * style (eg 9 zeros for billion, 12 for trillion), which albeit
    * being mathmetically incorrect, is (I believe) the commonly
    * accepted norm. Since this code was written for the purpose
    * of validating human vs spider form submissions, this is the
    * more appropriate way to go.
    *
    * @param  string $int The number to convert. Optional.
    * @return text        Resulting text
    */
    private static function ToString($int)
    {
        // Check for purely numeric chars
        if (!preg_match('#^[\d.]+$#', $int)) {
            throw new Exception('Invalid characters in input');
        }

        // Handle decimals
        if (strpos($int, '.') !== false) {
            $decimal = substr($int, strpos($int, '.') + 1);
            $int     = substr($int, 0, strpos($int, '.'));
        }

        // Lose insignificant zeros
        $int = ltrim($int, '0');

        // Check for valid number
        if ($int == '') {
            $int = '0';
        }


        // Lose the negative, don't use abs() so as to allow large numbers
        if ($negative = ($int < 0)) {
            $int = substr($int, 1);
        }

        // Number too big?
        if (strlen($int) > 18) {
            throw new Exception('Out of range');
        }

        // Keep original number
        $orig = $int;

        /**
        * Main number deciphering bit thing
        */
        switch (strlen($int)) {

            // Single digit number
            case '1':
                $text = self::$units[$int];
                break;


            // Two digit number
            case '2':
                if ($int{0} == '1') {
                    $text = self::$teens[$int{1}];

                } else if ($int{1} == '0') {
                    $text = self::$tens[$int{0}];

                } else {
                    $text = self::$tens[$int{0}] . '-' . self::$units[$int{1}];
                }
                break;


            // Three digit number
            case '3':
                if ($int % 100 == 0) {
                    $text = self::$units[$int{0}] . ' hundred';
                } else {
                    $text = self::$units[$int{0}] . ' hundred and ' . self::GetText(substr($int, 1));
                }
                break;


            // Anything else
            default:
                $pieces      = array();
                $suffixIndex = 0;

                // Handle the last three digits
                $num = substr($int, -3);
                if ($num > 0) {
                    $pieces[] = self::GetText($num);
                }
                $int = substr($int, 0, -3);

                // Now handle the thousands/millions etc
                while (strlen($int) > 3) {
                    $num   = substr($int, -3);

                    if ($num > 0) {
                        $pieces[] = self::GetText($num) . ' ' . self::$suffix[$suffixIndex];
                    }
                    $int = substr($int, 0, -3);
                    $suffixIndex++;
                }

                $pieces[] = self::GetText(substr($int, -3)) . ' ' . self::$suffix[$suffixIndex];

                /**
                * Figure out whether we need to add "and" in there somewhere
                */
                $pieces = array_reverse($pieces);

                if (count($pieces) > 1 AND strpos($pieces[count($pieces) - 1], ' and ') === false) {
                    $pieces[] = $pieces[count($pieces) - 1];
                    $pieces[count($pieces) - 2] = 'and';
                }

                // Create the text
                $text = implode(' ', $pieces);

                // Negative number?
                if ($negative) {
                    $text = 'minus ' . $text;
                }
                break;
        }

        /**
        * Handle any decimal part
        */
        if (!empty($decimal)) {
            $pieces  = array();
            $decimal = preg_replace('#[^0-9]#', '', $decimal);

            for ($i=0, $len=strlen($decimal); $i<$len; ++$i) {
                $pieces[] = self::$units[$decimal{$i}];
            }

            $text .= ' point ' . implode(' ', $pieces);
        }


        return $text;
    }


    /**
    * Returns text for given number. Parameter should ideally
    * be a string (to handle large numbers) though integers are
    * OK.
    *
    * @param  string $int Number to convert
    * @return string      Resulting textual representation
    */
    public function GetText($int)
    {
        return self::ToString($int);
    }


    /**
    * Returns text and number for a randomly generated number.
    *
    * @return array Array of number and textual representation
    */
    public function Get()
    {
        $int = mt_rand();
        return array($int, self::ToString($int));
    }


    /**
    * Returns currency version of a given number.
    *
    * @param  string $int   Number to convert
    * @param  string $major Word to use for left hand side of decimal point
    * @param  string $minor Word to use for right hand side of decimal point
    * @return string        Resulting string
    */
    public function GetCurrency($int, $major = 'pound', $minor = 'pence')
    {
        if (strpos($int, '.') !== false) {
            $left  = substr($int, 0, strpos($int, '.'));
            $right = substr($int, strpos($int, '.') + 1);

            // Plural $major ?
            if ((int)abs($left) != 1) {
                $major .= 's';
            }

            $text  = self::GetText($left) . " $major and " . self::GetText($right) . " $minor";

        } else {
            $text = self::GetText($int) . " $major";
        }

        return $text;
    }
}
?>