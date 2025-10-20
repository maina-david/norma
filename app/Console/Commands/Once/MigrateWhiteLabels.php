<?php

namespace App\Console\Commands\Once;

use App\Models\Partners\WhiteLabel;
use Illuminate\Console\Command;

/** @codeCoverageIgnore */
class MigrateWhiteLabels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:migrate-white-labels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the extra data into white labels table.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        WhiteLabel::all()->each(function (WhiteLabel $label) {
            $label->auth_provider = $label->auth_provider ?? 'norma';
            $label->login_logo = "/img/whitelabels/elc-{$label->shortname}.png";
            $label->app_logo = "/img/whitelabels/small-{$label->shortname}.png";
            $label->theme = $this->getTheme($label->shortname);
            $label->save();
        });

        $this->info('Updated white label info.');

        return 0;
    }

    /**
     * Get the theme for the given shortname.
     *
     * @param string $shortname
     *
     * @return array|string[]
     */
    protected function getTheme(string $shortname): array
    {
        // TODO: Add other themes
        $themes = [
            'isometrix' => [
                'primary' => '#006395',
                'tertiary' => '#343A40',
                'negative' => '#d83b12',
                'secondary' => '#989798',
                'warning' => '#FFB800',
                'positive' => '#79BC43',
                'navbar' => '#ffffff',
                'navbar-active' => '#006395',
                'navbar-text' => '#343A40',
            ],
            'sabinet' => [
                'primary' => '#CC2028',
                'tertiary' => '#424143',
                'negative' => '#FB777E',
                'secondary' => '#707071',
                'warning' => '#D0AC21',
                'positive' => '#48BA3C',
                'navbar' => '#ffffff',
                'navbar-active' => '#CC2028',
                'navbar-text' => '#424143',
            ],
            'gemserv' => [
                'primary' => '#6EB967',
                'tertiary' => '#424143',
                'negative' => '#FB777E',
                'secondary' => '#707071',
                'warning' => '#D0AC21',
                'positive' => '#48BA3C',
                'navbar' => '#424143',
                'navbar-active' => '#CC2028',
                'navbar-text' => '#424143',
            ],
        ];

        return $themes[$shortname] ?? [];
    }
}
