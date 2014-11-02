<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\Cache\Storage;

/**
 * MongoDB cache storage.
 *
 * @copyright   copyright (c) 2012-2014 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Mongodb extends \Octris\Core\Cache\Storage
{
    /**
     * Instance of MongoDB database device.
     *
     * @type    \octris\core\db\mongodb
     */
    protected $db;

    /**
     * Database connection.
     *
     * @type    \octris\core\db\mongodb\connection
     */
    protected $cn;

    /**
     * Namespace separator.
     *
     * @type    string
     */
    protected $ns_separator = '.';

    /**
     * Constructor.
     *
     * @param   \Octris\Core\Db\Mongodb     $db                     Instance of MongoDB database device.
     * @param   array                           $options                Optional cache options.
     */
    public function __construct(\Octris\Core\Db\Mongodb $db, array $options = array())
    {
        $this->db = $db;
        $this->cn = $db->getConnection();

        parent::__construct($options);

        $this->ns = 'caches' . $this->ns_separator . $this->ns;

        // create indexes
        $this->cn->ensureIndex(array('key' => 1), array('unique' => true));
    }

    /**
     * Make cache iteratable.
     */
    public function getIterator()
    {
        // TODO
    }

    /**
     * Return metadata from cache for a specified key.
     *
     * @param   string          $key                    The key of the value that should be removed.
     */
    public function getMetaData($key)
    {
    }

    /**
     * Compare and update a value. The value get's only updated, if the current value matches.
     *
     * @param   string          $key                    The key of the value to be updated.
     * @param   int             $v_current              Current stored value.
     * @param   int             $v_new                  New value to store.
     * @return  bool                                    Returns true, if the value was updated.
     */
    public function cas($key, $v_current, $v_new)
    {
        $result = $this->cn->update(
            $this->ns,
            array('key' => $key, 'value' => (int)$v_current),
            array('$set' => array('value' => (int)$v_new))
            // array('safe' => true)
        );

        return $result;
    }

    /**
     * Increment a stored value
     *
     * @param   string          $key                    The key of the value to be incremented.
     * @param   int             $step                   The step that the value should be incremented by.
     * @param   bool            $success                Optional parameter that returns true, if the update succeeded.
     * @return  int                                     The updated value.
     */
    public function inc($key, $step, &$success = null)
    {
        $result = $this->cn->command(
            array(
                'findandmodify' => $this->ns,
                'query'         => array('key' => $key),
                'update'        => array('$inc' => array('value' => $step)),
                'new'           => true
            )
        );

        return $result['value']['value'];
    }

    /**
     * Decrement a stored value.
     *
     * @param   string          $key                    The key of the value to be decremented.
     * @param   int             $step                   The step that the value should be decremented by.
     * @param   bool            $success                Optional parameter that returns true, if the update succeeded.
     * @return  int                                     The updated value.
     */
    public function dec($key, $step, &$success = null)
    {
        $result = $this->cn->command(
            array(
                'findandmodify' => $this->ns,
                'query'         => array('key' => $key),
                'update'        => array('$dec' => array('value' => $step)),
                'new'           => true
            )
        );

        return $result['value']['value'];
    }

    /**
     * Fetch data from cache without populating the cache, if no data is stored for specified id.
     *
     * @param   string          $key                    The key of the value to fetch.
     * @param   bool            $success                Optional parameter that returns true, if the fetch succeeded.
     * @return  mixed                                   The data stored in the cache.
     */
    public function fetch($key, &$success = null)
    {
        $return = null;

        if (($success = (($data = $this->cn->first($this->ns, array('key' => $key))) !== false))) {
            $return = $data;
        }

        return $return;
    }

    /**
     * Load a value from cache or create it from specified callback. In the latter case the created data returned by
     * the callback will be stored in the cache.
     *
     * @param   string          $key                    The key of the value to be loaded.
     * @param   callable        $cb                     Callback to call if the key is not found in the cache.
     * @param   int             $ttl                    Optional ttl. Uses the configured ttl if not specified.
     * @return  mixed                                   Stored data.
     */
    public function load($key, callable $cb, $ttl = null)
    {
        if (!($data = $this->cn->first($this->ns, array('key' => $key)))) {
            $data = $cb();

            $this->save($key, $data, $ttl);
        }

        return $data;
    }

    /**
     * Store a value to the cache.
     *
     * @param   string          $key                    The key the value should be stored in.
     * @param   mixed           $data                   Arbitrary (almost) data to store.
     * @param   int             $ttl                    Optional ttl. Uses the configured ttl if not specified.
     */
    public function save($key, $data, $ttl = null)
    {
        $this->cn->update(
            $this->ns,
            array('key'    => $key),
            array('$set'   => array('value' => $data)),
            array('upsert' => true)
        );
    }

    /**
     * Checks if a key exists in the cache.
     *
     * @param   string          $key                    The key to test.
     * @return  bool                                    Returns true if the key exists, otherwise false.
     */
    public function exists($key)
    {
        return ($this->cn->count($this->ns, array('key' => $key)) > 0);
    }

    /**
     * Remove a value from the cache.
     *
     * @param   string          $key                    The key of the value that should be removed.
     */
    public function remove($key)
    {
        $this->cn->remove($this->ns, array('key' => $key));
    }

    /**
     * Clear the entire cache.
     */
    public function clear()
    {
        $this->cn->remove($this->ns, array());
    }
}
