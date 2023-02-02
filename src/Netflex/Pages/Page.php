<?php
namespace Netflex\Pages;

class Page extends AbstractPage
{
    protected static function makeQueryBuilder($appends = [])
    {
        $pagetypes = [
            null,
            static::TYPE_DOMAIN,
            static::TYPE_EXTERNAL,
            static::TYPE_INTERNAL,
            static::TYPE_FOLDER,
            static::TYPE_PAGE
        ];

        $builder = parent::makeQueryBuilder($appends);

        return $builder->whereIn('type', $pagetypes);
    }

    public static function all ()
    {
        $pagetypes = [
            null,
            static::TYPE_DOMAIN,
            static::TYPE_EXTERNAL,
            static::TYPE_INTERNAL,
            static::TYPE_FOLDER,
            static::TYPE_PAGE
        ];

        return parent::all()
            ->whereIn('type', $pagetypes);
    }
}
