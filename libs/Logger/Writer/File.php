<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\Logger\Writer;

/**
 * Logger to write messages to a file.
 *
 * @copyright   copyright (c) 2011 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class File implements \Octris\Core\Logger\IWriter
{
    /**
     * Mapping of logger levels to textual names.
     *
     * @type    array
     */
    private static $level_names = array(
        \Octris\Core\Logger::T_EMERGENCY => 'emergency',
        \Octris\Core\Logger::T_ALERT     => 'alert',
        \Octris\Core\Logger::T_CRITICAL  => 'critical',
        \Octris\Core\Logger::T_ERROR     => 'error',
        \Octris\Core\Logger::T_WARNING   => 'warning',
        \Octris\Core\Logger::T_NOTICE    => 'notice',
        \Octris\Core\Logger::T_INFO      => 'info',
        \Octris\Core\Logger::T_DEBUG     => 'debug'
    );
    
    /**
     * Name of file to log to.
     *
     * @type    string
     */
    protected $filename;
    
    /**
     * Constructor.
     *
     * @param   string      $filename       Name of file to log to.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Write logging message to a file.
     *
     * @param   array       $message        Message to send.
     */
    public function write(array $message)
    {
        if (!($fp = fopen($this->filename, 'w'))) {
            // error handling
        } else {
            if (($is_html = ($this->filename == 'php://output' && php_sapi_name() != 'cli'))) {
                fwrite($fp, '<pre>');
            }

            fwrite($fp, "MESSAGE\n");
            fwrite($fp, sprintf("  id      : %s\n", md5(serialize($message))));
            fwrite($fp, sprintf("  message : %s\n", $message['message']));
            fwrite($fp, sprintf("  host    : %s\n", $message['host']));
            fwrite($fp, sprintf("  time    : %s.%d\n", strftime('%Y-%m-%d %H:%M:%S', $message['timestamp']), substr(strstr($message['timestamp'], '.'), 1)));
            fwrite($fp, sprintf("  level   : %s\n", self::$level_names[$message['level']]));
            fwrite($fp, sprintf("  facility: %s\n", $message['facility']));
            fwrite($fp, sprintf("  file    : %s\n", $message['file']));
            fwrite($fp, sprintf("  line    : %d\n", $message['line']));
            fwrite($fp, sprintf("  code    : %d\n", $message['code']));

            if (count($message['data']) > 0) {
                fwrite($fp, "DATA\n");

                $max = 0;
                array_walk($message['data'], function ($v, $k) use (&$max) {
                    $max = max(strlen($k), $max);
                });

                foreach ($message['data'] as $k => $v) {
                    fwrite($fp, sprintf(
                        "  %-" . $max . "s: %s\n",
                        $k,
                        (!is_scalar($v) ? json_encode($v) : $v)
                    ));
                }
            }

            if (!is_null($message['exception'])) {
                fwrite($fp, "TRACE\n");
                fwrite($fp, preg_replace('/^/m', '  ', $message['exception']->getTraceAsString()));
            }

            fwrite($fp, "\n");

            if ($is_html) {
                fwrite($fp, '</pre>');
            }

            fclose($fp);
        }
    }
}
