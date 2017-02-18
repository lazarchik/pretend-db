<?php

namespace PretendDb\Doctrine\Driver;


use Doctrine\DBAL\Driver\AbstractMySQLDriver;


/**
 * @author: Eugene Lazarchik
 * @date: 7/6/16
 */
class MySQL extends AbstractMySQLDriver
{
    /** @var MySQLStorage */
    protected $storage;

    public function __construct()
    {
        $this->storage = new MySQLStorage();
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return "pretenddb_mysql";
    }
    
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new MySQLConnection($this->storage);
    }

    /**
     * @return MySQLStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
