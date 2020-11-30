<?php
//if(!defined('AJAX')) define('AJAX',true);
if(!defined('APP_LOG_USEPRIORITY')) define('APP_LOG_USEPRIORITY', true);
require_once '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'bootstrap.php';

addlog('req',false);

$page=validatePage(getkey('page')) ?? 1;
$limit=validateLimit(getkey('limit')) ?? 10;
$action=getkey('action') ?? 'get';
$table=validateTable(getkey('table')) ?? 'controls';
$sortby=validateField(getkey('sortBy'));
$direction=validateDirection(getkey('direction'));

if(!empty($sortby)) $_SESSION['sortby'] = $sortby;
if(!empty($direction)) $_SESSION['direction'] = $direction;
$search_fields = null;
if (isset($_SESSION['search_fields'])) $search_fields = $_SESSION['search_fields'];
$search_fields_requested=array();
if(!empty($search_fields))
{
    
    foreach($search_fields as $key=>$val)
    {
        if(getkey($key)) $search_fields_requested[]=[$key=>$val];
        
    }
}
if(!empty($search_fields_requested)) $_SESSION['search_fields_requested']=$search_fields_requested;
else unset($_SESSION['search_fields_requested']);

$_SESSION['DB_TABLE']=$table;
$_SESSION['PAGE'] = $page;
$_SESSION['LIMIT'] = $limit;

tdump($page,'page');
tdump($limit,'limit');
tdump($action,'action');
tdump($_SESSION['DB_TABLE'],'Session: DB_TABLE');

if (empty($action)) {
   
    $msg = 'No action supplied in form post.';
    //logreq($msg,contextfl(__FILE__,__LINE__));
    $data = ['error' => $msg];
    return json_encode($data);
}

doaction($action);


function context_request($key,$val) {
    $data = ['#key' => $key,
             '#val' => $val
            ];
    return $data;
}

function doaction($action) {
    $response=null;
    switch ($action) {
        case 'get':
            $response=getdata();break;
        case 'save':
            $response=save($_POST);break;
        case 'delete':
            $response=delete($_POST);break;
        default:
        $msg = 'Required action (get, save, or delete) not supplied in form post.';
        //logreq($msg,contextfl(__FILE__,__LINE__));
        return json_encode(['error' => $msg]);
    }
}

function delete($post) {
    //logreq('#delete #post : ', $post, true);
    $options = initDBinfo();
    $db=new App\Database($options);
    $db->deleteGridRecord();
}

function validateLimit($limit)
{
    return is_numeric($limit) ? $limit : false;
}
function validatePage($page)
{
    return is_numeric($page) ? $page : false;
}
function validateDirection($dir)
{
    switch ($dir)
    {
        case 'asc': return 'asc';break;
        case 'desc': return 'desc';break;
        default:'asc';
    }
}

function validateTable($table)
{
    if(!isset($_SESSION['vaild_tables'])) return null;
    if(in_array($table, $_SESSION['vaild_tables'])) return $table;
    return null;
}

function validateField($field)
{
    if(!isset($_SESSION['vaild_fields'])) return null;
    if(in_array($field, $_SESSION['vaild_fields'])) return $field;
    return null;
}
function getdata()
{
    //logreq('#getdata #get');
    $options = initDBinfo();
    tdump($options,'db options');
    $db=new App\Database($options);

    $table=$_SESSION['DB_TABLE'];
    $page=$_SESSION['PAGE'];
    $limit=$_SESSION['LIMIT'];

    $rs=$db->getGridRecords($table,$page,$limit);
    echo json_encode($rs);
}

function save($post) {
    //logreq('#save #post : ', $post);
 
    $options = initDBinfo();
    try {
        $db=new App\Database($options);
    } catch (\PDOException $ex) {

    }
    //$db->log('#save post', $post['record'], true);
    $record = json_encode($post['record']);
    $db->insertGridRecord($record);

}
function initDBinfo() {

   /*
    $db = $_SESSION['DB_NAME'] ?? $_ENV['DB_SQLITE_DATABASE'] ?: $_ENV['DB_DATABASE'];
    $table = $_SESSION['DB_TABLE'] ?? $_ENV['DB_SQLITE_TABLE'] ?: 'players';
   
    if (\PHP_SESSION_ACTIVE === session_status()) {
        $_SESSION['DB_NAME'] = $db;
        $_SESSION['DB_TABLE'] = $table;
    }

    */
    $dblayer='sqlite';
    $dbpath='tblconfig.sqlite3';
    $dbdatadir='data';
    return ['dblayer'=>$dblayer,'dbpath'=>$dbpath,'dbdatadir'=>$dbdatadir];
}