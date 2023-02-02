<?php

namespace Netflex\Newsletters;

use Netflex\Pages\AbstractPage;

class NewsletterPage extends AbstractPage
{
    protected $cachesResults = false;

    protected static function makeQueryBuilder($appends = [])
    {
        $builder = parent::makeQueryBuilder($appends);

        return $builder->where('type', static::TYPE_NEWSLETTER);
    }

    public static function all()
    {
        return parent::all()
            ->where('type', static::TYPE_NEWSLETTER)
            ->values();
    }
}
