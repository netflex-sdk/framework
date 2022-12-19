<?php

namespace Netflex\Commerce\Traits\API;

use Exception;

use Netflex\API\Facades\API;
use Netflex\Commerce\Exceptions\OrderNotFoundException;

use Illuminate\Support\Facades\Session;

trait OrderAPI
{
  use OrderAddAPI;

  /**
   * @param $status
   * @return static
   * @throws Exception
   */
  public function saveStatus($status)
  {
    return $this->save(['status' => $status]);
  }

  /**
   * @param array $payload
   * @return static
   * @throws Exception
   */
  public function save($payload = [])
  {
    if ($this->fireModelEvent('saving') === false) {
      return $this;
    }

    foreach ($this->modified as $modifiedKey) {
      $payload[$modifiedKey] = $this->{$modifiedKey};
    }

    $payload['data'] = $payload['data'] ?? [];
    $payload['data']['_class'] = get_class($this);

    // Post new
    if (!$this->id) {
      if ($this->fireModelEvent('creating') === false) {
        return $this;
      }

      $this->attributes['id'] = API::post(trim(static::$base_path, '/'), $payload)->order_id;


      $this->refresh();

      if ($this->triedReceivedBySession) {
        $this->addToSession();
      }

      $this->fireModelEvent('created', false);
    } else {
      if ($this->fireModelEvent('updating') === false) {
        return $this;
      }

      // Put updates
      if (!empty($payload)) {
        API::put(static::basePath() . $this->id, $payload);

        $this->forgetInCache();
      }

      $this->fireModelEvent('updated', false);
    }

    $this->modified = [];

    $this->fireModelEvent('saved', false);

    return $this;
  }

  /**
   * @return static
   * @throws Exception
   */
  public function refresh()
  {
    if ($this->id) {
      $this->attributes = API::get(static::basePath() . $this->id, true);

      $this->addToCache();
      $this->fireModelEvent('retrieved', false);
    } else {
      $this->save();
    }

    return $this;
  }

  /**
   * @return static
   */
  public function addToSession()
  {
    Session::put(static::$sessionKey, $this->secret);

    return $this;
  }

  /**
   * @return static
   */
  public function removeFromSession()
  {
    if (Session::has(static::$sessionKey)) {
      Session::remove(static::$sessionKey);
    }

    return $this;
  }

  /**
   * Same as checkout, but set checkout_end date in payload for you
   * @param array $payload
   * @return static
   * @throws Exception
   */
  public function checkoutEnd($payload = [])
  {
    $payload['checkout_end'] = static::dateTimeNow();

    return $this->checkout($payload);
  }

  /**
   * @param array $payload
   * @return static
   * @throws Exception
   */
  public function checkout($payload = [])
  {
    API::put(static::basePath() . $this->id . '/checkout', $payload);

    $order = $this->forgetInCache();
    $order->fireModelEvent('checkout', false);

    return $order;
  }

  /**
   * @return static
   * @throws Exception
   */
  public function register()
  {
    API::put(static::basePath() . $this->id . '/register');

    $order = $this->forgetInCache();
    $order->fireModelEvent('registered', false);

    return $order;
  }

  /**
   * Set order->status to "n" and checkout->checkout_end to now
   * @return static
   * @throws Exception
   */
  public function lock()
  {
    API::put(static::basePath() . $this->id . '/lock');
    $this->setLocked(true);

    $order = $this->forgetInCache();
    $order->fireModelEvent('locked', false);

    return $order;
  }

  /**
   * @return static
   * @throws Exception
   */
  public function emptyCart()
  {
    API::delete(static::basePath() . $this->id . '/cart');

    return $this->forgetInCache();
  }

  /**
   * @return static
   * @throws Exception
   */
  public function delete()
  {
    if ($this->fireModelEvent('deleting') === false) {
      return $this;
    }

    API::delete(static::basePath() . $this->id);

    $order = $this->removeFromSession()->forgetInCache();

    $this->fireModelEvent('deleted', false);

    return $order;
  }

  /**
   * Creates empty order object based on orderData
   *
   * @param array $order
   * @return static
   * @throws Exception
   */
  public static function create($attributes = [])
  {
    $order = (new static)->newFromBuilder($attributes);
    foreach ($attributes as $key => $value) {
      $order->modified[] = $key;
    }
    return $order->save();
  }

  /**
   * If no session exist, it creates a new empty order in API with id and secret, and adds it to session.
   *
   * @param string|null $key
   * @return static
   * @throws Exception
   */
  public static function retrieveBySessionOrCreate($key = null)
  {
    $order = static::retrieveBySession($key);

    if (!$order->id) {
      $order->save()->addToSession();
    }

    return $order;
  }

  /**
   * If no session exist, it creates a new empty order object WITHOUT id or secret.
   * But; It makes shure session is set when order is saved.
   *
   * @param string|null $key
   * @return static
   * @throws Exception
   */
  public static function retrieveBySession($key = null)
  {
    if (session_status() == PHP_SESSION_NONE) {
      session_start();
    }

    if ($key) {
      static::$sessionKey = $key;
    }

    if (Session::has(static::$sessionKey)) {
      try {
        $order = static::retrieveBySecret(Session::get(static::$sessionKey));
      } catch (OrderNotFoundException $e) {
        $order = new static();
        $order->removeFromSession();
        $order->triedReceivedBySession = true;
      }
    } else {
      $order = new static();
      $order->triedReceivedBySession = true;
    }

    $order->fireModelEvent('retrieved', false);

    return $order;
  }

  /**
   * @param string $secret
   * @return static
   * @throws Exception|OrderNotFoundException
   */
  public static function retrieveBySecret($secret)
  {
    if (!$data = static::getFromCache($secret)) {
      $data = API::get(static::basePath() . 'secret/' . $secret);
    }

    $order = (new static)->newFromBuilder($data);

    if (!$order->id) {
      throw new OrderNotFoundException('Order not found with secret ' . $secret);
    }

    $order = $order->addToCache();

    $order->fireModelEvent('retrieved', false);

    return $order;
  }

  /**
   * @param string $id
   * @return static
   * @throws Exception|OrderNotFoundException
   */
  public static function retrieveByRegisterId($id)
  {
    $data = API::get(static::basePath() . 'register/' . $id);

    $order = (new static)->newFromBuilder($data);

    if (!$order->id) {
      throw new OrderNotFoundException('Order not found with register id ' . $id);
    }

    $order = $order->addToCache();
    $order->fireModelEvent('retrieved', false);

    return $order;
  }

  /**
   * @param string $id
   * @return static
   * @throws OrderNotFoundException
   */
  public static function retrieve($id)
  {
    if (!$data = static::getFromCache($id)) {
      $data = API::get(static::basePath() . $id);
    }

    $order = (new static)->newFromBuilder($data);

    if (!$order->id) {
      throw new OrderNotFoundException('Order not found with id ' . $id);
    }

    $order = $order->addToCache();
    $order->fireModelEvent('retrieved', false);

    return $order;
  }

  /**
   * @param string $key
   * @return array|null
   */
  protected static function getFromCache($key)
  {
    if (
      static::$useCache
      && class_exists('Illuminate\Support\Facades\Cache')
    ) {
      return \Illuminate\Support\Facades\Cache::get(
        static::$cacheBaseKey . '/' . $key
      );
    }

    return null;
  }

  /**
   * @return static
   */
  protected function addToCache()
  {
    if (
      $this->id
      && static::$useCache
      && class_exists('Illuminate\Support\Facades\Cache')
    ) {
      \Illuminate\Support\Facades\Cache::add(
        static::$cacheBaseKey . '/' . $this->id,
        $this->attributes,
        static::$cacheTTL
      );
      \Illuminate\Support\Facades\Cache::add(
        static::$cacheBaseKey . '/' . $this->secret,
        $this->attributes,
        static::$cacheTTL
      );
    }

    return $this;
  }

  /**
   * @return static
   */
  public function forgetInCache()
  {
    if (
      $this->id
      && static::$useCache
      && class_exists('Illuminate\Support\Facades\Cache')
    ) {
      \Illuminate\Support\Facades\Cache::forget(
        static::$cacheBaseKey . '/' . $this->id
      );
      \Illuminate\Support\Facades\Cache::forget(
        static::$cacheBaseKey . '/' . $this->secret
      );
    }

    return $this;
  }
}
