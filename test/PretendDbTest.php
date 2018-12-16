<?php

namespace PretendDb;


use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Tests\DBAL\Driver\AbstractMySQLDriverTest;
use Entities\BlogPost;
use Entities\User;
use PretendDb\Doctrine\Driver\MySQLDriver;
use PretendDb\Doctrine\Driver\Parser\Lexer;
use PretendDb\Doctrine\Driver\Parser\Parser;

class PretendDbTest extends AbstractMySQLDriverTest
{
    /** @var MySQLDriver */
    protected $driver;
    
    /** @var EntityManager */
    protected $entityMgr;
    
    /** @var User */
    protected $testUserEntity;
    
    /** @var User */
    protected $testUserEntity2;
    
    /** @var User */
    protected $testUserEntity3;
    
    /** @var BlogPost[] */
    protected $testBlogPostEntities;
    
    public function setUp()
    {
        parent::setUp();
        
        MySQLDriver::clearServersList();
        
        $this->driver = new MySQLDriver();
        
        $this->entityMgr = $this->createEntityManager();
        
        $this->entityMgr->getConnection()->query("CREATE DATABASE mydatabase");
        $this->entityMgr->getConnection()->query("USE mydatabase");
        $this->entityMgr->getConnection()->query("CREATE TABLE users (userID INT(10), name VARCHAR(127))");
        $this->entityMgr->getConnection()->query("CREATE TABLE blog_posts (postID INT(10), userID INT(10),
            body VARCHAR(255), createdAt TIMESTAMP)");
        
        $this->testUserEntity = new User();
        $this->testUserEntity->userID = 1;
        $this->testUserEntity->name = "new_user";
        $this->entityMgr->persist($this->testUserEntity);
        
        $this->testUserEntity2 = new User();
        $this->testUserEntity2->userID = 2;
        $this->testUserEntity2->name = "new_user2";
        $this->entityMgr->persist($this->testUserEntity2);
        
        $this->testUserEntity3 = new User();
        $this->testUserEntity3->userID = 3;
        $this->testUserEntity3->name = "new_user3";
        $this->entityMgr->persist($this->testUserEntity3);
        
        $blogPostCreationTime = new \DateTime("now");
        
        $this->testBlogPostEntities = [];
        
        $this->testBlogPostEntities[1] = new BlogPost();
        $this->testBlogPostEntities[1]->postID = 1;
        $this->testBlogPostEntities[1]->userID = 1;
        $this->testBlogPostEntities[1]->body = "post1";
        $this->testBlogPostEntities[1]->createdAt = $blogPostCreationTime;
        $this->entityMgr->persist($this->testBlogPostEntities[1]);
        
        $this->testBlogPostEntities[2] = new BlogPost();
        $this->testBlogPostEntities[2]->postID = 2;
        $this->testBlogPostEntities[2]->userID = 1;
        $this->testBlogPostEntities[2]->body = "post2_user1";
        $this->testBlogPostEntities[2]->createdAt = $blogPostCreationTime;
        $this->entityMgr->persist($this->testBlogPostEntities[2]);
        
        $this->testBlogPostEntities[3] = new BlogPost();
        $this->testBlogPostEntities[3]->postID = 3;
        $this->testBlogPostEntities[3]->userID = 2;
        $this->testBlogPostEntities[3]->body = "post3_user2";
        $this->testBlogPostEntities[3]->createdAt = $blogPostCreationTime;
        $this->entityMgr->persist($this->testBlogPostEntities[3]);
        
        $this->testBlogPostEntities[4] = new BlogPost();
        $this->testBlogPostEntities[4]->postID = 4;
        $this->testBlogPostEntities[4]->userID = 123;
        $this->testBlogPostEntities[4]->body = "post4_user123";
        $this->testBlogPostEntities[4]->createdAt = $blogPostCreationTime;
        $this->entityMgr->persist($this->testBlogPostEntities[4]);
        
        
        $this->entityMgr->flush();
        $this->entityMgr->clear();
    }

    /**
     * @return EntityManager
     */
    protected function createEntityManager()
    {
        $metadataCache = new ArrayCache;
        
        $config = new Configuration();
        $config->setMetadataCacheImpl($metadataCache);
        
        $ormMetaDriver = $config->newDefaultAnnotationDriver([__DIR__.'/Entities'], false);
        $config->setMetadataDriverImpl($ormMetaDriver);
        
        $config->setProxyDir("/tmp/doctrine/proxies");
        $config->setProxyNamespace("DoctrineProxies");
        
        $connection = new Connection(["host" => "mytestdb"], $this->driver, $config, null);
        
        $entityManager = EntityManager::create($connection, $config);
        
        return $entityManager;
    }

    protected function createDriver()
    {
        return $this->driver;
    }
    
    public function testDirectQuery()
    {
        $connection = $this->entityMgr->getConnection();
        
        $connection->query("set wait_timeout=900");
    }
    
    public function testBlah1()
    {
        $userEntity = $this->entityMgr->getRepository(User::class)->findOneBy([
            "userID" => 1,
            "name" => "new_user",
        ]);
        
        $this->assertInstanceOf(User::class, $userEntity, "find() should return an instance of the requested entity");
        $this->assertEquals($this->testUserEntity->userID, $userEntity->userID);
        $this->assertEquals($this->testUserEntity->name, $userEntity->name);
        
    }
    
    public function testBlah2()
    {
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select * from users where userID = ? and name = ?");
        
        $preparedStatement->bindValue(1, 2);
        $preparedStatement->bindValue(2, "test");
        
        $preparedStatement->execute();
        
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select * from users where userID = ? and name = ?");
        
        $preparedStatement->bindValue(1, 1);
        $preparedStatement->bindValue(2, "new_user");
        
        $preparedStatement->execute();
        
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select * from users where userID = ?");
        
        $preparedStatement->bindValue(1, 2);
        
        $preparedStatement->execute();
    }
    
    public function testBlah3()
    {
        $preparedStatement = $this->entityMgr->getConnection()
            ->prepare("Select 3 + NOT userID * ! 5 > 10 x1, name as x2 from users");
        
        $preparedStatement->execute();
    }
    
    public function testJoins1()
    {
        $entityMgr = $this->entityMgr;
        
        // Get users who created posts today
        
        $queryBuilder = $entityMgr->createQueryBuilder()
            ->select("u")
            ->from(User::class, "u")
            ->leftJoin(BlogPost::class, "p", "WITH", "u.userID = p.userID")
            ->where("p.createdAt > :yesterday AND p.body = :postBody")
            ->setParameter("yesterday", new \DateTime("yesterday"))
            ->setParameter("postBody", "post1");
        
        $result = $queryBuilder->getQuery()->getResult();
        
        $this->assertEquals([$this->testUserEntity], $result);
    }
    
    public function testJoins2()
    {
        $entityMgr = $this->entityMgr;
        
        $queryBuilder = $entityMgr->createQueryBuilder()
            ->select("p")
            ->from(User::class, "u")
            ->leftJoin(BlogPost::class, "p", "WITH", "u.userID = p.userID")
            ->where("u.name = :name")
            ->setParameter("name", $this->testUserEntity->name);
        
        $result = $queryBuilder->getQuery()->getResult();
        
        $this->assertEquals([$this->testBlogPostEntities[1], $this->testBlogPostEntities[2]], $result);
        
        
        
        $queryBuilder = $entityMgr->createQueryBuilder()
            ->select("p")
            ->from(User::class, "u")
            ->leftJoin(BlogPost::class, "p", "WITH", "u.userID = p.userID")
            ->where("u.name = :name")
            ->setParameter("name", $this->testUserEntity2->name);
        
        $result = $queryBuilder->getQuery()->getResult();
        
        $this->assertEquals([$this->testBlogPostEntities[3]], $result);
        
        
        
        
        $queryBuilder = $entityMgr->createQueryBuilder()
            ->select("u.userID, u.name, p.body, p.createdAt")
            ->from(User::class, "u")
            ->leftJoin(BlogPost::class, "p", "WITH", "u.userID = p.userID")
            ->where("u.name = :name")
            ->setParameter("name", $this->testUserEntity3->name);
        
        $result = $queryBuilder->getQuery()->getArrayResult();
        
        $expectedResult = [[
            "userID" => 3,
            "name" => "new_user3",
            "body" => NULL,
            "createdAt" => NULL,
        ]];
        
        $this->assertEquals($expectedResult, $result);
        
        
        
        
        $queryBuilder = $entityMgr->createQueryBuilder()
            ->select("u.userID, u.name, p.body, p.createdAt")
            ->from(User::class, "u")
            ->join(BlogPost::class, "p", "WITH", "u.userID = p.userID")
            ->where("u.name = :name")
            ->setParameter("name", $this->testUserEntity3->name);
        
        $result = $queryBuilder->getQuery()->getArrayResult();
        
        $this->assertEquals([], $result);
    }
    
    public function testTokenizeOr()
    {
        $parser = new Parser(new Lexer());
        
        // If bug is present, this will throw an exception:
        // Unknown token in simple expression: OR(or). Tokens: [OR(or), IDENTIFIER(ganization_name)]
        $parser->parse("organization_name");
    }
}
