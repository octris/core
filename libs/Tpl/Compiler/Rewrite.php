<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\Tpl\Compiler;

/**
 * Rewrite template code. Rewrite inline function calls and rewrite function calls according to
 * if they are allowed php function calls or calls to functions that have to be registered to
 * sandbox on template rendering.
 *
 * @copyright   copyright (c) 2010-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Rewrite
{
    /**
     * Inline method rewrite.
     *
     * @type    array
     */
    protected static $inline = array(
        // blocks
        '#bench'    => array('min' => 1, 'max' => 1),
        '#cache'    => array('min' => 2, 'max' => 3),
        '#copy'     => array('min' => 1, 'max' => 1),
        '#cron'     => array('min' => 1, 'max' => 2),
        '#cut'      => array('min' => 1, 'max' => 1),
        '#if'       => array('min' => 1, 'max' => 1),
        '#foreach'  => array('min' => 2, 'max' => 3),
        '#loop'     => array('min' => 4, 'max' => 5),
        '#onchange' => array('min' => 1, 'max' => 1),
        '#trigger'  => array('min' => 0, 'max' => 3),

        // functions
        'if'     => array('min' => 2, 'max' => 3),   // (... ? ... : ...)
        'ifset'  => array('min' => 2, 'max' => 3),   // (isset(...) ? ... : ...)
        'ifnull' => array('min' => 2, 'max' => 3),   // (is_null(...) ? ... : ...)

        'mul'    => array('min' => 2),               // ... * ...
        'div'    => array('min' => 2),               // ... / ...
        'mod'    => array('min' => 2, 'max' => 2),   // ... % ...
        'add'    => array('min' => 2),               // ... + ...
        'sub'    => array('min' => 2),               // ... - ...
        'incr'   => array('min' => 1, 'max' => 2),   // ++ / +=
        'decr'   => array('min' => 1, 'max' => 2),   // -- / -=
        'neg'    => array('min' => 1, 'max' => 1),   // -...

        'and'    => array('min' => 2),               // ... && ...
        'or'     => array('min' => 2),               // ... || ...
        'xor'    => array('min' => 2, 'max' => 2),   // ... xor ...
        'not'    => array('min' => 1, 'max' => 1),   // !...

        'lt'     => array('min' => 2, 'max' => 2),   // ... < ...
        'gt'     => array('min' => 2, 'max' => 2),   // ... > ...
        'eq'     => array('min' => 2, 'max' => 2),   // ... == ...
        'le'     => array('min' => 2, 'max' => 2),   // ... <= ...
        'ge'     => array('min' => 2, 'max' => 2),   // ... >= ...
        'ne'     => array('min' => 2, 'max' => 2),   // ... != ...

        'bool'       => array('min' => 1, 'max' => 1),  // (bool)...
        'int'        => array('min' => 1, 'max' => 1),  // (int)...
        'float'      => array('min' => 1, 'max' => 1),  // (float)...
        'string'     => array('min' => 1, 'max' => 1),  // (string)...
        'collection' => array('min' => 1, 'max' => 1),

        'now'       => array('min' => 0, 'max' => 0),
        'uniqid'    => array('min' => 0, 'max' => 0),
        'let'       => array('min' => 2, 'max' => 2),
        'ddump'     => array('min' => 1),
        'dprint'    => array('min' => 1),
        'error'     => array('min' => 1, 'max' => 1),

        'include'   => array('min' => 1, 'max' => 1),

        // string functions
        'explode'   => array('min' => 2, 'max' => 2),
        'implode'   => array('min' => 2, 'max' => 2),
        'lpad'      => array('min' => 2, 'max' => 3),
        'rpad'      => array('min' => 2, 'max' => 3),
        'totitle'   => array('min' => 1, 'max' => 1),
        'concat'    => array('min' => 2),

        // array functions
        'array'     => array('min' => 1),
        'cycle'     => array('min' => 1, 'max' => 3),
        'in'        => array('min' => 2, 'max' => 2),

        // misc functions
        'escape'    => array('min' => 2, 'max' => 2),

        // localisation functions
        'comify'    => array('min' => 2, 'max' => 3),
        'enum'      => array('min' => 2),
        'monf'      => array('min' => 1, 'max' => 2),
        'numf'      => array('min' => 1, 'max' => 2),
        'perf'      => array('min' => 1, 'max' => 2),
        'datef'     => array('min' => 1, 'max' => 2),
        'gender'    => array('min' => 4, 'max' => 4),
        'quant'     => array('min' => 2, 'max' => 4),
        'yesno'     => array('min' => 2, 'max' => 3),
    );

    /**
     * Allowed PHP functions and optional mapping to an PHP or framework internal name.
     *
     * @type    array
     */
    protected static $phpfunc = array(
        // string functions
        'chunk'          => array('min' => 3, 'max' => 3, 'map' => '\Octris\Core\Type\Text::chunk_split'),
        'chunk_id'       => array('min' => 1, 'max' => 5, 'map' => '\Octris\Core\Type\Text::chunk_id'),
        'cut'            => array('min' => 2, 'max' => 4, 'map' => '\Octris\Core\Type\Text::cut'),
        'escapeshellarg' => array('min' => 1, 'max' => 1, 'map' => 'escapeshellarg'),
        'lcfirst'        => array('min' => 1, 'max' => 1, 'map' => '\Octris\Core\Type\Text::lcfirst'),
        'length'         => array('min' => 1, 'max' => 1, 'map' => 'strlen'),
        'ltrim'          => array('min' => 1, 'max' => 2, 'map' => '\Octris\Core\Type\Text::ltrim'),
        'obliterate'     => array('min' => 2, 'max' => 4, 'map' => '\Octris\Core\Type\Text::obliterate'),
        'repeat'         => array('min' => 2, 'max' => 2, 'map' => 'str_repeat'),
        'replace'        => array('min' => 3, 'max' => 3, 'map' => '\Octris\Core\Type\Text::str_replace'),
        'rtrim'          => array('min' => 1, 'max' => 2, 'map' => '\Octris\Core\Type\Text::rtrim'),
        'shorten'        => array('min' => 1, 'max' => 3, 'map' => '\Octris\Core\Type\Text::shorten'),
        'sprintf'        => array('min' => 1,             'map' => '\Octris\Core\Type\Text::sprintf'),
        'substr'         => array('min' => 2, 'max' => 3, 'map' => '\Octris\Core\Type\Text::substr'),
        'tolower'        => array('min' => 1, 'max' => 1, 'map' => '\Octris\Core\Type\Text::strtolower'),
        'toupper'        => array('min' => 1, 'max' => 1, 'map' => '\Octris\Core\Type\Text::strtoupper'),
        'trim'           => array('min' => 1, 'max' => 2, 'map' => '\Octris\Core\Type\Text::trim'),
        'ucfirst'        => array('min' => 1, 'max' => 1, 'map' => '\Octris\Core\Type\Text::ucfirst'),
        'vsprintf'       => array('min' => 2, 'max' => 2, 'map' => '\Octris\Core\Type\Text::vsprintf'),

        // numeric functions
        'abs'        => array('min' => 1, 'max' => 1),
        'ceil'       => array('min' => 1, 'max' => 1),
        'floor'      => array('min' => 1, 'max' => 1),
        'max'        => array('min' => 2),
        'min'        => array('min' => 2),
        'round'      => array('min' => 1, 'max' => 2),

        // array functions
        'count'      => array('min' => 1, 'max' => 1),

        // misc functions
        'isset'      => array('min' => 1, 'max' => 1),
        'jsonencode' => array('min' => 1, 'max' => 2, 'map' => 'json_encode'),
        'jsondecode' => array('min' => 1, 'max' => 4, 'map' => 'json_decode'),
    );

    /**
     * Forbidden function names.
     *
     * @type    array
     */
    protected static $forbidden = array(
        'setvalue', 'setvalues', 'foreach', 'bufferstart', 'bufferend', 'cache', 'cron', 'loop', 'onchange', 'trigger',
        '__construct', '__call', 'registermethod', 'render', 'write'
    );

    /**
     * Last error occured.
     *
     * @type    string
     */
    protected static $last_error = '';

    /**
     * Constructor and clone magic method are protected to prevent instantiating of class.
     */
    protected function __construct()
    {
    }
    protected function __clone()
    {
    }

    /**
     * Return last occured error.
     *
     * @return  string                  Last occured error.
     */
    public static function getError()
    {
        return self::$last_error;
    }

    /**
     * Set error.
     *
     * @param   string      $name       Name of method the error occured for.
     * @param   string      $msg        Additional error message.
     */
    protected static function setError($name, $msg)
    {
        self::$last_error = sprintf('"%s" -- %s', $name, $msg);
    }

    /**
     * Wrapper for methods that can be rewritten.
     *
     * @param   string      $name       Name of method to rewrite.
     * @param   array       $args       Arguments for method.
     */
    public static function __callStatic($name, $args)
    {
        self::$last_error = '';

        $name = strtolower($name);
        $args = $args[0];

        if (in_array($name, self::$forbidden)) {
            self::setError($name, 'access denied');
        } elseif (isset(self::$phpfunc[$name])) {
            // call to allowed PHP function
            $cnt = count($args);

            if (isset(self::$phpfunc[$name]['min'])) {
                if ($cnt < self::$phpfunc[$name]['min']) {
                    self::setError($name, 'not enough arguments');
                }
            }
            if (isset(self::$phpfunc[$name]['max'])) {
                if ($cnt > self::$phpfunc[$name]['max']) {
                    self::setError($name, 'too many arguments');
                }
            }

            if (isset(self::$phpfunc[$name]['map'])) {
                // resolve 'real' PHP method name
                $name = self::$phpfunc[$name]['map'];
            }

            return $name . '(' . implode(', ', $args) . ')';
        } elseif (isset(self::$inline[$name])) {
            // inline function rewrite
            $cnt = count($args);

            if (isset(self::$inline[$name]['min'])) {
                if ($cnt < self::$inline[$name]['min']) {
                    self::setError($name, 'not enough arguments');
                }
            }
            if (isset(self::$inline[$name]['max'])) {
                if ($cnt > self::$inline[$name]['max']) {
                    self::setError($name, 'too many arguments');
                }
            }

            if (substr($name, 0, 1) == '#') {
                $name = 'block' . ucfirst(substr($name, 1));
            } else {
                $name = 'func' . ucFirst($name);
            }

            return self::$name($args);
        } elseif (substr($name, 0, 1) == '#') {
            // unknown block function
            self::setError($name, 'unknown block type');
        } else {
            return sprintf(
                '$this->%s(%s)',
                $name,
                implode(', ', $args)
            );
        }
    }

    /**
     * Helper function to create a uniq identifier required by several functions.
     *
     * @return  string                  Uniq identifier
     */
    protected static function getUniqId()
    {
        return md5(uniqid());
    }

    /**
     * Implementation of gettext compiler.
     *
     * @param   \Octris\Core\L10n       $l10n           Instance of l10n class.
     * @param   string                      $domain         Text domain to use.
     * @param   string                      $msg            Message to translate.
     * @param   array                       $args           Parameters for inline functions.
     * @return  string                                      Compiled code for gettext.
     */
    public static function gettext($l10n, $domain, $msg, $args)
    {
        self::$last_error = '';

        $fn = array('comify', 'enum', 'monf', 'numf', 'perf', 'datef', 'gender', 'quant', 'yesno');

        if (preg_match('/^(["\'])(.*?)\1$/', $msg, $match)) {
            $pattern = '/\[(?:(?P<cmd>[a-z]+), *)?_(?P<arg>\d+)(?:, *(?P<str>.*?))?(?<!\\\)\]/s';

            $chr = $match[1];                           // quotation character
            $txt = stripcslashes($match[2]);
            $txt = $l10n->lookup($txt, $domain);        // get translated text

            $txt = $chr . addcslashes($txt, ($chr == '"' ? '"' : "'")) . $chr;

            try {
                $txt = preg_replace_callback($pattern, function ($m) use ($args, $chr, $fn) {
                    $cmd = (isset($m['cmd']) ? $m['cmd'] : '');
                    $arg = $m['arg'];

                    if ($cmd != '') {
                        if (!in_array($cmd, $fn)) {
                            throw new \Exception(sprintf('unknown function "%s"', $cmd));
                        }

                        if ($arg > count($args)) {
                            throw new \Exception(sprintf('argument "%d" for function "%s" is not defined', $arg, $cmd));
                        }

                        $tmp = array_map(function ($arg) use ($chr) {
                            return $chr . trim($arg) . $chr;
                        }, (isset($m['str']) ? preg_split('/(?<!\\\),/', $m['str']) : array()));

                        array_unshift($tmp, $args[$arg - 1]);

                        $cnt = count($tmp);

                        if ($cnt < self::$inline[$cmd]['min']) {
                            throw new \Exception(self::getError(sprintf('not enough arguments for function "%s"', $cmd)));
                        }
                        if ($cnt > self::$inline[$cmd]['max']) {
                            throw new \Exception(self::getError(sprintf('too many arguments for function "%s"', $cmd)));
                        }

                        $code = $chr . ' . $this->l10n->' . $cmd . '(' . implode(', ', $tmp) . ') . ' . $chr;
                    } elseif ($arg > count($args)) {
                        throw new \Exception(sprintf('argument "%d" is not defined', $arg));
                    } else {
                        $code = $chr . ' . ' . $args[$arg - 1] . ' . ' . $chr;
                    }

                    return $code;
                }, $txt, -1, $cnt);
            } catch (\Exception $e) {
                self::setError('gettext', $e->getMessage());
            }

            $return = $txt;
        } else {
            $return = '$this->l10n->translate(' . $msg . ', array(), ' . $domain . ')';
        }

        return $return;
    }

    /*
     * inline block definitions, that can be converted directly
     */
    protected static function blockBench($args)
    {
        $var1 = '$_' . self::getUniqId();
        $var2 = '$_' . self::getUniqId();

        return array(
            sprintf(
                '%s = microtime(true); ' .
                'for (%s = 0; %s < abs(%s); ++%s) { ' .
                'if (%s == 1) ob_start();',
                $var1,
                $var2,
                $var2,
                $args[0],
                $var2,
                $var2
            ),
            sprintf(
                '} %s = microtime(true) - %s; ' .
                'if (abs(%s) > 0) ob_end_clean(); ' .
                'printf("[benchmark iterations: %%s, time: %%1.6f]", abs(%s), %s);',
                $var1,
                $var1,
                $args[0],
                $args[0],
                $var1
            )
        );
    }

    protected static function blockCache($args)
    {
        $var = '$_' . self::getUniqId();
        $key = $args[0];
        $ttl = $args[1];
        $esc = (isset($args[2]) ? $args[2] : \Octris\Core\Tpl::ESC_NONE);

        return array(
            sprintf(
                'if (!$this->cacheLookup(%s, "%s")) { $this->bufferStart(%s, false);',
                $key,
                $esc,
                $var
            ),
            sprintf(
                '$this->bufferEnd(); $this->cacheStore(%s, %s, %s); }',
                $key,
                $var,
                $ttl
            )
        );
    }

    protected static function blockCopy($args)
    {
        return array(
            '$this->bufferStart(' . implode(', ', $args) . ', false);',
            '$this->bufferEnd();'
        );
    }

    protected static function blockCron($args)
    {
        return array(
            'if ($this->cron(' . implode(', ', $args) . ')) {',
            '}'
        );
    }

    protected static function blockCut($args)
    {
        return array(
            '$this->bufferStart(' . implode(', ', $args) . ', true);',
            '$this->bufferEnd();'
        );
    }

    protected static function blockForeach($args)
    {
        if (count($args) == 2) {
            $return = [
                sprintf('foreach ($this->loop(%s) as list(%s, )) {', $args[1], $args[0]),
                '}'
            ];
        } else {
            $return = [
                sprintf('foreach ($this->loop(%s) as list(%s, %s)) {', $args[1], $args[0], $args[2]),
                '}'
            ];
        }

        return $return;
    }

    protected static function blockIf($args)
    {
        return array(
            'if (' . implode('', $args) . ') {',
            '}'
        );
    }

    protected static function blockLoop($args)
    {
        $params = [$args[0], range($args[1], $args[2], $args[3])];

        if (count($args) == 5) {
            $params[$args[4]];
        }

        return $this->blockForeach($params);
    }

    protected static function blockOnchange($args)
    {
        return array(
            'if ($this->onchange("' . self::getUniqId() . '", ' . implode(', ', $args) . ')) {',
            '}'
        );
    }

    protected static function blockTrigger($args)
    {
        return array(
            'if ($this->trigger("' . self::getUniqId() . '", ' . implode(', ', $args) . ')) {',
            '}'
        );
    }

    /*
     * inline functions, that can be converted directly
     */
    protected static function funcIf($args)
    {
        return sprintf(
            '(%s ? %s : %s)',
            $args[0],
            $args[1],
            (count($args) == 3 ? $args[2] : '')
        );
    }

    protected static function funcIfset($args)
    {
        return sprintf(
            '(isset(%s) ? %s : %s)',
            $args[0],
            $args[1],
            (count($args) == 3 ? $args[2] : '')
        );
    }

    protected static function funcIfnull($args)
    {
        return sprintf(
            '(is_null(%s) ? %s : %s)',
            $args[0],
            $args[1],
            (count($args) == 3 ? $args[2] : '')
        );
    }

    protected static function funcNeg($args)
    {
        return '(-' . $args[0] . ')';
    }

    protected static function funcMul($args)
    {
        return '(' . implode(' * ', $args) . ')';
    }

    protected static function funcDiv($args)
    {
        return '(' . implode(' / ', $args) . ')';
    }

    protected static function funcMod($args)
    {
        return '(' . implode(' % ', $args) . ')';
    }

    protected static function funcAdd($args)
    {
        return '(' . implode(' + ', $args) . ')';
    }

    protected static function funcSub($args)
    {
        return '(' . implode(' - ', $args) . ')';
    }

    protected static function funcIncr($args)
    {
        return sprintf('(%s)', (count($args) == 2 ? $arg[0] . ' += ' + $args[1] : '++' . $args[0]));
    }

    protected static function funcDecr($args)
    {
        return sprintf('(%s)', (count($args) == 2 ? $arg[0] . ' -= ' + $args[1] : '--' . $args[0]));
    }

    protected static function funcAnd($args)
    {
        return '(' . implode(' && ', $args) . ')';
    }

    protected static function funcOr($args)
    {
        return '(' . implode(' || ', $args) . ')';
    }

    protected static function funcXor($args)
    {
        return sprintf('(%d != %d)', !!$args[0], !!$args[1]);
    }

    protected static function funcNot($args)
    {
        return '!' . $args[0];
    }

    protected static function funcLt($args)
    {
        return '(' . implode(' < ', $args) . ')';
    }

    protected static function funcGt($args)
    {
        return '(' . implode(' > ', $args) . ')';
    }

    protected static function funcEq($args)
    {
        return '(' . implode(' == ', $args) . ')';
    }

    protected static function funcLe($args)
    {
        return '(' . implode(' <= ', $args) . ')';
    }

    protected static function funcGe($args)
    {
        return '(' . implode(' >= ', $args) . ')';
    }

    protected static function funcNe($args)
    {
        return '(' . implode(' != ', $args) . ')';
    }

    protected static function funcBool($args)
    {
        return '((bool)' . $args[0] . ')';
    }

    protected static function funcInt($args)
    {
        return '((int)' . $args[0] . ')';
    }

    protected static function funcFloat($args)
    {
        return '((float)' . $args[0] . ')';
    }

    protected static function funcString($args)
    {
        return '((string)' . $args[0] . ')';
    }

    protected static function funcCollection($args)
    {
        return '\\Octris\\Core\\Type::settype(' . $args[0] . ', "collection")';
    }

    protected static function funcNow()
    {
        return '(time())';
    }

    protected static function funcUniqid()
    {
        return '(uniqid(mt_rand()))';
    }

    protected static function funcLet($args)
    {
        return '(' . implode(' = ', $args) . ')';
    }

    protected static function funcDdump($args)
    {
        return '\\Octris\\Debug::getInstance()->ddump(' . implode(', ', $args) . ')';
    }

    protected static function funcDprint($args)
    {
        return '\\Octris\\Debug::getInstance()->dprint(' . implode(', ', $args) . ')';
    }

    protected static function funcError($args)
    {
        return '$this->error(' . implode(', ', $args) . ', __LINE__)';
    }

    protected static function funcInclude($args)
    {
        return '$this->includetpl(' . implode('', $args) . ')';
    }

    // string functions
    protected static function funcExplode($args)
    {
        return 'new \\Octris\\Core\\Type\\Collection(explode(' . implode(', ', $args) . '))';
    }

    protected static function funcImplode($args)
    {
        return '(implode(' . $args[0] . ', \\Octris\\Core\\Type::settype(' . $args[1] . ', "array")))';
    }

    protected static function funcLpad($args)
    {
        $args = $args + array(null, null, ' ');

        return '(str_pad(' . implode(', ', $args) . ', STR_PAD_LEFT))';
    }

    protected static function funcRpad($args)
    {
        $args = $args + array(null, null, ' ');

        return '(str_pad(' . implode(', ', $args) . ', STR_PAD_RIGHT))';
    }

    protected static function funcTotitle($args)
    {
        return '\\Octris\\Core\\Type\\String::convert_case(' . $args[0] . ', MB_CASE_TITLE)';
    }

    protected static function funcConcat($args)
    {
        return '(' . implode(' . ', $args) . ')';
    }

    // array functions
    protected static function funcArray($args)
    {
        return 'new \\Octris\\Core\\Type\\Collection(array(' . implode(', ', $args) . '))';
    }

    protected static function funcCycle($args)
    {
        return '($this->cycle("' . self::getUniqId() . '", ' . implode(', ', $args) . '))';
    }

    protected static function funcIn($args)
    {
        return 'in_array(' . $args[0] . ', \\Octris\\Core\\Type::setType(' . $args[1] . ', "array"))';
    }

    // misc functions
    protected static function funcEscape($args)
    {
        return '($this->escape(' . implode(', ', $args) . '))';
    }

    // localization functions
    protected static function funcComify($args)
    {
        return '($this->l10n->comify(' . implode(', ', $args) . '))';
    }

    protected static function funcEnum($args)
    {
        return '($this->l10n->enum(' . implode(', ', $args) . '))';
    }

    protected static function funcMonf($args)
    {
        return '($this->l10n->monf(' . implode(', ', $args) . '))';
    }

    protected static function funcNumf($args)
    {
        return '($this->l10n->numf(' . implode(', ', $args) . '))';
    }

    protected static function funcPerf($args)
    {
        return '($this->l10n->perf(' . implode(', ', $args) . '))';
    }

    protected static function funcDatef($args)
    {
        return '($this->l10n->datef(' . implode(', ', $args) . '))';
    }

    protected static function funcGender($args)
    {
        return '($this->l10n->gender(' . implode(', ', $args) . '))';
    }

    protected static function funcQuant($args)
    {
        return '($this->l10n->quant(' . implode(', ', $args) . '))';
    }

    protected static function funcYesno($args)
    {
        return '($this->l10n->yesno(' . implode(', ', $args) . '))';
    }
}
