<?php

namespace org\octris\core {
    require_once('app/autoloader.class.php');
    
    use \org\octris\core\validate as validate;

    /**
     * Core application class.
     *
     * @octdoc      c:core/app
     * @copyright   copyright (c) 2010-2011 by Harald Lapp
     * @author      Harald Lapp <harald@octris.org>
     */
    abstract class app
    /**/
    {
        /**
         * Used in combination with app/getPath to determine path.
         * 
         * @octdoc  d:config/T_PATH_CACHE, T_PATH_DATA, T_PATH_ETC, T_PATH_HOST, T_PATH_LIBS, T_PATH_LIBSJS, T_PATH_LOCALE, T_PATH_RESOURCES, T_PATH_STYLES, T_PATH_LOG, T_PATH_WORK, T_PATH_WORK_LIBSJS, T_PATH_WORK_RESOURCES, T_PATH_WORK_STYLES, T_PATH_WORK_TPL
         */
        const T_PATH_CACHE          = '%s/cache/%s';
        const T_PATH_DATA           = '%s/data/%s';
        const T_PATH_ETC            = '%s/etc/%s';
        const T_PATH_HOST           = '%s/host/%s';
        const T_PATH_LIBS           = '%s/libs/%s';
        const T_PATH_LIBSJS         = '%s/host/%s/libsjs';
        const T_PATH_LOCALE         = '%s/locale/%s';
        const T_PATH_LOG            = '%s/log/%s';
        const T_PATH_RESOURCES      = '%s/host/%s/resources';
        const T_PATH_STYLES         = '%s/host/%s/styles';
        const T_PATH_TOOLS          = '%s/tools/%s';
        const T_PATH_WORK           = '%s/work/%s';
        const T_PATH_WORK_LIBS      = '%s/work/%s/libs';
        const T_PATH_WORK_LIBSJS    = '%s/work/%s/libsjs';
        const T_PATH_WORK_RESOURCES = '%s/work/%s/resources';
        const T_PATH_WORK_STYLES    = '%s/work/%s/styles';
        const T_PATH_WORK_TPL       = '%s/work/%s/templates';
        /**/

        /**
         * Used to abstract application context types.
         * 
         * @octdoc  d:app/T_CONTEXT_UNDEFINED, T_CONTEXT_CLI, T_CONTEXT_WEB, T_CONTEXT_TEST
         */
        const T_CONTEXT_UNDEFINED = 0;
        const T_CONTEXT_CLI       = 1;
        const T_CONTEXT_WEB       = 2;
        const T_CONTEXT_TEST      = 3;
        /**/

        /**
         * Application instance.
         *
         * @octdoc  v:app/$instance
         * @var     \org\octris\core\app
         */
        private static $instance = null;
        /**/

        /**
         * Context of the application.
         *
         * @octdoc  v:app/$context
         * @var     int
         */
        protected $context = self::T_CONTEXT_UNDEFINED;
        /**/
        
        /**
         * Application state.
         *
         * @octdoc  v:app/$state
         * @var     \org\octris\core\app\state
         */
        protected $state = null;
        /**/
        
        /**
         * Constructor is protected to force creation of instance using 'getInstance' method.
         *
         * @octdoc  m:app/__construct
         */
        protected function __construct()
        /**/
        {
            if (!$_ENV['OCTRIS_APP']->isSet || !$_ENV['OCTRIS_BASE']->isSet) {
                die("unable to import OCTRIS_APP or OCTRIS_BASE!\n");
            }

            if (!$_ENV->validate('OCTRIS_APP', validate::T_PATTERN, array('pattern' => '/^[a-z0-9.]+$/')) || !$_ENV->validate('OCTRIS_BASE', validate::T_PRINTABLE)) {
                print_r($_ENV);
                
                die("unable to import OCTRIS_APP or OCTRIS_BASE - invalid settings!\n");
            }
    
            $_ENV['OCTRIS_DEVEL']->value = ($_ENV->validate('OCTRIS_DEVEL', validate::T_BOOL) && $_ENV['OCTRIS_DEVEL']->value);
            
            $this->initialize();
        }

        /**
         * Abstract method definition. Initialize must be implemented by any subclass.
         *
         * @octdoc  m:app/initialize
         * @abstract
         */
        abstract public function initialize();
        /**/

        /**
         * Abstract method definition. Process must be implemented by any subclass.
         *
         * @octdoc  m:app/process
         * @abstract
         */
        abstract public function process();
        /**/
        
        /**
         * Helper method that is registered as error handler to catch non exceptional errors and convert them
         * to real exceptions.
         *
         * @octdoc  m:app/triggerError
         * @param   int             $code               Error code.
         * @param   string          $msg                The error message.
         * @param   string          $file               The file the error war raised in.
         * @param   int             $line               The line number the error was raised in.
         * @param   array           $context            Array of active symbol table when error was raised.
         */
        public static function triggerError($code, $string, $file, $line, $context)
        /**/
        {
            // TODO: implementation
        }

        /**
         * Return context the application is running in.
         *
         * @octdoc  m:app/getContext
         * @return  int                                 Application context.
         */
        public static final function getContext()
        /**/
        {
            return static::$context;
        }

        /**
         * Returns path for specified path type for current application instance.
         *
         * @octdoc  m:app/getPath
         * @param   string          $type               The type of the path to return.
         * @param   string          $module             Optional name of module to return path for. Default is: current application name.
         * @return  string                              Existing path or empty string, if path does not exist.
         */
        public static function getPath($type, $module = '')
        /**/
        {
            $return = sprintf(
                $type,
                $_ENV['OCTRIS_BASE']->value,
                ($module 
                    ? $module 
                    : $_ENV['OCTRIS_APP']->value)
            );

            return realpath($return);
        }

        /**
         * Return instance of main application class.
         *
         * @octdoc  m:app/getInstance
         * @return  \org\octris\core\app                Instance of main application class.
         */
        public static function getInstance()
        /**/
        {
            if (is_null(self::$instance)) {
                self::$instance = new static();
            }
            
            return self::$instance;
        }
    }

    // register error handler for 'normal' php errors
    set_error_handler(array('\org\octris\core\app', 'triggerError'), E_ALL);
}
