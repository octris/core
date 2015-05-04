<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core\App\Web\Page;

/**
 * Custom error pages.
 *
 * @copyright   copyright (c) 2015 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Error extends \Octris\Core\App\Web\Page
{
    /**
     * Status code.
     *
     * @type    string
     */
    protected $status = '200';

    /**
     * Instance of a logger.
     *
     * @type    \Octris\Core\Logger
     */
    private $logger = null;

    /**
     * Constructor.
     *
     * @param   \Octris\Core\App                        Application instance.
     */
    public function __construct(\Octris\Core\App\Web $app)
    {
        parent::__construct($app);
    }

    /**
     * Configure a logger instance to log critical exception to.
     *
     * @param   \Octris\Core\Logger             $logger         Logger instance.
     */
    public function setLogger(\Octris\Core\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Implements abstract prepare methof of parent class.
     *
     * @param   \Octris\Core\App\Page           $last_page      Instance of last called page.
     * @param   string                          $action         Action that led to current page.
     * @return  mixed                                           Returns either page to redirect to or null.
     */
    public function prepare(\Octris\Core\App\Page $last_page, $action)
    {
        $this->status = $action;
    }

    /**
     * Renders critical error page.
     */
    public function render()
    {
        http_response_code((int)$this->status);

        $tpl = $this->getTemplate();
        $tpl->render('error/' . $this->status . '.html');
    }
}