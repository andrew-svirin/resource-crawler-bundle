<?php

namespace AndrewSvirin\ResourceCrawlerBundle\Tests\Fixtures\App\CompilerPass;

use AndrewSvirin\ResourceCrawlerBundle\Process\ProcessManager;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathComposer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathNormalizer;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\PathValidator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegexCreator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Regex\PathRegexMatcher;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitutionCreator;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Path\Substitution\PathSubstitutor;
use AndrewSvirin\ResourceCrawlerBundle\Resource\Uri\UriFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeServicesPublicPass implements CompilerPassInterface
{

  public function process(ContainerBuilder $container): void
  {
    $container->getDefinition(PathValidator::class)->setPublic(true);
    $container->getDefinition(PathComposer::class)->setPublic(true);
    $container->getDefinition(UriFactory::class)->setPublic(true);
    $container->getDefinition(PathRegexMatcher::class)->setPublic(true);
    $container->getDefinition(PathRegexCreator::class)->setPublic(true);
    $container->getDefinition(PathSubstitutor::class)->setPublic(true);
    $container->getDefinition(PathSubstitutionCreator::class)->setPublic(true);
    $container->getDefinition(PathNormalizer::class)->setPublic(true);
    $container->getDefinition(ProcessManager::class)->setPublic(true);
  }
}
