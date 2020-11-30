<?php

namespace App;

use App\Database;

class Model
{
    protected $db;
    protected $log;

    public function __construct($options = [])
    {
        $this->initLog();
        if (!empty($options) && is_array($options)) {
            $this->db = new Database($options);
        }
    }
    public function GetDB()
    {
        return $this->db;
    }

    protected function initLog()
    {
        $this->log = new \Monolog\Logger('model');
        $streamHandler = new \Monolog\Handler\StreamHandler(BASEPATH, \Monolog\Logger::DEBUG);
        $streamHandler->setFormatter(new \Monolog\Formatter\JsonFormatter());
        $this->log->pushHandler($streamHandler);
    }
    public function log($msg, $context = [])
    {
        $this->log->info($msg, $context);
    }
}
