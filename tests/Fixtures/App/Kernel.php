<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\App;

use AndrewSvirin\ResourceCrawlerBundle\ResourceCrawlerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends BaseKernel
{
    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        (new Filesystem())->remove($this->getCacheDir());
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new ResourceCrawlerBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/tests/Fixtures/App/storage/symfony-cache';
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/tests/Fixtures/App/storage/symfony-cache';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/tests/Fixtures/App/config/framework.yml');
        $loader->load($this->getProjectDir() . '/tests/Fixtures/App/config/services.yml');
        $loader->load($this->getProjectDir() . '/tests/Fixtures/App/config/resource_crawler.yml');
    }
}
