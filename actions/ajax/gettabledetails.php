<?php
if(!defined('AJAX')) define('AJAX',true);
if(!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '../../bootstrap.php';

use Optimal\DB;
use App\Model\TableConfigModel;

$tblname = getkey('tblname');

//echo json_encode(['tblname'=>$tblname]);
//die;
if(!$tblname) {
    $tblname='users';
}

$db = new DB();

$tblconfig = new TableConfigModel($db);

$rs = $tblconfig->getTableDetails($tblname);

echo json_encode($rs);