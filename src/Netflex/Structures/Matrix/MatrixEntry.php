<?php
namespace Netflex\Structure\Matrix;

use Netflex\Structure\Model;
use Netflex\Structure\Structure;

abstract class MatrixEntry extends Model {
    abstract public function getMatrixType(): string;
}
