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
    
    /** @var User */
    protected $testUserEntity;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->driver = new MySQL();
        
        $this->driver->getStorage()->createTable("users", [
            new MySQLColumnMeta("id"),
            new MySQLColumnMeta("name"),
        ]);
        
        $this->entityMgr = $this->getEntityManager();

        $this->assertNull($this->entityMgr->find(User::class, 1), "We haven't created the user yet");
        
        $this->testUserEntity = new User();
        $this->testUserEntity->id = 1;
        $this->testUserEntity->name = "new_user";
        
        $this->entityMgr->persist($this->testUserEntity);
        $this->entityMgr->flush();
        
        $this->entityMgr->clear();
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
        $userEntity = $this->entityMgr->getRepository(User::class)->findOneBy([
            "id" => 1,
            "name" => "new_user",
        ]);
        
        $this->assertInstanceOf(User::class, $userEntity, "find() should return an instance of the requested entity");
        $this->assertEquals($this->testUserEntity->id, $userEntity->id);
        $this->assertEquals($this->testUserEntity->name, $userEntity->name);
        
    }
    
    public function testBlah2()
    {
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select * from users where id = ? and name = ?");
        
        $preparedStatement->bindValue(1, 2);
        $preparedStatement->bindValue(2, "test");
        
        $preparedStatement->execute();
        
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select * from users where id = ? and name = ?");
        
        $preparedStatement->bindValue(1, 1);
        $preparedStatement->bindValue(2, "new_user");
        
        $preparedStatement->execute();
        
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select * from users where id = ?");
        
        $preparedStatement->bindValue(1, 2);
        
        $preparedStatement->execute();
    }
    
    public function testBlah3()
    {
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select 3 + NOT id * ! 5 > 10 x1, name as x2 from users");
        
        $preparedStatement->execute();
    }
}
