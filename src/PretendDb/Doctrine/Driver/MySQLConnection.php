<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/7/16
 */

namespace PretendDb\Doctrine\Driver;

use Doctrine\DBAL\Driver\Connection;

class MySQLConnection implements Connection
{
    /** @var MySQLStorage */
    protected $storage;
    
    /** @var int */
    protected $lastInsertId = 0;

    /**
     * @param MySQLStorage $storage
     * @internal param Parser $parser
     */
    public function __construct($storage)
    {
        $this->storage = $storage;
    }
    
    public function prepare($prepareString)
    {
        return new MySQLStatement($this->storage, $this, $prepareString);
    }
    
    public function query()
    {
        $methodArguments = func_get_args();
        
        /** @var string $queryString */
        $queryString = $methodArguments[0];
        
        $statementObject = new MySQLStatement($this->storage, $this, $queryString);
        
        $statementObject->execute();
        
        return $statementObject;
    }
    
    public function quote($input, $type=\PDO::PARAM_STR)
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function exec($statement)
    {
        throw new \RuntimeException("Not implemented yet");
    }

    /**
     * @TODO support $name
     * 
     * @param string|null $name
     * @return int
     */
    public function lastInsertId($name = null)
    {
        return $this->lastInsertId;
    }

    /**
     * @param int $lastInsertId
     */
    public function setLastInsertId($lastInsertId)
    {
        $this->lastInsertId = $lastInsertId;
    }
    
    public function beginTransaction()
    {
        // @TODO implement this
        
        return;
    }
    
    public function commit()
    {
        // @TODO implement this
        
        return;
    }
    
    public function rollBack()
    {
        // @TODO implement this
        
        return;
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
