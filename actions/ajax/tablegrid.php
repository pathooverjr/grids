<?php
//if(!defined('AJAX')) define('AJAX',true);
if(!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '../../bootstrap.php';

use Optimal\DB;
use App\TableGridModel;

addlog('req');
