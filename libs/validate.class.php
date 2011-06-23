<?php

/*
 * This file is part of the 'org.octris.core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace org\octris\core {
    /**
     * Validation base class.
     *
     * octdoc       c:core/validate
     * @copyright   copyright (c) 2010-2011 by Harald Lapp
     * @author      Harald Lapp <harald.lapp@gmail.com>
     */
    class validate 
    /**/
    {
        /**
         * Schema structure types.
         *
         * @octdoc  d:validate/T_OBJECT, T_ARRAY
         */
        const T_OBJECT = 1;
        const T_ARRAY  = 2;
        /**/
         
        /**
         * Available validation types.
         *
         * @octdoc  d:validate/T_ALPHA, T_ALPHANUM, T_BOOL, T_CALLBACK, T_DIGIT, T_PATH, T_PRINTABLE, T_PROJECT, T_XDIGIT, T_UTF8
         */
        const T_ALPHA     = '\org\octris\core\validate\type\alpha';
        const T_ALPHANUM  = '\org\octris\core\validate\type\alphanum';
        const T_BOOL      = '\org\octris\core\validate\type\bool';
        const T_CALLBACK  = '\org\octris\core\validate\type\callback';
        const T_CHAIN     = '\org\octris\core\validate\type\chain';
        const T_DIGIT     = '\org\octris\core\validate\type\digit';
        const T_PATH      = '\org\octris\core\validate\type\path';
        const T_PATTERN   = '\org\octris\core\validate\type\pattern';
        const T_PRINTABLE = '\org\octris\core\validate\type\printable';
        const T_PROJECT   = '\org\octris\core\validate\type\project';
        const T_URL       = '\org\octris\core\validate\type\url';
        const T_UTF8      = '\org\octris\core\validate\type\utf8';
        const T_XDIGIT    = '\org\octris\core\validate\type\xdigit';
        /**/
        
        /**
         * Protected constructor and magic clone method to prevent existance of multiple instances.
         *
         * @octdoc  m:validate/__construct, __clone
         */
        protected function __construct() {}
        protected function __clone() {}
        /**/
         
        /**
         * Test a value if it validates to the specified schema.
         *
         * @octdoc  m:validate/test
         * @param   mixed           $value              Value to test.
         * @param   array           $schema             Validation schema.
         * @param   int             $mode               Optional validation mode.
         * @return  mixed                               Returns true, if valid otherwise an array with error messages.
         */
        public static function validate($value, array $schema, $mode = \org\octris\core\validate\schema::T_STRICT)
        /**/
        {
            $instance = new \org\octris\core\validate\schema($schema, $mode);
            $is_valid = $instance->validate($value);
            
            return ($is_valid === true
                    ? $is_valid 
                    : $instance->getErrors());
        }
    }
}
