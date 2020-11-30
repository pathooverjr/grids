<?php

namespace App;

class Database
{
    //https://sourcemaking.com/design_patterns/proxy/php
    protected $db;
    protected $dblayer;
    protected $dsn;

    protected $tables = array();

    protected $lastRecordSet = null;

    protected $table_insert_stmt = null;
    protected $identity_field = 'ID';
    protected $debug = true;
    protected $data_dir='data';

    public function __construct($options = [])
    {
        $this->initLog();
        if (!empty($options) && is_array($options)) {
            $success = $this->OpenDB($options);
            if(!$success) throw new \Exception("Failed to OpenDB");
        }
    }

    public function getDB()
    {
        return $this->db;
    }

    public function OpenDB($options = [])
    {
        if (empty($options) || !is_array($options) || !array_key_exists('dblayer',$options) ) {
            return false;
        }
        
        $this->dblayer = $options['dblayer'];
        if ('sqlite' === $options['dblayer']) {
            if(!array_key_exists('dbpath',$options)) throw new \Exception("Missing Parameter: dbpath");
            if(array_key_exists('dbdatadir',$options) ) $this->data_dir=$options['dbdatadir'];
            $db_path=BASEPATH.DIRECTORY_SEPARATOR.$this->data_dir.DIRECTORY_SEPARATOR.$options['dbpath'];
            //tdump($db_path);
            $db_path=realpath($db_path);
            //tdump($db_path);
            $this->dsn = 'sqlite:' . $db_path;
            //$this->log('#Database->OpenDB : sqlite dsn: '.$this->dsn,$options);
            return $this->connectSQLite();
        } elseif ('PDO' === $options['dblayer']) {
           
            return $this->ConnectPDO();
        }
    }
    protected function connectSQLite()
    {
        try {
            //tdump('#connectSQLite dsn : '.$this->dsn);
            $this->db = new \PDO($this->dsn);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $ex) {
            $error = json_encode(array(
                'error' => array(
                    'msg' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                ),
            ));
            $this->log($error);
            throw new \Exception($error);
        }
        return true;
    }

    public function ConnectPDO()
    {
        try {
            # MS SQL Server https://docs.microsoft.com/en-us/sql/connect/php/pdo-construct?view=sql-server-2017
            $server = $_ENV['DB_SERVER'];
            $dbname = $_ENV['DB_DATABASE'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            $dsn = 'sqlsrv:Server = ' . $server;
            $dsn .= ";Database = " . $dbname;
            $charset = 'utf8mb4'; // use?
            $this->db = new \PDO($dsn, $user, $pass);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $ex) {
            $error = json_encode(array(
                'error' => array(
                    'msg' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                ),
            ));
            $this->log($error);
            throw new \Exception($error);
        }
        return true;
    }
    protected function initLog()
    {
        $this->log = new \Monolog\Logger('database');
        $log_path = BASEPATH.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'database.log';
        //tdump('#log_path : '.$log_path);
        $streamHandler = new \Monolog\Handler\StreamHandler($log_path, \Monolog\Logger::DEBUG);
        $streamHandler->setFormatter(new \Monolog\Formatter\JsonFormatter());
        $this->log->pushHandler($streamHandler);
    }
    public function log($msg, $context = [])
    {
        $this->log->info($msg, $context);
    }
    public function deleteGridRecord($id, $tblname = false, $idkey = false)
    {
        // TODO wrap in try block, and catch errors
        $sql = 'DELETE FROM ' . $tblname ?: $this->tblname;
        $sql .= ' WHERE ' . $idkey ?: $this->identity_field;
        $sql .= ' = ' . $this->db->quote($id);
        $rs = $this->db->exec($sql);
        return $this->lastRecordSet = $rs;
    }
    public function insertGridRecord($record)
    {
        // Sanity checks
        $isstr = is_string($record);
        if ($isstr) $record = json_decode($record);
        $isarr = is_array($record);
        $isobj = is_object($record);
        if (!($isarr || $isobj)) {
            $this->log('#prepare insert: not an array or object, can\'t process');
        }
        // TODO wrap in try block, and catch errors
        // TODO try execute as an object and skip bindValue
        if (!$this->table_insert_stmt) $this->prepareGridInsertStmt($record);
        $this->log('#prepare insertstmt: ' . $this->table_insert_stmt, false, true);
        $stmt = $this->db->prepare($this->table_insert_stmt);
        $this->log('#insert:' . $this->table_insert_stmt);

        //foreach ($record as $f => $v)
        //{
        //    if($f != $this->identity_field) $stmt->bindValue(':' . $f, $v);
        //}
        // $rs should be the number of affected rows
        $isarr = is_array($record);
        if (!$isarr) $record = (array)$record;
        $hasidkey = array_key_exists($this->identity_field, $record);
        if ($hasidkey) {
            $idkey = $this->identity_field;
            unset($record[$idkey]);
        }
        $rs = $stmt->execute($record);
        $this->log('#insert result:' . $rs);
        return $this->lastRecordSet = $rs;
    }


    public function getGridRecords($tblname = false, $page, $limit, $sortby=null, $direction=null,$search=null)
    {
        // TODO wrap in try block, and catch errors
        if ($tblname) 
        {
            $this->tblname = $tblname;
        }
        else
        {
            $msg="Fatal: No table name provided in getGridRecords!";
            return ['error' => $msg];
        }

        $query = "SELECT count(*) FROM {$this->tblname}";
        $s = $this->db->query($query);
        $total_results = $s->fetchColumn();
        $total_pages = ceil($total_results/$limit);
        $this->log('#getGridRecords table:'.$this->tblname.': page:'.$page.': limit:'.$limit.': total_pages:'.$total_pages.': total_results:'.$total_results.':');
        //$sql = "SELECT * FROM {$this->tblname}";
        $sql = "SELECT * FROM {$this->tblname}" ; 
       
        $this->log('#getGridRecords #select: ' . $sql);   
        $start = ($page-1)*$limit;
        $search_sql = " WHERE ";
        if(!empty($search))
        {
            foreach($search as $key=>$val)
            {
                $search_sql .= "{$key} LIKE %{$val}% AND ";    
            }
        }
        $search = rtrim($search,'AND');
        $this->log('#getGridRecords #search: ' . $search);  
        if(!empty($soryby) && !empty($direction)) $sql .= " ORDERBY {$sortby} {$direction}";
        $sql .= " LIMIT :starting_limit,:end_limit";
        $stmt = $this->db->prepare($sql);
        
        //$this->db->bindValue(':starting_limit', $start, PDO::PARAM_INT);
        //$this->db->bindValue(':end_limit', $limit, PDO::PARAM_INT);
        $this->log('#getGridRecords #select: ' . $sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $params=[':starting_limit'=>$start,':end_limit'=>$limit];
        $stmt->execute($params);
        $rs = $stmt->fetchAll();
        if ($rs) {
            $records = ['records' => $rs, 'total' => $total_results];
            return $records;
        }
        $msg="No Records returned!";
        return ['error' => $msg];
    }



    protected function prepareGridInsertStmt($tablerow)
    {
        $this->log('#prepare insert tablerow: ', $tablerow, true);
        /* if(is_object($tablerow)) {
            $tablerow = (array)$tablerow;
        } */
        $isstr = \is_string($tablerow);
        if ($isstr) $tablerow = json_decode($tablerow);
        $isarr = \is_array($tablerow);
        $isobj = \is_object($tablerow);
        if (!($isarr || $isobj)) {
            $this->log('#prepare insert: not an array or object, can\'t process');
        }
        $datafields = array();
        $named = array();
        foreach ($tablerow as $field => $value) {
            if ($field != $this->identity_field) {
                $datafields[] = $field;
                $named[] = ':' . $field;
            }
        }
        $namedins = implode(",", $named);
        $fields = implode(",", $datafields);
        $this->table_insert_stmt = "INSERT INTO {$this->tblname} ($fields) VALUES ($namedins)";
    }

    function createGridTestTables($sql = false)
    {
        /**************************************
         * Create tables                       *
         **************************************/
        $rs = 0;
        try {
            // set in constructor
            $tblname = $this->tblname;

            $this->db->exec("DROP TABLE IF EXISTS {$tblname}");


            $testsql = <<<SQL
        CREATE TABLE IF NOT EXISTS $tblname (
            ID INTEGER PRIMARY KEY,
            Name TEXT,
            PlaceOfBirth TEXT,
            DateOfBirth TEXT,
            CountryID INTEGER,
            CountryName TEXT,
            IsActive INTEGER DEFAULT 0,
            OrderNumber TEXT
            )

SQL;
            // Create table
            if (!$sql) $sql = $testsql;
            $sql = trim(preg_replace('/\s\s+/', ' ', $sql));
            $sql = preg_replace("/\r|\n/", "", $sql);
            $sql = rtrim(ltrim($sql));
            $this->log('#create ' . $sql);
            $rs = $this->db->exec($sql);
            return $rs;
        } catch (\PDOException $e) {
            $this->log($e->getMessage(), contextfl(__FILE__, __LINE__), 'error');
        }
        return $this->lastRecordSet = $rs;
    }

    function insertGridTestData($json_fname = false, $json = false)
    {
        // TODO specify json table data from external source
        if (!$json_fname) $json_fname = $this->data_dir.DIRECTORY_SEPARATOR.'players.json';
        if (!$json) $json = json_decode(file_get_contents($json_fname));
        // $json is expected as 'records' => [tablerows]
        $this->prepareGridInsertStmt($json->records[0]);
        try {
            $this->db->beginTransaction();
            foreach ($json->records as $row) {
                $this->insertGridRecord($row);
            }
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->log($e->getMessage(), contextfl(__FILE__, __LINE__), 'error');
            return false;
        }
    }

    public function setRequiredApplicationParameters()
    {
        $appname = 'noappname';
        $sqlcode = 'nousername';
        $dbuser = $_ENV['DB_USER'] ?? '';

        if (isset($_SESSION['SQLCODE'])) {
            $sqlcode = $_SESSION['SQLCODE'];
        }
        if (defined('APP_NAME')) {
            $appname = APP_NAME;
        }

        $data = $sqlcode . '-' . $appname . '-' . $dbuser;

        $this->db->setConnectionParameter('APP', $data);
        $remote = 'unknownhost';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $remote = $_SERVER['REMOTE_ADDR'];
        }
        $this->db->setConnectionParameter('WSID', $remote);
    }
    public function getAllDBTables()
    {
        // TODO #hardcoded db specific
        // TODO wrap in try block, and catch errors
        $sql = "select * from information_schema.tables";
        $this->tables = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        // TODO optional create all insert statments here
        return $this->tables;
    }
    public function insert($tSQL, $parameters = array())
    {
        $stmt = $this->db->prepare($tSQL);
        return $stmt->execute($parameters);
    }
    public function executePreparedStatement($tSQL, $parameters = array())
    {
        // TODO #catch #exceptions here
        if ($this->dblayer == 'PDO') {
            $stmt = $this->db->prepare($tSQL);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $stmt->execute($parameters);
            return $this->rs = $stmt->fetchAll();
        } else {
            $stmt = $this->db->prepare($tSQL);
            $this->log('#execPreparedStatement :' . is_string($stmt) . ' : ' . var_export($stmt, true));
            return $this->lastRecordSet = $this->db->execute(array($stmt, $parameters));
        }
    }
    protected function validateTableName( $inTable ) 
{
    switch($inTable)
    {
        case 'controls':
            $outTable = 'controls';
            break;
    }

    return $outTable;
    
    }
}
