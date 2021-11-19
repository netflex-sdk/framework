<?php

namespace Netflex\Encryption;

use Netflex\Foundation\Variable;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Encryption\EncryptionServiceProvider as ServiceProvider;

class EncryptionServiceProvider extends ServiceProvider
{
  /**
   * Extract the encryption key from the given configuration.
   *
   * @param  array  $config
   * @return string
   *
   * @throws \RuntimeException
   */
  protected function key(array $config)
  {
    if (empty($config['key'])) {
      $key = Variable::get('netflex_api');

      if (empty($key)) {
        throw new RuntimeException(
          'No application encryption key has been specified.'
        );
      }

      $config['key'] = Str::substr($key, 0, 32);
      $config['cipher'] = 'AES-256-CBC';
    }

    return $config['key'];
  }
}
