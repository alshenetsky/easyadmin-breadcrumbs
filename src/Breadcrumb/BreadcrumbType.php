<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

enum BreadcrumbType: string
{
    case INDEX = Action::INDEX;
    case EDIT = Action::EDIT;
}
