<?php
/**
 * 基础Model
 * @author ypliang
 * @version 2019-01-02
 */
namespace App\Model;
use DB;

class DBModel
{
    public $connectionName = null;
    public $connection = null;
    protected $table = null;

    public function __construct($db = 'keyword_analyses')
    {
        $this->connectionName = $db;
        $this->connection = DB::connection($this->connectionName);
    }


    protected function getInstanceConnection()
    {
        if (is_null($this->connection) || !isset ($this->connection)) {
            $this->connection = DB::connection($this->connectionName);
        }
        return $this->connection;
    }


    /**
     * 获取连接表对象
     * @param string $table
     * @return \Illuminate\Database\Query\Builder
     */
    public function getConnectionTable($table = '')
    {
        if (empty($table)) {
            $table = $this->table;
        }
        if (empty($table)) {
            return false;
        }
        return $this->getInstanceConnection()->table($table);
    }
}