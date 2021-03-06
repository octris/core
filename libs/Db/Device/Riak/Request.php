<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\Db\Device\Riak;

/**
 * Riak request class.
 *
 * @copyright   copyright (c) 2012-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Request extends \Octris\Core\Net\Client\Http
{
    /**
     * HTTP status code of last request.
     *
     * @type    int
     */
    protected $status;
    
    /**
     * Constructor.
     *
     * @param   \Octris\Core\Type\Uri       $uri                URI the request is located at.
     */
    public function __construct(\Octris\Core\Type\Uri $uri, $method = self::T_GET)
    {
        parent::__construct($uri, $method);
    }

    /**
     * Execute request.
     *
     * @param   string|array|resource   $body           Optional body to set for POST or PUT request.
     * @param   bool                    $binary         Optional binary transfer mode for POST or PUT request.
     * @return  mixed                                   Returns response body or false if request failed.
     */
    public function execute($body = null, $binary = false)
    {
        $result = parent::execute($body, $binary);

        if (($this->getStatus()) == 200) {
            switch ($this->getContentType()) {
                case 'text/html':
                    $result = trim($result);
                    break;
                case 'application/json':
                    $result = json_decode($result, true);
                    break;
                default:
                    $result = null;
                    break;
            }
        } else {
            $result = false;
        }

        return $result;
    }
}
