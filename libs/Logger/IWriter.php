<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\Logger;

/**
 * Interface for write handlers.
 *
 * @copyright   copyright (c) 2011-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
interface IWriter
{
    /**
     * Each writer implementation needs to implement this method.
     *
     * @param   array       $message        Message to send.
     */
    public function write(array $message);
    /**/
}