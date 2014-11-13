<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\Db\Device;

/**
 * MySQL database device.
 *
 * @copyright   copyright (c) 2012-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Mysql extends \Octris\Core\Db\Device
{
    /**
     * Configuration of attempts a query should be executed, till a deadlock is actually
     * recognized and query is failing.
     */
    const DEADLOCK_ATTEMPTS = 5;

    /**
     * Host of database server.
     *
     * @type    string
     */
    protected $host;

    /**
     * Port of database server.
     *
     * @type    int
     */
    protected $port;

    /**
     * Name of database to connect to.
     *
     * @type    string
     */
    protected $database;

    /**
     * Username to use for connection.
     *
     * @type    string
     */
    protected $username;

    /**
     * Password to use for connection.
     *
     * @type    string
     */
    protected $password;

    /**
     * Constructor.
     *
     * @param   string          $host               Host of database server.
     * @param   int             $port               Port of database server.
     * @param   string          $database           Name of database.
     * @param   string          $username           Username to use for connection.
     * @param   string          $password           Optional password to use for connection.
     */
    public function __construct($host, $port, $database, $username, $password = '')
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Create database connection.
     *
     * @return  \Octris\Core\Db\Device\Mysql\Connection             Connection to a mysql database.
     */
    public function getConnection()
    {
        $cn = new \Octris\Core\Db\Device\Mysql\Connection(
            array(
                'host'     => $this->host,
                'port'     => $this->port,
                'database' => $this->database,
                'username' => $this->username,
                'password' => $this->password
            )
        );

        return $cn;
    }
}
