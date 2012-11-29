<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\db\device\riak {
    /**
     * Riak database connection.
     *
     * @octdoc      c:riak/connection
     * @copyright   copyright (c) 2012 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class connection implements \org\octris\core\db\device\connection_if
    /**/
    {
        /**
         * Device the connection belongs to.
         *
         * @octdoc  p:connection/$device
         * @var     \org\octris\core\db\device\riak
         */
        protected $device;
        /**/

        /**
         * URI instance.
         *
         * @octdoc  p:connection/$uri
         * @var     \org\octris\core\type\uri
         */
        protected $uri;
        /**/

        /**
         * Constructor.
         *
         * @octdoc  m:connection/__construct
         * @param   \org\octris\core\db\device\riak     $device             Device the connection belongs to.
         * @param   array                               $options            Connection options.
         */
        public function __construct(\org\octris\core\db\device\riak $device, array $options)
        /**/
        {
            $this->device = $device;
            
            $this->uri = \org\octris\core\type\uri::create(
                $options['host'], $options['port']
            );
        }

        /**
         * Release connection.
         *
         * @octdoc  m:connection/release
         */
        public function release()
        /**/
        {
            $this->device->release($this);
        }

        /**
         * Check connection.
         *
         * @octdoc  m:connection/isAlive
         * @return  bool                                            Returns true if the connection is alive.
         */
        public function isAlive()
        /**/
        {
            $uri = clone($this->uri);
            $uri->path = '/ping';
            
            $result = (new \org\octris\core\net\client\http($uri))->execute();
            
            return (trim($result) == 'OK');
        }

        /**
         * Return instance of collection object.
         *
         * @octdoc  m:connection/getCollection
         * @param   string          $name                               Name of collection to return instance of.
         * @return  \org\octris\core\db\device\riak\collection          Instance of riak collection.
         */
        public function getCollection($name)
        /**/
        {
            return new \org\octris\core\db\device\riak\collection(
                $this->device, 
                $this->db->selectCollection($name)
            );
        }
    }
}
