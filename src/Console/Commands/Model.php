<?php

namespace Kbdxbt\Generate\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Model.
 */
class Model extends GeneratorCommand
{
    /**
     * @var string
     */
    protected $name = 'generate:model';

    /**
     * @var string
     */
    protected $description = 'Create a new model class';

    /**
     * @var string
     */
    protected $type = 'Model';

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/model.stub';
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
