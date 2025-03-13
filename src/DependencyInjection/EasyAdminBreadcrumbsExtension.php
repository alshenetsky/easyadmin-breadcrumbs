<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\DependencyInjection;

use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\Breadcrumbs;
use Alshenetsky\EasyAdminBreadcrumbs\Contracts\BreadcrumbInterface;
use Alshenetsky\EasyAdminBreadcrumbs\Twig\BreadcrumbsExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class EasyAdminBreadcrumbsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(Breadcrumbs::class)
            ->addTag('easyadmin-breadcrumbs-bundle');
        $container->registerForAutoconfiguration(BreadcrumbsExtension::class)
            ->addTag('twig.extension');
        $container->registerForAutoconfiguration(BreadcrumbInterface::class)
            ->addTag(BreadcrumbInterface::SERVICE_TAG)
        ;

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');
    }
}
