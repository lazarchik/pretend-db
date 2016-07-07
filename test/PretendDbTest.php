<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/6/16
 */

namespace PretendDb;


use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Tests\DBAL\Driver\AbstractMySQLDriverTest;
use Entities\User;
use PretendDb\Doctrine\Driver\MySQL;

class PretendDbTest extends AbstractMySQLDriverTest
{
    public function getEntityManager()
    {
        $objMetadataCache = new ArrayCache;
        
        $objOrmConfig = new Configuration();
        $objOrmConfig->setMetadataCacheImpl($objMetadataCache);
        
        $objOrmMetaDriver = $objOrmConfig->newDefaultAnnotationDriver([__DIR__.'/Entities'], false);
        $objOrmConfig->setMetadataDriverImpl($objOrmMetaDriver);
        
        $objOrmConfig->setProxyDir("/tmp/doctrine/proxies");
        $objOrmConfig->setProxyNamespace("DoctrineProxies");
        
        $objEntityManager = EntityManager::create(['driverClass' => MySQL::class], $objOrmConfig);
        
        return $objEntityManager;
    }

    protected function createDriver()
    {
        return new \PretendDb\Doctrine\Driver\MySQL();
    }
    
    public function testBlah()
    {
        $objEntityMgr = $this->getEntityManager();
        
        $objUser = $objEntityMgr->find(User::class, 1);
        
        //$this->assertInstanceOf(User::class, $objUser, "find() should return an instance of the requested entity");
    }
}
