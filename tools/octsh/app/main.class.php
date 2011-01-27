<?php

namespace org\octris\core\octsh\app {
    /**
     * Main application class for octris shell.
     *
     * @octdoc      c:app/main
     * @copyright   copyright (c) 2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    class main extends \org\octris\core\app\cli
    /**/
    {
        /**
         * Mapping of an option to an application class
         *
         * @octdoc  v:cli/$option_map
         * @var     
         */
        protected $option_map = array(
            '--help'    => 'org\octris\core\octsh\app\help'
        );
        /**/
    }
}
