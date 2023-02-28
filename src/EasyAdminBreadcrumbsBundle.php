<?php

namespace Alshenetsky\EasyAdminBreadcrumbs;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EasyAdminBreadcrumbsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
