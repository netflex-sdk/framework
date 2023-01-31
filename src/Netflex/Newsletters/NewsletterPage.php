<?php 
namespace Netflex\Newsletters;

use Netflex\Pages\AbstractPage;

class NewsletterPage extends AbstractPage
{
    protected $cachesResults = false;

    protected static function makeQueryBuilder($appends = [])
    {
        $builder = parent::makeQueryBuilder($appends);

        return $builder->where('type', 'newsletter');
    }

    public static function all() {
        return parent::all()
            ->filter(fn ($page) => $page->type === 'newsletter');
    }
}
