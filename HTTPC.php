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
 * HTTP Controller static class
 * @package      : cf-git/tools
 * @author       : is.captain.fail@gmail.com
 * @user         : CF
 * @license      : http://opensource.org/licenses/MIT   MIT License
 */
namespace CF\Tools;

class HTTPC
{
    protected static $cookie = null;
    protected static $route = null;
    protected static $query = null;
    protected static $post = null;

    public static function route()
    {
        if (is_null(static::$route)) {
            $p = parse_url(filter_input_array(INPUT_SERVER)['REQUEST_URI']);
            static::$route = array_values(array_filter(explode('/', $p['path'])));
        }
        return static::$route;
    }

    public static function path()
    {
        return implode('/', static::route());
    }

    public static function cookie($key = null)
    {
        if (is_null(static::$cookie)) {
            static::$cookie = filter_input_array(INPUT_COOKIE);
        }
        if (!is_null($key)) {
            return static::$cookie[$key] ?? false;
        }
        return static::$cookie;
    }

    public static function query($key = null)
    {
        if (is_null(static::$query)) {
            static::$query = filter_input_array(INPUT_GET);
        }
        if (!is_null($key)) {
            return static::$query[$key] ?? false;
        }
        return static::$query ?? [];
    }

    public static function post($key = null)
    {
        if (is_null(static::$post)) {
            static::$post = filter_input_array(INPUT_POST);
        }
        if (!is_null($key)) {
            return static::$post[$key] ?? false;
        }
        return static::$post ?? [];
    }
}