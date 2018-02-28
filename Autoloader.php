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
namespace  CF\Tools;

final class Autoloader
{
    private static $routes = [];
    private static $defaultRoute = null;
    private static $initState = false;

    protected static $requireFilersExceptions = ['.', '..'];

    public static function tryFile($file, $require = true)
    {
        if (is_file($file)) {
            if ($require) require_once $file;
            return true;
        }
        return false;
    }

    public static function load($class)
    {
//        var_dump($class);
        $c = explode('\\', $class);
        $cc = count($c);
        if ($cc > 1) {
            $vendor = array_shift($c);
            $className = array_pop($c);
            $path = implode(DIRECTORY_SEPARATOR, $c).DIRECTORY_SEPARATOR;
            if (isset(self::$routes[$vendor])) {
//                var_dump(self::$routes[$vendor]);
                if(!self::tryFile(self::$routes[$vendor].$path.$className.'.php')) {
                    return self::tryFile(strtolower(self::$routes[$vendor].$path).$className.'.php');
                }
            } else {
                $path = $vendor.DIRECTORY_SEPARATOR.$path;
                if(!self::tryFile(self::$defaultRoute.$path.$className.'.php')) {
                    return self::tryFile(strtolower(self::$defaultRoute.$path).$className.'.php');
                }
            }
        }
        return self::tryFile(self::$defaultRoute.$class.'.php');
    }

    public static function vendor($vendor, $route)
    {
        self::$routes[$vendor] = self::dir($route);
    }

    public static function init($defaultRoute)
    {
        self::$defaultRoute = self::dir($defaultRoute);
        if(!self::$initState) {
            spl_autoload_register(
                array(self::class, 'load')
            );
            self::$initState = true;
        }
    }

    public static function dir($dirPath)
    {
        if (!is_dir($dirPath)) {
            return false;
        }
        $dirPath = implode(DIRECTORY_SEPARATOR, preg_split('/[\\/]+/', $dirPath));
        if (substr($dirPath, -1) !== DIRECTORY_SEPARATOR) {
            $dirPath.= DIRECTORY_SEPARATOR;
        }
        return $dirPath;
    }

    public static function requireFiles($path, ... $files)
    {
        if (!$files) {
            $files = array_diff(scandir($path), static::$requireFilersExceptions);
        }
        foreach ($files as $file) {
            $file = $path . '/' . $file;
            $file = str_replace('/', DIRECTORY_SEPARATOR, str_replace('//', '/', $file));
            if (is_file($file)) {
                require_once $file;
            }
        }
    }
}