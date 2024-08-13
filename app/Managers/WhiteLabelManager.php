<?php

namespace App\Managers;

use App\Contracts\WhiteLabels\WhiteLabel as WhiteLabelContract;
use App\Models\Partners\WhiteLabel as WhiteLabelModel;
use App\WhiteLabels\WhiteLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Throwable;

class WhiteLabelManager
{
    /**
     * Cache of the enabled white labels.
     *
     * @var WhiteLabelContract[]
     */
    protected static array $cache = [];

    /**
     * @param Request      $request
     * @param AppManager   $appManager
     * @param ThemeManager $themeManager
     */
    public function __construct(
        protected Request $request,
        protected AppManager $appManager,
        protected ThemeManager $themeManager
    ) {
    }

    /**
     * Register the provided white labels for use in the app. Each white label should
     * extend this class.
     *
     * @return void
     */
    public function register(): void
    {
        try {
            $cached = WhiteLabelModel::cached();
            $cached->each(function (WhiteLabelContract $label) {
                // @codeCoverageIgnoreStart
                foreach ($label->urls() as $url) {
                    self::$cache[$url] = $label;
                }

                app(ThemeManager::class)->register([$label]);
                // @codeCoverageIgnoreEnd
            });
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Get the current white label.
     *
     * @return WhiteLabelContract
     */
    public function current(): WhiteLabelContract
    {
        return self::$cache[$this->request->getHost()] ?? new WhiteLabel();
    }

    /**
     * Activate the white label and theme if present.
     *
     * @return void
     */
    public function activate(): void
    {
        $label = $this->current();

        session()->put('whiteLabel', $label);

        Config::set('app.name', $label->appName());

        $this->themeManager->activate($this->appManager->requestSubdomain());
    }

    /**
     * Check if the current URL matches a valid white label.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (AppManager::appDomain() === $this->appManager->requestDomain()) {
            return true;
        }

        return $this->current()->shortname() !== 'default';
    }
}
