<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\Twig;

use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\Breadcrumbs;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Autoconfigure(tags: ['twig.extension'])]
class BreadcrumbsExtension extends AbstractExtension
{
    public function __construct(
        private Breadcrumbs $breadcrumbs,
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(name: 'breadcrumbs', callable: [$this, 'breadcrumbs'], options: ['is_safe' => ['html']]),
        ];
    }

    public function breadcrumbs()
    {
        return $this->breadcrumbs->render();
    }
}
