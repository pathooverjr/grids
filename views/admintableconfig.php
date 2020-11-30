<?php
if(!defined('AJAX')) define('AJAX', true);
require_once 'bootstrap.php';

//$database=new Optimal\DB();
//$dbr = $database->getDB();
//$database->showTables();


$fields = [ 'grid_fields' => [
    [ 'name' => 'ID', 'width' => '48' ],
    [ 'name' => 'Name', 'sortable' => 'true' ],
    [ 'name' => 'PlaceOfBirth', 'title' => 'Place Of Birth', 'sortable' => 'true' ]
]];

$context=[ 'table_name' => 'Player',
            'ajax_path' => '/grids/gridconfigajax.php',
            'delete_id' => 'xid: e.data.id'
];
$context=array_merge($context,$fields);

$twig->display('@config\table.html.twig',$context);