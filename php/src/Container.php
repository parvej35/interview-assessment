<?php
declare(strict_types=1);

use App\Service;

/**
 * Class Container
 * Dependency Injection Container that manages service registration, compilation, and retrieval.
 */
class Container
{
    private array $services = [];   // Registered services
    private array $resolving = [];  // Services currently being resolved
    private array $resolvedServices = []; // Resolved services
    private array $transientServices = []; // Transient services


    /*
     * Automatically discover and register services marked with the #[Service] attribute
     *
     * Function to discover services marked with the #[Service] attribute.
     * The function uses Composer's classmap to discover all classes.
     *
     * @param string $directory The directory to search for services.
     * @return void
     */
    /**
     * @throws ReflectionException
     */
    public function discoverServices(string $directory): void
    {
        // Use Composer's classmap to discover all classes
        $classMap = require __DIR__ . '/../vendor/composer/autoload_classmap.php';

        echo "Total ClassMap: " . count($classMap) . " classes\n";

        foreach ($classMap as $className => $file) {
            if (str_starts_with($file, realpath($directory))) {
                $reflector = new ReflectionClass($className);

                // Check for the #[Service] attribute
                $attributes = $reflector->getAttributes(Service::class);

                if (!empty($attributes)) {
                    // Register class if #[Service] attribute is found
                    $this->register($className);
                }
            }
        }
    }

    /**
     * This function registers a class by name ($class) in the container.
     * The class will be automatically resolved by the container when it is requested.
     *
     * @param string $class The service class name.
     * @param bool $isTransient Whether the service should be transient.
     * @return void
     */
    public function register(string $class, bool $isTransient = false): void
    {
        //A closure (or anonymous function) that will later be executed when the service is requested.
        $this->services[$class] = function () use ($class) {
            //Resolve dependencies via constructor injection.
            return $this->resolve($class);
        };

        // A transient service means a new instance is created every time the service is requested.
        if ($isTransient) {
            $this->transientServices[$class] = true;
        }
    }

    /*
     * This function registers a factory (a callable) to create the service.
     * Instead of letting the container automatically resolve and create the service,
     * we explicitly define how the service is created using the factory function.
     *
     * @param string $name The service class name.
     * @param callable $factory The factory function to create the service.
     * @param bool $isTransient Whether the service should be transient.
     * @return void
     */
    public function registerFactory(string $class, callable $factory, bool $isTransient = false): void
    {
        $this->services[$class] = $factory;
        if ($isTransient) {
            $this->transientServices[$class] = true;
        }
    }

    /**
     * Function to compile all services to detect circular dependencies.
     *
     * @return void
     * @throws Exception
     */
    public function compile(): void
    {
        foreach ($this->services as $name => $factory) {
            $this->get($name);
        }
    }

    /*
     * Function to retrieve a service from the container.
     *
     * @param string $name The service class name.
     * @return mixed The resolved service.
     */
    public function get(string $name): mixed
    {
        // return a resolved service if it exists and is not transient
        if (isset($this->resolvedServices[$name]) && !isset($this->transientServices[$name])) {
            return $this->resolvedServices[$name];
        }

        // throw an error if a requested service is not registered.
        if (!isset($this->services[$name])) {
            throw new Exception("Service '$name' is not registered.");
        }

        // throw an error if a circular reference is detected when compiling services.
        if (isset($this->resolving[$name])) {
            throw new Exception("Circular reference detected for service '$name'.");
        }

        $this->resolving[$name] = true;
        $service = $this->services[$name]();

        unset($this->resolving[$name]);

        if (!isset($this->transientServices[$name])) {
            $this->resolvedServices[$name] = $service;
        }

        return $service;
    }

    /**
     * Resolve dependencies via constructor injection.
     *
     * @param string $class The class name to resolve.
     * @throws ReflectionException
     * @return object The resolved class instance.
     */
    private function resolve(string $class): object
    {
        //The ReflectionClass creates a reflection object for the given class.
        //The ReflectionClass class reports information about a class.

        //https://www.php.net/manual/en/class.reflectionclass.php
        $reflector = new ReflectionClass($class);

        //Retrieves the constructor of the class.
        //If this class has no constructor, it returns null.
        //Otherwise, it returns a ReflectionMethod object.
        $constructor = $reflector->getConstructor();

        //If the class does not have a constructor, it simply creates a new instance of the class.
        if (!$constructor) {
            return new $class();
        }

        //Retrieves the list of parameters that the constructor requires.
        // The parameters are returned as an array of ReflectionParameter objects.
        $params = $constructor->getParameters();

        $dependencies = [];

        foreach ($params as $param) {
            /*$dependency = $param->getClass();
            if ($dependency) {
                $dependencies[] = $this->get($dependency->name);
            } else {
                throw new Exception("Unable to resolve dependency for '$class'.");
            }*/

            $type = $param->getType();
            echo "Type: " . $type . "\n";
            echo "Type name: " . $type->getName() . "\n";

            // Check if the type is not null and is a class (not a built-in type)
            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
            } else {
                throw new Exception("Unable to resolve dependency for '$class'.");
            }
        }

        //Creates a new class instance from given arguments
        //https://www.php.net/manual/en/reflectionclass.newinstanceargs.php
        return $reflector->newInstanceArgs($dependencies);
    }
}
