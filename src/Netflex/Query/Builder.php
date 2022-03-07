<?php

namespace Netflex\Query;

use Closure;
use DateTimeInterface;

use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Carbon;
use Netflex\API\Contracts\APIClient;
use Netflex\API\Facades\APIClientConnectionResolver;

use Netflex\Query\Exceptions\QueryException;
use Netflex\Query\Exceptions\IndexNotFoundException;
use Netflex\Query\Exceptions\InvalidAssignmentException;
use Netflex\Query\Exceptions\InvalidOperatorException;
use Netflex\Query\Exceptions\InvalidSortingDirectionException;
use Netflex\Query\Exceptions\InvalidValueException;
use Netflex\Query\Exceptions\NotFoundException;

use Netflex\Structure\Model;
use Netflex\Structure\Structure;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Traits\Macroable;
use Netflex\Query\Exceptions\InvalidArrayValueException;
use Netflex\Query\Exceptions\NoSortableFieldToOrderByException;

class Builder
{
  use Macroable;

  /** @var int The minimum allowed results per query */
  const MIN_QUERY_SIZE = 1;

  /** @var int The maximum allowed results per query */
  const MAX_QUERY_SIZE = 10000;

  /** @var array Special characters that must be escaped */
  const SPECIAL_CHARS = ['"', '\\'];

  /** @var string The ascending sort direction */
  const DIR_ASC = 'asc';

  /** @var string The decending sort direction */
  const DIR_DESC = 'desc';

  const DIR_DEFAULT = 'default';

  /** @var array The supported value types */
  const VALUE_TYPES = [
    'NULL',
    'array',
    'boolean',
    'integer',
    'string',
    'DateTime'
  ];

  const REPLACEMENT_ENTITIES = [
    '-' => '##D##'
  ];

  /** @var array The valid sorting directions */
  const SORTING_DIRS = [
    Builder::DIR_DEFAULT,
    Builder::DIR_ASC,
    Builder::DIR_DESC,
  ];

  /** @var string The equals operator */
  const OP_EQ = '=';

  /** @var string The not equals operator */
  const OP_NEQ = '!=';

  /** @var string The less than operator */
  const OP_LT = '<';

  /** @var string The less than or equals operator */
  const OP_LTE = '<=';

  /** @var string The greater than operator */
  const OP_GT = '>';

  /** @var string The greater than or equals operator */
  const OP_GTE = '>=';

  /** @var string The like operator */
  const OP_LIKE = 'like';

  /** @var array The valid operators */
  const OPERATORS = [
    Builder::OP_EQ,
    Builder::OP_NEQ,
    Builder::OP_LT,
    Builder::OP_LTE,
    Builder::OP_GT,
    Builder::OP_GTE,
    Builder::OP_LIKE
  ];

  /** @var string */
  protected string $connection;

  /** @var array */
  protected $fields;

  protected array $relations = [];

  /** @var int */
  protected $relation_id;

  protected int $size = self::MAX_QUERY_SIZE;

  /** @var string[] */
  protected array $orderBy = [];

  /** @var string[] */
  protected array $sortDir = [];

  protected array $query;

  protected bool $respectPublishingStatus = true;

  /** @var Closure|null */
  protected ?Closure $mapper;

  protected bool $assoc = false;

  protected bool $shouldCache = false;

  protected string $cacheKey;

  protected bool $debug = false;

  /** @var callable[] */
  protected array $appends = [];

  protected string $model;

  protected bool $useScores = false;

  /**
   * @param bool $respectPublishingStatus
   * @param array|null $query
   * @param Closure|null $mapper
   * @param callable[] $appends
   */
  public function __construct(
    ?bool $respectPublishingStatus = true,
    ?array $query = null,
    ?Closure $mapper = null,
    array $appends = []
  ) {
    $this->query = $query ?? [];
    $this->mapper = $mapper;
    $this->respectPublishingStatus = $respectPublishingStatus ?? true;
    $this->appends = $appends;
  }

  /**
   * @param string|null $name
   * @return static
   */
  public function connection(?string $name)
  {
    return $this->setConnectionName($name);
  }

  /**
   * @param string|null $connection
   * @return static
   */
  public function setConnectionName(?string $connection)
  {
    $this->connection = $connection;
    return $this;
  }

  public function getConnectionName()
  {
    return $this->connection ?? 'default';
  }

  /**
   * @return APIClient
   */
  public function getConnection(): APIClient
  {
    return APIClientConnectionResolver::resolve($this->getConnectionName());
  }

  /**
   * Append a query modifier
   *
   * @param Closure $callback
   * @return static
   */
  public function append(Closure $callback)
  {
    $this->appends[] = $callback;
    return $this;
  }

  /**
   * @param string $model
   * @return void
   */
  public function setModel(string $model)
  {
    $this->model = $model;
  }

  /**
   * @return string|null
   */
  public function getModel()
  {
    return $this->model;
  }

  /**
   * Cache the results with the given key
   *
   * @param string $key
   * @return static
   */
  public function cacheResultsWithKey(string $key)
  {
    $this->shouldCache = true;
    $this->cacheKey = $key;

    return $this;
  }

  /**
   * Adds a score weight to the previous statement
   *
   * @param float $weight
   * @return static
   */
  public function score(float $weight)
  {
    if ($query = array_pop($this->query)) {
      $this->query[] = "{$query}^{$weight}";
      $this->useScores = true;
    }

    return $this;
  }

  /**
   * Marks the previous statement as fuzzy
   *
   * @param integer|null $distance Distance to use for fuzzy matching
   * @return static
   */
  public function fuzzy(?int $distance = null)
  {
    if ($query = array_pop($this->query)) {
      $matches = [];
      $query = preg_match('/^\((.+)\)$/', $query, $matches) ? $matches[1] : $query;
      $this->query[] = "{$query}~" . ($distance ? $distance : null);
    }

    return $this;
  }

  /**
   * @param string $relation
   * @return bool
   */
  protected function hasRelation(string $relation)
  {
    return in_array(Str::singular($relation), $this->relations ?? []);
  }

  /**
   * @param null|array|boolean|integer|string|DateTimeInterface $value
   * @param string|null $operator
   * @return array|bool|DateTimeInterface|int|string|string[]|null
   */
  protected function escapeValue($value, string $operator = null)
  {
    if (is_string($value)) {
      if ($operator !== 'like') {
        return "\"{$value}\"";
      }

      return str_replace(' ', '*', $value);
    }

    if (is_bool($value)) {
      $value = (int)$value;
    }

    if ($value instanceof DateTimeInterface) {
      return $this->escapeValue($value->format('Y-m-d H:i:s'), $operator);
    }

    return $value;
  }

  /**
   * @param string $field
   * @param null|array|boolean|integer|string|DateTimeInterface $value
   * @return string
   */
  protected function compileTermQuery(string $field, $value)
  {
    return "{$field}:{$value}";
  }

  /**
   * @param string $field
   * @return string
   */
  protected function compileNullQuery($field)
  {
    return "(NOT _exists_:{$field})";
  }

  /**
   * @param array $args
   * @param string|null $operator
   * @return string
   */
  protected function compileScopedQuery(array $args, ?string $operator = 'AND')
  {
    $callback = count($args) === 1 ? array_pop($args) : function (self $scope) use ($args) {
      return $scope->where(...$args);
    };

    $builder = new static(false, []);

    $callback($builder);
    $this->useScores = $this->useScores || $builder->useScores;
    $query = $builder->compileQuery(true, $operator);

    $compiledQuery = $this->compileQuery(true);

    return $compiledQuery && $operator
      ? "({$compiledQuery} {$operator} {$query})"
      : $query;
  }

  /**
   * Compiles the field name into something ES can understand
   *
   * @param string $field
   * @return string
   */
  protected function compileField($field)
  {
    foreach (static::REPLACEMENT_ENTITIES as $entity => $replacement) {
      $field = str_replace($entity, $replacement, $field);
    }

    return $field;
  }

  /**
   * @param string $field
   * @param string $operator
   * @param null|array|Collection|boolean|integer|QueryableModel|string|DateTimeInterface $value
   * @return string
   * @throws InvalidOperatorException If an invalid operator is passed
   * @throws InvalidValueException
   */
  protected function compileWhereQuery(string $field, string $operator, $value)
  {
    $field = $this->compileField($field);

    if ($value instanceof Collection) {
      /** @var Collection */
      $value = $value->all();
    }

    if ($value instanceof QueryableModel) {
      /** @var QueryableModel */
      $value = $value->getKey();
    }

    if (is_object($value) && method_exists($value, '__toString')) {
      /** @var object */
      $value = $value->__toString();
    }

    if (
      !in_array(gettype($value), static::VALUE_TYPES) ||
      (is_object($value) && !in_array(get_class($value), static::VALUE_TYPES))
    ) {
      throw new InvalidValueException($value);
    }

    if (is_array($value)) {
      if (!count($value)) {
        throw new InvalidArrayValueException();
      }

      $queries = [];
      foreach ($value as $item) {
        $queries[] = $this->compileWhereQuery($field, $operator, $item);
      }

      return count($queries) > 1
        ? '(' . implode(' OR ', $queries) . ')'
        : array_pop($queries);
    }

    $value = $this->escapeValue($value, $operator);

    if ($value === null && $operator === static::OP_NEQ) {
      $value = $field;
      $field = '_exists_';
      $operator = static::OP_EQ;
    }

    $term = $value === null ? $this->compileNullQuery($field) : $this->compileTermQuery($field, $value);

    switch ($operator) {
      case static::OP_LIKE:
      case static::OP_EQ:
        return $term;
      case static::OP_NEQ:
        return "(NOT {$term})";
      case static::OP_GT:
        if ($value === null) {
          return null;
        }

        if (is_string($value)) {
          return "{$field}:{{$value} TO *}";
        }

        return "{$field}:>{$value}";
      case static::OP_GTE:
        if ($value === null) {
          $this->query = [$this->compileWhereQuery($field, '!=', null)];
          return null;
        }

        if (is_string($value)) {
          return "{$field}:[{$value} TO *]";
        }

        return "{$field}:>={$value}";
      case static::OP_LT:
        if ($value === null) {
          return null;
        }

        if (is_string($value)) {
          return "{$field}:{* TO {$value}}";
        }

        return "{$field}:<{$value}";
      case static::OP_LTE:
        if ($value === null) {
          $this->query = [$this->compileWhereQuery($field, '=', null)];
          return null;
        }

        if (is_string($value)) {
          return "{$field}:[* TO {$value}]";
        }

        return "{$field}:<={$value}";
      default:
        throw new InvalidOperatorException($operator);
        break;
    }
  }

  /**
   * Sets the debug flag of the query
   * Making the API reflect the compiled query in the output
   *
   * @return static
   */
  public function debug()
  {
    $this->debug = true;
    return $this;
  }

  /**
   * Compiles the query and retrieves the query string.
   * Used for debugging purposes.
   *
   * @param bool $scoped Determines if the query shouls be compiled in a scoped context.
   * @return string
   */
  public function getQuery(bool $scoped = false): string
  {
    return $this->compileQuery($scoped);
  }

  /**
   * Compiles the query and retrieves the query string.
   * Used for debugging purposes.
   *
   * @return string
   */
  public function getRequest(): string
  {
    return $this->compileRequest();
  }

  /**
   * Performs a raw query, use carefully.
   *
   * @param string $query
   * @return static
   */
  public function raw(string $query)
  {
    $this->query[] = $query;
    return $this;
  }

  /**
   * Sets the field which to order the results by
   *
   * @param string $field
   * @param string $direction
   * @return static
   * @throws InvalidSortingDirectionException If an invalid $direction is passed
   */
  public function orderBy(string $field, string $direction = Builder::DIR_DEFAULT)
  {
    $direction = $direction ?: static::DIR_DEFAULT;
    $this->orderBy[] = $this->compileField($field);

    if (!in_array($direction, static::SORTING_DIRS)) {
      throw new InvalidSortingDirectionException($direction);
    }

    $this->sortDir[] = $direction;

    if ($field === '_score') {
      $this->useScores = true;
    }

    return $this;
  }

  /**
   * Sets the direction to order the results by
   *
   * @param string $direction
   * @return static
   * @throws InvalidSortingDirectionException If an invalid $direction is passed
   * @throws NoSortableFieldToOrderByException If no orderBy field has been set
   * @deprecated 4.4.1 Use orderBy() with direction argument instead
   */
  public function orderDirection(string $direction)
  {
    if (!in_array($direction, static::SORTING_DIRS)) {
      throw new InvalidSortingDirectionException($direction);
    }

    $this->sortDir[] = $direction;

    if (!count($this->orderBy)) {
      throw new NoSortableFieldToOrderByException();
    }

    return $this;
  }

  /**
   * Sets the relation for the query
   *
   * @param string|null $relation
   * @param int|null $relation_id
   * @return static
   */
  public function relation(?string $relation, ?int $relation_id = null)
  {
    if (class_exists($relation)) {
      /** @var QueryableModel $model */
      $model = new $relation;

      if ($model instanceof QueryableModel) {
        $relation = $model->getRelation();
        $relation_id = $model->getRelationId();

        if (class_exists(Structure::class) && $model instanceof Model) {
          Structure::registerModel(get_class($model));
        }
      }
    }

    if ($relation) {
      $this->relations = $this->relations ?? [];
      $this->relations[] = Str::singular($relation);
      $this->relations = array_filter(array_unique($this->relations));
    }

    if ($relation_id) {
      $this->relation_id = $relation_id;
    }

    return $this;
  }

  public function relations(array $relations)
  {
    foreach ($relations as $relation) {
      $this->relation($relation);
    }

    return $this;
  }

  /**
   * Limits the results to $limit amount of hits
   *
   * @param int $limit
   * @return static
   */
  public function limit(int $limit)
  {
    $this->size = max(min($limit, static::MAX_QUERY_SIZE), static::MIN_QUERY_SIZE);
    return $this;
  }

  /**
   * Sets which fields to retrieve (default: All fields)
   *
   * @param array $fields
   * @return static
   */
  public function fields(array $fields)
  {
    foreach ($fields as $field) {
      $this->field($field);
    }

    return $this;
  }

  /**
   * Adds a field that should be retrieved
   *
   * @param string $field
   * @return static
   */
  public function field(string $field)
  {
    $this->fields = $this->fields ?? [];
    $this->fields[] = $this->compileField($field);
    $this->fields = array_filter(array_unique($this->fields));

    if ($field === '_score') {
      $this->useScores = true;
    }

    return $this;
  }

  public function includeScores($includeScores = true)
  {
    $this->useScores = $includeScores;
    return $this;
  }

  /**
   * @param array $args compiledScopeQuery arguments
   * @param string|null $prefix
   * @param string $operator
   * @param boolean $wrapInParentheses
   * @return static
   */
  protected function prefixedScopedQuery(
    array $args,
    ?string $prefix = null,
    string $operator = 'AND',
    bool $wrapInParentheses = false
  ) {
    $builder = (new static(false, []));
    $query = $builder->compileScopedQuery($args, $operator);
    if ($query) {
      $combinedQuery = implode(' ', array_filter([$prefix, $query]));

      $this->query[] = $wrapInParentheses
        ? "({$combinedQuery})"
        : $combinedQuery;
    }
    $this->useScores = $this->useScores || $builder->useScores;
    return $this;
  }

  /**
   * Creates a nested query scope.
   *
   * @param callable|Closure $closure
   * @param string $operator
   * @return static
   */
  public function group($closure, string $operator = 'AND')
  {
    return $this->prefixedScopedQuery([$closure], null, $operator);
  }

  /**
   * Creates a nested OR separated query scope.
   *
   * @param callable|Closure $closure
   * @return static
   */
  public function or($closure)
  {
    return $this->group($closure, 'OR');
  }

  /**
   * Creates a nested AND separated query scope.
   *
   * @param callable|Closure $closure
   * @return static
   */
  public function and($closure)
  {
    return $this->group($closure, 'AND');
  }

  /**
   * Creates a negated nested query scope.
   *
   * @param callable|Closure $closure
   * @param string $operator
   * @return static
   */
  public function not($closure, string $operator = 'AND')
  {
    return $this->prefixedScopedQuery([$closure], 'NOT', $operator, true);
  }

  /**
   * Performs a 'where' query
   *
   * If $value is omitted, $operator is used as the $value, and the $operator will be set to '='.
   *
   * @param string $field
   * @param string $operator
   * @param null|array|boolean|integer|string|DateTimeInterface $value
   * @return static
   */
  public function where(...$args)
  {
    /** @deprecated use group() instead. */
    if (count($args) === 1) {
      return $this->group(array_pop($args));
    }

    $field = $args[0] ?? null;
    $operator = $args[1] ?? null;
    $value = $args[2] ?? null;

    if (!array_key_exists(2, $args)) {
      $value = $args[1] ?? null;
      $operator = static::OP_EQ;
    }

    $this->query[] = $this->compileWhereQuery($field, $operator, $value);

    return $this;
  }

  /**
   * Queries where field exists in the values
   *
   * @param string $field
   * @param array $values
   * @return static
   */
  public function whereIn(string $field, array $values)
  {
    return $this->where($field, '=', $values);
  }

  /**
   * Queries where field is between $from and $to
   *
   * @param string $field
   * @param null|array|boolean|integer|string|DateTimeInterface $from
   * @param null|array|boolean|integer|string|DateTimeInterface $to
   * @return static
   */
  public function whereBetween(string $field, $from, $to)
  {
    $field = $this->compileField($field);
    $from = $this->escapeValue($from, '=');
    $to = $this->escapeValue($to, '=');
    $this->query[] = "{$field}:[{$from} TO {$to}]";
    return $this;
  }

  /**
   * Queries where field is not between $from and $to
   *
   * @param string $field
   * @param null|array|boolean|integer|string|DateTimeInterface $from
   * @param null|array|boolean|integer|string|DateTimeInterface $to
   * @return static
   *
   * @deprecated 4.5.0 use whereBetween() inside a not() query
   */
  public function whereNotBetween(string $field, $from, $to)
  {
    $field = $this->compileField($field);
    $from = $this->escapeValue($from, '=');
    $to = $this->escapeValue($to, '=');
    $this->query[] = "NOT {$field}:[{$from} TO {$to}]";
    return $this;
  }

  /**
   * Performs a 'whereNot' query
   *
   * If a closure is passed as the only argument, a new query scope will be created.
   * If $value is omitted, $operator is used as the $value, and the $operator will be set to '='.
   *
   * @param Closure|string $field
   * @param string $operator
   * @param null|array|boolean|integer|string|DateTimeInterface $value
   * @return static
   *
   * @deprecated 4.5.0 use where('field', '!=', 'value') or where() inside a not() query
   */
  public function whereNot(...$args)
  {
    if (count($args) === 1) {
      return $this->not(array_pop($args));
    }

    $field = $args[0] ?? null;
    $operator = $args[1] ?? null;
    $value = $args[2] ?? null;

    if (!array_key_exists(2, $args)) {
      $value = $args[1] ?? null;
      $operator = static::OP_EQ;
    }

    $prefix = 'NOT ';

    if ($operator === static::OP_NEQ && $value !== null) {
      $prefix = '';
      $operator = static::OP_EQ;
    }

    if ($operator === static::OP_EQ && $value === null) {
      $prefix = '';
      $operator = static::OP_NEQ;
    }

    $this->query[] = $prefix . $this->compileWhereQuery($field, $operator, $value);

    return $this;
  }

  /**
   * Performs a 'orWhere' query
   *
   * If a closure is passed as the only argument, a new query scope will be created.
   * If $value is omitted, $operator is used as the $value, and the $operator will be set to '='.
   *
   * @param Closure|string $field
   * @param string $operator
   * @param null|array|boolean|integer|string|DateTimeInterface $value
   * @return static
   * @throws InvalidAssignmentException If left hand side of query is not set
   *
   * @deprecated 4.5.0 use where() inside an or() query
   */
  public function orWhere(...$args)
  {
    if (!$this->query || !count($this->query)) {
      throw new InvalidAssignmentException('orWhere');
    }

    return $this->prefixedScopedQuery($args, 'OR');
  }

  /**
   * Performs a 'andWhere' query
   *
   * If a closure is passed as the only argument, a new query scope will be created.
   * If $value is omitted, $operator is used as the $value, and the $operator will be set to '='.
   *
   * @param Closure|string $field
   * @param string $operator
   * @param null|array|boolean|integer|string|DateTimeInterface $value
   * @return static
   * @throws InvalidAssignmentException If left hand side of query is not set
   *
   * @deprecated 4.5.0 use where() inside an and() query
   */
  public function andWhere(...$args)
  {
    if (!$this->query || !count($this->query)) {
      throw new InvalidAssignmentException('andWhere');
    }

    return $this->prefixedScopedQuery($args, 'AND');
  }

  /**
   * Creates a paginated result
   *
   * @param int $size
   * @param int|null $page
   * @return PaginatedResult
   * @throws QueryException|IndexNotFoundException
   */
  public function paginate(int $size = 100, $page = 1)
  {
    $originalSize = $this->size;
    $this->size = $size;
    $paginator = PaginatedResult::fromBuilder($this, $page);
    $this->size = $originalSize;
    return $paginator;
  }

  /**
   * Determines if we should return values as array or object
   *
   * @param bool $assoc
   * @return static
   */
  public function assoc(bool $assoc)
  {
    $this->assoc = $assoc;
    return $this;
  }

  /**
   * Retrieves the raw query result from the API
   *
   * @param int $page
   * @param int $size
   * @return object
   * @throws IndexNotFoundException|QueryException
   */
  public function fetch($size = null, $page = null)
  {
    try {
      $fetch = function () use ($size, $page) {
        return $this->getConnection()
          ->get($this->compileRequest($size, $page), $this->assoc);
      };

      if ($this->shouldCache) {
        if (Facade::getFacadeApplication() && Facade::getFacadeApplication()->has('cache')) {
          return Cache::rememberForever($this->cacheKey, $fetch);
        }
      }

      return $fetch();
    } catch (BadResponseException $e) {
      $response = $e->getResponse();
      $index = $this->relations ? implode(',', $this->relations) : null;
      $index .= $this->relation_id ? ('_' . $this->relation_id) : null;

      if ($response->getStatusCode() === 500) {
        throw new IndexNotFoundException($index);
      }

      $error = json_decode($e->getResponse()->getBody());

      throw new QueryException($this->compileQuery(true), $error);
    }
  }

  /**
   * Retrieves the results of the query
   *
   * @return Collection
   * @throws QueryException|IndexNotFoundException
   */
  public function get()
  {
    $result = $this->fetch();
    $hits = new Collection(($this->assoc ? $result['data'] : $result->data) ?? []);

    if ($this->mapper) {
      return $hits->map($this->mapper)->filter()->values();
    }

    return $hits;
  }

  public function getMapper()
  {
    return $this->mapper;
  }

  /**
   * @param callable $mapper
   * @return static
   */
  public function setMapper(callable $mapper)
  {
    $this->mapper = $mapper;
    return $this;
  }

  /**
   * Retrieves the first result
   *
   * @return object|null
   * @throws QueryException|IndexNotFoundException
   */
  public function first()
  {
    $size = $this->size;
    $this->size = 1;
    $first = $this->get()->first();
    $this->size = $size;

    return $first;
  }

  /**
   * Retrieves the first result
   *
   * @return object|null
   * @throws NotFoundException
   * @throws QueryException|IndexNotFoundException
   */
  public function firstOrFail()
  {
    if ($model = $this->first()) {
      return $model;
    }

    $e = new NotFoundException;

    if ($model = $this->getModel()) {
      $e->setModel($model);
    }

    throw $e;
  }

  /**
   * Retrives all results for the given query, ignoring the query limit
   * @return LazyCollection|Collection
   * @throws QueryException|IndexNotFoundException
   */
  public function all()
  {
    if ($this->model) {
      /** @var QueryableModel */
      $instance = new $this->model;
      if ($instance->usesChunking()) {
        $size = $instance->getPageSize() ?? 100;
        return LazyCollection::make(
          function () use ($size) {
            $chunk = $this->paginate($size);
            foreach ($chunk->all() as $item) {
              yield $item;
            }
            while ($chunk->hasMorePages()) {
              /** @var PaginatedResult $page */
              $chunk = $this->paginate($size, $chunk->currentPage() + 1);
              foreach ($chunk->all() as $item) {
                yield $item;
              }
            }
          }
        );
      }
    }

    $size = $this->size;
    $this->size = static::MAX_QUERY_SIZE;
    $results = $this->get();
    $this->size = $size;

    return $results;
  }

  /**
   * Returns random results for the given query
   * @param int|null $amount If not provided, will use the current query limit
   * @return Collection
   * @throws QueryException|IndexNotFoundException
   */
  public function random($amount = null)
  {
    if ($amount) {
      $this->limit($amount);
    }

    $size = $this->size;
    $fields = $this->fields;
    $query = $this->query;

    $this->size = static::MAX_QUERY_SIZE;
    $this->fields = ['id'];

    $result = array_map(function ($result) {
      return $result['id'];
    }, $this->fetch()['data']);

    $random = [];

    if (count($result)) {
      $amount = min(($amount ?? count($result)), count($result));
      $keys = array_rand($result, $amount);
      $keys = !is_array($keys) ? [$keys] : $keys;
      $keys = array_values($keys);

      foreach ($keys as $key) {
        $random[] = $result[$key];
      }
    }

    $this->size = $size;
    $this->fields = $fields;
    $this->query = [];

    $orderBy = $this->orderBy;
    $this->orderBy = [];

    $result = $this->where('id', $random)->get();

    $this->query = $query;
    $this->size = $size;
    $this->orderBy = $orderBy;

    return $result->shuffle()->values();
  }

  /**
   * Also include unpublished results
   * Only applies to entry and page relations
   *
   * @return static
   * @deprecated 4.5.0 use respectPublishingStatus(false) instead.
   */
  public function ignorePublishingStatus()
  {
    $this->respectPublishingStatus = false;
    return $this;
  }

  /**
   * Only include published results
   * Only applies to entry and page relations
   *
   * @param bool
   *
   * @return static
   */
  public function respectPublishingStatus($respect = true)
  {
    $this->respectPublishingStatus = $respect;
    return $this;
  }

  /**
   * Get the count of items matching the current query
   *
   * @return int
   * @throws QueryException|IndexNotFoundException
   */
  public function count()
  {
    $fields = $this->fields;
    $this->fields = ['id'];
    $count = $this->paginate(1, 1)->total();
    $this->fields = $fields;

    return $count;
  }

  /**
   * @param string|DateTimeInterface|null $date
   */
  public function publishedAt($date)
  {
    $date = Carbon::parse($date)->toDateTimeString();

    $this->respectPublishingStatus(false);

    $builder = new static(false, []);

    $query = $builder
      ->and(fn(Builder $query) => (
        $query
          ->where('published', true)
          ->or(fn(Builder $query) => (
            $query
              ->where('use_time', false)
              ->and(fn(Builder $query) => (
                $query
                  ->where('use_time', true)
                  ->or(fn(Builder $query) => (
                    $query
                      ->and(fn(Builder $query) => (
                        $query
                          ->where('start', '!=', null)
                          ->where('stop', '!=', null)
                          ->where('start', '<=', $date)
                          ->where('stop', '>=', $date)
                      ))
                      ->and(fn(Builder $query) => (
                        $query
                          ->where('start', '!=', null)
                          ->where('stop', '=', null)
                          ->where('start', '<=', $date)
                      ))
                      ->and(fn(Builder $query) => (
                        $query
                          ->where('start', '=', null)
                          ->where('stop', '!=', null)
                          ->where('stop', '>=', $date)
                      ))
                      ->and(fn(Builder $query) => (
                        $query
                          ->where('start', '=', null)
                          ->where('stop', '=', null)
                      ))
                  ))
              ))
          ))
      ))
      ->score(0)
      ->getQuery(true);

    $this->query[] = $query;

    return $this;
  }

  /**
   * @param bool $scoped
   * @return string
   */
  protected function compileQuery(bool $scoped = false, ?string $operator = 'AND')
  {
    $appends = $this->appends;
    $this->appends = [];

    foreach ($appends as $append) {
      $append($this, $scoped);
    }

    if (!$scoped && $this->respectPublishingStatus) {
      $this->publishedAt(Carbon::now());
    }

    if (!$scoped && $this->hasRelation('entry') && $this->relation_id) {
      $this->where('directory_id', '=', $this->relation_id)->score(0);
    }

    $compiledQuery = array_reduce(
      array_filter($this->query, fn($term) => ($term !== '()')),
      function ($query, $term) use ($operator) {
        if (!$query) {
          return $term;
        }

        // This logic is here to support `orWhere` and `andWhere`, and should be removed once they are.
        $op = Str::startsWith($term, ['AND ', 'OR ']) ? '' : $operator;

        return implode(' ', array_filter([$query, $op, $term]));
      }
    );

    return count($this->query) > 1
      ? "({$compiledQuery})"
      : $compiledQuery;
  }

  /**
   * @return string
   */
  protected function compileRequest($size = null, $page = null)
  {
    $query = $this->compileQuery();

    $params = [
      'order' => urlencode(implode(',', $this->orderBy)),
      'dir' => urlencode(implode(',', $this->sortDir)),
      'relation' => $this->relations ? implode(',', $this->relations) : null,
      'fields' => $this->fields ? implode(',', $this->fields) : null,
      'relation_id' => $this->relation_id,
      'size' => $size ?? $this->size,
      'page' => $page,
      'q' => urlencode($query),
      'scores' => $this->useScores ? 1 : false
    ];

    if ($this->debug) {
      $params['debug'] = 1;
    }

    $params = array_filter(array_map(function ($key) use ($params) {
      if ($params[$key]) {
        return "{$key}={$params[$key]}";
      }

      return false;
    }, array_keys($params)));

    $url = 'search?' . implode('&', $params);

    return $url;
  }

  /**
   * Conditional query
   *
   * @param boolean|Closure $clause
   * @param Closure $then
   * @param null|Closure $else
   * @return static
   */
  public function if($clause, Closure $then, ?Closure $else = null)
  {
    if (is_callable($clause)) {
      $clause = $clause();
    }

    if ($clause) {
      $then($this);
    } else {
      if (is_callable($else)) {
        $else($this);
      }
    }

    return $this;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->compileQuery();
  }

  public function getSize(): int
  {
    return $this->size;
  }
}
