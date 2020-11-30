<?php
if(!defined('AJAX')) define('AJAX',true);
if(!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '../../bootstrap.php';

use Optimal\DB;
use App\TableConfigModel;

$xid = getkey('xid');

//echo json_encode(['tblname'=>$tblname]);
//die;

if(!xid) {
    $xid='1';
}

$db = new DB();

$tblconfig = new TableConfigModel($db);

$rs = $tblconfig->deleteTableDetail($xid);

echo json_encode($rs);