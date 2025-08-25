<?php

namespace Netflex\Notifications;

use Illuminate\Mail\Mailable;
use Netflex\Foundation\Template;

class Notification extends Mailable
{
  /** @var Template */
  protected $template;

  /** @var string */
  public $alias;

  protected function __construct(Template $template, $data = [])
  {
    $this->template = $template;
    $this->alias = $this->template->alias;
    $this->viewData = $data;
  }

  public static function all($viewData = [])
  {
    return Template::all()
      ->filter(function (Template $template) {
        return $template->type === 'email';
      })->map(function (Template $template) use ($viewData) {
        return new static($template, $viewData);
      })->values();
  }

  public static function get($id, $data = [])
  {
    if ($template = Template::get($id)) {
      if ($template->type === 'email') {
        return new static($template, $data);
      }
    }
  }

  /**
   * @param string $alias
   * @return static|null
   */
  public static function resolve(string $alias, $viewData = [])
  {
    return static::all($viewData)
      ->first(function (Notification $notification) use ($alias) {
        return $notification->alias === $alias;
      });
  }

  public function build(...$args)
  {
    return $this->html(
      mustache($this->template->body, $this->viewData)
    );
  }
}
