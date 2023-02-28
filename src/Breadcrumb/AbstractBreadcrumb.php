<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb;

use Alshenetsky\EasyAdminBreadcrumbs\Contracts\BreadcrumbInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractBreadcrumb implements BreadcrumbInterface
{
    protected EntityManagerInterface $entityManager;
    private RequestStack $requestStack;
    private AdminUrlGenerator $adminUrlGenerator;

    protected string $name = '?';
    protected ?string $url = null;
    private ?string $entityId = null;
    private ?array $filters = null;

    #[Required]
    public function setDependencies(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        AdminUrlGenerator $adminUrlGenerator,
    ): void {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function supports(AdminContext $context): bool
    {
        return true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url ?? $this->getDefaultUrl()->generateUrl();
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function getParent(): ?string
    {
        return null;
    }

    public function gather(AdminContext $context): BreadcrumbData
    {
        return new BreadcrumbData();
    }

    public function provide(BreadcrumbData $gatheredData): BreadcrumbData
    {
        return new BreadcrumbData();
    }

    public function configure(BreadcrumbData $gatheredData): void
    {
    }

    public function getDefaultUrl(): AdminUrlGenerator
    {
        $context = $this->requestStack->getCurrentRequest()->get(EA::CONTEXT_REQUEST_ATTRIBUTE);

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setAction($this->getType()->value)
            ->setController($context->getCrudControllers()->findCrudFqcnByEntityFqcn($this->getEntityFqdn()))
        ;

        if ($this->entityId) {
            $url->setEntityId($this->entityId);
        }

        if ($this->filters) {
            $url->set('filters', $this->filters);
        }

        return $url;
    }

    public function setEntityId(string $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function setFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }
}
