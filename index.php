<?php
//if (!defined('AJAX')) define('AJAX', true);
require_once 'bootstrap.php';


$context = [
    'table_name' => 'controls',
    'ajax_path' => '/actions/ajax/grid_crud.php',
    'primary_id' => 'xid'
];


$_SESSION['search_fields'] = getArray(getSearchFields(),'search_fields');
tdump($_SESSION['search_fields'],'search_fields');


if(isset($_SESSION['search_fields_requested'])) tdump($_SESSION['search_fields_requested'],'search_fields_requested');
else tdump(array(),'search_fields_requested');



$fields = getGridConfiguration();
$valid_fields=getArray($fields,'grid_fields');


$_SESSION['valid_fields']=$valid_fields;
tdump($_SESSION['valid_fields'],'valid_fields');

$_SESSION['vaild_tables'] = getValidTables();
tdump($_SESSION['vaild_tables'],'vaild_tables');



$context = array_merge($context, $fields);
$context = array_merge($context, getSearchFields());

$context = array_merge($context, getJSControleclarations());
$context = array_merge($context, getJSControls());

tdump($context,'context');

$twig->display('@basic\table.html.twig', $context);

//$twig->display('@config\table.html.twig', $context);

function getArray($arr, $idx)
{
    
    $new_arr=array();
    foreach($arr[$idx] as $outter_key=>$outter_fields)
    {
        foreach($outter_fields as $key=>$val)
        {
            if($key === 'name') $new_arr[]=$val;
        }
        
    }
    return $new_arr;
}

function getValidTables()
{
    return array('controls');
}

function getSearchFields()
{
    
    return ['search_fields' => [
        ['name' => 'name'],
        ['name' => 'category']
    ]];

}


function getJSControls()
{
    return ['controls'=>[ 
        'grid'=>['name' => 'grid'],
        'gridDetails'=>['name' => 'gridDetails'],
        'controlsgrid'=>['name' => 'controlsgrid'],
        'controldetailsgrid'=>['name' => 'controldetailsgrid'],
        'tablesDropDown'=>['name' => 'tablesDropDown'],
        'typeDropDown'=>['name' => 'typeDropDown'],
        'dialog'=>['name' => 'dialog'],
        'gridDetails'=>['name' => 'gridDetails'],
    
    ]];
}

function getJSControleclarations()
{
    return ['control_declarations' => [
        ['name' => 'grid'],
        ['name' => 'gridDetails'],
        ['name' => 'controlsgrid'],
        ['name' => 'controldetailsgrid'],
        ['name' => 'tablesDropDown'],
        ['name' => 'typeDropDown'],
        ['name' => 'dialog'],
        ['name' => 'gridDetails'],
    ]];

    
}

function getGridConfiguration()
{
    return ['grid_fields' => [
        'xid'=>['name' => 'xid', 'hidden' => 'true'],
        'type'=>['name' => 'type', 'sortable' => 'true', 'filterable' => 'true'  ],
        'name'=>['name' => 'name'],
        'desc'=>['name' => 'desc'],
        'href'=>['name' => 'href', 'tmpl' => "'<a href={href}>{href}</a>'"],
        'category'=>['name' => 'category', 'sortable' => 'true', 'filterable' => 'true'],
        'createdon'=>['name' => 'createdon']
    ]];

   
}