<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/7/16
 */

namespace PretendDb\Doctrine\Driver;

use Doctrine\DBAL\Driver\Connection;

class MySQLConnection implements Connection
{
    public function prepare($prepareString)
    {
        return new MySQLStatement();
    }
    
    public function query()
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function quote($input, $type=\PDO::PARAM_STR)
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function exec($statement)
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function lastInsertId($name = null)
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function beginTransaction()
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function commit()
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function rollBack()
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function errorCode()
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function errorInfo()
    {
        throw new \RuntimeException("Not implemented yet");
    }
}
