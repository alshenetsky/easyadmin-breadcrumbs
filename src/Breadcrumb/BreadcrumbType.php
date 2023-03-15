<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

enum BreadcrumbType: string
{
    case DETAIL = Crud::PAGE_DETAIL;
    case INDEX = Crud::PAGE_INDEX;
    case EDIT = Crud::PAGE_EDIT;
    case NEW = Crud::PAGE_NEW;
}
