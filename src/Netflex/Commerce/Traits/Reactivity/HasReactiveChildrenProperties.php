<?php

namespace Netflex\Commerce\Traits\Reactivity;

use Illuminate\Support\Collection;
use Netflex\Commerce\AbstractOrder;
use Netflex\Support\ItemCollection;
use Netflex\Support\ReactiveObject;
use ReflectionClass;
use RuntimeException;

trait HasReactiveChildrenProperties
{
  private function setReactiveObject(
    object|array|null $object,
    string $attributeKey,
    string $protectedReactiveObjectKey,
  ): void {
    if ($object instanceof ReactiveObject) {
      if ($object->parent !== null && $object->parent !== $this) {
        $objectName = (new ReflectionClass($object))->getShortName();
        $selfName = (new ReflectionClass($this))->getShortName();

        throw new RuntimeException(
          "Argument \$object can not be a {$objectName} instance whose parent is a different {$selfName} instance",
        );
      }

      $object->parent = $this;
      $object->addHook('modified', function () use ($attributeKey) {
        $this->modified[] = $attributeKey;
        $this->modified = array_unique($this->modified);
        $this->performHook('modified');
      });

      $this->{$protectedReactiveObjectKey} = $object;
      $this->attributes[$attributeKey] = $object->jsonSerialize();
    } else {
      if (is_object($object) || is_array($object)) {
        foreach ($object as $property => $value) {
          $this->attributes[$attributeKey][$property] = $value;
        }
      } else {
        $this->attributes[$attributeKey] = $object;
      }

      unset($this->{$protectedReactiveObjectKey});
    }

    $this->modified[] = $attributeKey;
    $this->modified = array_unique($this->modified);
    $this->performHook('modified');
  }

  /**
   * @template T of ReactiveObject
   * @param object|array|null $attributes
   * @param class-string<T> $reactiveObjectClassString
   * @param string $attributeKey
   * @param string $protectedReactiveObjectKey
   * @return T
   */
  private function getReactiveObject(
    object|array|null $attributes,
    string $reactiveObjectClassString,
    string $attributeKey,
    string $protectedReactiveObjectKey,
  ): ReactiveObject {
    return $this->{$protectedReactiveObjectKey}
      ??= $reactiveObjectClassString::factory($attributes, $this)
      ->addHook('modified', function () use ($attributeKey) {
        $this->modified[] = $attributeKey;
        $this->modified = array_unique($this->modified);
        $this->performHook('modified');
      });
  }

  private function setItemCollection(
    Collection|array|null $items,
    string $attributeKey,
    string $protectedItemCollectionKey,
  ): void {
    if ($items instanceof ItemCollection) {
      if ($items->parent !== null && $items->parent !== $this) {
        $collectionName = (new ReflectionClass($items))->getShortName();
        $selfName = (new ReflectionClass($this))->getShortName();

        throw new RuntimeException(
          "Argument \$items can not be a {$collectionName} instance whose parent is a different {$selfName} instance",
        );
      }

      $items->parent = $this;
      $items->addHook('modified', function () use ($attributeKey) {
        $this->modified[] = $attributeKey;
        $this->modified = array_unique($this->modified);
        $this->performHook('modified');
      });

      $this->{$protectedItemCollectionKey} = $items;
      $this->attributes[$attributeKey] = $items->jsonSerialize();
    } else {
      if ($items instanceof Collection || is_array($items)) {
        $this->attributes[$attributeKey] = [];

        foreach ($items as $item) {
          if (
            $item instanceof ItemCollection
            || $item instanceof ReactiveObject
          ) {
            $this->attributes[$attributeKey][] = $item
              ->jsonSerialize();
          } else {
            $this->attributes[$attributeKey][] = $item;
          }
        }
      } else {
        $this->attributes[$attributeKey] = $items;
      }

      unset($this->{$protectedItemCollectionKey});
    }

    $this->modified[] = $attributeKey;
    $this->modified = array_unique($this->modified);
    $this->performHook('modified');
  }

  /**
   * @template T of ItemCollection
   * @param array|null $items
   * @param class-string<T> $itemCollectionClassString
   * @param string $attributeKey
   * @param string $protectedItemCollectionObjectKey
   * @return T
   */
  private function getItemCollection(
    array|null $items,
    string $itemCollectionClassString,
    string $attributeKey,
    string $protectedItemCollectionObjectKey,
  ): ItemCollection {
    return $this->{$protectedItemCollectionObjectKey}
      ??= $itemCollectionClassString::factory($items, $this)
      ->addHook('modified', function () use ($attributeKey) {
        $this->modified[] = $attributeKey;
        $this->modified = array_unique($this->modified);
        $this->performHook('modified');
      });
  }
}
