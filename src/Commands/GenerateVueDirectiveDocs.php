<?php

declare(strict_types=1);

namespace Kristapsv\VueDirectiveDocs\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Symfony\Component\Finder\SplFileInfo;

class GenerateVueDirectiveDocs extends Command
{
    protected $signature = 'make:vue-directive-docs';

    protected $description = 'Generate IDE documentation for custom Vue directives';

    public function handle(): int
    {
        $sourcePath = config('directives.source');
        $outputPath = base_path('vue-directives-docs.json');
        $excludes = config('directives.excludes');

        if (!File::exists($sourcePath)) {
            $this->error("Directives source directory not found: {$sourcePath}");
            return CommandAlias::FAILURE;
        }

        $directives = collect(File::files($sourcePath))
            ->filter(fn (SplFileInfo $file): bool => !in_array($file->getFilename(), $excludes))
            ->map(function (SplFileInfo $file): array {
                $content = File::get($file->getPathname());
                $filename = $file->getFilenameWithoutExtension();

                // Parse docblock for @directive and @description
                $directiveName = $this->parseDirectiveName($content, $filename);
                $description = $this->parseDescription($content);

                return [
                    "name" => $directiveName,
                    "description" => $description,
                    "source" => [
                        "module" => "./resources/js/src/Directives/{$filename}.ts",
                        "symbol" => "default"
                    ]
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        if (empty($directives)) {
            $this->warn("No valid directive files found in: {$sourcePath}");
            return CommandAlias::SUCCESS;
        }

        $stub = File::get(base_path('stubs/vue-directives.stub'));
        $directivesJson = json_encode($directives, JSON_PRETTY_PRINT);
        $content = str_replace('{{directives}}', $directivesJson, $stub);

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);

        $this->info("✓ Processed " . count($directives) . " directive(s)");
        $this->info("✓ Generated: {$outputPath}");

        return CommandAlias::SUCCESS;
    }

    /**
     * Parse @directive tag from file content
     */
    protected function parseDirectiveName(string $content, string $fallbackFilename): string
    {
        // Look for @directive directive-name in docblock
        if (preg_match('/@directive\s+([a-z0-9-]+)/', $content, $matches)) {
            return $matches[1];
        }

        // Fallback: Convert filename (MiddleEllipsisDirective -> middle-ellipsis)
        $name = str_replace('Directive', '', $fallbackFilename);
        return Str::kebab($name);
    }

    /**
     * Parse @description tag from file content
     */
    protected function parseDescription(string $content): string
    {
        // Capture everything after @description until the docblock ends (*/)
        if (preg_match('/@description\s+(.*?)(?=\s*\*\/)/s', $content, $matches)) {
            $description = $matches[1];

            // Clean up: Remove leading * and exactly one space from each line
            // This preserves indentation for code blocks
            $description = preg_replace('/^\s*\*\s?/m', '', $description);

            // Remove the first empty line if present
            $description = preg_replace('/^\n/', '', $description);

            // Preserve all other line breaks and spaces
            return $description;
        }

        return '';
    }
}