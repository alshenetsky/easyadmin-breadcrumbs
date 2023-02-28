<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\DependencyInjection;

use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\Breadcrumbs;
use Alshenetsky\EasyAdminBreadcrumbs\Twig\BreadcrumbsExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EasyAdminBreadcrumbsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(Breadcrumbs::class)
            ->addTag('easyadmin-breadcrumbs-bundle');
        $container->registerForAutoconfiguration(BreadcrumbsExtension::class)
            ->addTag('twig.extension');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');
    }
}
