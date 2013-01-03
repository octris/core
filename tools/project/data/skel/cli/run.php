#!/usr/bin/env php
<?php

/*
 * This file is part of the '{{$directory}}' package.
 *
 * (c) {{$author}} <{{$email}}>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * {{$description}}
 *
 * @octdoc      h:{{$module}}/run.php
 * @copyright   copyright (c) {{$year}} by {{$company}}
 * @author      {{$author}} <{{$email}}>
 */
/**/

require_once(__DIR__ . '/libs/autoloader.class.php');

$main = new {{$namespace}}\libs\main();
$main->run();
