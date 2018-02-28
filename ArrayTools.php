<?php
/**
 * IT Systems of Ukraine
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2016, Ukraine, Ltd. "MYKOLAIV TECH SPEC".
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
 * @package      : ${PRODUCT}
 * @author       : Dooit Dev Team
 * @user         : CF
 * @copyright    : Copyright (c) 2016, Ltd. "MYKOLAIV TECH SPEC" DOOIT™. (http://dooit.com.ua/)
 * @license      : http://opensource.org/licenses/MIT	MIT License
 * @link         : http://dooit.com.ua
 * @since        : Version 2.0.0 (last revision: 26-10-2016)
 */

namespace CF\Tools;


class ArrayTools
{
    /**
     * @param array ...$a
     * @return bool
     */
    final public static function equalKeys(... $a)
    {
        switch (count($a)) {
            case 0:
                return false;
            case 1:
                return true;
            case 2:
                return array_diff_key($a[0], $a[1]) === array_diff_key($a[1], $a[0]);
            default:
                for ($i = 0, $il = count($a); $i < $il - 1; $i++) {
                    if (!static::equalKeys($a[$i], $a[$i + 1])) {
                        return false;
                    }
                }
                return true;
                break;
        }
    }

    /**
     * @param array $needleKeys
     * @param array ...$arrays
     * @return bool
     */
    final public static function innerKeys(array $needleKeys, ... $arrays)
    {
        foreach ($arrays as $array) {
            for ($i = 0, $il = count($needleKeys); $i < $il; $i++) {
                if (!array_key_exists($needleKeys[$i], $array)) {
                    return false;
                }
            }
        }
        return true;
    }
}