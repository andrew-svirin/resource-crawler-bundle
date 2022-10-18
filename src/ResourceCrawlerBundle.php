<?php

namespace AndrewSvirin\ResourceCrawlerBundle;

use AndrewSvirin\ResourceCrawlerBundle\DependencyInjection\Compiler\CheckFilesystemClientPass;
use AndrewSvirin\ResourceCrawlerBundle\DependencyInjection\Compiler\CheckWebClientPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * ResourceCrawlerBundle
 */
class ResourceCrawlerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CheckFilesystemClientPass());
        $container->addCompilerPass(new CheckWebClientPass());
    }
}
