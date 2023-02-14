<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\App;

use AndrewSvirin\ResourceCrawlerBundle\ResourceCrawlerBundle;
use AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\App\CompilerPass\MakeServicesPublicPass;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
  public function __construct(string $environment, bool $debug)
  {
    parent::__construct($environment, $debug);

    (new Filesystem())->remove($this->getCacheDir());
  }

  protected function build(ContainerBuilder $containerBuilder): void
  {
    $containerBuilder->addCompilerPass(new MakeServicesPublicPass());
  }

  public function registerBundles(): iterable
  {
    return [
      new FrameworkBundle(),
      new DoctrineBundle(),
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
    $loader->load($this->getProjectDir() . '/tests/Fixtures/App/config/framework.yaml');
    $loader->load($this->getProjectDir() . '/tests/Fixtures/App/config/doctrine.yaml');
    $loader->load($this->getProjectDir() . '/tests/Fixtures/App/config/resource_crawler.yaml');
    $loader->load($this->getProjectDir() . '/tests/Fixtures/App/config/services.yaml');
  }
}
