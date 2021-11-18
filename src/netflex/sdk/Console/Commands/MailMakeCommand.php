<?php

namespace Netflex\Console\Commands;

use Illuminate\Foundation\Console\MailMakeCommand as Command;
use Symfony\Component\Console\Input\InputOption;

class MailMakeCommand extends Command
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return;
        }

        if ($this->option('mjml')) {
            $this->writeMjmlTemplate();
        }
    }

    /**
     * Write the Markdown template for the mailable.
     *
     * @return void
     */
    protected function writeMjmlTemplate()
    {
        $path = $this->viewPath(
            str_replace('.', '/', $this->option('mjml')) . '.mjml'
        );

        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }

        $this->files->put($path, file_get_contents(__DIR__ . '/stubs/mjml.stub'));
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        if ($this->option('mjml')) {
            $class = str_replace('DummyView', $this->option('mjml'), $class);
        }

        return $class;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('mjml')) {
            return __DIR__ . '/stubs/mjml-mail.stub';
        }

        if ($this->option('markdown')) {
            __DIR__ . '/stubs/markdown-mail.stub';
        }

        return __DIR__ . '/stubs/mail.stub';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ...parent::getOptions(),
            ['mjml', 'j', InputOption::VALUE_OPTIONAL, 'Create a new MJML template for the mailable']
        ];
    }
}
