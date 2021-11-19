<?php

namespace Netflex\Console\Commands;

use Closure;
use Exception;

use Netflex\API\Client;
use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;

use Dotenv\Dotenv;

use Illuminate\Foundation\Console\ServeCommand as Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

use Apility\Laravel\Ngrok\NgrokProcessBuilder;
use Apility\Laravel\Ngrok\NgrokWebService;

class ServeCommand extends Command
{
  /**
   * Process builder.
   *
   * @var \Apility\Laravel\Ngrok\NgrokProcessBuilder
   */
  protected $processBuilder;

  /**
   * Web service.
   *
   * @var \Apility\Laravel\Ngrok\NgrokWebService
   */
  protected $webService;

  /**
   * Netflex Proxy Configuration Variable
   *
   * @var \Netflex\Foundation\Variable
   */
  protected $variable;

  /**
   * Netflex Proxy Configuration
   *
   * @var object
   */
  protected $configuration;

  /**
   * Netflex User ID
   *
   * @var int
   */
  protected $userId;

  /**
   * Netflex Client authenticated as User
   *
   * @var \Netflex\API\Client
   */
  protected $userClient;

  /**
   * Progressbar
   *
   * @var Symfony\Component\Console\Helper\ProgressBar
   */
  protected $progressBar;

  /**
   * The file holding the Netflex credentials
   *
   * @var string
   */
  protected $credentialsFile = '.netflexrc';

  /**
   * Get the port for the command.
   *
   * @return string
   */
  protected function port()
  {
    $port = $this->input->getOption('port') ?: 8000;

    return $port + $this->portOffset;
  }

  protected function region()
  {
    return $this->input->getOption('region') ?: 'eu';
  }

  protected function client()
  {
    if (!$this->userClient) {
      try {
        Dotenv::createMutable($_SERVER['HOME'], $this->credentialsFile)->safeLoad();

        $username = env('NETFLEX_USERNAME');
        $password = env('NETFLEX_PASSWORD');

        if (!$username || !$password) {
          return $this->promptCredentials(function () {
            return $this->client();
          });
        }

        $this->userClient = new Client([
          'auth' => [
            $username,
            $password
          ]
        ]);
      } catch (Exception $e) {
        return $this->promptCredentials(function () {
          return $this->client();
        });
      }
    }

    return $this->userClient;
  }

  protected function userId()
  {
    if (!$this->userId) {
      try {
        $response = $this->client()->get('user/auth');
        $this->userId = (int) $response->user->id;
      } catch (Exception $e) {
        $this->progressBar->clear();
        $this->error('Invalid credentials');
        $this->deleteCredentials();
        $this->userId = null;
        $this->userClient = null;
        $this->progressBar->display();

        return $this->userId();
      }
    }

    return $this->userId;
  }

  protected function siteAlias()
  {
    return basename(app()->basePath());
  }

  protected function variable()
  {
    if (!$this->variable) {
      $this->progressBar->advance(1);
      Artisan::call('cache:clear');
      $this->variable = Variable::retrieve('netflex_editor_proxy');
    }

    return $this->variable;
  }

  protected function proxyConfiguration()
  {
    if (!$this->variable()) {
      $this->configuration = (object) [
        'default' => 'https://' . $this->siteAlias() . '.netflex.dev',
        'authorization' => 'jwt',
        'path' => '.well-known/netflex',
        'proxies' => Collection::make([])
      ];
    } else {
      $this->configuration = $this->variable()->value;
      $this->configuration->proxies = Collection::make($this->configuration->proxies);
    }

    return $this->configuration;
  }

  protected function addProxy($uri)
  {
    $configuration = $this->proxyConfiguration();

    $configuration->proxies = $configuration->proxies->filter(function ($proxy) {
      return $proxy->id !== $this->userId();
    });

    $configuration->proxies->push((object) [
      'id' => $this->userId(),
      'uri' => $uri
    ]);

    $this->saveProxyConfiguration($configuration);
  }

  protected function removeProxy()
  {
    $this->variable = null;
    $configuration = $this->proxyConfiguration();

    $configuration->proxies = $configuration->proxies->filter(function ($proxy) {
      return $proxy->id !== $this->userId();
    });

    $this->saveProxyConfiguration($configuration);
  }

  protected function saveProxyConfiguration($configuration)
  {
    $this->configuration = $configuration;
    $this->configuration->proxies = $this->configuration->proxies->values();

    $payload = (object) [
      'name' => 'Editor Proxy',
      'alias' => 'netflex_editor_proxy',
      'format' => 'json',
      'value' => json_encode($this->configuration, JSON_PRETTY_PRINT)
    ];

    if ($this->variable) {
      API::put("foundation/variables/{$this->variable()->id}", $payload);
    } else {
      API::post("foundation/variables", $payload);
    }

    $this->progressBar->advance(1);
  }

  protected function deleteCredentials()
  {
    $path = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . $this->credentialsFile;

    if (file_exists($path)) {
      unlink($path);
    }
  }

  protected function promptCredentials(Closure $callback)
  {
    $path = $_SERVER['HOME'] . DIRECTORY_SEPARATOR . $this->credentialsFile;

    $this->deleteCredentials();

    $this->progressBar->clear();
    $this->error('Please setup your Netflex credentials');
    $username = $this->ask('Username');
    $password = $this->secret('Password');
    $this->progressBar->display();

    $config = "NETFLEX_USERNAME=\"$username\"\nNETFLEX_PASSWORD=\"{$password}\"\n";
    file_put_contents($path, $config);

    return $callback();
  }

  /**
   * Execute the console command.
   *
   * @return int
   *
   * @throws \Exception
   */
  public function handle()
  {
    chdir(public_path());

    if ($this->input->getOption('local')) {
      passthru(implode(' ', $this->serverCommand()), $status);

      if ($status && $this->canTryAnotherPort()) {
        $this->portOffset += 1;
        return $this->handle();
      }

      return $status;
    }

    $this->progressBar = $this->output->createProgressBar(6);

    if ('\\' !== \DIRECTORY_SEPARATOR || 'Hyper' === getenv('TERM_PROGRAM')) {
      $this->progressBar->setEmptyBarCharacter(' ');
      $this->progressBar->setProgressCharacter('');
      $this->progressBar->setBarCharacter('<fg=green>█</>');
    }

    $this->progressBar->setRedrawFrequency(1);
    $this->progressBar->minSecondsBetweenRedraws(0.0);
    $this->progressBar->start();

    declare(ticks=1); // Handle async signals PHP 7.1
    pcntl_async_signals(true); // Handle async signals PHP ^7.1
    pcntl_signal(SIGINT, [$this, 'shutdown']); // Call $this->shutdown() on SIGINT
    pcntl_signal(SIGTERM, [$this, 'shutdown']); // Call $this->shutdown() on SIGTERM

    $this->processBuilder = new NgrokProcessBuilder(app()->basePath());
    $this->progressBar->advance(1);
    $this->webService = new NgrokWebService(new \GuzzleHttp\Client());
    $this->progressBar->advance(1);

    return $this->runProcess(
      $this->processBuilder->buildProcess(
        $this->host(),
        $this->port(),
        $this->region()
      )
    );
  }

  private function shutdown()
  {
    $this->removeProxy();
    $this->progressBar->finish();
    $this->progressBar->clear();
    $this->line('<info>👋 Server stopped, bye!</info>');
  }

  private function runProcess(Process $process): int
  {
    $webService = $this->webService;

    $webServiceStarted = false;
    $tunnelStarted = false;

    $process->run(function ($type, $data) use (&$process, &$webService, &$webServiceStarted, &$tunnelStarted) {
      if (!$webServiceStarted) {
        if (preg_match('/msg="starting web service".*? addr=(?<addr>\S+)/', $process->getOutput(), $matches)) {
          $webServiceStarted = true;
          $webServiceUrl = 'http://' . $matches['addr'];
          $webService->setUrl($webServiceUrl);
          $this->progressBar->advance(1);
        }
      }

      if ($webServiceStarted && !$tunnelStarted) {
        $tunnels = $webService->getTunnels();

        if (!empty($tunnels)) {
          $tunnelStarted = true;

          $tunnel = Collection::make($tunnels)->first(function ($tunnel) {
            return $tunnel['proto'] === 'https';
          });

          $this->progressBar->advance(1);

          $this->addProxy($tunnel['public_url']);

          $this->progressBar->finish();
          $this->progressBar->clear();

          $this->line("<info>⚡ Ready: </info><fg=cyan;options=underscore>http://{$this->host()}:{$this->port()}</>");
          $this->line('<info>🌐 Proxy: </info><fg=cyan;options=underscore>' . $tunnel['public_url'] . '</> -> <fg=cyan;options=underscore>http://' . $this->host() . ':' . $this->port() . '</>');
          $this->line('');

          $command = implode(' ', array_merge(['NGROK_PROXY=true'], $this->serverCommand(), ['-d variables_order=EGPCS ']));
          passthru($command, $status);

          if ($status && $this->canTryAnotherPort()) {
            $this->portOffset += 1;
            return $this->handle();
          }

          return $status;
        }
      }

      if (Process::OUT === $type) {
        $process->clearOutput();
      } else {
        $this->error($data);
        $process->clearErrorOutput();
      }
    });

    $this->error($process->getErrorOutput());

    return $process->getExitCode();
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    $options = parent::getOptions();
    $options[] = ['local', 'l', InputOption::VALUE_OPTIONAL, 'Only serve locally, skips ngrok', env("NETFLEX_SKIP_NGROK", false)];
    $options[] = ['region', 'r', InputOption::VALUE_OPTIONAL, 'Specify ngrok region, defaults to eu', 'eu'];

    return $options;
  }
}
