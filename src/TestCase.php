<?php

namespace ClawRock\MagentoTesting;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * Get ObjectManager helper instance
     *
     * @return \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = new ObjectManager($this);
        }

        return $this->objectManager;
    }

    /**
     * Shortcut for ObjectManager::getObject
     *
     * @param string $class
     * @param array $args
     *
     * @return object
     */
    protected function createObject($class, $args = [])
    {
        return $this->getObjectManager()->getObject($class, $args);
    }

    /**
     * Returns passed stub or value wrapped in stub.
     *
     * @param mixed $value
     *
     * @return \PHPUnit_Framework_MockObject_Stub
     */
    protected function stubOrValue($value)
    {
        if (is_object($value) && $value instanceof \PHPUnit_Framework_MockObject_Stub) {
            return $value;
        }

        return $this->returnValue($value);
    }

    /**
     * Gets value for an object's property.
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getObjectValue($object, string $property)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Mocks factory and sets it as object property
     *
     * @param object $object
     * @param string $property
     * @param string $class
     * @param mixed $createReturn Value to be returned by factory::create method
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function setObjectMockFactory(
        $object,
        string $property,
        string $class,
        $createReturn = null
    ) {
        $factory = $this->mockFactory($class, $createReturn);
        $this->setObjectValue($object, $property, $factory);

        return $factory;
    }

    /**
     * Mocks factory
     *
     * @param string $class
     * @param mixed $createReturn Value to be returned by factory::create method
     * @param array $mockMethods Methods to mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockFactory(string $class, $createReturn = null, $mockMethods = ['create'])
    {
        $factory = $this->getMockBuilder($class)
            ->setMethods($mockMethods)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->any())
            ->method('create')
            ->willReturn($createReturn ?: $this->returnSelf());

        return $factory;
    }

    /**
     * Mock collection
     *
     * @param string $class
     * @param array  $data
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockCollection($class, $data = [])
    {
        return $this->getObjectManager()->getCollectionMock($class, $data);
    }

    /**
     * Sets value for an object's property.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    protected function setObjectValue($object, string $property, $value)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Creates reference to object's property.
     *
     * @param object $object
     * @param string $property
     * @param string $reference Name of the property in this TestCase object
     */
    protected function referObjectValue($object, string $property, string $reference)
    {
        $this->$reference = $this->getObjectValue($object, $property);
    }

    /**
     * Mocks App object manager singleton. Helpful when someone used this in the
     * code instead of DI
     *
     * @return ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockObjectManagerSingleton()
    {
        /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockBuilder(ObjectManagerInterface::class)
                    ->getMockForAbstractClass();

        \Magento\Framework\App\ObjectManager::setInstance($mock);

        return $mock;
    }

    /**
     * Mocks plugin proceed param
     *
     * @param  mixed    $returnValue
     *
     * @return callable
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }

    /**
     * @return \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStoreManagerMock()
    {
        if ($this->storeManagerMock === null) {
            $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
                ->getMockForAbstractClass();
        }

        return $this->storeManagerMock;
    }

    /**
     * @return \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStoreMock()
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getStoreManagerMock()->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        return $storeMock;
    }

    /**
     * @return ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getScopeConfigMock()
    {
        if ($this->scopeConfigMock === null) {
            $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
                ->getMockForAbstractClass();
        }

        return $this->scopeConfigMock;
    }

    protected function mockScopeConfigGetValue($path, $value, $scope = ScopeInterface::SCOPE_STORE)
    {
        $this->getScopeConfigMock()->expects($this->any())
            ->method('getValue')
            ->with($path, $scope)
            ->will($this->stubOrValue($value));
    }
}
