# easyadmin-breadcrumbs
A bundle that allows you to add breadcrumbs to EasyAdmin

![A bundle that allows you to add breadcrumbs to EasyAdmin](/doc/images/promo.png)

Installation
------------

This bundle requires PHP 8.1 or higher and Symfony 6.0 or higher. Run the
following command to install it in your application:

```
$ composer require alshenetsky/easyadmin-breadcrumbs
```

Documentation
-------------
### Concept
EasyAdmin, as we know, does not have functionality for placing breadcrumbs on admin pages. Navigation in the admin area is based on the GET request data, packed into a class named AdminContext. Transitions between controller methods are implemented by generating the URL to the desired CRUD and, if necessary, applying filters to it. So it becomes difficult to build a breadcrumb tree, because you need to somehow store the hierarchy of controllers without losing filters and controllers' connections to each other.

This bundle allows you to recreate such a hierarchy. You create a Breadcrumb class, which in turn creates a reference to the parent Breadcrumb, and so on.

### Creating breadcrumb hierarchy

Such a class must implement the BreadcrumbInterface. The easiest way to do it is to inherit the AbstractBreadcrumb class, which already implements this interface and contains useful methods, reducing the number of boilerplate:

```php
<?php

namespace App\Controller\Admin\Breadcrumb;

use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\AbstractBreadcrumb;
use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\BreadcrumbType;
use App\Entity\User;


class UserIndexBreadcrumb extends AbstractBreadcrumb
{
    public function getType(): BreadcrumbType
    {
        return BreadcrumbType::INDEX;
    }

    public function getEntityFqdn(): string
    {
        return User::class;
    }

    public function getName(): string
    {
        return 'Users';
    }
}
```

Every Breadcrumb class will match against current url by `getType()` and `getEntityFqdn()` methods. So the breadcrumb from this example will appear on the `UserController::index` page.

Method `getType()` returns `BreadcrumbType` enum which is perfectly matched with Action::* constants in EasyAdmin bundle.

Additionally, you can implement `supports()` method if you need more complex logic whether to display breadcrumb on the page:

```php
  public function supports(AdminContext $context): bool
    {
        return isset($context->getRequest()->get('filters')['parent']['value']);
    }
```

Let's go deeper into the navigation tree. Where there is a list of users, there will most likely be a user edit. Let's create a second level breadcrumb:

```php
<?php

namespace App\Controller\Admin\Breadcrumb;

use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\AbstractBreadcrumb;
use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\BreadcrumbData;
use Alshenetsky\EasyAdminBreadcrumbs\Breadcrumb\BreadcrumbType;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;

class UserEditBreadcrumb extends AbstractBreadcrumb
{

    public function getType(): BreadcrumbType
    {
        return BreadcrumbType::EDIT;
    }

    public function getEntityFqdn(): string
    {
        return User::class;
    }

    public function getParent(): ?string
    {
        return UserIndexBreadcrumb::class;
    }

    public function gather(AdminContext $context): BreadcrumbData
    {
        return parent::gather($context)
            ->set('userId', $context->getEntity()->getPrimaryKeyValue())
        ;
    }

    public function configure(BreadcrumbData $gatheredData): void
    {
        /** @var User $user */
        $user = $this->getEntityManager()
            ->getReference(
                User::class,
                $gatheredData->get('userId')
            )
        ;

        $this
            ->setName(sprintf('Editing user %s', $user->getName()))
            ->setEntityId($user->getId())
        ;
    }
}
```
You may see some previously unfamiliar methods.
 * The first one is `getParent()`. It forms a link between the child and parent breadcrumb.
 * The second one is `gather()`. It gathers the data from the current query (only if the given breadcrumb is defined as current) and stores it in the BreadcrumbData class, which serves as a data store.
 * The third one is `configure()`. It receives `BreadcrumbData` object (the one we generated in the `gather()` method). Based on this data we can configure the name and the URL for this breadcrumb. The `setEntityId()` call here is an auxiliary method call that allows us to add an `entityId` key to the URL generator and use the default URL generation logic for the rest. But we're free to completely override this mechanism by calling `setUrl()` instead:
     ```php
            ->setName(sprintf('Editing user %s', $user->getName()))
            ->setUrl(
                $this
                    ->getDefaultUrl() // returns EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator instance with controller and action already provided
                    ->setEntityId($user->getId())
                    ->set('foo', 'bar')
            )
    ```

That's it. Now we have two levels of breadcrumb navigation:

**Users** -> **Editing user John Doe**

Now go deeper. Assume that on the user's edit page we have a link to the user's orders, which are displayed in the OrdersController. Assume that on the user's edit page we have a link to the user's orders, which are displayed in the OrdersController. Most likely you'll make a custom Action on the user's edit page and create a link for it that leads to OrdersController::index and sets a filter for it (`'user' => ['comparison' => '=', 'value' => $user->getId()]]`). It is easy to breadcrumb the next level of nesting:

**Users** -> **Editing user John Doe** -> **User orders**

```php
<?php

class OrdersIndexBreadcrumb extends AbstractBreadcrumb
{
    public function getType(): BreadcrumbType
    {
        return BreadcrumbType::INDEX;
    }

    public function getEntityFqdn(): string
    {
        return Order::class;
    }

    public function getParent(): ?string
    {
        return UserEditBreadcrumb::class;
    }

    public function gather(AdminContext $context): BreadcrumbData
    {
        // gather userId from request filters:
        return parent::gather($context)
            ->set('userId', $context->getRequest()->get('filters')['userId']['value'])
        ;
    }
    
     public function provide(BreadcrumbData $gatheredData): BreadcrumbData
    {
        // provide UserEditBreadcrumb with userId we gathered:
        return parent::provide($gatheredData)
            ->set('userId', $gatheredData->get('userId'))
        ;
    }

    public function configure(BreadcrumbData $gatheredData): void
    {
        $this
            ->setName('User orders')
            ->setFilters(['userId' => ['comparison' => '=', 'value' => $gatheredData->get('userId')]])
        ;
    }
}
```

The last method you should know about is `provide()`. It also receives BreadcrumbData which is gathered in `gather()`, but returns another `BreadcrumbData` class to populate parent breadcrumb with it. You SHOULD provide very same keys that your parent breadcrumb needs.

You see, being on a different page (in a different context) invokes `gather()` method only on the current breadcrumb, and the parent breadcrumbs get theirs `BreadcrumbData` via the `provide()` method up the chain. This is how the breadcrumb hierarchy is formed. You can form as many nesting levels as you wish.

Summary:
 * use `configure()` method to set url and name of the breadcrumb from `BreadcrumbData`
 * use `gather()` method to gather `BreadcrumbData` from current context (only for the CURRENT breadcrumb)
 * use `provide()` method to send `BreadcrumbData` to parent breadcrumb with very same keys that parent breadcrumb needs.


### Placing breadcrumbs on the page:
1. Override EadyAdmin `layout.html` twig template by creating file `templates/bundles/EasyAdminBundle/layout.html.twig`
2. Place `{{ breadcrumbs() }}` there, in `content_header_wrapper` block for example:
    ```
    {% extends '@!EasyAdmin/layout.html.twig' %}
    {% block content_header_wrapper %}
        {{ breadcrumbs()}}
        {{ parent() }}
    {% endblock %}
    ```

Contribution
-------------
Contributions are very welcome!

### TODO list:
 * add tests
 * add static analysis tool
 * configure CI

License
-------

This software is published under the [MIT License](LICENSE)