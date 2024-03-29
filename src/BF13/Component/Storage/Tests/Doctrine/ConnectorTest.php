<?php
namespace BF13\Component\Storage\Tests\Doctrine;

use BF13\Component\Storage\DoctrineUnit\Connector;
use BF13\Component\Storage\DoctrineUnit\Repository;
use BF13\Component\Storage\Tests\Doctrine\Mock\DoctrineEntity;

/**
 * @author FYAMANI
 *
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{
    protected $storage;

    protected function setUp()
    {
        $this->em = $this->getMock('Doctrine\ORM\EntityManager', array('getRepository', 'persist', 'flush', 'remove'), array(), '', false);

        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\Kernel', array('locateResource', 'registerBundles', 'registerContainerConfiguration'), array(), '', false);

        $this->storage = new Connector($this->em, $this->kernel);
    }

    /**
     * Test the connection to the Handler
     * 
     */
    public function testGetHandler()
    {
        $stub_repository = $this->getMock('BF13\Component\Storage\StorageRepositoryInterface', array('find', 'findOneBy', 'getClassName', 'getDefaultSchema'), array(), '', false);

        $this->em->expects($this->any())->method('getRepository')->will($this->returnValue($stub_repository));

        $handler = $this->storage->getHandler('My\Identity');

        $this->assertInstanceOf('BF13\Component\Storage\StorageHandlerInterface', $handler);
    }

    /**
     * Test the connection to the Querizer
     * 
     */
    public function testGetQuerizer()
    {
        $schema = array('columns' => array(), 'from' => array(), 'conditions' => array());

        $stub_repository = $this->getMock('BF13\Component\Storage\StorageRepositoryInterface', array('getDefaultSchema'), array(), '', false);

        $stub_repository->expects($this->any())->method('getDefaultSchema')->will($this->returnValue($schema));

        $this->em->expects($this->any())->method('getRepository')->will($this->returnValue($stub_repository));

        $schema_path = __DIR__ . '/Mock/doctrine/DoctrineEntity.dql.yml';

        $this->kernel->expects($this->any())->method('locateResource')->will($this->returnValue($schema_path));

        $querizer = $this->storage->getQuerizer('@BF13DemoBundle:DoctrineEntity');

        $this->assertInstanceOf('BF13\Component\Storage\StorageQuerizerInterface', $querizer);
    }

    /**
     * Test the connection to the Querizer
     * 
     */
    public function testFailedGetQuerizer()
    {
        $this->setExpectedException('BF13\Component\Storage\Exception\StorageException');
        
        $schema = array('columns' => array(), 'from' => array(), 'conditions' => array());

        $stub_repository = $this->getMock('BF13\Component\Storage\StorageRepositoryInterface', array('getDefaultSchema'), array(), '', false);

        $stub_repository->expects($this->any())->method('getDefaultSchema')->will($this->returnValue($schema));

        $this->em->expects($this->any())->method('getRepository')->will($this->returnValue($stub_repository));

        $schema_path = false;

        $this->kernel->expects($this->any())->method('locateResource')->will($this->returnValue($schema_path));

        $querizer = $this->storage->getQuerizer('@BF13DemoBundle:DoctrineEntity');
    }

    public function testStore()
    {
        $item = new DoctrineEntity();
        
        $this->storage->store($item);
        
        $this->assertTrue(true);
    }

    public function testDelete()
    {
        $item = new DoctrineEntity();
        
        $this->storage->delete($item);
        
        $this->assertTrue(true);
    }
}

