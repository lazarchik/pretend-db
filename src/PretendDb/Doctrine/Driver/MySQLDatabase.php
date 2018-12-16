<?php

namespace PretendDb\Doctrine\Driver;


class MySQLDatabase
{
    /** @var MySQLTable[] */
    protected $tables = [];

    /**
     * @param string $tableName
     * @return MySQLTable
     * @throws \RuntimeException
     */
    public function getTable($tableName)
    {
        if (!array_key_exists($tableName, $this->tables)) {
            throw new \RuntimeException("Table doesn't exist: ".$tableName);
        }
        
        return $this->tables[$tableName];
    }

    /**
     * @param string $tableName
     * @param MySQLColumnMeta[] $columns
     */
    public function createTable($tableName, $columns)
    {
        $tableObject = new MySQLTable($columns);
        
        $this->tables[$tableName] = $tableObject;
    }

    /**
     * @param string $tableName
     * @return bool
     */
    public function tableExists($tableName)
    {
        return array_key_exists($tableName, $this->tables);
    }
}
