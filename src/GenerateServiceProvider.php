<?php

namespace Kbdxbt\Generate;

use Kbdxbt\Generate\Console\Commands\Controller;
use Kbdxbt\Generate\Console\Commands\Magic;
use Kbdxbt\Generate\Console\Commands\Model;
use Kbdxbt\Generate\Console\Commands\Repository;
use Kbdxbt\Generate\Console\Commands\Request;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;

class GenerateServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $namespaceName = 'repository';

    /**
     * @var string
     */
    protected $packagePath = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;

    /**
     * @return void
     */
    public function boot()
    {
        //move config path
        if (function_exists('config_path')) {
            $this->publishes([
                $this->packagePath.'config' => config_path(),
            ]);
        }
    }

    /**
     * @return void
     */
    public function register()
    {
        //bind commands
        $this->app->singleton('command.repository.generate', Repository::class);
        $this->app->singleton('command.magic.generate', Magic::class);
        $this->app->singleton('command.request.generate', Request::class);
        $this->app->singleton('command.model.generate', Model::class);
        $this->app->singleton('command.controller.generate', Controller::class);

        // Register commands
        $this->commands([
            'command.repository.generate',
            'command.magic.generate',
            'command.request.generate',
            'command.model.generate',
            'command.controller.generate'
        ]);
    }

    /**
     * isLumen.
     *
     * @return bool
     */
    protected function isLumen(): bool
    {
        return $this->app instanceof \Laravel\Lumen\Application;
    }
}
