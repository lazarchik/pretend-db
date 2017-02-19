<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/13/16
 */

namespace PretendDb\Doctrine\Driver;


class MySQLStorage
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
        $tableObject = new MySQLTable($tableName, $columns);
        
        $this->tables[$tableName] = $tableObject;
    }
}
