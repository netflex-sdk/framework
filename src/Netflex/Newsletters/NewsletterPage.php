<?php

namespace Netflex\Newsletters;

use Illuminate\Support\Facades\App;
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

    public function getLangAttribute()
    {
      foreach($this->attributes['content'] as $content)
      {
        if($content['area'] === 'newsletterLocale') {
          return $content['text'];
        }
      }
      return $this->attributes['lang'] ?? App::getLocale();
    }

}
