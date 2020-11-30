<?php
if(!defined('AJAX')) define('AJAX',true);
if(!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '../../bootstrap.php';

use Optimal\DB;
use App\TableConfigModel;

$db = new DB();

$tblconfig = new TableConfigModel($db);

$rs = $tblconfig->getControlSelect();

echo json_encode($rs);