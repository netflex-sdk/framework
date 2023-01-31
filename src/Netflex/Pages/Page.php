<?php
namespace Netflex\Pages;

use Netflex\Pages\AbstractPage;

class Page extends AbstractPage
{
    protected static function makeQueryBuilder($appends = [])
    {
        $builder = parent::makeQueryBuilder($appends);

        return $builder->where('type', 'page');
    }

    public static function all() {
        return parent::all()
            ->filter(fn ($page) => $page->type === null || $page->type === 'page');
    }
}