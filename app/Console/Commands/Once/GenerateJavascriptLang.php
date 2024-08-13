<?php

namespace App\Console\Commands\Once;

use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class GenerateJavascriptLang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-javascript-lang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the current language files and convert them to json.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Generating language files.');
        @mkdir(resource_path('js/lang'));

        /** @var array<int, string> $content */
        $content = scandir(lang_path());

        $languages = $this->clearHidden($content);
        $imports = [];
        $exports = [];

        foreach ($languages as $lang) {
            /** @var string $content */
            $content = json_encode($this->extractLanguage($lang));

            /** @var string $content */
            $content = preg_replace('/:(\w+)/', '{$1}', $content);
            file_put_contents(resource_path("js/lang/{$lang}.json"), $content);
            $imports[] = "import {$lang} from './{$lang}.json'";
            $exports[] = $lang;
            $this->info("Generated {$lang}.");
        }

        $content = implode("\n", $imports);
        $content .= sprintf("\n\nexport default { %s };", implode(',', $exports));
        file_put_contents(resource_path('js/lang/index.js'), $content);

        return 0;
    }

    /**
     * Remove dot files.
     *
     * @param array<int, string> $files
     *
     * @return array<int, string>
     */
    protected function clearHidden(array $files): array
    {
        return collect($files)->filter(fn ($item) => !str_starts_with($item, '.'))->values()->all();
    }

    /**
     * Extract the language definition for the given lang.
     *
     * @param string $language
     *
     * @return array<string, mixed>
     */
    protected function extractLanguage(string $language): array
    {
        /** @var array<int, string> $content */
        $content = scandir(lang_path($language));

        return collect($this->clearHidden($content))
            ->mapWithKeys(function ($file) {
                $namespace = str_replace('.php', '', $file);

                return [$namespace => __($namespace)];
            })
            ->all();
    }
}
