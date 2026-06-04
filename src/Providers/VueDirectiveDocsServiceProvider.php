<?php

declare(strict_types=1);

namespace Kristapsv\VueDirectiveDocs\Providers;

use Illuminate\Support\ServiceProvider;
use Kristapsv\VueDirectiveDocs\Commands\GenerateVueDirectivesDocs;

class VueDirectiveDocsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateVueDirectivesDocs::class,
            ]);
        }

        // Publish config file
        $this->publishes([
            __DIR__ . '/Config/directives.php' => config_path('directives.php'),
        ], 'vue-directives-docs-config');

        // Publish stub file
        $this->publishes([
            __DIR__ . '/../stubs/vue-directives.stub' => base_path('stubs/vue-directives.stub'),
        ], 'vue-directives-docs-stubs');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/Config/directives.php',
            'directives'
        );
    }
}