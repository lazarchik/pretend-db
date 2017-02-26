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
        return new MySQLStatement($this->storage, $prepareString);
    }
    
    public function query()
    {
        $methodArguments = func_get_args();
        
        /** @var string $queryString */
        $queryString = $methodArguments[0];
        
        $statementObject = new MySQLStatement($this->storage, $queryString);
        
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
    
    public function lastInsertId($name = null)
    {
        throw new \RuntimeException("Not implemented yet");
    }
    
    public function beginTransaction()
    {
        // @FIXME: implement this
    }
    
    public function commit()
    {
        // @FIXME: implement this
    }
    
    public function rollBack()
    {
        // @FIXME: implement this
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
