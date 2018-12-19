<?php

namespace PretendDb\Doctrine\Driver;

use Doctrine\DBAL\Driver\Connection;

class MySQLConnection implements Connection
{
    /** @var MySQLServer */
    protected $server;
    
    /** @var int */
    protected $lastInsertId = 0;
    
    /** @var string|null */
    protected $currentDatabaseName;
    
    /**
     * This is mostly for debugging, so that it's printed when we're doing var_dump's
     * @var array
     */
    protected $connectionParams;

    /**
     * @param MySQLServer $server
     * @param string|null $databaseName
     * @param array $connectionParams
     * @internal param Parser $parser
     */
    public function __construct($server, $databaseName, $connectionParams)
    {
        $this->server = $server;
        $this->currentDatabaseName = $databaseName;
        $this->connectionParams = $connectionParams;
    }
    
    /**
     * @param string $currentDatabaseName
     * @throws \InvalidArgumentException
     */
    public function setCurrentDatabaseName($currentDatabaseName)
    {
        if (!$this->server->databaseExists($currentDatabaseName)) {
            throw new \InvalidArgumentException("Can't USE a database that doesn't exist: ".$currentDatabaseName
                .". Existing databases: ".join(", ", array_keys($this->server->getExistingDatabaseNames())));
        }
        
        $this->currentDatabaseName = $currentDatabaseName;
    }

    /**
     * @return string
     */
    public function getCurrentDatabaseName()
    {
        return $this->currentDatabaseName;
    }
    
    public function prepare($prepareString)
    {
        return new MySQLStatement($this->server, $this, $prepareString);
    }
    
    public function query()
    {
        $methodArguments = func_get_args();
        
        /** @var string $queryString */
        $queryString = $methodArguments[0];
        
        $statementObject = new MySQLStatement($this->server, $this, $queryString);
        
        $statementObject->execute();
        
        return $statementObject;
    }
    
    public function quote($input, $type=\PDO::PARAM_STR)
    {
        /** TODO: implement dependency on charset of connection */
        return "'".preg_replace('~[\x00\x0A\x0D\x1A\x22\x27\x5C]~u', '\\\$0', $input)."'";
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
