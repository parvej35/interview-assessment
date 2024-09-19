<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once 'src/Container.php';
require_once 'src/Logger.php';
require_once 'src/ServiceA.php';
require_once 'src/ServiceB.php';

class ContainerTest extends TestCase
{
    /*
     * Test Service Registration and Resolution:
     * Ensures that the container can register and resolve a service.
     */
    public function testRegisterAndResolve()
    {
        $container = new Container();
        $container->register(Logger::class);

        $service = $container->get(Logger::class);

        $this->assertInstanceOf(Logger::class, $service);
    }

    /*
     * Test non-transient (singleton) Service:
     * Ensures that non-transient (singleton) services return the same instance on each call.
     */
    public function testSingletonService()
    {
        $container = new Container();
        $container->register(Logger::class);

        $service1 = $container->get(Logger::class);
        $service2 = $container->get(Logger::class);

        $this->assertSame($service1, $service2);
    }

    /*
     * Test Transient Service:
     * Ensures that transient services return different instances on each call.
     */
    public function testTransientService()
    {
        $container = new Container();

        // Register as transient service
        $container->register(Logger::class, true);

        $service1 = $container->get(Logger::class);
        $service2 = $container->get(Logger::class);

        $this->assertNotSame($service1, $service2);
    }

    /*
     * Test Circular Dependency Detection:
     * Ensures that an exception is thrown when a circular dependency is detected.
     */
    /**
     * @throws Exception
     */
    public function testCircularDependency()
    {
        $this->expectException(Exception::class);

        $container = new Container();
        $container->register(TestServiceA::class);
        $container->register(TestServiceB::class);

        // Compiling services to detect circular dependency
        $container->compile();
    }

    /*
     * Test Unregistered Service:
     * Ensures that an exception is thrown when trying to resolve a service that has not been registered.
     */
    public function testUnregisteredService()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Service 'NonExistentService' is not registered.");

        $container = new Container();
        $container->get('NonExistentService');
    }

    /*
     * Test Constructor Injection:
     * Ensures that constructor dependencies are resolved and injected correctly.
     */
    public function testConstructorInjection()
    {
        $container = new Container();
        $container->register(ServiceA::class);//ServiceWithDependency
        $container->register(Logger::class);//DependencyService

        //$container->compile();

        $service = $container->get(ServiceA::class);

        $this->assertInstanceOf(ServiceA::class, $service);
        $this->assertInstanceOf(Logger::class, $service->get_dependency());
    }

    /*
     * Test Method Injection:
     * Ensures that a service can be registered using a factory function.
     */
    public function testServiceFactoryRegistration()
    {
        $container = new Container();
        $container->registerFactory(Logger::class, function () {
            return new Logger();
        });

        $service = $container->get(Logger::class);

        $this->assertInstanceOf(Logger::class, $service);
    }

}

class TestServiceA {
    public function __construct(TestServiceB $serviceB) {
        echo "Contractor of TestServiceA class";
    }
}

class TestServiceB {
    public function __construct(TestServiceA $serviceA) {
        echo "Contractor of TestServiceB class";
    }
}
