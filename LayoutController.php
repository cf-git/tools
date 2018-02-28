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

use CF\Components\TName;

class LayoutController
{
    use TName {
        setName as private;
    }

    /**
     *   {tag}
     *   {foo()}
     *   {instance:keІіy}
     *   {lay/Oяut.namйЇїe}
     *   {lay_out.$n-ame}{lay-out.n_ame}
     *   {instance:method(param1, $param2)}
     *   {instance:method(param1, param2),}
     *   {instance::method(param1, param2),}
     *   {instance::param}
     */
//    const T = "/\{((?<instance>[\d\w\-\-\/]+)(?<separator>\.|\:|\:\:))?(?<call>[\d\w\-\_\,$\/]+){1}(\((?<params>[\s\$\d\w\,\ \:\;\.\/\<\>\=\"\'\-\_\!\|]*)\)(?<ps>[\|\,\;\.])?)?\}/u";
//    const T = "/\{((?<instance>[\d\w\-\-\/]+)(?<separator>\.|\:|\:\:))?(?<call>[\d\w\-\_\,$\/]+){1}(\((?<params>[\@\&\s\$\%\d\w\,\ \:\;\.\/\<\>\=\"\'\-\(\)\_\!\|]*)\)(?<ps>[\|\,\;\.])?)?\}/u";
    const T = "/\{((?<instance>[\d\w\-\-\/]+)(?<separator>\.|\:|\:\:))?(?<call>[\d\w\-\_\,$\/]+){1}(\((?<params>[\@\&\s$\%\d\w\,\ \:\;\.\/\<\>\=\"\'\-\(\)\_\!\|\?]*)\)(?<ps>[\|\,\;\.])?)?\}/u";

    protected $tags = [];
    protected $calls = [];
    protected $layouts = [];
    protected $forwards = [];
    protected $instances = [];
    protected $bindedTags = [];
    protected $bindedCalls = [];
    protected $bindedLayouts = [];
    protected $bindedInstances = [];
    protected static $globalTags = [];
    protected static $layoutsRoot = '/';

    protected $layoutStyles = [];
    protected $layoutScripts = [];

    protected $tpl = null;
    protected $tplPath = null;
    protected $parentLayout = false;

    protected $renderStateCallback = null;

    /**
     * @param $name
     * @param $value
     */
    public static function tag($name, $value)
    {
        static::$globalTags[$name] = $value;
    }

    /**
     * @param $root
     */
    public static function setLayoutsRoot($root)
    {
        static::$layoutsRoot = $root;
    }

    /**
     * @param string $tplPath - if start with / - start will from module root else from call route
     * @param null $name
     * @return static|string
     * @throws \Exception
     */
    public static function layout(string $tplPath, $name = null)
    {
        /** @var LayoutController|static|$this $inst */
        $inst = new static;
        $callback = false;
        if ($tplPath[0] === '/') {
            $path = static::$layoutsRoot . substr($tplPath, 1);
        } else {
            $dbt = debug_backtrace();
            if (($dbt[1]['function'] === 'forward_static_call_array' || $dbt[1]['function'] === 'App\{closure}') &&
                ($dbt[1]['args'][0] instanceof static) ||
                (($dbt[1]['args'][0][0] === static::class) && ($dbt[2]['function'] === 'renderForwards'))
            ) {
                if (isset($dbt[2]['object'])) {
                    $object = $dbt[2]['object'];
                } else {
                    $object = $dbt[1]['args'][0];
                }
                $path = explode('/', $inst->parent($object)->getTplPath());
                array_pop($path);
                $path = implode('/', $path);
                $path .= '/' . $tplPath;
            } else {
                throw new \Exception('Forward way error');
            }
        }
        if (!is_null($name)) {
            $inst->setName($name);
        } else {
            $inst->setName($tplPath);
        }
        if (is_file($path . '.html')) {
            $inst->load($path . '.html');
        } else {
            $inst->tplPath = $path;
        }
        if (is_file($path . '.php')) {
            /** @noinspection PhpIncludeInspection */
            $callback = (include $path . '.php') ?? false;
        }
        if (is_function($callback)) {
            $data = $callback($inst);
            if (is_null($data)) {
                $data = $inst;
            }
        } else {
            return $inst;
        }
        return $data;
    }

    /**
     * @param bool|static|LayoutController $parentLayout
     * @return bool|static|LayoutController
     */
    public function parent($parentLayout = false)
    {
        if ($parentLayout) {
            $this->parentLayout = $parentLayout;
        }
        return $this->parentLayout;
    }

    /**
     * @return null|string - file path name
     */
    public function getTplPath()
    {
        return $this->tplPath;
    }

    /**
     * @param $layout
     * @return bool|mixed
     */
    public function getLayout($layout)
    {
        if (isset($this->bindedLayouts[$layout])) {
            return $this->bindedLayouts[$layout];
        }
        return false;
    }

    /**
     * @param $tagName
     * @param $value
     * @return mixed|$this|false
     */
    public function bind($tagName, $value = null)
    {
        if (is_null($value)) {
            if (isset($this->bindedTags[$value])) {
                return $this->bindedTags[$value];
            }
            return false;
        }
        $this->bindedTags[$tagName] = $value;
        return $this;
    }

    /**
     * @param $name
     * @param $callback
     * @return $this
     */
    public function bindCall($name, $callback)
    {
        $this->bindedCalls[$name] = $callback;
        return $this;
    }

    /**
     * @param LayoutController $layout
     * @return $this
     */
    public function bindLayout(LayoutController $layout)
    {
        if (!$layout->parent()) {
            $layout->parent($this);
        }
        $this->bindedLayouts[$layout->getName()] = $layout;
        return $this;
    }

    /**
     * @param $name
     * @param $instance
     * @return $this
     */
    public function bindInstance($name, $instance)
    {
        $this->bindedInstances[$name] = $instance;
        return $this;
    }

    /**
     * @return $this
     */
    public function bindLayoutStyle()
    {
        $this->tpl = "<link rel='stylesheet' href='" . str_replace(
                __ROOT,
                '',
                substr_replace($this->getTplPath(), 'css', -4)
            ) . "'>" . $this->tpl;
        return $this;
    }

    /**
     * @return $this
     */
    public function bindLayoutScript()
    {
        $this->tpl .= "<script src='" . str_replace(
                __ROOT,
                '',
                substr_replace($this->getTplPath(), 'js', -4)
            ) . "'></script>";
        return $this;
    }

    /**
     * @param $filePath
     * @return $this
     */
    public function bindStyle($filePath)
    {
        if ($filePath[0] !== '/') {
            $filePath = '/' . $filePath;
            if (substr($filePath, -4) !== '.css') {
                $filePath .= '.css';
            }
            $filePath = substr_replace($this->tplPath, $filePath, strrpos($this->tplPath, '/'));
        }
        $this->tpl = "<link rel='stylesheet' href='" . str_replace(
                __ROOT,
                '',
                $filePath
            ) . "'>" . $this->tpl;
        return $this;
    }

    /**
     * @param $filePath
     * @return $this
     */
    public function bindScript($filePath)
    {
        if ($filePath[0] !== '/') {
            $filePath = '/' . $filePath;
            if (substr($filePath, -3) !== '.js') {
                $filePath .= '.js';
            }
            $filePath = substr_replace($this->tplPath, $filePath, strrpos($this->tplPath, '/'));
        }
        if (is_file($filePath)) {
            $this->tpl .= "<script src='" . str_replace(
                    __ROOT,
                    '',
                    $filePath
                ) . "'></script>";
        }
        return $this;
    }

    /**
     * @param $tplPath
     * @return $this
     * @throws \Error
     */
    public function load($tplPath)
    {
        $this->tplPath = $tplPath;
        $this->tpl = file_get_contents($tplPath);
        if (($tags = preg_match_all(self::T, $this->tpl, $matches, PREG_SET_ORDER))) {
            foreach ($matches as $match) {
                if (!isset($match['separator']) || $match['separator'] === "") {
                    if (!isset($match['params'])) {
                        $this->tags[$match[0]] = $match['call'];
                    } else {
                        if (isset($match['ps'])) {
                            $params = array_map('trim', explode($match['ps'], $match['params']));
                        } else {
                            $params = $match['params'];
                        }
                        $this->calls[$match[0]] = [$match['call'], $params];
                    }
                } else {
                    switch ($match['separator']) {
                        case '.':
                            if (!isset($match['params'])) {
                                $this->layouts[$match[0]] = $match['instance'] . '.' . $match['call'];
                            } else {
                                throw new \Error("Layout can\'t call params {$match['params']}");
                            }
                            break;
                        case ':':
                            if (!isset($match['params'])) {
                                $this->instances[$match[0]] = [$match['instance'], $match['call']];
                            } else {
                                if (isset($match['ps'])) {
                                    $params = array_map('trim', explode($match['ps'], $match['params']));
                                } else {
                                    $params = $match['params'];
                                }
                                $this->instances[$match[0]] = [$match['instance'], [$match['call'], $params]];
                            }
                            break;
                        case '::':
                            if (!isset($match['params'])) {
                                $this->forwards[$match[0]] = [$match['instance'], $match['call']];
                            } else {
                                if (isset($match['ps'])) {
                                    $params = array_map('trim', explode($match['ps'], $match['params']));
                                } else {
                                    $params = $match['params'];
                                }
                                $this->forwards[$match[0]] = [$match['instance'], [$match['call'], $params]];
                            }
                            break;
                    }
                }
            }
        }
        return $this;
    }

    /********************
     ***** [Outputs] ****
     ********************/
    /**
     * @param $callback
     * @return $this
     */
    public function setRenderStateCallback($callback)
    {
        $this->renderStateCallback = $callback;
        return $this;
    }

    /**
     * @return $this
     */
    protected function renderLayouts()
    {
        foreach ($this->layouts as $key => $layout) {
            $layoutName = $layout;
            if ($layout[0] === '$') {
                $varName = substr($layout, 1);
                if (isset($this->bindedTags[$varName])) {
                    $layoutName = $this->bindedTags[$varName];
                } else {
                    if (isset(static::$globalTags[$varName])) {
                        $layoutName = static::$globalTags[$varName];
                    }
                }
            }
            if (isset($this->bindedLayouts[$layout])) {
                $this->tpl = str_replace(
                    $key,
                    $this->bindedLayouts[$layoutName]->output(),
                    $this->tpl
                );
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function renderForwards()
    {
        foreach ($this->forwards as $key => $inst) {
            if (isset($this->bindedInstances[$inst[0]])) {
                if (is_array($inst[0]) || is_array($inst[1])) {
                    $params = $inst[1][1];
                    if (!is_array($params)) {
                        $params = [$params];
                    }
                    foreach ($params as $i => $p) {
                        if ($p && $p[0] === '$') {

                            $k = substr($p, 1);
                            if (isset($this->bindedTags[$k])) {
                                $params[$i] = $this->bindedTags[$k];
                            } else {
                                if (isset(static::$globalTags[$k])) {
                                    $params[$i] = static::$globalTags[$k];
                                }
                            }
                        }
                    }
                    $this->tpl = str_replace(
                        $key,
                        forward_static_call_array([$this->bindedInstances[(string)($inst[0])], $inst[1][0]], $params),
                        $this->tpl
                    );
                } else {
                    $instance = $this->bindedInstances[$inst[0]];
                    $this->tpl = str_replace(
                        $key,
                        $instance::${$inst[1]},
                        $this->tpl
                    );
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function renderInstances()
    {
        /** NOT WORK - RESERVED */
        foreach ($this->instances as $key => $inst) {
            if (isset($this->bindedInstances[$inst[0]])) {
                if (is_array($inst[0]) || is_array($inst[1])) {
                    $params = $inst[1][1];
                    if (!is_array($params)) {
                        $params = [$params];
                    }
                    foreach ($params as $i => $p) {
                        if ($p && $p[0] === '$') {
                            $k = substr($p, 1);
                            if (isset($this->bindedTags[$k])) {
                                $params[$i] = $this->bindedTags[$k];
                            } else {
                                if (isset(static::$globalTags[$k])) {
                                    $params[$i] = static::$globalTags[$k];
                                }
                            }
                        }
                    }
                    $this->tpl = str_replace(
                        $key,
                        call_user_func_array([$this->bindedInstances[(string)($inst[0])], $inst[1][0]], $params),
                        $this->tpl
                    );
                } else {
                    $this->tpl = str_replace(
                        $key,
                        $this->bindedInstances[$inst[0]]->{$inst[1]},
                        $this->tpl
                    );
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function renderCallback()
    {
        foreach ($this->calls as $key => $call) {
            $params = $call[1];
            if (!is_array($params)) {
                $params = [$params];
            }
            foreach ($params as $i => $p) {
                if ($p && $p[0] === '$') {
                    $k = substr($p, 1);
                    if (isset($this->bindedTags[$k])) {
                        $params[$i] = $this->bindedTags[$k];
                    } else {
                        if (isset(static::$globalTags[$k])) {
                            $params[$i] = static::$globalTags[$k];
                        }
                    }
                }
            }
            if (isset($this->bindedCalls[$call[0]]) && (is_callable($this->bindedCalls[$call[0]]) ||
                    (is_string($this->bindedCalls[$call[0]]) && function_exists($this->bindedCalls[$call[0]])) ||
                    (is_object($this->bindedCalls[$call[0]]) && ($this->bindedCalls[$call[0]] instanceof \Closure)))
            ) {
                $this->tpl = str_replace(
                    $key,
                    call_user_func_array($this->bindedCalls[$call[0]], $params),
                    $this->tpl
                );
            } else {
                if (function_exists($call[0])) {
                    $this->tpl = str_replace(
                        $key,
                        call_user_func_array($call[0], $params),
                        $this->tpl
                    );
                }
            }
//            if (isset($this->bindedCalls[$call[0]])) {
//                $this->tpl = str_replace(
//                    $key,
//                    $this->bindedCalls[$call[0]]($call[1]),
//                    $this->tpl
//                );
//            } else {
//                if (function_exists($call[0])) {
//                    $this->tpl = str_replace(
//                        $key,
//                        call_user_func_array($call[0], $params),
//                        $this->tpl
//                    );
//                }
//            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function renderTags()
    {
        foreach ($this->tags as $key => $tag) {
            if (isset($this->bindedTags[$tag])) {
                $this->tpl = str_replace(
                    $key,
                    $this->bindedTags[$tag],
                    $this->tpl
                );
            } else {
                if (isset(static::$globalTags[$tag])) {
                    $this->tpl = str_replace(
                        $key,
                        static::$globalTags[$tag],
                        $this->tpl
                    );
                }
            }
        }
        return $this;
    }

    /**
     * @return bool|mixed
     */
    protected function renderState()
    {
        if (is_null($this->renderStateCallback)) {
            return true;
        } else {
            if (is_callable($this->renderStateCallback) ||
                (is_string($this->renderStateCallback) && function_exists($this->renderStateCallback)) ||
                (is_object($this->renderStateCallback) && ($this->renderStateCallback instanceof \Closure))
            ) {
                return call_user_func($this->renderStateCallback);
            }
            return false;
        }
    }

    /**
     * @return string
     */
    public function output()
    {
        if (!$this->renderState()) {
            return '';
        }
        $this->renderLayouts()
            ->renderForwards()
            ->renderInstances()
            ->renderCallback()
            ->renderTags();
        return (string)$this->tpl;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->output();
    }
}
