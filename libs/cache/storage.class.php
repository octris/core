<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core\cache {
    /**
     * Cache storage base class.
     *
     * @octdoc      c:cache/storage
     * @copyright   copyright (c) 2012 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class storage implements \IteratorAggregate
    /**/
    {
        /**
         * Storage namespace.
         *
         * @octdoc  p:storage/$ns
         * @var     string
         */
        protected $ns = '';
        /**/
        
        /**
         * Namespace separator.
         *
         * @octdoc  p:storage/$ns_separator
         * @var     string
         */
        protected $ns_separator = ':';
        /**/

        /**
         * Time to live in seconds.
         *
         * @octdoc  p:storage/$ttl
         * @var     int
         */
        protected $ttl = 0;
        /**/

        /**
         * Constructor.
         *
         * @octdoc  m:storage/__construct
         * @param   array           $options                Cache options
         */
        public function __construct(array $options)
        /**/
        {
            if (isset($options['ns_separator'])) {
                $this->ns_separator = $options['ns_separator'];
            }
            if (isset($options['ns'])) {
                $this->ns = $options['ns'] . $this->ns_separator;
            }
            if (isset($options['ttl'])) {
                $this->ttl = $options['ttl'];
            }
        }

        /** methods that need to be implemented by child class **/

        /**
         * Compare and update a value. The value get's only updated, if the current value matches. The name of the
         * method CAS means: 'Compare And Swap'.
         *
         * @octdoc  m:storage/cas
         * @param   string          $key                    The key of the value to be updated.
         * @param   int             $v_current              Current stored value.
         * @param   int             $v_new                  New value to store.
         * @return  bool                                    Returns true, if the value was updated.
         */
        abstract public function cas($key, $v_current, $v_new);
        /**/

        /**
         * Increment a stored value
         *
         * @octdoc  m:storage/inc
         * @param   string          $key                    The key of the value to be incremented.
         * @param   int             $step                   The step that the value should be incremented by.
         * @param   bool            $success                Optional parameter that returns true, if the update succeeded.
         * @return  int                                     The updated value.
         */
        abstract public function inc($key, $step, &$success = null);
        /**/

        /**
         * Decrement a stored value.
         *
         * @octdoc  m:storage/dec
         * @param   string          $key                    The key of the value to be decremented.
         * @param   int             $step                   The step that the value should be decremented by.
         * @param   bool            $success                Optional parameter that returns true, if the update succeeded.
         * @return  int                                     The updated value.
         */
        abstract public function dec($key, $step, &$success = null);
        /**/

        /**
         * Load a value from cache or create it from specified callback. In the latter case the created data returned by 
         * the callback will be stored in the cache.
         *
         * @octdoc  m:storage/load
         * @param   string          $key                    The key of the value to be loaded.
         * @param   callable        $cb                     Callback to call if the key is not found in the cache.
         * @param   int             $ttl                    Optional ttl. Uses the configured ttl if not specified.
         * @return  mixed                                   Stored data.
         */
        abstract public function load($key, callable $cb, $ttl = null);
        /**/

        /**
         * Store a value to the cache.
         *
         * @octdoc  m:storage/save
         * @param   string          $key                    The key the value should be stored in.
         * @param   mixed           $data                   Arbitrary (almost) data to store.
         * @param   int             $ttl                    Optional ttl. Uses the configured ttl if not specified.
         */
        abstract public function save($key, $data, $ttl = null);
        /**/

        /**
         * Checks if a key exists in the cache.
         *
         * @octdoc  m:storage/exists
         * @param   string          $key                    The key to test.
         * @return  bool                                    Returns true if the key exists, otherwise false.
         */
        abstract public function exists($key);
        /**/

        /**
         * Remove a value from the cache.
         *
         * @octdoc  m:storage/remove
         * @param   string          $key                    The key of the value that should be removed.
         */
        abstract public function remove($key);
        /**/

        /**
         * Clear the entire cache.
         *
         * @octdoc  m:storage/clear
         */
        abstract public function clear();
        /**/
    }
}
