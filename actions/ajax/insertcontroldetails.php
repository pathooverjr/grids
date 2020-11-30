<?php
if(!defined('AJAX')) define('AJAX',true);
if(!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '../../bootstrap.php';

use Optimal\DB;
use App\TableConfigModel;

$fieldid = getkey('xid');
$type = getkey('type');

//echo json_encode(['tblname'=>$tblname]);
//die;

if(!fieldid) {
    $fieldid='25';
}
if(!type) {
    $dropdown='25';
}

$db = new DB();

$tblconfig = new TableConfigModel($db);

$rs = $tblconfig->deleteTableDetail($xid);

echo json_encode($rs);