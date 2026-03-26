MonterHealth ApiFilterBundle
======================
MonterHealth ApiFilterBundle makes it easy to add filters and ordering to Rest API requests with full control within your Symfony Controller classes. Inspired by the filter system of [API Platform](https://api-platform.com) but with its focus on usage within a controller.

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require monterhealth/api-filter-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require monterhealth/api-filter-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
<?php
// config/bundles.php

return [
// ...
    MonterHealth\ApiFilterBundle\MonterHealthApiFilterBundle::class => ['all' => true],
];
```

Usage
=====
First define what combinations of parameters and filter typs can be used. This is possible by adding attributes to an entity class. Load the MonterHealth/ApiFilterBundle/MonterHealthApiFilter in your controller and pass it a Doctrine QueryBuilder, the entity's class name and the Request query. It will add all the constraints and orderings of the Request query to the QueryBuilder.

Entity
-------
Add attributes to an entity class. You can add them at class or property level. The bundle can handle nested properties like author.name multiple levels deep.
```php
<?php
// src/Entity/Book.php

namespace App\Entity;

use MonterHealth\ApiFilterBundle\Attribute\ApiFilter;
use MonterHealth\ApiFilterBundle\Filter\BooleanFilter;
use MonterHealth\ApiFilterBundle\Filter\DateFilter;
use MonterHealth\ApiFilterBundle\Filter\OrderFilter;
use MonterHealth\ApiFilterBundle\Filter\RangeFilter;
use MonterHealth\ApiFilterBundle\Filter\SearchFilter;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\GeneratedValue;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ApiFilter(BooleanFilter::class, properties: ["available"])]
#[ApiFilter(SearchFilter::class, properties: [
    "bookReferences.referencedBook.title"
])]
#[ApiFilter(SearchFilter::class, properties: [
    "title",
    "author" => OrderFilter::ASCENDING, // set the default order direction
    "pages" => OrderFilter::DESCENDING,
    "published"
])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[ApiFilter(SearchFilter::class)]
    private ?string $title = null;

    #[ORM\OneToMany(mappedBy: 'books', targetEntity: Author::class)]
    #[ApiFilter(SearchFilter::class)]
    private ?string $author = null;

    #[ORM\Column]
    #[ApiFilter(SearchFilter::class)]
    private ?int $pages = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[ApiFilter(DateFilter::class)]
    private ?\DateTimeInterface $published = null;

    #[ORM\Column]
    #[ApiFilter(OrderFilter::class, properties: [OrderFilter::DESCENDING])]
    private ?bool $available = null;

    #[ORM\OneToMany(mappedBy: 'book', targetEntity: BookReference::class)]
    private Collection $bookReferences;
// ...
}
```
Controller
-------
Load the MonterHealth/ApiFilterBundle/MonterHealthApiFilter service into your controller using auto wiring.

```php
<?php
// src/Controller/BookController.php

namespace App\Controller;


use App\Repository\BookRepository;
use MonterHealth\ApiFilterBundle\MonterHealthApiFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BookController extends AbstractController
{
    /**
     * @param Request $request
     * @param BookRepository $repository
     * @param MonterHealthApiFilter $monterHealthApiFilter
     * @return JsonResponse
     * @throws \ReflectionException
     */
    #[Route('books', name: 'get_books', methods: ["GET"])]
    public function getBooks(Request $request, BookRepository $repository, MonterHealthApiFilter $monterHealthApiFilter): JsonResponse
    {
        $queryBuilder = $repository->findAllQueryBuilder();
        $monterHealthApiFilter->addFilterConstraints($queryBuilder, $repository->getClassName(), $request->query);

        return new JsonResponse($queryBuilder->getQuery()->getArrayResult());
    }
}
```

Filters
-------

### Boolean filter
Query: parameter=<true|false|0|1>

For example:

`/books?available=true` shows all books that are available.

### Search filter
Available strategies:
* equals (default)
* partial
* start
* end
* word_start
* in
* null

Query: parameter[strategy]=value or parameter[][strategy] when setting multiple constraints on the same parameter. Add [not] before or after the strategy to get the opposite filter effect.

For example:

`/books?author:name=agatha%20christie` returns all books where author.name equals 'agatha christie'.

`/books?author[not][end]=rowling` returns all books where author doesn't end with 'rowling'.

`/books?author:author[in]=agatha%20christie|j.k.%20rowling` returns all books where author matches 'agatha christie' or 'j.k. rowling'.

`/books?title[][start]=harry&title[][not][word_start]=philosopher` returns all books that have a title that starts with 'harry' and where none of the words in the title start with 'philosopher'.

`/books?title[null]` returns all books where the title is null. The null strategy doesn't need any extra value.

The *in* strategy includes the option to include NULL values. For example:

`/books?author:id[in]=48|24|NULL` returns all books where the author id matches 48 or 24, and all books that have no author set.

`/books?author:id[not][in]=48|24|NULL` returns all books where the author id doesn't match 48 and 24, and all books that have no author set.

As you may have noticed, nested properties must be referenced with a : sign like author:name in the uri.

Join-oriented examples:

`/books?author:name[partial]=rowling` filters books by a nested relation field (`author.name`).

`/authors?name[partial]=martin` filters the base author resource by author name.

`/authors?books:title[partial]=harry` filters authors through their related books by title.

`/authors?books:pages[gte]=340` filters authors that have related books with pages >= 340.

### Order filter
Available strategies:
* asc
* desc

Query: order_parameter_name[strategy]=parameter or order_parameter_name[][strategy]=parameter with multiple orderings.

For example:

`/books?order=author:name` orders the list by author in the default direction (ascending) or in the direction set on the entity.

`/books?order[desc]=author:name` orders the list by author in descending direction.

`/books?order[][asc]=author:name&order[][asc]=title` orders the list by author in ascending direction and by title in ascending direction.

### Numeric filter

Query: parameter=value

For example:

`/books?stock=10` return all the books that have 10 stock.

### Range filter
Available strategies:
* gt (greater than)
* lt (less than)
* gte (greater than equal)
* lte (less than equal)
* bt (between)
* equals

Query: parameter[strategy]=value OR parameter[][strategy]=value when setting multiple constraints on the same parameter.

For example:

`/books?stock[lte]=10` return all the books which stock is less than equal to 10.

`/books?stock[bt]=0|10` return all the books which stock is between 0 and 10.

### Date filter
Available strategies:
* before (<=)
* after (>=)
* strictly_before (<)
* strictly_after (>)
* equals (=) (default)
* null_or_before
* null_or_after
* null_or_strictly_before
* null_or_strictly_after
* null

Query: parameter[strategy]=value OR parameter[][strategy]=value when setting multiple constraints on the same parameter.

For example:

`books?createdAt[before]=2020-12-04` return all the books which createdAt <= 2020-12-04

`books?createdAt[][after]=2020-12-01&createdAt[][before]=2020-12-04` return all the books which createdAt >= 2020-12-01 AND <= 2020-12-04

### Grouped filters (explicit AND between groups)

Query: `mh_groups[groupIndex][parameter][strategy]=value`

When one or more `mh_groups[...]` filters are present, the bundle combines filters as:
`group 0 AND group 1 AND ... AND globals`.
All non-group query filters are treated as an implicit final group (`globals`), while `order[...]` still applies as ordering.

Scope:
- Supported: explicit AND between groups + existing per-parameter behavior inside each group.
- Not supported: arbitrary OR trees between groups/parameters.

For example:

`/books?mh_groups[0][title][partial]=Harry&mh_groups[1][pages][gte]=300&order[desc]=pages`

`/authors?mh_groups[0][books:title][partial]=acc&books:title[partial]=ac`

`/authors?mh_groups[0][name][partial]=martin&mh_groups[1][books:pages][gte]=300`

`/books?mh_groups[0][author:name][partial]=rowling&title[partial]=harry`

`/books?mh_groups[0][title][partial]=domain&mh_groups[1][pages][lte]=340&order[asc]=title`

The prefix is configured via `filter_groups_query_prefix` (default: `mh_groups`) in the configuration section below.

When adding multiple constraints for the same (nested) parameter, use indexed/array syntax so all commands are preserved:

`/authors?books:title[0][partial]=acc&books:title[1][partial]=ac&books:title[2][partial]=v`

or:

`/authors?books:title[][partial]=acc&books:title[][partial]=ac&books:title[][partial]=v`

**Future JSON filter AST**: deeper boolean trees (nested AND/OR) may be supported later via a structured POST body. Such a DSL should enforce **maximum depth**, **maximum node count**, and reject ambiguous input; treat any internal normal form (for example CNF) as an implementation detail with awareness of exponential clause growth—not as a required client wire format.

Configuration
=============

```yaml
monter_health_api_filter:
    # The name of the query parameter to order results.
    order_parameter_name: 'order'
    # The default order strategy (ascending or descending).
    default_order_strategy: 'ascending'
    # Top-level query key for grouped filters (FilterGroupsQueryParser). Default mh_groups.
    filter_groups_query_prefix: mh_groups
    # Possibility to override the default ParameterCollectionFactory.
    parameter_collection_factory: ~
    # Possibility to override the default attribute reader.
    attribute_reader: ~
    # Possibility to override the default ApiFilterFactory.
    api_filter_factory: ~

# Add a custom filter:
services:
    CustomFilter:
        tags: [monter_health_api_filter]
```

Use `MonterHealthApiFilter::getFilterGroupsQueryPrefix()` when calling `FilterGroupsQueryParser::splitQueryBag()` so the parser matches `filter_groups_query_prefix`.

Upgrade to version 2
=============
To upgrade to version 2 it is advised to use [rector](https://github.com/rectorphp/rector). Add a configuration rule to convert the ApiFilter annotations to attributes:

```php
$rectorConfig->ruleWithConfiguration`(AnnotationToAttributeRector::class, [
    new AnnotationToAttribute('ApiFilter'),
]);
```

Development
=============

Run the sandbox + tests (recommended: Docker, no local PHP)
-------------------------------------------------------------

The sandbox app lives under `tests/Application` and exercises the bundle through a real Symfony kernel, Doctrine entity mapping and HTTP endpoint (`/books`).

Start dependencies (MariaDB + composer install inside the PHP container):

```console
$ ./bin/dev-up.sh
```

Start a browser-accessible sandbox server (keep this command running; this command blocks):

```console
$ ./bin/serve-sandbox.sh
```

Then open [http://127.0.0.1:18080/books?title[partial]=Harry](http://127.0.0.1:18080/books?title[partial]=Harry) or [http://127.0.0.1:18080/authors?books:title[partial]=Harry](http://127.0.0.1:18080/authors?books:title[partial]=Harry).

In another terminal, run the integration tests against MariaDB:

```console
$ ./bin/test-application.sh
```

Optional unit tests only:

```console
$ ./bin/test-unit.sh
```

Optional: run the full suite:

```console
$ ./bin/test-all.sh
```

If you just changed the bundle code, re-run:
`./bin/test-application.sh` (or `./bin/test-all.sh`) to verify behavior against MariaDB.

Stop the stack (equivalent of `docker compose down`):

```console
$ ./bin/dev-down.sh
```

Use `./bin/dev-down.sh -v` if you also want to drop MariaDB volumes.

Optional: run locally (only if you have PHP installed)
--------------------------------------------------------

```console
$ composer install
$ ./vendor/bin/simple-phpunit --testsuite unit
$ ./vendor/bin/simple-phpunit --testsuite application
```

Database URL and tests (**SQLite** vs **MariaDB**)
------------------------------------------------

- **`phpunit.xml.dist`** sets a default `DATABASE_URL` to **SQLite** under the kernel cache dir. That is meant for **on-host** runs (`composer install` + `./vendor/bin/simple-phpunit`) so you do not need MariaDB locally.
- **Docker** sets `DATABASE_URL` on the `php` service (see `docker-compose.yml`) to **MariaDB**. When you use `./bin/test-application.sh`, `./bin/test-all.sh`, or `./bin/test-unit.sh`, PHPUnit runs **inside that container**, so integration tests use **MariaDB** as in production-like setups.

If a host run should use MariaDB too, export `DATABASE_URL` before PHPUnit (same DSN as in Compose) so it overrides the XML default.

Docker development (PHP + MariaDB)
----------------------------------
Recommended: use Docker + helper scripts (no local PHP required).

Helper scripts (recommended):

```console
# Start the full sandbox stack (PHP + MariaDB) and install dependencies.
$ ./bin/dev-up.sh
$ ./bin/dev-up.sh -h

# Start the browser sandbox server (keep it running).
$ ./bin/serve-sandbox.sh
$ ./bin/serve-sandbox.sh -h

# Run unit/service tests only.
$ ./bin/test-unit.sh
$ ./bin/test-unit.sh -h

# Run sandbox integration tests against MariaDB.
$ ./bin/test-application.sh
$ ./bin/test-application.sh -h

# Run all tests.
$ ./bin/test-all.sh
$ ./bin/test-all.sh -h

# Stop containers (after serve-sandbox or dev-up).
$ ./bin/dev-down.sh
$ ./bin/dev-down.sh -h
```

Equivalent raw Docker commands:

Start the full sandbox stack (PHP + MariaDB):

```console
$ docker compose up -d
```

Install dependencies in the long-running PHP container:

```console
$ docker compose exec -T php composer install
```

Run the sandbox integration tests against MariaDB:

```console
$ docker compose exec -T php ./vendor/bin/simple-phpunit --testsuite application
```

Run all tests:

```console
$ docker compose exec -T php ./vendor/bin/simple-phpunit
```

Stop and remove containers:

```console
$ docker compose down
```

The Docker setup provides `DATABASE_URL` for MariaDB. Outside Docker, PHPUnit still defaults to SQLite via `phpunit.xml.dist`.

Note for bundle consumers: everything in `docker/`, `bin/`, and `tests/Application/` is development-only. The Docker/sandbox dependencies were added under `require-dev` and will not be installed when your application installs the bundle with `--no-dev`.
