<?php

namespace Netflex\Newsletters;

use Carbon\Carbon;
use Handlebars\Handlebars;
use Handlebars\Helpers;
use Handlebars\Loader\StringLoader;
use Illuminate\Support\Facades\App;
use Netflex\Commerce\Order;
use Netflex\Customers\Customer;
use Netflex\Foundation\Template;

class AutomationMailMessage implements \Stringable
{
  private static array $cache = [];

  private ?int $mailId = null;
  private array $data = [];
  private array $replacementTags = [];
  private ?string $subject = null;

  private ?array $from = null;
  private ?string $replyTo = null;

  public function withTemplate(int $id): self
  {
    $this->mailId = $id;
    return $this;
  }

  public function withOrder(Order $order): self
  {
    $this->replacementTags['order'] = $order->toArray();
    return $this;
  }

  public function withCustomer(Customer $customer): self
  {
    $this->replacementTags['customer'] = $customer->toArray();
    return $this;
  }

  /**
   * Server side variables that will be passed to the template as variables.
   * Just like when using view('view', $variables);
   *
   * @param array $variables
   * @return $this
   */
  public function withData(array $variables)
  {
    $this->data = $variables;
    return $this;
  }

  public function withFromAddress(?string $address = null, ?string $name = null)
  {
    $this->from = [
      'name' => $name ?: $address ?: variable('mail_sender_name'),
      'mail' => $address ?: variable('mail_sender_mail')
    ];
    return $this;
  }

  public function withReplyTo(string $address, ?string $name = null)
  {
    $this->replyTo = $name ? "$name <$address>" : $address;
    return $this;
  }

  /**
   * Replacement tags that will be replaced using handlebars.
   * This is to allow the user to add certain fields into their manually edited text.
   *
   * @param array $replacementTags
   * @return $this
   */
  public function withReplacementTags(array $replacementTags)
  {
    $this->replacementTags['data'] = $replacementTags;
    return $this;
  }

  public static function make(): self
  {
    return new static();
  }

  public function getNewsletter(): ?Newsletter
  {
    if (!$this->mailId) throw new \Exception("You must supply a mail id");
    static::$cache[$this->mailId] ??= Newsletter::find($this->mailId);
    return static::$cache[$this->mailId] ?? null;
  }

  public function getNewsletterId(): ?int
  {
    return $this->mailId;
  }

  public function getSubject()
  {
    $handlebars = new Handlebars([
      'loader' => new StringLoader(),
      'helpers' => new Helpers(),
    ]);

    return $handlebars->render($this->subject ?: $this->getNewsletter()->subject ?? '', $this->replacementTags);
  }

  public function getReplyTo(): ?string
  {
    return $this->replyTo;
  }

  public function getFrom(): array
  {
    return $this->from ?: [
      'name' => variable('mail_sender_name'),
      'mail' => variable('mail_sender_mail')
    ];
  }

  public function __toString()
  {
    $mail = $this->getNewsletter();
    $page = data_get($mail, 'page');
    /** @var Template $template */
    $template = data_get($mail, 'page.template');
    if (!$template) throw new \Exception("Expected the newsletter to return a page and a template, template is missing");

    current_newsletter($mail);
    current_page($page);

    if ($locale = $page->lang ?: optional($page->master)->lang) {
      App::setLocale($locale);
      Carbon::setLocale($locale);
    }

    $handlebars = new Handlebars([
      'loader' => new StringLoader(),
      'helpers' => new Helpers(),
    ]);

    $response = $template->toResponse($this->data);
    if (current_mode() === 'live') {
      return $handlebars->render($response, $this->replacementTags);
    }
    return $response;

  }
}
