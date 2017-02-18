<?php
/**
 * @author: Eugene Lazarchik
 * @date: 7/6/16
 */

namespace PretendDb;


use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Tests\DBAL\Driver\AbstractMySQLDriverTest;
use Entities\User;
use PretendDb\Doctrine\Driver\MySQL;
use PretendDb\Doctrine\Driver\MySQLColumnMeta;

class PretendDbTest extends AbstractMySQLDriverTest
{
    /** @var MySQL */
    protected $driver;
    
    /** @var EntityManager */
    protected $entityMgr;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->driver = new MySQL();
        
        $this->driver->getStorage()->createTable("users", [
            new MySQLColumnMeta("id"),
            new MySQLColumnMeta("name"),
        ]);
        
        $this->entityMgr = $this->getEntityManager();
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        $metadataCache = new ArrayCache;
        
        $config = new Configuration();
        $config->setMetadataCacheImpl($metadataCache);
        
        $ormMetaDriver = $config->newDefaultAnnotationDriver([__DIR__.'/Entities'], false);
        $config->setMetadataDriverImpl($ormMetaDriver);
        
        $config->setProxyDir("/tmp/doctrine/proxies");
        $config->setProxyNamespace("DoctrineProxies");
        
        $connection = new Connection([], $this->driver, $config, null);
        
        $entityManager = EntityManager::create($connection, $config);
        
        return $entityManager;
    }

    protected function createDriver()
    {
        return $this->driver;
    }
    
    public function testBlah1()
    {
        $objEntityMgr = $this->entityMgr;

        $this->assertNull($objEntityMgr->find(User::class, 1), "We haven't created the user yet");
        
        $userEntity = new User();
        $userEntity->id = 1;
        $userEntity->name = "new_user";
        
        $objEntityMgr->persist($userEntity);
        $objEntityMgr->flush();
        
        $objEntityMgr->clear();
        
        var_dump($this->driver->getStorage()->getTable("users"));
        
        $userEntity = $objEntityMgr->getRepository(User::class)->findBy([
            "id" => $userEntity->id,
            "name" => $userEntity->name,
        ]);
        
        
        $this->assertInstanceOf(User::class, $userEntity, "find() should return an instance of the requested entity");
    }
    
    public function testBlah2()
    {
        $objEntityMgr = $this->entityMgr;

        $this->assertNull($objEntityMgr->find(User::class, 1), "We haven't created the user yet");
        
        $userEntity = new User();
        $userEntity->id = 1;
        $userEntity->name = "new_user";
        
        $objEntityMgr->persist($userEntity);
        $objEntityMgr->flush();
        
        $objEntityMgr->clear();
        
        $preparedStatement = $objEntityMgr->getConnection()
            ->prepare("Select * from users where (id < if(id, 1, 10) or (id2 = ? and name=?))");
        
        //$preparedStatement->bindValue(1, 1);
        $preparedStatement->bindValue(1, 2);
        $preparedStatement->bindValue(2, "test");
        
        $result = $preparedStatement->execute();
        
        var_dump("\result", $result);
    }
}
