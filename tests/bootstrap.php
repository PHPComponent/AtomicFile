<?php
/*
 * This file is part of PHPComponent/AtomicFile.
 *
 * Copyright (c) 2016 František Šitner <frantisek.sitner@gmail.com>
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

$dir_name = dirname(__FILE__);
@mkdir($dir_name.'/tmp', 0777, true);
require_once $dir_name.'/../vendor/autoload.php';

register_shutdown_function(
    function() use($dir_name){
        @unlink($dir_name.'/tmp/test.txt');
        @rmdir($dir_name.'/tmp');
    }
);