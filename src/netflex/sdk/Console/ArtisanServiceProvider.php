<?php

namespace Netflex\Console;

use Netflex\Console\Commands\CacheClearCommand;
use Netflex\Console\Commands\ModelMakeCommand;
use Netflex\Console\Commands\ServeCommand;
use Netflex\Console\Commands\NetflexSetupCommand;
use Netflex\Console\Commands\MailMakeCommand;

use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\Console\ForgetCommand as CacheForgetCommand;
use Illuminate\Console\Scheduling\ScheduleFinishCommand;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Console\Scheduling\ScheduleWorkCommand;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Console\ChannelMakeCommand;
use Illuminate\Foundation\Console\ClearCompiledCommand;
use Illuminate\Foundation\Console\ComponentMakeCommand;
use Illuminate\Foundation\Console\ConfigCacheCommand;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Illuminate\Foundation\Console\DownCommand;
use Illuminate\Foundation\Console\EnvironmentCommand;
use Illuminate\Foundation\Console\EventCacheCommand;
use Illuminate\Foundation\Console\EventClearCommand;
use Illuminate\Foundation\Console\EventGenerateCommand;
use Illuminate\Foundation\Console\EventListCommand;
use Illuminate\Foundation\Console\EventMakeCommand;
use Illuminate\Foundation\Console\ExceptionMakeCommand;
use Illuminate\Foundation\Console\JobMakeCommand;
use Illuminate\Foundation\Console\KeyGenerateCommand;
use Illuminate\Foundation\Console\ListenerMakeCommand;
use Illuminate\Foundation\Console\NotificationMakeCommand;
use Illuminate\Foundation\Console\ObserverMakeCommand;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use Illuminate\Foundation\Console\PolicyMakeCommand;
use Illuminate\Foundation\Console\ProviderMakeCommand;
use Illuminate\Foundation\Console\RequestMakeCommand;
use Illuminate\Foundation\Console\ResourceMakeCommand;
use Illuminate\Foundation\Console\RouteCacheCommand;
use Illuminate\Foundation\Console\RouteClearCommand;
use Illuminate\Foundation\Console\RouteListCommand;
use Illuminate\Foundation\Console\RuleMakeCommand;
use Illuminate\Foundation\Console\TestMakeCommand;
use Illuminate\Foundation\Console\UpCommand;
use Illuminate\Foundation\Console\VendorPublishCommand;
use Illuminate\Foundation\Console\ViewCacheCommand;
use Illuminate\Foundation\Console\ViewClearCommand;
use Netflex\Console\Commands\ControllerMakeCommand;
use Illuminate\Routing\Console\MiddlewareMakeCommand;

class ArtisanServiceProvider extends ServiceProvider implements DeferrableProvider
{
  /**
   * The commands to be registered.
   *
   * @var array
   */
  protected $commands = [
    'CacheClear' => 'command.cache.clear',
    'CacheForget' => 'command.cache.forget',
    'ClearCompiled' => 'command.clear-compiled',
    'ConfigCache' => 'command.config.cache',
    'ConfigClear' => 'command.config.clear',
    'Down' => 'command.down',
    'Environment' => 'command.environment',
    'KeyGenerate' => 'command.key.generate',
    'PackageDiscover' => 'command.package.discover',
    'RouteCache' => 'command.route.cache',
    'RouteClear' => 'command.route.clear',
    'RouteList' => 'command.route.list',
    'ScheduleFinish' => ScheduleFinishCommand::class,
    'ScheduleRun' => ScheduleRunCommand::class,
    'ScheduleWork' => ScheduleWorkCommand::class,
    'Up' => 'command.up',
    'ViewCache' => 'command.view.cache',
    'ViewClear' => 'command.view.clear',
  ];

  /**
   * The commands to be registered.
   *
   * @var array
   */
  protected $devCommands = [
    'ChannelMake' => 'command.channel.make',
    'ComponentMake' => 'command.component.make',
    'ConsoleMake' => 'command.console.make',
    'ControllerMake' => 'command.controller.make',
    'EventGenerate' => 'command.event.generate',
    'EventMake' => 'command.event.make',
    'ExceptionMake' => 'command.exception.make',
    'JobMake' => 'command.job.make',
    'ListenerMake' => 'command.listener.make',
    'MiddlewareMake' => 'command.middleware.make',
    'ModelMake' => 'command.model.make',
    'NotificationMake' => 'command.notification.make',
    'ObserverMake' => 'command.observer.make',
    'PolicyMake' => 'command.policy.make',
    'ProviderMake' => 'command.provider.make',
    'RequestMake' => 'command.request.make',
    'ResourceMake' => 'command.resource.make',
    'RuleMake' => 'command.rule.make',
    'Serve' => 'command.serve',
    'NetflexSetup' => 'command.netflex.setup',
    'VendorPublish' => 'command.vendor.publish',
    'MailMake' => 'command.mail.make',
  ];

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {
    $this->registerCommands(array_merge(
      $this->commands,
      $this->devCommands
    ));
  }

  /**
   * Register the given commands.
   *
   * @param  array  $commands
   * @return void
   */
  protected function registerCommands(array $commands)
  {
    foreach (array_keys($commands) as $command) {
      call_user_func_array([$this, "register{$command}Command"], []);
    }

    $this->commands(array_values($commands));
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerCacheClearCommand()
  {
    $this->app->singleton('command.cache.clear', function ($app) {
      return new CacheClearCommand($app['cache'], $app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerCacheForgetCommand()
  {
    $this->app->singleton('command.cache.forget', function ($app) {
      return new CacheForgetCommand($app['cache']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerChannelMakeCommand()
  {
    $this->app->singleton('command.channel.make', function ($app) {
      return new ChannelMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerClearCompiledCommand()
  {
    $this->app->singleton('command.clear-compiled', function () {
      return new ClearCompiledCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerConfigCacheCommand()
  {
    $this->app->singleton('command.config.cache', function ($app) {
      return new ConfigCacheCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerConfigClearCommand()
  {
    $this->app->singleton('command.config.clear', function ($app) {
      return new ConfigClearCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerComponentMakeCommand()
  {
    $this->app->singleton('command.component.make', function ($app) {
      return new ComponentMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerConsoleMakeCommand()
  {
    $this->app->singleton('command.console.make', function ($app) {
      return new ConsoleMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerControllerMakeCommand()
  {
    $this->app->singleton('command.controller.make', function ($app) {
      return new ControllerMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerEventGenerateCommand()
  {
    $this->app->singleton('command.event.generate', function () {
      return new EventGenerateCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerEventMakeCommand()
  {
    $this->app->singleton('command.event.make', function ($app) {
      return new EventMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerExceptionMakeCommand()
  {
    $this->app->singleton('command.exception.make', function ($app) {
      return new ExceptionMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMailMakeCommand()
  {
    $this->app->singleton('command.mail.make', function ($app) {
      return new MailMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerDownCommand()
  {
    $this->app->singleton('command.down', function () {
      return new DownCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerEnvironmentCommand()
  {
    $this->app->singleton('command.environment', function () {
      return new EnvironmentCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerEventCacheCommand()
  {
    $this->app->singleton('command.event.cache', function () {
      return new EventCacheCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerEventClearCommand()
  {
    $this->app->singleton('command.event.clear', function ($app) {
      return new EventClearCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerEventListCommand()
  {
    $this->app->singleton('command.event.list', function () {
      return new EventListCommand();
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerJobMakeCommand()
  {
    $this->app->singleton('command.job.make', function ($app) {
      return new JobMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerKeyGenerateCommand()
  {
    $this->app->singleton('command.key.generate', function () {
      return new KeyGenerateCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerListenerMakeCommand()
  {
    $this->app->singleton('command.listener.make', function ($app) {
      return new ListenerMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerMiddlewareMakeCommand()
  {
    $this->app->singleton('command.middleware.make', function ($app) {
      return new MiddlewareMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerModelMakeCommand()
  {
    $this->app->singleton('command.model.make', function ($app) {
      return new ModelMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerNotificationMakeCommand()
  {
    $this->app->singleton('command.notification.make', function ($app) {
      return new NotificationMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerObserverMakeCommand()
  {
    $this->app->singleton('command.observer.make', function ($app) {
      return new ObserverMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerPackageDiscoverCommand()
  {
    $this->app->singleton('command.package.discover', function () {
      return new PackageDiscoverCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerPolicyMakeCommand()
  {
    $this->app->singleton('command.policy.make', function ($app) {
      return new PolicyMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerProviderMakeCommand()
  {
    $this->app->singleton('command.provider.make', function ($app) {
      return new ProviderMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerRequestMakeCommand()
  {
    $this->app->singleton('command.request.make', function ($app) {
      return new RequestMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerResourceMakeCommand()
  {
    $this->app->singleton('command.resource.make', function ($app) {
      return new ResourceMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerRuleMakeCommand()
  {
    $this->app->singleton('command.rule.make', function ($app) {
      return new RuleMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerRouteCacheCommand()
  {
    $this->app->singleton('command.route.cache', function ($app) {
      return new RouteCacheCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerRouteClearCommand()
  {
    $this->app->singleton('command.route.clear', function ($app) {
      return new RouteClearCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerRouteListCommand()
  {
    $this->app->singleton('command.route.list', function ($app) {
      return new RouteListCommand($app['router']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerScheduleFinishCommand()
  {
    $this->app->singleton(ScheduleFinishCommand::class);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerScheduleRunCommand()
  {
    $this->app->singleton(ScheduleRunCommand::class);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerScheduleWorkCommand()
  {
    $this->app->singleton(ScheduleWorkCommand::class);
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerServeCommand()
  {
    $this->app->singleton('command.serve', function () {
      return new ServeCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerNetflexSetupCommand()
  {
    $this->app->singleton('command.netflex.setup', function () {
      return new NetflexSetupCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerTestMakeCommand()
  {
    $this->app->singleton('command.test.make', function ($app) {
      return new TestMakeCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerUpCommand()
  {
    $this->app->singleton('command.up', function () {
      return new UpCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerVendorPublishCommand()
  {
    $this->app->singleton('command.vendor.publish', function ($app) {
      return new VendorPublishCommand($app['files']);
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerViewCacheCommand()
  {
    $this->app->singleton('command.view.cache', function () {
      return new ViewCacheCommand;
    });
  }

  /**
   * Register the command.
   *
   * @return void
   */
  protected function registerViewClearCommand()
  {
    $this->app->singleton('command.view.clear', function ($app) {
      return new ViewClearCommand($app['files']);
    });
  }

  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides()
  {
    return array_merge(array_values($this->commands), array_values($this->devCommands));
  }
}
