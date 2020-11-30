<?php
//if (!defined('AJAX')) define('AJAX', true);
if (!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '../../bootstrap.php';

use App\Model\Database;

$basepath = getBasePath();
$dbpath = $basepath . 'data\tblconfig.sqlite3';
$options = array(['dblayer' => 'sqlite']); //, 'dbpath' => $dbpath]
$db = new Database($options);
$tables = $db->getAllDBTables();

echo json_encode($tables);
