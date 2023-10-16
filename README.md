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

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 * @ApiFilter(BooleanFilter::class, properties={"available"})
 * @ApiFilter(SearchFilter::class, properties={
 *      "bookReferences.referencedBook.title"
 * })
 * @ApiFilter(OrderFilter::class, properties={
 *     "title",
 *     "author": OrderFilter::ASCENDING, // set the default order direction
 *     "pages": OrderFilter::DESCENDING,
 *     "published"
 * })
 */
class Book
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer", unique=true)
     * @GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiFilter(SearchFilter::class)
     */
    private $title;
    /**
     * @ORM\OneToMany(targetEntity="Author", mappedBy="books")
     * @ApiFilter(SearchFilter::class)
     */
    private $author;
    /**
     * @ORM\Column(type="integer")
     * @ApiFilter(RangeFilter::class})
     */
    private $pages;
    /**
     * @ORM\Column(type="date")
     * @ApiFilter(DateFilter::class})
     */
    private $published;
    /**
     * @ORM\Column(type="boolean")
     * @ApiFilter(OrderFilter::class, properties={OrderFilter::DESCENDING})
     */
    private $available;
    /**
     * @var Collection
     * @ORM\OneToMany(targetEntity="App\Repository\BookReference", mappedBy="book")
     */
    private $bookReferences;
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
     * @Route("books", name="get_books", methods={"GET"})
     * @param Request $request
     * @param BookRepository $repository
     * @param MonterHealthApiFilter $monterHealthApiFilter
     * @return JsonResponse
     * @throws \ReflectionException
     */
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

As you may have noticed, nested properties must be referenced with a : sign like author:name in the uri.

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

Query: parameter[strategy]=value OR parameter[][strategy]=value when setting multiple constraints on the same parameter.

For example:

`books?createdAt[before]=2020-12-04` return all the books which createdAt <= 2020-12-04

`books?createdAt[][after]=2020-12-01&createdAt[][before]=2020-12-04` return all the books which createdAt >= 2020-12-01 AND <= 2020-12-04

Configuration
=============

```yaml
monter_health_api_filter:
    # The name of the query parameter to order results.
    order_parameter_name: 'order'
    # The default order strategy (ascending or descending).
    default_order_strategy: 'ascending'
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