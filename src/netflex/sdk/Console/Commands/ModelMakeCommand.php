<?php

namespace Netflex\Console\Commands;

use Netflex\API\Facades\API;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends GeneratorCommand
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'make:model';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create a new Structure model class';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'Model';

  /**
   * Get the root namespace for the class.
   *
   * @return string
   */
  protected function rootNamespace()
  {
    return $this->laravel->getNamespace() . 'Models\\';
  }

  /**
   * Get the destination class path.
   *
   * @param  string  $name
   * @return string
   */
  protected function getPath($name)
  {
    $name = Str::replaceFirst($this->rootNamespace(), '', $name);

    return $this->laravel['path'] . '/Models/' . str_replace('\\', '/', $name) . '.php';
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function handle()
  {
    if (!$this->option('directory')) {
      $menu = $this->menu('Which relationId should the model use?')
        ->setForegroundColour('white')
        ->setBackgroundColour('red')
        ->setExitButtonText('Skip');

      $structures = API::get('builder/structures');

      Collection::make($structures)
        ->each(function ($structure) use ($menu) {
          $menu->addOption($structure->id, "{$structure->name} ({$structure->id})");
        });

      $menu->addStaticItem('');

      if ($relationId = $menu->open()) {
        $this->input->setOption('directory', $relationId);
      }
    }

    if (parent::handle() === false && !$this->option('force')) {
      return false;
    }

    if ($this->option('all')) {
      $this->input->setOption('controller', true);
      $this->input->setOption('resource', true);
    }

    if ($this->option('controller') || $this->option('resource')) {
      $this->createController();
    }
  }

  /**
   * Create a controller for the model.
   *
   * @return void
   */
  protected function createController()
  {
    $controller = Str::studly(class_basename($this->argument('name')));

    $modelName = $this->qualifyClass($this->getNameInput());

    $this->call('make:controller', [
      'name' => "{$controller}Controller",
      '--model' => $this->option('resource') ? $modelName : null,
      '--api'   => $this->option('api'),
    ]);
  }

  /**
   * Get the stub file for the generator.
   *
   * @return string
   */
  protected function getStub()
  {
    return __DIR__ . '/stubs/model.stub';
  }

  protected function replaceRelationId(&$stub, $relationId)
  {
    $stub = str_replace(
      ['DummyRelationId'],
      [$relationId ?? 'null'],
      $stub
    );

    return $this;
  }

  /**
   * Build the class with the given name.
   *
   * @param  string  $name
   * @return string
   *
   * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
   */
  protected function buildClass($name)
  {
    $stub = $this->files->get($this->getStub());

    return $this->replaceNamespace($stub, $name)
      ->replaceRelationId($stub, $this->option('directory'))
      ->replaceClass($stub, $name);
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return [
      ['all', 'a', InputOption::VALUE_NONE, 'Generate a resource controller for the model'],
      ['directory', 'd', InputOption::VALUE_OPTIONAL, 'Specifies the directory id for the model'],
      ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model'],
      ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
      ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated controller should be a resource controller'],
      ['api', null, InputOption::VALUE_NONE, 'Indicates if the generated controller should be an api controller']
    ];
  }
}
