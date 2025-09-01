<?php

namespace Netflex\Structure\Matrix;

/**
 * @property string $type
 */
class BaseMatrixEntry extends MatrixEntry
{

    public function getMatrixType(): string {
        return $this->type;
    }

    public function setRawAttributes(array $attributes, $sync = false)
    {
        $renamedAttributes = [];
        foreach ($attributes as $key => $value) {
            $renamedAttributes[($this->renames ?? [])[$key] ?? $key] = $value;
        }

        return parent::setRawAttributes($renamedAttributes, $sync);
    }
}
