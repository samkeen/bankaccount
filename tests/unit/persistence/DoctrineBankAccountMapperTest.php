<?php

use \Doctrine\ORM\EntityManager;

/**
 * @large
 */
class DoctrineBankAccountMapperTest extends PHPUnit_Extensions_Database_TestCase
{
    protected $db;
    protected $mapper;

    /**
     * @covers DoctrineBankAccountMapper::__construct
     */
    protected function setUp()
    {
        $this->db = new PDO('sqlite::memory:');

        $cache  = new \Doctrine\Common\Cache\ArrayCache;
        $config = new Doctrine\ORM\Configuration;
        $config->setProxyDir(__DIR__ . '/proxies');
        $config->setProxyNamespace('BankAccountProxies');
        $config->setAutoGenerateProxyClasses(TRUE);
        $config->setMetadataCacheImpl($cache);
        $config->setMetadataDriverImpl(
          $config->newDefaultAnnotationDriver(
            array(__DIR__ . '/../../../src/model')
          )
        );

        $em = EntityManager::create(array('pdo' => $this->db), $config);

        $this->mapper = new DoctrineBankAccountMapper($em);

        $this->db->exec(
          file_get_contents(__DIR__ . '/../../../database/bankaccount.sql')
        );

        parent::setUp();
    }

    public function getConnection()
    {
        return $this->createDefaultDBConnection($this->db, ':memory:');
    }

    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(
          __DIR__ . '/fixture/bankaccount-seed.xml'
        );
    }

    /**
     * @covers DoctrineBankAccountMapper::getAllIds
     */
    public function testListOfBankAccountIdsCanBeRetrieved()
    {
        $this->assertEquals(array(1, 2), $this->mapper->getAllIds());
    }

    /**
     * @covers DoctrineBankAccountMapper::findById
     */
    public function testBankAccountCanBeFoundById()
    {
        $ba = $this->mapper->findById(1);
        $this->assertEquals(1.0, $ba->getBalance());

        $ba = $this->mapper->findById(2);
        $this->assertEquals(2.0, $ba->getBalance());

        $this->assertSame($ba, $this->mapper->findById(2));
    }

    /**
     * @covers            DoctrineBankAccountMapper::findById
     * @expectedException OutOfBoundsException
     */
    public function testExceptionIsRaisedWhenBankAccountCannotBeFoundById()
    {
        $this->mapper->findById(3);
    }

    /**
     * @covers DoctrineBankAccountMapper::insert
     */
    public function testBankAccountCanBeInserted()
    {
        $this->mapper->insert(new BankAccount);

        $this->assertDataSetsEqual(
          $this->createFlatXMLDataSet(
            __DIR__ . '/fixture/bankaccount-after-insert.xml'
          ),
          $this->getConnection()->createDataSet()
        );
    }

    /**
     * @covers            DoctrineBankAccountMapper::insert
     * @covers            MapperException
     * @expectedException MapperException
     */
    public function testBankAccountCannotBeInsertedTwice()
    {
        $ba = new BankAccount;

        $this->mapper->insert($ba);
        $this->mapper->insert($ba);
    }

    /**
     * @covers DoctrineBankAccountMapper::update
     */
    public function testBankAccountCanBeUpdated()
    {
        $ba = $this->mapper->findById(1);
        $ba->withdrawMoney(1);

        $this->mapper->update($ba);

        $this->assertDataSetsEqual(
          $this->createFlatXMLDataSet(
            __DIR__ . '/fixture/bankaccount-after-update.xml'
          ),
          $this->getConnection()->createDataSet()
        );
    }

    /**
     * @covers            DoctrineBankAccountMapper::update
     * @covers            MapperException
     * @expectedException MapperException
     */
    public function testBankAccountThatDoesNotExistCannotBeUpdated()
    {
        $ba = new BankAccount;
        $this->mapper->update($ba);
    }

    /**
     * @covers DoctrineBankAccountMapper::delete
     */
    public function testBankAccountCanBeDeleted()
    {
        $ba = $this->mapper->findById(1);

        $this->mapper->delete($ba);

        $this->assertDataSetsEqual(
          $this->createFlatXMLDataSet(
            __DIR__ . '/fixture/bankaccount-after-delete.xml'
          ),
          $this->getConnection()->createDataSet()
        );
    }

    /**
     * @covers            DoctrineBankAccountMapper::delete
     * @covers            MapperException
     * @expectedException MapperException
     */
    public function testBankAccountThatDoesNotExistCannotBeDeleted()
    {
        $ba = new BankAccount;
        $this->mapper->delete($ba);
    }
}
