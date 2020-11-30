<?php
if(!defined('AJAX')) define('AJAX',true);
if(!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '../../bootstrap.php';

use Optimal\DB;
use App\Model\TableConfigModel;

$type = getkey('type');

if(!$type) {
    $type='dropdown';
}

$db = new DB();

$tblconfig = new TableConfigModel($db);

$rs = $tblconfig->getControlDetails($type);

echo json_encode(['records'=>$rs,'total'=>sizeof($rs)]);