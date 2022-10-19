<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests;

use AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\App\Kernel;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends BaseTestCase
{
    protected Kernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }
}
