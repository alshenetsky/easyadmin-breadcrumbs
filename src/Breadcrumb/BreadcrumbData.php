<?php

namespace Alshenetsky\EasyadminBreadcrumbs\Breadcrumb;

final class BreadcrumbData
{
    private array $fields = [];

    public function set(string $key, mixed $value): self
    {
        $this->fields[$key] = $value;

        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->fields[$key] ?? null;
    }
}
