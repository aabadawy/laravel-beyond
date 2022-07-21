<?php

namespace Regnerisch\LaravelBeyond\Resolvers;

use Illuminate\Console\Command;
use Regnerisch\LaravelBeyond\Actions\FetchDirectoryNamesFromPathAction;
use Regnerisch\LaravelBeyond\Contracts\Schema;
use Regnerisch\LaravelBeyond\Schema\AppSchema;
use Regnerisch\LaravelBeyond\Schema\SupportSchema;

class AppNameSchemaResolver extends BaseNameSchemaResolver
{
    public function __construct(
        protected Command $command,
        protected ?string $className = null,
        protected ?string $moduleName = null,
        protected ?string $appName = null,
        protected bool $support = false,
    ) {
        parent::__construct($this->command, $this->className, $this->support);
    }

    public function handle(): Schema
    {
        if ($this->support) {
            $className = $this->askClassName();

            return new SupportSchema('', $className);
        }

        $namespace = $this->askNamespace();
        $className = $this->askClassName();

        return new AppSchema($namespace, $className);
    }

    protected function askNamespace(): string
    {
        $action = new FetchDirectoryNamesFromPathAction();

        $appName = $this->appName;
        if (!$appName) {
            $apps = $action->execute(base_path() . '/src/App');
            do {
                $appName = $this->command->anticipate('Please enter the app name:', $apps);
            } while (!$appName);
        }

        $moduleName = $this->moduleName;
        if (!$moduleName) {
            $modules = $action->execute(base_path() . '/src/App/' . $appName);
            do {
                $moduleName = $this->command->anticipate('Please enter the module name (in App/' . $appName . '):', $modules);
            } while (!$moduleName);
        }

        return $appName . '/' . $moduleName;
    }

    protected function askClassName(): string
    {
        return $this->className ?? $this->command->ask('Please enter the class name:');
    }
}
