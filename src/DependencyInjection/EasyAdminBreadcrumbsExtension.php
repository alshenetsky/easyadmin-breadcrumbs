<?php

namespace Alshenetsky\EasyadminBreadcrumbs\DependencyInjection;

use Alshenetsky\EasyadminBreadcrumbs\Breadcrumb\Breadcrumbs;
use Alshenetsky\EasyadminBreadcrumbs\Twig\BreadcrumbsExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class EasyAdminBreadcrumbsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(Breadcrumbs::class)
            ->addTag('easyadmin-breadcrumbs-bundle');
        $container->registerForAutoconfiguration(BreadcrumbsExtension::class)
            ->addTag('twig.extension');
    }
}
