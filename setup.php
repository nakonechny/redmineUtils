<?php

if (is_file(__DIR__ . '/setup.override.php')) {
	// setup.override.php usually defines APPMODE constant due to choose a config file to use
	include __DIR__ . '/setup.override.php';
}

if (! defined('APPMODE')) {
	define('APPMODE', 'sample');
}

define('ROOT', __DIR__ . '/');

if (! defined('NAF_ROOT')) {
	define('NAF_ROOT', ROOT . 'ext/naf/');
}
define('APP_CONF_ROOT', ROOT . 'conf/');
define('APP_LIB_ROOT', ROOT . 'lib/');

require_once NAF_ROOT . 'Naf.php';
Naf::loadConfig(APP_CONF_ROOT.'/'.APPMODE.'.php');
Naf::setDefaultLibraryRoot(APP_LIB_ROOT);
Naf::setUp();
