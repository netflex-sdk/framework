<?php

namespace Netflex\Customers;

use Netflex\Support\Retrievable;
use Netflex\Support\ReactiveObject;
use Netflex\Customers\Traits\API\Groups as GroupsAPI;

/**
 * @property-read int $id
 * @property string $name
 * @property string $description
 * @property string $alias
 * @property string $relation
 * @property int $relation_id
 * */

class Group extends ReactiveObject
{

  use GroupsAPI;
  use Retrievable;

  /** @var array */
  protected $timestamps = [];

  /** @var string */
  protected static $base_path = 'relations/customers/groups';
}
