<?php

namespace Netflex\Breadcrumbs;

use Netflex\Pages\Page;

class Breadcrumb {
  protected $currentPage;
  protected $crumbs;
  protected $hideCurrentPage;
  protected $options = [];
  protected $defaultOptions = [
    'hideCurrentPage' => false,
    'hideRootPage' => false,
    'overrideRootPageLabel' => null,
    'overrideRootPagePath' => null,
    'inferCurrentPage' => true,
  ];

  public function __construct(Page $currentPage, $options = []) {
    $this->currentPage = $currentPage;
    $this->crumbs = collect([]);
    $this->options = array_merge($this->defaultOptions, $options);

    if ($currentPage) {
      $structure = collect(static::breadcrumbTraverser($this, $currentPage));

      $structure = $structure->filter(function ($page) use ($currentPage) {
        if (($page === $currentPage) && $this->options['hideCurrentPage']) return false;

        return true;
      });

      $structure->each(function ($item) use ($structure) {
        $options = [];

        if ($item === $structure->last()) {
          if ($this->options['overrideRootPageLabel']) {
            $options['label'] = $this->options['overrideRootPageLabel'];
          }

          if ($this->options['overrideRootPagePath']) {
            $options['path'] = $this->options['overrideRootPagePath'];
          }
        }

        $this->prepend($item, $options);
      });
    }
  }

  public static function breadcrumbTraverser (Breadcrumb $breadcrumb, ?Page $page) {
    if (is_null($page)) return [];
    if ($breadcrumb->options['hideRootPage'] && is_null($page->parent)) return [];

    return array_merge([$page], static::breadcrumbTraverser($breadcrumb, $page->parent));
  }

  public function createItem (Page $page, array $options) {
    $defaultOptions = [
      'current' => $this->options['inferCurrentPage'] ? ($page === $this->currentPage) : false,
      'label' => $page->name,
      'path' => null,
    ];

    $options = array_merge($defaultOptions, $options);

    if (is_null($options['path'])) {
      $options['path'] = $page->url;
    }

    return [
      'label' => $options['label'],
      'originalLabel' => $page->name,
      'path' => $options['path'],
      'id' => $page->id,
      'type' => $page->type,
      'published' => (bool) $page->published,
      'current' => $options['current']
    ];
  }

  public function append (Page $page, array $options = []) {
    $item = $this->createItem($page, $options);

    if ($item !== null) {
      $this->crumbs->push($item);
    }
  }

  public function prepend (Page $page, array $options = []) {
    $item = $this->createItem($page, $options);

    if ($item !== null) {
      $this->crumbs->prepend($item);
    }
  }

  public function get () {
    return $this->crumbs;
  }
}