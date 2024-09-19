<?php
declare(strict_types=1);

use App\Service;

/*
 * The class is marked with #[Service] attribute.
 * It is a marker class to indicate that a class is a service.
 * The container uses this attribute to detect services.
 */
#[Service]
class ServiceB
{
    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->logger->log('ServiceB created');
    }

    public function doSomething(): void
    {
        $this->logger->log('ServiceB doing something');
    }
}
