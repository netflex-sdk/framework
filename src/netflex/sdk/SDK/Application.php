<?php

namespace Netflex\SDK;

use Exception;
use Netflex\Log\LogServiceProvider;
use Netflex\API\Providers\APIServiceProvider;

use Illuminate\Routing\RoutingServiceProvider;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Foundation\Application as BaseApplication;
use Illuminate\Foundation\Mix;

class Application extends BaseApplication
{
  /**
   * Get the path to the bootstrap directory.
   *
   * @param  string  $path Optionally, a path to append to the bootstrap path
   * @return string
   */
  public function bootstrapPath($path = '')
  {
    return $this->basePath . DIRECTORY_SEPARATOR . 'storage/sdk' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
  }

  /**
   * Determine if the application is in the local environment.
   *
   * @return bool
   */
  public function isLocal()
  {
    return $this['env'] !== 'master';
  }

  /**
   * Determine if the application is in the production environment.
   *
   * @return bool
   */
  public function isProduction()
  {
    return $this['env'] === 'master';
  }

  /**
   * Register all of the base service providers.
   *
   * @return void
   */
  protected function registerBaseServiceProviders()
  {
    $this->register(new APIServiceProvider($this));
    $this->register(new EventServiceProvider($this));
    $this->register(new LogServiceProvider($this));
    $this->register(new RoutingServiceProvider($this));
  }

  /**
   * Register the core class aliases in the container.
   *
   * @return void
   */
  public function registerCoreContainerAliases()
  {
    foreach ([
      'app'                  => [\Illuminate\Foundation\Application::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class, \Psr\Container\ContainerInterface::class],
      'auth'                 => [\Illuminate\Auth\AuthManager::class, \Illuminate\Contracts\Auth\Factory::class],
      'auth.driver'          => [\Illuminate\Contracts\Auth\Guard::class],
      'blade.compiler'       => [\Illuminate\View\Compilers\BladeCompiler::class],
      'cache'                => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
      'cache.store'          => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class, \Psr\SimpleCache\CacheInterface::class],
      'cache.psr6'           => [\Symfony\Component\Cache\Adapter\Psr16Adapter::class, \Symfony\Component\Cache\Adapter\AdapterInterface::class, \Psr\Cache\CacheItemPoolInterface::class],
      'config'               => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
      'cookie'               => [\Illuminate\Cookie\CookieJar::class, \Illuminate\Contracts\Cookie\Factory::class, \Illuminate\Contracts\Cookie\QueueingFactory::class],
      'encrypter'            => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class],
      'events'               => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
      'files'                => [\Illuminate\Filesystem\Filesystem::class],
      'filesystem'           => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
      'filesystem.disk'      => [\Illuminate\Contracts\Filesystem\Filesystem::class],
      'filesystem.cloud'     => [\Illuminate\Contracts\Filesystem\Cloud::class],
      'hash'                 => [\Illuminate\Hashing\HashManager::class],
      'hash.driver'          => [\Illuminate\Contracts\Hashing\Hasher::class],
      'translator'           => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
      'log'                  => [\Netflex\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
      'mail.manager'         => [\Illuminate\Mail\MailManager::class, \Illuminate\Contracts\Mail\Factory::class],
      'mailer'               => [\Illuminate\Mail\Mailer::class, \Illuminate\Contracts\Mail\Mailer::class, \Illuminate\Contracts\Mail\MailQueue::class],
      'auth.password'        => [\Illuminate\Auth\Passwords\PasswordBrokerManager::class, \Illuminate\Contracts\Auth\PasswordBrokerFactory::class],
      'auth.password.broker' => [\Illuminate\Auth\Passwords\PasswordBroker::class, \Illuminate\Contracts\Auth\PasswordBroker::class],
      'queue'                => [\Illuminate\Queue\QueueManager::class, \Illuminate\Contracts\Queue\Factory::class, \Illuminate\Contracts\Queue\Monitor::class],
      'queue.connection'     => [\Illuminate\Contracts\Queue\Queue::class],
      'queue.failer'         => [\Illuminate\Queue\Failed\FailedJobProviderInterface::class],
      'redirect'             => [\Illuminate\Routing\Redirector::class],
      'redis'                => [\Illuminate\Redis\RedisManager::class, \Illuminate\Contracts\Redis\Factory::class],
      'request'              => [\Illuminate\Http\Request::class, \Symfony\Component\HttpFoundation\Request::class],
      'router'               => [\Illuminate\Routing\Router::class, \Illuminate\Routing\Router::class, \Illuminate\Contracts\Routing\Registrar::class,  \Illuminate\Contracts\Routing\BindingRegistrar::class],
      'session'              => [\Illuminate\Session\SessionManager::class],
      'session.store'        => [\Illuminate\Session\Store::class, \Illuminate\Contracts\Session\Session::class],
      'url'                  => [\Illuminate\Routing\UrlGenerator::class, \Illuminate\Contracts\Routing\UrlGenerator::class],
      'validator'            => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
      'view'                 => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
    ] as $key => $aliases) {
      foreach ($aliases as $alias) {
        $this->alias($key, $alias);
      }
    }
  }
}
