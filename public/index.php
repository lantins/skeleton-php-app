<?php

# Set the runtime enviroment (`production` is the default if not set)
define('RUNTIME_ENV', 'development');

/* --- BOOTSTRAP APPLICATION ------------------------------------------------ */
require_once '../vendor/autoload.php';
require_once '../lib/bootstrap.class.php';
Bootstrap::setup_base(getcwd()); # Define path constants and extend include path.
Bootstrap::setup_environment();
#Bootstrap::setup_database();
Bootstrap::setup_twig_instance();
Bootstrap::handle_magic_quotes();

/* --- LOAD APPLICATION ------------------------------------------------------*/
#require_once LIB_PATH . '/myapp/my-extra-code.inc.php';
require_once APP_PATH . '/controllers/default.inc.php';

/* --- SESSIONS ------------------------------------------------------------- */
#session_save_path('../tmp/session');
#session_start();

/* --- START FLIGHT --------------------------------------------------------- */
Flight::start();

/* --- SHUTDOWN & CLEANUP --------------------------------------------------- */
#ORM::close_db();
