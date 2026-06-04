<?php

declare(strict_types=1);

namespace Kristapsv\VueDirectiveDocs\Providers;

use Illuminate\Support\ServiceProvider;
use Kristapsv\VueDirectiveDocs\Commands\GenerateVueDirectiveDocs;

class VueDirectiveDocsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateVueDirectiveDocs::class,
            ]);
        }

        // Publish config file - go up one more level to reach src/
        $this->publishes([
            __DIR__ . '/../Config/directives.php' => config_path('directives.php'),
        ], 'vue-directives-docs-config');

        // Publish stub file - go up to package root, then into stubs/
        $this->publishes([
            __DIR__ . '/../../stubs/vue-directives.stub' => base_path('stubs/vue-directives.stub'),
        ], 'vue-directives-docs-stubs');
    }

    public function register(): void
    {
        // Merge config - go up one more level to reach src/
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/directives.php',
            'directives'
        );
    }
}