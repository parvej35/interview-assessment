<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Container.php';
require_once __DIR__ . '/src/Logger.php';
require_once __DIR__ . '/src/ServiceA.php';
require_once __DIR__ . '/src/ServiceB.php';
require_once __DIR__ . '/vendor/autoload.php';


try{

    echo "<pre>";
    $container = new Container();

    //Composer's autoload functionality to automatically discover and classes annotated with the #[Service] attribute
    //Not working
    //$container->discoverServices(__DIR__ . '/src');


    //Approach 1: Register services manually in the container class.
    $container->register(Logger::class);


    // Approach 2: Register a service via a factory.
    // We explicitly define how the service is created using the factory function.
    $container->registerFactory(Logger::class, function () {
        return new Logger();
    }, true);


    // Approach 3: Register services manually in the container class without transient flag.
    // A singleton instance will be created, means the same instance is returned on every request.
    $container->register(ServiceA::class);


    // Approach 4: Register services manually in the container class with transient flag.
    // A new instance of the service will be created every time it is requested.
    $container->register(ServiceB::class, true);


    // Compile all services to detect circular dependencies
    $container->compile();


    //$logger = $container->get(Logger::class);
    //$logger->log('Hello Parvej');

    $serviceA = $container->get(ServiceA::class);
    $serviceA->doSomething();

    $serviceB = $container->get(ServiceB::class);
    $serviceB->doSomething();

    echo "</pre>";

} catch (Exception $e) {
    echo $e->getMessage();
}

