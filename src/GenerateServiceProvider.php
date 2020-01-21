<?php

namespace Kbdxbt\Generate;

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
        //merge config
        if ($this->isLumen()) {
            $this->app->configure($this->namespaceName);
        }
        $configFile = $this->packagePath."config/{$this->namespaceName}.php";
        if (file_exists($configFile)) {
            $this->mergeConfigFrom($configFile, $this->namespaceName);
        }
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
