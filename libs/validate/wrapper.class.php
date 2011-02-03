<?php

namespace org\octris\core\validate {
    require_once('wrapper/value.class.php');
    
    /**
     * Enable validation for arrays.
     *
     * @octdoc      c:validate/wrapper
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class wrapper extends \org\octris\core\type\collection
    /**/
    {
        /**
         * Constructor.
         *
         * @octdoc  m:wrapper/__construct
         * @param   array       $source         Source array to use for validation.
         */
        public function __construct(array $source)
        /**/
        {
            if (($cnt = count($source)) > 0) {
                $this->keys = array_keys($source);
                
                foreach ($source as $k => $v) {
                    $this->data[$k] = new \org\octris\core\validate\wrapper\value($v);
                }
            }
        }

        /**
         * Return array entry of specified offset.
         *
         * @octdoc  m:wrapper/offsetGet
         * @param   mixed       $offs           Offset to return.
         * @return  stdClass                    Value.
         */
        public function offsetGet($offs)
        /**/
        {
            if (($idx = array_search($offs, $this->keys, true)) !== false) {
                $key    = $this->keys[$idx];
                $return = $this->data[$key];
            } else {
                throw new \Exception("'$offs' is not available");
            }
        
            return $return;
        }

        /**
         * Overwrite an offset with an own value.
         *
         * @octdoc  m:wrapper/offsetSet
         * @param   mixed       $offs           Offset to overwrite.
         * @param   mixed       $value          Value to set.
         */
        public function offsetSet($offs, $value)
        /**/
        {
            throw new \Exception('forbidden to set wrapped data value');
        }
        
        /**
         * Filter wrapper for prefix.
         *
         * @octdoc  m:wrapper/filter
         * @param   string                                      $prefix     Prefix to use for filter.
         * @return  \org\octris\core\validate\wrapper\filter                Filter iterator.
         */
        public function filter($prefix)
        /**/
        {
            return new \org\octris\core\validate\wrapper\filter($this->getIterator(), $prefix);
        }

        /**
         * Set a value in wrapper.
         *
         * @octdoc  m:wrapper/set
         * @param   string      $name           Name of value to set.
         * @param   mixed       $value          Value to set.
         * @param   mixed       $validator      Validation instance or type name.
         * @param   array       $options        Optional options.
         * @return  bool                        Result of validation.
         */
        public function set($name, $value, $validator, array $options = array())
        /**/
        {
            if (is_scalar($validator) && class_exists($validator)) {
                $validator = new $validator($options);
            }

            if (!($validator instanceof \org\octris\core\validate\type)) {
                throw new \Exception('invalid validator');
            }
            
            if (($idx = array_search($name, $this->keys, true)) === false) {
                $this->keys[] = $name;
            }
                
            $this->data[$name] = new \org\octris\core\validate\wrapper\value($value);
            
            return $this->data[$name]->validate($validator, $options);
        }
    }
}
