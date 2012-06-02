<?php

/**
 * Test helper file, should be included from all unit tests
 */

// Enable full error reporting
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

// Set the include path
$_rootPath = dirname(dirname(__FILE__));
$_libPath = $_rootPath . DIRECTORY_SEPARATOR . 'library';
$_testsPath = $_rootPath . DIRECTORY_SEPARATOR . 'tests';

set_include_path($_libPath . PATH_SEPARATOR . 
                 $_testsPath . PATH_SEPARATOR . 
                 get_include_path());
                 

