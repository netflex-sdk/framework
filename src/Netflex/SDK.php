<?php

namespace Netflex;

use Exception;

use Netflex\SDK\Application;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Http\Kernel as HttpKernel;

class SDK
{
  /** @var Application */
  protected $app;

  /** @var static */
  protected static $instance;

  /**
   * @param string|null $basePath
   */
  private function __construct($basePath = null)
  {
    if (!$basePath) {
      $script = $_SERVER['SCRIPT_FILENAME'];
      $basePath = realpath(pathinfo($script, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . '..');
    }

    $this->app = new Application($basePath);
    $this->app->setBasePath($basePath);
    Facade::setFacadeApplication($this->app);
  }

  /**
   * Resolve the given type from the container.
   *
   * (Overriding Container::make)
   *
   * @param string  $abstract
   * @param array  $parameters
   * @return mixed
   */
  public function make($abstract, array $parameters = [])
  {
    return $this->app->make($abstract, $parameters);
  }

  /**
   * @param string|null $basePath
   * @throws Exception
   * @return static
   */
  public static function init($basePath = null)
  {
    if (!static::$instance) {
      static::$instance = new static($basePath);
      return static::$instance;
    }

    throw new Exception('SDK already initialized');
  }

  /**
   * Register a shared binding in the container.
   *
   * @param string $abstract
   * @param \Closure|string|null $concrete
   * @return void
   */
  public function singleton($abstract, $concrete = null)
  {
    return $this->app->singleton($abstract, $concrete);
  }

  /**
   * @return Application
   */
  public function getApp()
  {
    return $this->app;
  }

  /**
   * @param Request $request = null
   * @return void
   */
  public function handle(Request $request = null)
  {
    $request = $request ?? Request::capture();
    $kernel = $this->app->make(HttpKernel::class);
    $response = $kernel->handle($request);
    $response->send();

    return $kernel->terminate($request, $response);
  }
}
