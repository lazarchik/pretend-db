<?php

namespace PretendDb\Doctrine\Driver;


use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use PretendDb\Doctrine\Driver\Parser\Lexer;
use PretendDb\Doctrine\Driver\Parser\Parser;


/**
 * @author: Eugene Lazarchik
 * @date: 7/6/16
 */
class MySQL extends AbstractMySQLDriver
{
    /** @var MySQLStorage */
    protected $storage;
    
    /** @var Parser */
    protected $parser;

    public function __construct()
    {
        $this->storage = new MySQLStorage();
        $this->parser = new Parser(new Lexer());
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
        return new MySQLConnection($this->storage, $this->parser);
    }

    /**
     * @return MySQLStorage
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
