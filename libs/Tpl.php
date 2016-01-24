<?php

/*
 * This file is part of the 'octris/core' package.
 *
 * (c) Harald Lapp <harald@octris.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Octris\Core;

use \Octris\Core\Tpl\Compiler as compiler;

/**
 * Main class of template engine.
 *
 * @copyright   copyright (c) 2010-2016 by Harald Lapp
 * @author      Harald Lapp <harald@octris.org>
 */
class Tpl
{
    /**
     * Escape types.
     */
    const ESC_NONE        = '';
    const ESC_AUTO        = 'auto';
    const ESC_ATTR        = 'attr';
    const ESC_CSS         = 'css';
    const ESC_HTML        = 'html';
    const ESC_HTMLCOMMENT = 'htmlcomment';
    const ESC_JS          = 'js';
    const ESC_TAG         = 'tag';
    const ESC_URI         = 'uri';

    /**
     * Instance of sandbox for executing template in.
     *
     * @type    \Octris\Core\Tpl\Sandbox
     */
    protected $sandbox;

    /**
     * Whether to fetch compiled template from cache.
     *
     * @type    bool
     */
    protected $use_cache = false;

    /**
     * Stores pathes to look into when searching for template to load.
     *
     * @type    array
     */
    protected $searchpath = array();

    /**
     * Instance of locale class.
     *
     * @type    \Octris\Core\L10n
     */
    protected $l10n;

    /**
     * Output path for compiled templates.
     *
     * @type    string
     */
    protected $outputpath = '/tmp';

    /**
     * Postprocessors.
     *
     * @type    array
     */
    protected $postprocessors = array();

    /**
     * Constructor.
     *
     * @param   string                      $charset    Charset of template.
     */
    public function __construct($charset = 'utf-8')
    {
        $this->sandbox = new Tpl\Sandbox($charset);
    }

    /**
     * Set l10n dependency.
     *
     * @param   \Octris\Core\L10n       $l10n       Instance of l10n class.
     */
    public function setL10n(\Octris\Core\L10n $l10n)
    {
        $this->sandbox->setL10n($l10n);
        $this->l10n = $l10n;
    }

    /**
     * Set values for multiple template variables.
     *
     * @param   array       $array      Key/value array with values.
     */
    public function setValues($array)
    {
        $this->sandbox->setValues($array);
    }

    /**
     * Set value for one template variable.
     *
     * @param   string      $name       Name of template variable to set value of.
     * @param   mixed       $value      Value to set for template variable.
     */
    public function setValue($name, $value)
    {
        $this->sandbox->setValue($name, $value);
    }

    /**
     * Register a custom template method.
     *
     * @param   string      $name       Name of template method to register.
     * @param   mixed       $callback   Callback to map to template method.
     * @param   array       $args       Optional parametert for specifying min/max number of arguments required for callback method.
     */
    public function registerMethod($name, callable $callback, array $args = array('min' => 0, 'max' => 0))
    {
        $this->sandbox->registerMethod($name, $callback, $args);
    }

    /**
     * Add a post-processor.
     *
     * @param   \Octris\Core\Tpl\PostprocessInterface       $processor          Instance of class for postprocessing.
     */
    public function addPostprocessor($processor)
    {
        $this->postprocessors[] = $processor;
    }

    /**
     * Register pathname for looking up templates in.
     *
     * @param   mixed       $pathname       Name of path to register.
     */
    public function addSearchPath($pathname)
    {
        if (is_array($pathname)) {
            foreach ($pathname as $path) {
                $this->addSearchPath($path);
            }
        } else {
            if (!in_array($pathname, $this->searchpath)) {
                $this->searchpath[] = $pathname;
            }
        }
    }

    /**
     * Returns iterator for iterating over all templates in all search pathes.
     *
     * @return  \ArrayIterator                                  Instance of ArrayIterator.
     */
    public function getTemplatesIterator()
    {
        foreach ($this->searchpath as $path) {
            $len = strlen($path);

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $filename => $cur) {
                $rel = substr($filename, $len);

                yield $rel => $cur;
            }
        }
    }

    /**
     * Set output path for compiled templates.
     *
     * @param   string      $path       Name of path to set.
     */
    public function setOutputPath($path)
    {
        if (!is_dir($path) || !is_writable($path)) {
            trigger_error('Path is not writable "' . $path . '"');
        } else {
            $this->outputpath = $path;
        }
    }

    /**
     * Set cache for template snippets.
     *
     * @param   \Octris\Core\Cache      $cache          Caching instance.
     */
    public function setSnippetCache(\Octris\Core\Cache $cache)
    {
        $this->sandbox->setSnippetCache($cache);
    }

    /**
     * Executes template toolchain -- compiler and compressors.
     *
     * @param   string      $inp        Input filename.
     * @param   string      $out        Output filename.
     * @param   string      $escape     Escaping to use.
     */
    protected function process($inp, $out, $escape)
    {
        $c = new Tpl\Compiler();

        if (!is_null($this->l10n)) {
            $c->setL10n($this->l10n);
        }

        $c->addSearchPath($this->searchpath);

        if (($filename = $c->findFile($inp)) !== false) {
            $tpl = $c->process($filename, $escape);

            foreach ($this->postprocessors as $processor) {
                $tpl = $processor->postProcess($tpl);
            }

            file_put_contents($out, $tpl);
        } else {
            die(sprintf(
                'unable to locate file "%s" in "%s"',
                $inp,
                implode(':', $this->searchpath)
            ));
        }

        return $out;
    }

    /**
     * Lint template.
     *
     * @param   string      $filename       Name of template file to compile.
     * @param   string      $escape         Optional escaping to use.
     */
    public function lint($filename, $escape = self::ESC_HTML)
    {
        $inp = ltrim(preg_replace('/\/\/+/', '/', preg_replace('/\.\.?\//', '/', $filename)), '/');

        $c = new Tpl\Lint();

        if (!is_null($this->l10n)) {
            $c->setL10n($this->l10n);
        }

        $c->addSearchPath($this->searchpath);

        if (($filename = $c->findFile($inp)) !== false) {
            $tpl = $c->process($filename, $escape);
        } else {
            die(sprintf(
                'unable to locate file "%s" in "%s"',
                $inp,
                implode(':', $this->searchpath)
            ));
        }
    }

    /**
     * Compile template.
     *
     * @param   string      $filename       Name of template file to compile.
     * @param   string      $escape         Optional escaping to use.
     */
    public function compile($filename, $escape = self::ESC_HTML)
    {
        $inp = ltrim(preg_replace('/\/\/+/', '/', preg_replace('/\.\.?\//', '/', $filename)), '/');
        $out = $this->outputpath . '/' . preg_replace('/[\s\.]/', '_', $inp) . '-' . $this->l10n . '.php';

        $out = $this->process($inp, $out, $escape);
    }

    /**
     * Check if a template exists.
     *
     * @param   string      $filename       Filename of template to check.
     * @return  bool                        Returns true if template exists.
     */
    public function templateExists($filename)
    {
        $inp = ltrim(preg_replace('/\/\/+/', '/', preg_replace('/\.\.?\//', '/', $filename)), '/');

        $c = new Tpl\Compiler();
        $c->addSearchPath($this->searchpath);

        return (($filename = $c->findFile($inp)) !== false);
    }

    /**
     * Render a template and send output to stdout.
     *
     * @param   string      $filename       Filename of template to render.
     * @param   string      $escape         Optional escaping to use.
     */
    public function render($filename, $escape = self::ESC_HTML)
    {
        $inp = ltrim(preg_replace('/\/\/+/', '/', preg_replace('/\.\.?\//', '/', $filename)), '/');
        $out = $this->outputpath . '/' . preg_replace('/[\s\.]/', '_', $inp) . '-' . $this->l10n . '.php';

        if (!$this->use_cache) {
            // do not use cache -- first process template using
            // template compiler and javascript/css compressor
            $out = $this->process($inp, $out, $escape);
        }

        $this->sandbox->render($out);
    }

    /**
     * Render a template and return output as string.
     *
     * @param   string      $filename       Filename of template to render.
     * @param   string      $escape         Optional escaping to use.
     * @return  string                      Rendered template.
     */
    public function fetch($filename, $escape = self::ESC_HTML)
    {
        ob_start();

        $this->render($filename, $escape);

        $return = ob_get_contents();
        ob_end_clean();

        return $return;
    }

    /**
     * Render a template and save output to a file.
     *
     * @param   string      $savename       Filename to save output to.
     * @param   string      $filename       Filename of template to render.
     * @param   string      $escape         Optional escaping to use.
     */
    public function save($savename, $filename, $escape = self::ESC_HTML)
    {
        file_put_contents($savename, $this->fetch($filename, $escape));
    }
}
