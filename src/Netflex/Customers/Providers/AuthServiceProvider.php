<?php

namespace Netflex\Customers\Providers;

use Netflex\Customers\Customer;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider implements UserProvider
{
  /** @var string */
  protected $model;

  public function __construct($app, $model = Customer::class)
  {
    parent::__construct($app);
    $this->model = $model;
  }
  /**
   * Register any application authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    Auth::provider('netflex', function ($app, array $config) {
      return new static($app, $config['model'] ?? Customer::class);
    });
  }

  /**
   * Retrieve a user by their unique identifier.
   *
   * @param  mixed  $identifier
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   */
  public function retrieveById($identifier)
  {
    return $this->model::find($identifier);
  }

  /**
   * Retrieve a user by their unique identifier and "remember me" token.
   *
   * @param  mixed  $identifier
   * @param  string  $token
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   */
  public function retrieveByToken($identifier, $token)
  {
  }

  /**
   * Update the "remember me" token for the given user in storage.
   *
   * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
   * @param  string  $token
   * @return void
   */
  public function updateRememberToken(Authenticatable $user, $token)
  {
  }

  /**
   * Retrieve a user by the given credentials.
   *
   * @param  array  $credentials
   * @return \Illuminate\Contracts\Auth\Authenticatable|null
   */
  public function retrieveByCredentials(array $credentials)
  {
    if (
      empty($credentials) ||
      (count($credentials) === 1 &&
        array_key_exists('password', $credentials))
    ) {
      return;
    }

    return $this->model::authenticate($credentials);
  }

  /**
   * Validate a user against the given credentials.
   *
   * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
   * @param  array  $credentials
   * @return bool
   */
  public function validateCredentials(Authenticatable $user, array $credentials)
  {
    return !!($this->model::authenticate($credentials));
  }
}
