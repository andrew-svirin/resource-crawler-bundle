<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\App;

use AndrewSvirin\ResourceCrawlerBundle\ResourceCrawlerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends BaseKernel
{
    public function __construct($environment, $debug)
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

    public function getRootDir(): string
    {
        return __DIR__ . '/';
    }

    public function getCacheDir(): string
    {
        return $this->getRootDir() . 'storage/symfony-cache';
    }

    public function getLogDir(): string
    {
        return $this->getRootDir() . 'storage/symfony-cache';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . 'config/framework.yml');
        $loader->load($this->getRootDir() . 'config/services.yml');
        $loader->load($this->getRootDir() . 'config/resource_crawler.yml');
    }
}
