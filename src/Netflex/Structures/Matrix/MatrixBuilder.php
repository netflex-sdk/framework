<?php

namespace Netflex\Structure\Matrix;

use Illuminate\Support\Collection;
use Netflex\Structure\Model;

class MatrixBuilder
{
    private Model $owner;
    private $items;

    public function __construct($owner, $items = [], $default = BaseMatrixEntry::class)
    {
        $this->owner = $owner;
        $this->items = $items;
        $this->resolvers['default'] = $default;
    }

    private array $resolvers = [];

    public function resolve(string $type, string $class): self
    {
        $this->resolvers[$type] = $class;
        return $this;
    }


    private function getResolver(string $type): Model
    {
        $class = ($this->resolvers[$type] ?? $this->resolvers['default']);
        return new $class;
    }

    public function render(): Collection
    {
        $data = collect($this->items)
            ->map(fn(array $data) => $this->getResolver($data['type'])->newFromBuilder($data, true));
        return $data;
    }


}
