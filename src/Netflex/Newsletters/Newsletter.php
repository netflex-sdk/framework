<?php

namespace Netflex\Newsletters;

use Exception;
use Html2Text\Html2Text;
use Throwable;

use Netflex\Query\QueryableModel;
use Netflex\Query\Exceptions\NotFoundException;

use Netflex\Newsletters\Traits\CastsDefaultFields;
use Netflex\Newsletters\Traits\HidesDefaultFields;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;
use voku\CssToInlineStyles\CssToInlineStyles;

/**
 * @property int $id
 * @property string $name
 * @property string $subject
 * @property Carbon $created
 * @property Carbon $updated
 * @property bool $sent
 * @property int $userid
 * @property Carbon|null $sent_date
 * @property Carbon|null $schedule_date
 * @property array $tags
 * @property ?NewsletterPage $page
 */
class Newsletter extends QueryableModel
{
  use CastsDefaultFields;
  use HidesDefaultFields;

  /**
   * The connection name for the model.
   *
   * @var string|null
   */
  protected $connection = 'default';

  /**
   * The relation associated with the model.
   *
   * @var string
   */
  protected $relation = 'newsletter';

  /**
   * The resolvable field associated with the model.
   *
   * @var string
   */
  protected $resolvableField = 'token';

  /**
   * The number of models to return for pagination. Also determines chunk size for LazyCollection
   *
   * @var int
   */
  protected $perPage = 100;

  /**
   * Determines if QueryableModel::all() calls in queries should chunk the result.
   * NOTICE: If chunking is enabled, the results of QueryableModel::all() will not be cached, and can result in a performance hit on large structures.
   *
   * @var bool
   */
  protected $useChunking = false;

  /**
   * Indicates if the model should hide default fields
   *
   * @var bool
   */
  protected $hidesDefaultFields = true;

  /**
   * If an accessor method exists, determines if the cast or accessor should run.
   *
   * @var bool
   */
  protected $castIfAccessorExists = false;

  /**
   * Indicates which fields are considered default fields
   *
   * @var string[]
   */
  protected $defaultFields = [
    'name',
    'subject',
    'previewText',
    'consents',
    'to_query',
    'to_query_es',
    'from_mail',
    'from_name',
    'tags',
    'analytics_campaign_name',
    'template_id',
    'userid',
    'output',
    'outputText',
    'token',
    'config',
  ];

  /**
   * Indicates if the model should be timestamped.
   *
   * @var bool
   */
  public $timestamps = true;

  /**
   * The attributes that should be mutated to dates.
   *
   * @var array
   */
  protected $dates = [
    'created',
    'updated',
    'sent_date',
    'scheduled_date',
    'report_date'
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [];

  /**
   * Determines if we should cache some results.
   *
   * @var bool
   */
  protected $cachesResults = true;

  /**
   * @var string A regular campaign
   */
  const TYPE_CAMPAIGN = 'campaign';

  /**
   * @var string A template
   */
  const TYPE_TEMPLATE = 'template';

  /**
   * @var string An automation email
   */
  const TYPE_AUTOMATION = 'automation';

  /**
   * @var string A transactional e-mail
   */
  const TYPE_TRANSACTIONAL = 'transactional';

  /**
   * @var string A transactional e-mail
   */
  const TYPE_TRANSACTIONAL_CAMPAIGN = 'transactional_campaign';

  /**
   * Retrieves a record by key
   *
   * @param int|null $relationId
   * @param mixed $key
   * @return array|null
   */
  protected function performRetrieveRequest(?int $relationId = null, $key)
  {
    return $this->getConnection()
      ->get('relations/newsletters/' . $key, true);
  }

  /**
   * Inserts a new record, and returns its id
   *
   * @property int|null $relationId
   * @property array $attributes
   * @return mixed
   */
  protected function performInsertRequest(?int $relationId = null, array $attributes = [])
  {
    $response = $this->getConnection()
      ->post('relations/newsletters/' . $relationId, $attributes);

    return $response->newsletter_id;
  }

  /**
   * Updates a record
   *
   * @param int|null $relationId
   * @param mixed $key
   * @param array $attributes
   * @return void
   */
  protected function performUpdateRequest(?int $relationId = null, $key, $attributes = [])
  {
    return $this->getConnection()
      ->put('relations/newsletters/' . $key, $attributes);
  }

  /**
   * Deletes a record
   *
   * @param int|null $relationId
   * @param mixed $key
   * @return bool
   */
  protected function performDeleteRequest(?int $relationId = null, $key)
  {
    try {
      $this->getConnection()
        ->delete('relations/newsletters/' . $key);
      return true;
    } catch (Throwable $e) {
      return false;
    }
  }

  /**
   * Retrieve the model for a bound value.
   *
   * @param  mixed  $rawValue
   * @param  string|null $field
   * @return \Illuminate\Database\Eloquent\Model|null
   * @throws NotFoundException
   */
  public function resolveRouteBinding($rawValue, $field = null)
  {
    $field = $field ?? $this->getResolvableField();

    if ($field === 'id') {
      return static::resolvedRouteBinding(static::find($rawValue));
    }

    $query = static::where($field, $rawValue);

    /** @var static */
    if ($model = $query->first()) {
      return static::resolvedRouteBinding($model);
    }

    throw new NotFoundException;
  }

  protected static function resolvedRouteBinding(?Newsletter $model = null)
  {
    return $model;
  }

  /**
   * Gets the page object attached to the newsletter
   *
   * @return NewsletterPage|null
   */
  public function getPageAttribute(): ?NewsletterPage {
    if ($page = NewsletterPage::where('id', $this->page_id)->first()) {
      return $page->loadRevision($page->revision);
    }

    return null;
  }

  /**
   * Renders html of newsletter
   *
   * @return HtmlString
   */
  public function render(): HtmlString {
    if ($page = $this->page) {
        App::setLocale($page->lang ?? App::getLocale());
        return new HtmlString($page->toResponse(request()));
    }

    return new HtmlString;
}

  /**
   * Reunders preview of newsletter in html or text
   *
   * @param string $type
   * @return HtmlString|string
   */
  public function renderPreview($type = 'html')
  {
    $content = $this->render()->toHtml();
    $output = (new CssToInlineStyles($content))->convert();
    $outputText = (new Html2Text($content))->getText();
    switch($type) {
      case 'text':
        return $outputText;
      default:
        return new HtmlString($output);
    }
  }

  /**
   * Renders html and saves the render state
   *
   * @return null
   */
  public function renderAndSave()
  {
      $content = $this->render()->toHtml();
      $this->output = (new CssToInlineStyles($content))->convert();
      $this->outputText = (new Html2Text($content))->getText();
      $this->save();
  }

}
