# Searchable

Full-text search and reusable queries in laravel.

- Currently supports MySQL only.
- Helpful for complex table queries with multiple joins and derived columns.
- Reusable queries and column definitions.

## Overview

### Full-text search on eloquent models

Simple setup for searchable model and can search on derived columns.

```php
use AjCastro\Searchable\Searchable;

class Post
{
    use Searchable;

    protected $searchable = [
        // This will search on the defined searchable columns
        'columns' => [
            'posts.title',
            'posts.body',
            'author_full_name' => 'CONCAT(authors.first_name, " ", authors.last_name)'
        ],
        'joins' => [
            'authors' => ['authors.id', 'posts.author_id']
        ]
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
}

// Usage
Post::search("Some title or body content or even the author's full name")
    ->with('author')
    ->paginate();
```

Imagine we have an api for a table or list that has full-text searching and column sorting and pagination.
This is a usual setup for a table or list. The internal explanations will be available on the documentation below.
Our api call may look like this:

`
http://awesome-app.com/api/posts?per_page=10&page=1&sort_by=title&descending=true&search=SomePostTitle
`

Your code can look like this:

```php
class PostsController
{
    public function index()
    {
        return Post::sortByRelevance(!request()->bool('sort_by'))
            ->search(request('search'))
            ->when(Post::isColumnValid($sortColumn = request('sort_by')), function ($query) use ($sortColumn) {
                $query->orderBy(
                    \DB::raw(
                        (new Post)->getSortableColumn($sortColumn) ?? // valid sortable column
                        (new Post)->searchQuery()->getColumn($sortColumn) ?? // valid search column
                        $sortColumn // valid original table column
                    ),
                    request()->bool('descending') ? 'desc' : 'asc'
                );
            })
            ->paginate();
    }

}
```

## Documentation

### Installation

```
composer require ajcastro/searchable
```

### Searchable Model

```php
use AjCastro\Searchable\Searchable;

class Post extends Model
{
    use Searchable;

    /**
     * Searchable model definitions.
     */
     protected $searchable = [
        // Searchable columns of the model.
        // If this is not defined it will default to all table columns.
        'columns' => [
            'posts.title',
            'posts.body',
            'author_full_name' => 'CONCAT(authors.first_name, " ", authors.last_name)'
        ],
        // This is needed if there is a need to join other tables for derived columns.
        'joins' => [
            'authors' => ['authors.id', 'posts.author_id'], // defaults to leftJoin method of eloquent builder
            'another_table' => ['another_table.id', 'authors.another_table_id', 'join'], // can pass leftJoin, rightJoin, join of eloquent builder.
        ]
    ];

    /**
     * Can also be written like this for searchable columns.
     *
     * @var array
     */
    protected $searchableColumns = [
        'title',
        'body',
        'author_full_name' => 'CONCAT(authors.first_name, " ", authors.last_name)'
    ];

    /**
     * Can also be written like this for searchable joins.
     *
     * @var array
     */
    protected $searchableJoins = [
        'authors' => ['authors.id', 'posts.author_id']
    ];
}

// Usage
// Call search anywhere
// This only search on the defined columns.
Post::search('Some post')->paginate();
Post::where('likes', '>', 100)->search('Some post')->paginate();

```

This will addSelect field `sort_index` which will used to order or sort by relevance.
If you want to disable sort by relevance, call method `sortByRelevance(false)` before `search()` method.
Example:

```
Post::sortByRelevance(false)->search('Some post')->paginate();
Post::sortByRelevance(false)->where('likes', '>', 100)->search('Some post')->paginate();
```

### Set searchable configurations on runtime.

```php
$post = new Post;
$post->setSearchable([ // addSearchable() method is also available
    'columns' => [
        'posts.title',
        'posts.body',
    ],
    'joins' => [
        'authors' => ['authors.id', 'posts.author_id']
    ]
]);
// or
$post->setSearchableColumns([ // addSearchableColumns() method is also available
    'posts.title',
    'posts.body',
]);
$post->setSearchableJoins([ // addSearchableJoins() method is also available
    'authors' => ['authors.id', 'posts.author_id']
]);
```

### Easy Sortable Columns

You can define columns to be only sortable but not be part of search query constraint.
Just put it under `sortable_columns` as shown below .
This column can be easily access to put in `orderBy` of query builder. All searchable columns are also sortable columns.

```php
class Post {
     protected $searchable = [
        'columns' => [
            'title' => 'posts.title',
        ],
        'sortable_columns' => [
            'status_name' => 'statuses.name',
        ],
        'joins' => [
            'statuses' => ['statuses.id', 'posts.status_id']
        ]
    ];
}

// Usage

Post::search('A post title')->orderBy(Post::getSortableColumn('status_name'));
// This will only perform search on `posts`.`title` column and it will append "order by `statuses`.`name`" in the query.
// This is beneficial if your column is mapped to a different column name coming from front-end request.
```

### Searchable Model Custom Search Query

Sometimes our queries have lots of things and constraints to do and we can contain it in a search query class like this `PostSearch`.

```php
use AjCastro\Searchable\BaseSearchQuery;

class PostSearch extends BaseSearchQuery
{
    public function query()
    {
        // The query conditions here is always applied to our search.
        return $this->query
        ->leftJoin('authors', 'authors.id', '=', 'posts.author_id')
        ->where('posts.likes', '>', 100)
        ->where('is_active', 1)
        ->orderBy('some_column')
        // We can even access our column definition here that will result to the equivalent actual column
        // CAUTION:
        // MySQL functions need to be wrapped with DB::raw() to be parsed properly.
        // Also we can use orderByRaw() for this example.
        // Also consider wrapping it in the columns() method so it will be ready
        // everytime we use it in orderBy() or where() methods.
        ->orderBy($this->author_full_name);
    }

    public function columns()
    {
        return [
            'posts.title',
            'posts.body',
            // We wrap CONCAT() column so it will always be ready to be used in orderBy() and where() methods
            'author_full_name' => DB::raw('CONCAT(authors.first_name, " ", authors.last_name)')
        ];
    }
}

```

Then, we can use it as the default search query for the model like:

```php
class Post
{
    public function defaultSearchQuery()
    {
        return new PostSearch;
    }
}

// Usage
Post::search($searchStr)->paginate();
```

We can also use custom search query temporarily by passing it as second parameter in `search()` method.

```php
Post::search('William Shakespeare', new PostSearch)->paginate();
```

### Using derived columns for order by and where conditions

Usually we have queries that has a derived columns like our example for `PostSearch`'s `author_full_name`.
Sometimes we need to sort our query results by this column.

```php
// CAUTION:
// Remember to wrap column with MySQL functions with DB::raw() in column definition
Post::search('Some search')->orderBy(Post::searchQuery()->author_full_name, 'desc')->paginate();
Post::search('Some search')->where(Post::searchQuery()->author_full_name, 'William%')->paginate();
```

### Running gridQuery and searchQuery on its own

You can run gridQuery and searchQuery on its own but you need to make sure you initiliaze your query.

```php
use AjCastro\Searchable\BaseSearchQuery;

class PostSearch extends BaseSearchQuery
{
    public function query()
    {
        // Initialize query when $this->query is not available.
        $query = $this->query ?? Post::query();
        return $this->query;
        // ->leftJoin('authors', 'authors.id', '=', 'posts.author_id')
        // -> ... and so on
    }
}

// Then you can run it...
(new PostSearch)->search('something')->paginate();
```

### Grid Query Declarative Definition

```php
use AjCastro\Searchable\BaseGridQuery;

class PostGridQuery extends BaseGridQuery
{
    public function initQuery()
    {
        return Post::leftJoin('authors', 'authors.id', '=', 'posts.author_id');
    }

    public function columns()
    {
        return [
            'posts.title', // same with 'title' => 'posts.title'
            'text' => 'posts.body', // will result to "posts.body as text"
            'author_full_name' => 'CONCAT(authors.first_name, " ", authors.last_name)'
        ];
    }
}
```

```php
$gridQuery = new PostGridQuery;
$actualColumn = $gridQuery->getColumn('author_full_name');
$actualColumn = $gridQuery->author_full_name; // or using magic getters
$gridQuery
    ->selectColumns() // puts columns() to $query->select() and return the laravel query builder
    ->orderBy($actualColumn, 'desc')
    ->get();
```

### Search Query Declarative Definition

```php
use AjCastro\Searchable\BaseSearchQuery;

class PostSearch extends BaseSearchQuery
{
    public function query()
    {
        // $this->query is available since this is set on Searchable trait scopeSearch() method
        // If you're going to run this searchQuery on its own and not via scopeSearch()
        // you should consider to initialize $this->query first or use initQuery() method instead of query()
        // just like the above example
        return $this->query->leftJoin('authors', 'authors.id', '=', 'posts.author_id');
    }

    public function columns()
    {
        return [
            'posts.title', // same with 'title' => 'posts.title'
            'text' => 'posts.body', // will result to "posts.body as text"
            'author_full_name' => 'CONCAT(authors.first_name, " ", authors.last_name)'
        ];
    }
}
```

```php
// All defined columns are searchable in the query
$searchQuery = new PostSearch;
$searchQuery->search('This is a post title.');
$searchQuery->search('This is a post body.');
$searchQuery->search('William Shakespeare');
// You can chain laravel query builder's paginate() or get() afterwards
$searchQuery->search('William Shakespeare')->get();
// If you want to select the columns from the columns() we call selectColumns(), use initQuery for this
$results = tap($searchQuery)->search('William Shakespeare')->selectColumns()->get();
$results = [
    [
        'title' => 'This is a post title',
        'text' => 'This is a post body.',
        'author_full_name' => 'William Shakespeare'
    ],
    // ... and so on
];
```

## Helper methods available on model

### isColumnValid [static]

- Identifies if the column is a valid column, either a regular table column or derived column.
- Useful for checking valid columns to avoid sql injection especially in `orderBy` query.

```php
Post::isColumnValid(request('sort_by'));
```

### getTableColumns [static]

- Get the table columns.

```php
Post::getTableColumns();
```

### enableSearchable

- Enable the searchable behavior.

```php
$query->getModel()->enableSearchable();
$query->search('foo');
```

### disableSearchable

- Disable the searchable behavior.
- Calling `search()` method will not perform a search.

```php
$query->getModel()->disableSearchable();
$query->search('foo');
```

### setSearchable

- Set or override the model's `$searchable` property.
- Useful for building searchable config on runtime.

```php
$query->getModel()->setSearchable([
  'columns' => ['title', 'status'],
  'joins' => [...],
]);
$query->search('foo');
```

## Warning

Calling `select()` after `search()` will overwrite `sort_index` field, so it is recommended to call `select()`
before `search()` which is also the normal case.

## Credits

- Ray Anthony Madrona [@raymadrona](https://github.com/raymadrona), for the tips on using MySQL `LOCATE()` for sort relevance.
- [nicolaslopezj/searchable](https://github.com/nicolaslopezj/searchable), for the `$searchable` property declaration style.
