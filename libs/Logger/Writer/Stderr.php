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
 * Logger to write messages to stdErr.
 *
 * @copyright   copyright (c) 2011-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Stderr extends \Octris\Core\Logger\Writer\File
{
    /**
     * Make sure, that parent class will use STDERR for logging.
     *
     * @type    string
     */
    protected $filename = 'php://stderr';

    /**
     * Constructor.
     */
    public function __construct()
    {
    }
}
