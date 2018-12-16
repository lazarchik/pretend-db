<?php

namespace PretendDb\Doctrine\Driver;


use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use PretendDb\Doctrine\Driver\Parser\Lexer;
use PretendDb\Doctrine\Driver\Parser\Parser;

class MySQLDriver extends AbstractMySQLDriver
{
    /**
     * Emulated MySQL servers to which we can connect and run queries against.
     * Must be static, since servers should exist independently of instances of the driver object
     * @var MySQLServer[]
     */
    protected static $servers = [];
    
    /** @var Parser */
    protected $parser;

    public function __construct()
    {
        $this->parser = new Parser(new Lexer());
    }
    
    public static function clearServersList()
    {
        self::$servers = [];
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
        $connectionParams = $params;
        $connectionParams["password"] = isset($connectionParams["password"]) ? "********" : null;
        $connectionParams["connect_username"] = $username;
        $connectionParams["connect_password"] = null === $password ? null : "********";
        $connectionParams["connect_driverOptions"] = $driverOptions;
        
        $databaseName = !empty($params["dbname"]) ? $params["dbname"] : null;
        
        $host = isset($params["host"]) ? $params["host"] : "unspecified_host";
        if (false === ip2long($host)) {
            $host = gethostbyname($host);
        }
        
        $port = isset($params["port"]) ? (int)$params["port"] : 3306;
        $serverCacheKey = $host.":".$port;
        
        if (!array_key_exists($serverCacheKey, self::$servers)) {
            self::$servers[$serverCacheKey] = new MySQLServer($this->parser);
        }
        
        return new MySQLConnection(self::$servers[$serverCacheKey], $databaseName, $connectionParams);
    }

    /**
     * @return MySQLServer[]
     */
    public function getServers()
    {
        return array_values(self::$servers);
    }
}
