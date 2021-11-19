<?php

namespace Netflex\Commerce\Traits\API;

use Exception;
use Netflex\API\Facades\API;

trait OrderAddAPI
{
  /**
   * @param array $item
   * @return static
   * @throws Exception
   */
  public function addCart($item)
  {
    if (!$this->id) {
      $this->save();
    }

    API::post(static::basePath() . $this->id . '/cart', $item);

    return $this->forgetInCache();
  }

  /**
   * @param array $item
   * @return static
   * @throws Exception
   */
  public function addDiscount($item)
  {
    if (!$this->id) {
      $this->save();
    }

    API::post(static::basePath() . $this->id . '/discount', $item);

    return $this->forgetInCache();
  }

  /**
   * @param array $item
   * @return static
   * @throws Exception
   */
  public function addPayment($item)
  {
    if (!$this->id) {
      $this->save();
    }

    API::post(static::basePath() . $this->id . '/payment', $item);

    return $this->forgetInCache();
  }

  /**
   * @param string $key
   * @param string $value
   * @param string $label
   * @return static
   * @throws Exception
   */
  public function addData($key, $value, $label = '')
  {
    if (!$this->id) {
      $this->save();
    }

    $item = [
      'data_alias' => $key,
      'value' => $value
    ];

    if (!empty($label)) {
      $item['label'] = $label;
    }

    API::put(static::basePath() . $this->id . '/data', $item);

    return $this->forgetInCache();
  }

  /**
   * @param array|string $item
   * @return static
   * @throws Exception
   */
  public function addLogInfo($item)
  {
    return $this->addLog($item, 'n');
  }

  /**
   * @param array|string $item
   * @return static
   * @throws Exception
   */
  public function addLogWarning($item)
  {
    return $this->addLog($item, 'w');
  }

  /**
   * @param array|string $item
   * @return static
   * @throws Exception
   */
  public function addLogDanger($item)
  {
    return $this->addLog($item, 'd');
  }

  /**
   * @param array|string $item
   * @return static
   * @throws Exception
   */
  public function addLogSuccess($item)
  {
    return $this->addLog($item, 's');
  }

  /**
   * @param array|string $item
   * @param string $type
   * @return static
   * @throws Exception
   */
  public function addLog($item, $type = 'i')
  {
    if (!$this->id) {
      $this->save();
    }

    if (is_string($item)) {
      $item = ['msg' => $item];
    }

    $item['type'] = $item['type'] ?? $type;

    API::post(static::basePath() . $this->id . '/log', $item);

    return $this->forgetInCache();
  }
}
