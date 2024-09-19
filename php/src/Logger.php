<?php
declare(strict_types=1);

use App\Service;

// Logger service marked with #[Service]
#[Service]
class Logger
{
    public function log(string $message): void
    {
        echo $message . PHP_EOL;
    }
}
