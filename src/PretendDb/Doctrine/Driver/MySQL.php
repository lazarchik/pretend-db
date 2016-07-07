<?php

namespace PretendDb\Doctrine\Driver;


use Doctrine\DBAL\Driver\AbstractMySQLDriver;


/**
 * @author: Eugene Lazarchik
 * @date: 7/6/16
 */
class MySQL extends AbstractMySQLDriver
{
    /**
     * @return string
     */
    public function getName()
    {
        return "pretenddb_mysql";
    }
    
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        return new MySQLConnection();
    }
}
