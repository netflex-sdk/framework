<?php

namespace Netflex\Console\Commands;

use Exception;

use Dotenv\Dotenv;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

use Netflex\API\Client;
use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;

use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Process;

class ProxyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy';

    /**
     * Netflex User ID
     *
     * @var int
     */
    protected $userId;

    /**
     * Netflex Proxy Configuration
     *
     * @var object|null
     */
    protected $configuration = null;

    /**
     * Netflex Proxy Configuration Variable
     *
     * @var \Netflex\Foundation\Variable
     */
    protected $variable;

    /**
     * The file holding the Netflex credentials
     *
     * @var string
     */
    protected $credentialsFile = '.netflexrc';

    /**
     * Netflex Client authenticated as User
     *
     * @var \Netflex\API\Client
     */
    protected $userClient;

    protected ?string $home;
    protected ?string $basepath;
    protected ?string $herd;
    protected ?string $php;
    protected ?string $tmpdir;
    protected ?string $path;

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function isHidden()
    {
        return PHP_OS_FAMILY !== 'Darwin';
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
                throw new Exception('Could not authenticate user. Check your Netflex API credentials');
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
                throw new Exception('Could not authenticate user. Check your Netflex API credentials');
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
            $this->configuration->proxies = Collection::make($this->configuration->proxies ?? []);
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
    }

    public function shutdown()
    {
        if (file_exists("{$this->path}/php")) {
            unlink("{$this->path}/php");
        }

        if (file_exists("{$this->tmpdir}/herd")) {
            unlink("{$this->tmpdir}/herd");
        }

        $this->removeProxy();
    }

    public function handle()
    {
        try {
            $this->home = $_SERVER['HOME'];
            $this->basepath = realpath("{$this->home}/Library/Application Support/Herd/bin");

            $this->herd = "{$this->basepath}/herd";
            $this->php = "{$this->basepath}/php82";
            $this->tmpdir = sys_get_temp_dir();
            $this->path = "{$this->tmpdir}/herd/bin";

            if (PHP_OS_FAMILY !== 'Darwin') {
                $this->error(PHP_OS_FAMILY . ' is not currently supported');
                return 1;
            }

            if (!file_exists($this->herd)) {
                $this->info($this->herd);
                $this->error('Herd is not installed');
                $this->info('Visit https://herd.laravel.com/ to install');
                return 2;
            }

            if (!file_exists($this->php)) {
                $this->error('PHP 8.2 is not installed in Herd.');
                return 3;
            }

            if (!file_exists($this->path)) {
                mkdir($this->path, 0777, true);
            }

            if (!file_exists("{$this->path}/php")) {
                symlink($this->php, "{$this->path}/php");
            }

            declare(ticks=1); // Handle async signals PHP 7.1
            pcntl_async_signals(true); // Handle async signals PHP ^7.1
            pcntl_signal(SIGINT, [$this, 'shutdown']); // Call $this->shutdown() on SIGINT
            pcntl_signal(SIGTERM, [$this, 'shutdown']); // Call $this->shutdown() on SIGTERM

            $process = new Process([$this->herd, 'share'], base_path(), ['PHP_EXECUTABLE' => $this->php, 'PATH' => "{$this->path}:" . getenv('PATH')]);
            $process->setTimeout(null);

            $publicHttps = null;

            $process->run(function ($type, $buffer) use ($process, &$publicHttps) {

                if ($type === Process::OUT && !$publicHttps) {
                    $matches = [];
                    if (preg_match('/Public HTTPS:\t\t(https:\/\/\w+\.sharedwithexpose.com)/', $buffer, $matches)) {
                        $publicHttps = $matches[1];
                        $this->line('<info>🌐 Proxy: </info><fg=cyan;options=underscore>' . $publicHttps . '</> -> <fg=cyan;options=underscore>http://' . $this->siteAlias() . '.test' . '</>');
                        $this->addProxy($publicHttps);
                    }
                }

                if ($type === Process::ERR) {
                    $this->error($buffer);
                }
            });
        } catch (ProcessSignaledException $e) {
            return $e->getSignal();
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
