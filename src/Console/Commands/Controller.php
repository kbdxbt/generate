<?php

namespace Kbdxbt\Generate\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Controller.
 */
class Controller extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'generate:controller';

    /**
     * @var string
     */
    protected $description = 'Create a new controller class';

    /**
     * @var string
     */
    protected $type = 'Controller';

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/controller.stub';
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the repository already exists'],
        ];
    }
}
