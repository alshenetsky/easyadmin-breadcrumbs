<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\Contracts;

use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\BreadcrumbData;
use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\BreadcrumbType;
use Alshenetsky\EasyAdminBreadcrumbs\Exception\BreadcrumbNotApplicableException;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: [self::SERVICE_TAG])]
interface BreadcrumbInterface
{
    public const SERVICE_TAG = 'easyadmin.breadcrumb';

    /**
     * Breadcrumb CRUD type (also matches with EasyCorp\Bundle\EasyAdminBundle\Config\Action::*)
     * Used when generating a URL and determining which crumb is current.
     */
    public function getType(): BreadcrumbType;

    /**
     * The class name of the entity related to the CRUD controller for which the breadcrumb is created
     * Used when generating a URL and determining which crumb is current.
     *
     * @return class-string
     */
    public function getEntityFqdn(): string;

    /**
     * An additional criterion to determine if this crumb is current.
     */
    public function supports(AdminContext $context): bool;

    /**
     * Class name of the parent breadcrumb.
     *
     * @return class-string<BreadcrumbInterface>|null
     */
    public function getParent(): ?string;

    /**
     * Gathers data from current context and stores it into Breadcrumb data.
     * This method is invoking only for the current breadcrumb that matched current url.
     *
     * @throws BreadcrumbNotApplicableException
     */
    public function gather(AdminContext $context): BreadcrumbData;

    /**
     * Configures the breadcrumb by calling setName() and setUrl() here using previously gathered data @see gather().
     *
     * @throws BreadcrumbNotApplicableException
     */
    public function configure(BreadcrumbData $gatheredData): void;

    /**
     * This method provides parent breadcrumb with data it needs.
     * Should provide every data key that parent breadcrumb gathers: @see gather().
     *
     * @throws BreadcrumbNotApplicableException
     */
    public function provide(BreadcrumbData $gatheredData): BreadcrumbData;

    public function setName(string $name): static;

    public function getName(): string;

    public function setUrl(string $url): static;

    public function getUrl(): string;
}
