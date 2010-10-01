<?php

namespace %%SKEL_NAMESPACE%%\app {
    /****c* app/index
     * NAME
     *      index
     * FUNCTION
     *      index page
     * COPYRIGHT
     *      copyright (c) %%SKEL_YEAR%% by %%SKEL_COMPANY%%
     * AUTHOR
     *      %%SKEL_AUTHOR%% <%%SKEL_EMAIL%%>
     ****
     */

    class index extends \org\octris\core\app\page {
        /****v* index/$next_pages
         * SYNOPSIS
         */
        protected $next_pages = array(
            'default' => 'index',
        );
        /*
         * FUNCTION
         *      the index points to which the current page should allow requests 
         *      to, have to be defined through this array.
         ****
         */

        /****m* index/__construct
         * SYNOPSIS
         */
        public function __construct(\org\octris\core\app $app) 
        /*
         * FUNCTION
         *      constructor. the constructor is used to setup common settings for example
         *      validation rulesets must be defined through the page object constructor.
         * INPUTS
         *      *   $app (\org\octris\core\app) -- application object
         ****
         */
        {
            parent::__construct($app);
        }

        /****m* index/prepareRender
         * SYNOPSIS
         */
        public function prepareRender(\org\octris\core\app $app, \org\octris\core\app\page $last_page, $action) 
        /*
         * FUNCTION
         *      prepare rendering for a page. this method is called _BEFORE_ rendering 
         *      a page.
         * INPUTS
         *      *   $app (\org\octris\core\app) -- application object
         *      *   $last_page (\org\octris\core\app\page) -- last page that was active before current page has been activated
         * OUTPUTS
         *      a page can be returned
         ****
         */
        {        
        }

        /****m index/render
         * SYNOPSIS
         */
        public function render(\org\octris\core\app $app) 
        /*
         * FUNCTION
         *      this method is used to populate a template with data and send it to the 
         *      web browser.
         * INPUTS
         *      *   $app (\org\octris\core\app) -- application object
         ****
         */
        {
            die('error!');
        }
    }
}