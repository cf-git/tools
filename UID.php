<?php
/**
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Copyright (c) 2017, Ukraine, Shubin Sergei
 *
 * Unique id static class
 * @package      : cf-git/tools
 * @author       : is.captain.fail@gmail.com
 * @user         : CF
 * @license      : http://opensource.org/licenses/MIT   MIT License
 */
namespace CF\Tools;

class UID
{
    /**
     * Simple uid generator
     */
    public static function simple($length = 25)
    {
        $s = "qwertyuiopasdfghjklzxcvbnm-_147852369";
        $r = "";
        for ($i = 0; $i < $length; $i++) {
            switch ($i) {
                case 0:
                    do {
                        $c = str_shuffle($s)[rand(0, 36)];
                    } while (strpos('_-', $c) !== false);
                    break;
                case $length-1:
                    do {
                        $c = str_shuffle($s)[rand(0, 36)];
                    } while (strpos('_-', $c) !== false);
                    break;
                default:
                    $c = str_shuffle($s)[rand(0, 36)];
                    break;
            }
            $r.=$c;
        }
        return $r;
    }

    /**
     * Special uid generator by templatestring
     * template can use
     * n - numbers
     * s - special symbols kit for random getting symbols
     * S - special symbols kit for random getting symbols
     * l - lower case letters
     * L - upper case letters
     * x - numbers and lower case letters
     * X - numbers and upper case letters
     * A - numbers and letters all case
     * \ - chars escaping, next char used like char
     * other symbols can be used like chars to use templater sings, need use escape symbol
     * @param $template string - some like "nxx[nnn]lll-nn-\xxxx" equal "n2x[3n]3l-2n-\x3x"
     *                                  or "xxxxx" equal "5x"
     * @param $special  string - special simbols kit which can be used for random selection
     * @result          string - random string by template params
     * @return string
     */
    public static function complex($template = 'xxxxx', $special = NULL)
    {
        $numbers = '0123456789';
        $letters = 'qwertyuiopasdfghjklzxcvbnm';
        $uLetters = strtoupper($letters);
        $x = $numbers.$letters;
        $X = $numbers.$uLetters;
        $A = $x.$uLetters;
        if (is_null($special)) {
            $special = $A;
        }
        $result = '';
        for ($i = 0, $length = strlen($template); $i < $length; $i++) {
            if ( is_numeric($template[$i])) {
                $cnt = '';
                while (($i < $length) && is_numeric($template[$i])) {
                    $cnt .= $template[$i];
                    $i++;
                }
                $cnt = (int)$cnt;
                switch (!0) {
                    case !isset($template[$i]):
                        $result.=$cnt;
                        break;
                    case (strpos('AXxLlnSs',$template[$i]) !== false):
                        $result.= static::complex(
                            implode(
                                '',
                                array_fill(0, $cnt, $template[$i])
                            )
                        );
                        break;
                    case $template[$i] === "\\":
                        if (isset($template[$i+1])) {
                            $i++;
                            $result.= implode(
                                '',
                                array_fill(0, $cnt, $template[$i])
                            );
                        } else {
                            $result.= implode(
                                '',
                                array_fill(0, $cnt, "\\")
                            );
                        }
                        break;
                    default:
                        $result.= implode(
                            '',
                            array_fill(0, $cnt, $template[$i])
                        );
                }
                continue;
            }
            switch ($template[$i]) {
                case 'A':
                    $result.= str_shuffle($A)[0];
                    break;
                case 'X':
                    $result.= str_shuffle($X)[0];
                    break;
                case 'x':
                    $result.= str_shuffle($x)[0];
                    break;
                case 'L':
                    $result.= str_shuffle($uLetters)[0];
                    break;
                case 'n':
                    $result.= str_shuffle($numbers)[0];
                    break;
                case 'l':
                    $result.= str_shuffle($letters)[0];
                    break;
                case 's':
                    $result.= str_shuffle($special)[0];
                case 'S':
                    $result.= str_shuffle($special.$A)[0];
                    break;
                case '\\':
                    $i++;
                    if (isset($template[$i])) {
                        $result.= $template[$i];
                    }
                    break;
                default:
                    $result.= $template[$i];
                    break;
            }
        }
        return $result;
    }
}
