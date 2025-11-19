<?php

namespace Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb;

use Alshenetsky\EasyAdminBreadcrumbs\Contracts\BreadcrumbInterface;
use Alshenetsky\EasyAdminBreadcrumbs\Exception\BreadcrumbNotApplicableException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

final class Breadcrumbs
{
    /**
     * @var array<class-string<BreadcrumbInterface>,BreadcrumbInterface>
     */
    private array $breadcrumbs = [];

    /**
     * @param iterable<BreadcrumbInterface> $breadcrumbs
     */
    public function __construct(
        private RequestStack $requestStack,
        #[TaggedIterator(BreadcrumbInterface::SERVICE_TAG)] iterable $breadcrumbs,
    ) {
        foreach ($breadcrumbs as $breadcrumb) {
            $this->breadcrumbs[$breadcrumb::class] = $breadcrumb;
        }
    }

    private function getCurrentBreadcrumb(AdminContext $context): ?BreadcrumbInterface
    {
        $crud = $context->getCrud();

        if (null === $crud) {
            return null;
        }
        
        $breadcrumbType = BreadcrumbType::tryFrom($crud->getCurrentPage());

        if (null === $breadcrumbType) {
            return null;
        }

        foreach ($this->breadcrumbs as $breadcrumb) {
            if (
                $breadcrumb->getType() === $breadcrumbType
                && $breadcrumb->getEntityFqdn() === $context->getEntity()->getFqcn()
                && $breadcrumb->supports($context)
            ) {
                return $breadcrumb;

                // TODO: throw if duplicates
            }
        }

        return null;
    }

    /**
     * @return array<BreadcrumbInterface>
     */
    public function getBreadcrumbs(AdminContext $context): array
    {
        $currentBreadcrumb = $this->getCurrentBreadcrumb($context);

        if (null === $currentBreadcrumb) {
            return [];
        }

        /** @var array<BreadcrumbInterface> $stack */
        $stack = [];
        foreach ($this->walkBreadcrumbs($currentBreadcrumb, $context) as $item) {
            $stack[] = $item;
        }

        if (isset($stack[0])) {
            // make sure current breadcrumb has current url
            $stack[0]->setUrl($context->getRequest()->getRequestUri());

            // update page title to match current breadcrumb
            $context->getCrud()->setCustomPageTitle($context->getCrud()->getCurrentPage(), $stack[0]->getName());
        }

        return array_reverse(array: $stack);
    }

    public function render(): string
    {
        /** @var AdminContext $context */
        $context = $this->requestStack->getCurrentRequest()->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
        $breadcrumbs = $this->getBreadcrumbs($context);

        $links = [];
        foreach ($breadcrumbs as $breadcrumb) {
            $links[] = sprintf(
                '<li><a class="text-secondary" href="%s">%s</a></li>',
                $breadcrumb->getUrl(),
                $breadcrumb->getName()
            );
        }

        return sprintf('<ul class="breadcrumb">%s</ul>',
            implode(separator: '&nbsp;<span class="divider"><i class="action-icon fa-fw fa fa-arrow-right"></i></span>&nbsp;', array: $links)
        );
    }

    /**
     * @return \Generator<BreadcrumbInterface>
     */
    private function walkBreadcrumbs(BreadcrumbInterface $breadcrumb, AdminContext $context, ?BreadcrumbData $parentData = null): \Generator
    {
        try {
            $collectedData = $parentData ?? $breadcrumb->gather($context);
            $breadcrumb->configure($collectedData);

            yield $breadcrumb;

            $parent = $this->breadcrumbs[$breadcrumb->getParent()] ?? null;

            if ($parent) {
                yield from $this->walkBreadcrumbs($parent, $context, $breadcrumb->provide($collectedData));
            }
        } catch (BreadcrumbNotApplicableException) {
            return;
        }
    }

    public function getRedirectForParentBreadcrumb(AdminContext $context): ?RedirectResponse
    {
        $breadcrumbs = $this->getBreadcrumbs($context);

        if (count($breadcrumbs) < 2) {
            return null;
        }

        $breadcrumb = $breadcrumbs[count($breadcrumbs) - 2];

        return new RedirectResponse($breadcrumb->getUrl());
    }
}
