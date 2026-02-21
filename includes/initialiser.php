<?php

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
$project_path = dirname(__FILE__);
$project_path = str_replace('includes', '', $project_path);
defined('SITE_ROOT') ? null :
	define('SITE_ROOT', $project_path);

defined('SITE_PATH') ? null :
	define('SITE_PATH', dirname($_SERVER['PHP_SELF']));

defined('LIB_PATH') ? null : define('LIB_PATH', SITE_ROOT . 'includes');

// charger fichier config  avant tout
require_once(LIB_PATH . DS . 'config.php');

// charger fonctions
require_once(LIB_PATH . DS . 'fonctions.php');

// charger core objects
require_once(LIB_PATH . DS . 'session.php');
require_once(LIB_PATH . DS . 'bd.php');


// charger  classes
require_once(LIB_PATH . DS . 'accounts.php');
require_once(LIB_PATH . DS . 'societes.php');
require_once(LIB_PATH . DS . 'exercice.php');
require_once(LIB_PATH . DS . 'grade.php');
require_once(LIB_PATH . DS . 'tableau_1.php');
require_once(LIB_PATH . DS . 'tableau_2.php');
require_once(LIB_PATH . DS . 'detailTab1.php');
require_once(LIB_PATH . DS . 'detailTab1_hp.php');
require_once(LIB_PATH . DS . 'tableau_3.php');
require_once(LIB_PATH . DS . 'detailTab3.php');

require_once(LIB_PATH . DS . 'wilayas.php');
//require_once(LIB_PATH.DS.'commune.php');
