<?php

namespace Netflex\Structure\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Netflex\Structure\Matrix\BaseMatrixEntry;
use Netflex\Structure\Matrix\MatrixEntry;

class AsMatrix implements Castable
{
    public static function castUsing(array $attributes)
    {
        return new class implements CastsAttributes {

            public function get($model, string $key, $value, array $attributes)
            {
                $default = ($model->matrix ?? [])['default'] ?? BaseMatrixEntry::class;
                $builder = new MatrixBuilder($model, $value, $default);
                $types = ($model->matrix ?? [])[$key] ?? [];

                foreach ($types as $key => $value) {
                    $builder->resolve($key, $value);
                }

                return $builder->render();
            }

            public function set($model, string $key, $value, array $attributes)
            {
                return $value->map(
                    fn(MatrixEntry $mi) => array_merge($mi->getAttributes(), ['type' => $mi->getMatrixType()])
                )->toArray();
            }
        };
    }
}
