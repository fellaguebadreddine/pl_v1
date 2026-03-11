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
require_once(LIB_PATH . DS . 'super_admin.php');

require_once(LIB_PATH . DS . 'tableau_1.php');
require_once(LIB_PATH . DS . 'tableau_1_1.php');
require_once(LIB_PATH . DS . 'detailTab1.php');
require_once(LIB_PATH . DS . 'detailTab1_sup.php');
require_once(LIB_PATH . DS . 'detail_tab_1_1.php');
require_once(LIB_PATH . DS . 'detailTab1_hp.php');

require_once(LIB_PATH . DS . 'tableau_2.php');
require_once(LIB_PATH . DS . 'detailTab2.php');
require_once(LIB_PATH . DS . 'tableau_2_1.php');
require_once(LIB_PATH . DS . 'detailTab2_1.php');
require_once(LIB_PATH . DS . 'tableau_2_2.php');
require_once(LIB_PATH . DS . 'detailTab2_2.php');

require_once(LIB_PATH . DS . 'tableau_3.php');
require_once(LIB_PATH . DS . 'detailTab3.php');

require_once(LIB_PATH . DS . 'tableau_4.php');
require_once(LIB_PATH . DS . 'detailTab4.php');
require_once(LIB_PATH . DS . 'tableau_4_1.php');
require_once(LIB_PATH . DS . 'detailTab4_1.php');

require_once(LIB_PATH . DS . 'tableau_5.php');
require_once(LIB_PATH . DS . 'detailTab5.php');

require_once(LIB_PATH . DS . 'tableau_6.php');
require_once(LIB_PATH . DS . 'detailTab6.php');
require_once(LIB_PATH . DS . 'tableau_6_1.php');
require_once(LIB_PATH . DS . 'tableau_6_2.php');
require_once(LIB_PATH . DS . 'detailTab6_2.php');

require_once(LIB_PATH . DS . 'tableau_7.php');
require_once(LIB_PATH . DS . 'detailTab7.php');

require_once(LIB_PATH . DS . 'tableau_8.php');

require_once(LIB_PATH . DS . 'tableau_9.php');
require_once(LIB_PATH . DS . 'detailTab9.php');

require_once(LIB_PATH . DS . 'tableau_10.php');
require_once(LIB_PATH . DS . 'tableau_11.php');
require_once(LIB_PATH . DS . 'tableau_12.php');

require_once(LIB_PATH . DS . 'wilayas.php');
require_once(LIB_PATH . DS . 'employees.php');
//require_once(LIB_PATH.DS.'commune.php');
