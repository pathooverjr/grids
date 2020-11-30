<?php
require_once '..'.DIRECTORY_SEPARATOR.'bootstrap.php';

use Goutte\Client;



define('SQLITE_CONFIG_DB', BASEPATH.DIRECTORY_SEPARATOR.$_SERVER['DATA_DIR'].DIRECTORY_SEPARATOR.$_SERVER['SQLITE_CONFIG_DB']);
define('SQL_PATH', BASEPATH.DIRECTORY_SEPARATOR.$_SERVER['SQL_DIR'].DIRECTORY_SEPARATOR);
dropCreate();
loadTable();

//file_put_contents("dropdown.json",json_encode($dropdownConfig));
//$dropdownConfig = json_decode(file_get_contents('dropdown.json'), true);
//tdump($dropdownConfig);

echo "<H1>Crawl Completed!</H1>";

function loadTable()
{
    $types = [
        'checkbox',
        'datepicker',
        'datetimepicker',
        'dialog',
        'draggable',
        'dropdown',
        'droppable',
        'editor',
        'grid',
        'slider',
        'timepicker',
        'tree'
    ];
    foreach ($types as $type) {
        $config = getConfig($type, 'configuration');
        saveConfig($config);
    }
    foreach ($types as $type) {
        $config = getConfig($type, 'methods');
        saveConfig($config);
    }
    foreach ($types as $type) {
        $config = getConfig($type, 'events');
        saveConfig($config);
    }
}


function dropCreate()
{
    try {
        $db = new \PDO('sqlite:' . SQLITE_CONFIG_DB);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $db->exec('DROP TABLE IF EXISTS controls');

        $createsql = file_get_contents(SQL_PATH.DIRECTORY_SEPARATOR.'create'.DIRECTORY_SEPARATOR.'controls.sql');

        $sql = trim(preg_replace('/\s\s+/', ' ', $createsql));
        $sql = preg_replace("/\r|\n/", "", $sql);
        $sql = rtrim(ltrim($sql));
        $db->exec($sql);
    } catch (\PDOException $ex) {
        tdump($ex);
    }
}

function saveConfig($rs)
{
    try {
        $db = new \PDO('sqlite:' . SQLITE_CONFIG_DB);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $ex) {
    }

    $datafields = array();
    $named = array();
    foreach ($rs[0] as $field => $value) {
        $datafields[] = $field;
        $named[] = ':' . $field;
    }
    $namedins = implode(",", $named);
    $fields = implode(",", $datafields);
    $insertsql = "INSERT INTO controls ($fields) VALUES ($namedins)";

    try {
        $stmt = $db->prepare($insertsql);
        $db->beginTransaction();
        foreach ($rs as $row) {
            tdump($row);
            $stmt->execute($row);
        }
        $db->commit();
    } catch (\PDOException $e) {
        $db->rollBack();
    }
}

function getConfig($control, $category)
{

    $client = new Client();
    $URL = 'https://gijgo.com/' . $control . '/' . $category;
    tdump($URL);
    $crawler = $client->request('GET', $URL);

    $rs = $crawler->filter('.list-group-item')->each(
        function ($node) use ($control, $category) {
            $arr = array();
            $namepieces = explode(" ", trim($node->filter('h4')->text()));
            $name = trim(preg_replace('/\s\s+/', ' ', $namepieces[0]));
            $name = preg_replace("/\r|\n/", "", $name);
            $arr['type'] = $control;
            $arr['name'] = $name;
            $arr['desc'] = trim($node->filter('p')->text());
            $arr['href'] = 'https://gijgo.com' . trim($node->attr('href'));
            $arr['plugin'] = trim($node->filter('small')->text());
            $arr['category'] = $category;
            return $arr;
        }
    );

    return $rs;
}
