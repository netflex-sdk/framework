<?php

namespace Netflex\Structure\Traits;

use Netflex\Structure\AsMatrix;

trait MatrixCasts
{
    public static function bootMatrixCasts()
    {
        static::retrieved(fn($model) => $model->__resolveMatrixTypes());
    }

    private function __resolveMatrixTypes()
    {
        foreach ($this->getCasts() as $key => $value) {
            if ($value == 'matrix') {
                $this->mergeCasts([$key => AsMatrix::class]);
            }
        }
    }
}
