<?php

/**
* Bootstrap functions are used to get the application off the ground.
* Like loading configuration files and setting up database connections.
*/
class Bootstrap
{

  # Setup the 'base' of the application.
  #
  # We define useful path constants and extend the include search paths.
  #
  # @param [String] $dir full path to the root of the application.
  static function setup_base($dir) {
    define('ROOT_PATH',   dirname(realpath($dir)));
    define('APP_PATH',    ROOT_PATH . '/app');
    define('LIB_PATH',    ROOT_PATH . '/lib');
    define('DATA_PATH',   ROOT_PATH . '/data');
    define('PUBLIC_PATH', ROOT_PATH . '/public');
    define('VENDOR_PATH', ROOT_PATH . '/vendor');

    set_include_path(get_include_path() . PATH_SEPARATOR . LIB_PATH);
    set_include_path(get_include_path() . PATH_SEPARATOR . APP_PATH);
    set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);
  }

  # Setup for diffrent runtime enviroments.
  #
  # Standard enviroments: 'production' (default), 'development'
  static function setup_environment() {
    # Check the PHP version.
    version_compare(PHP_VERSION, '5.3', '<') and exit('PHP 5.3 or newer is required.');

    # Default to `production` env unless its already set
    if (!defined('RUNTIME_ENV')) {
      define('RUNTIME_ENV',  'production');
    }

    # Set timezone.
    #date_default_timezone_set('UTC');
    date_default_timezone_set('Europe/London');

    # We always want the details. But do we display it or just log?
    error_reporting(E_ERROR|E_WARNING|E_PARSE|E_NOTICE);
    #error_reporting(E_ALL|E_STRICT);

    switch (RUNTIME_ENV) {
      case 'development':
        ini_set('display_errors', true);
        ini_set('display_startup_errors', true);
        break;

      case 'production':
      default:
        ini_set('display_errors', false);
        ini_set('display_startup_errors', false);
        break;
    }
  }
  
  # Load a ini config file.
  #
  # @param [String] $filename of config file, include .ini
  # @return [Array] config settings in an associative array
  # @return [Boolean] false on failure
  static function load_config($filename) {
    $file_path = ROOT_PATH . '/config/' . $filename;
    $result = parse_ini_file($file_path, true);

    if ($result == false) {
      return false;
    }

    return $result[RUNTIME_ENV];
  }

  # Setup a PDO database connection.
  static function setup_database() {
    $config = Bootstrap::load_config('database.ini');
    \ORM::configure(array(
      'connection_string' => $config['dsn'],
      //'return_result_sets' => true
      'logging' => true // FIXME: Do not use in production.
    ));
    \ORM::configure('username', $config['username']);
    \ORM::configure('password', $config['password']);
  }

  # Setup twig template engine and register with Flight.
  static function setup_twig_instance() {
    # use file system loader.
    $loader = new \Twig_Loader_Filesystem(array(APP_PATH . '/views', APP_PATH . '/layouts')); 
    $twigConfig = array(
      //'cache'  => ROOT_PATH . '/tmp/cache/twig',
      'cache' => false,
      'debug' => true,
    );

    \Flight::register('view', 'Twig_Environment', array($loader, $twigConfig), function($twig) {
      // Add debug extension.
      $twig->addExtension(new \Twig_Extension_Debug());
    });
  }

  /* handle magic quotes */
  static function handle_magic_quotes() {
    // do we need to strip slashes?
    if (!get_magic_quotes_gpc()) return;

    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
      foreach ($val as $k => $v) {
        unset($process[$key][$k]);
        if (is_array($v)) {
          $process[$key][stripslashes($k)] = $v;
          $process[] = &$process[$key][stripslashes($k)];
        } else {
          $process[$key][stripslashes($k)] = stripslashes($v);
        }
      }
    }
    unset($process);
  }

}
