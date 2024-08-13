<?php

namespace App\Models\Partners;

use App\Contracts\WhiteLabels\WhiteLabel as WhiteLabelAliasContract;
use App\Managers\AppManager;
use App\Models\AbstractModel;
use App\Themes\Theme;
use App\Traits\CompilesCSSVariables;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @mixin IdeHelperWhiteLabel
 */
class WhiteLabel extends AbstractModel implements WhiteLabelAliasContract
{
    use SoftDeletes;
    use CompilesCSSVariables;

    /** @var string */
    protected $table = 'whitelabels';

    /** @var array<string, string> */
    protected $casts = [
        'theme' => 'array',
        'urls' => 'array',
    ];

    /**
     * Get the css variables that are to be applied to the white label
     * e.g. ['primary' => '#f24f3e'].
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        return $this->theme ?? [];
    }

    /**
     * Get the login page logo.
     *
     * @return string
     */
    public function loginLogo(): string
    {
        return $this->login_logo ?? Theme::libryoLogo();
    }

    /**
     * Get the main app logo.
     *
     * @return string
     */
    public function appLogo(): string
    {
        return $this->app_logo ?? Theme::libryoLogo();
    }

    /**
     * Get the main app favicon.
     *
     * @return string
     */
    public function favicon(): string
    {
        return $this->favicon ?? Theme::libryoFavicon();
    }

    /**
     * Get the CSS background value for the auth pages.
     *
     * @return string
     */
    public function authBackground(): string
    {
        return $this->auth_background ?? '#fff';
    }

    /**
     * Get the name of the application. This will be used to set the title bar
     * and also the config('app.name') environment variable.
     *
     * @return string
     */
    public function appName(): string
    {
        return $this->title;
    }

    /**
     * Get the subdomain of the application.
     *
     * @return string
     */
    public function shortname(): string
    {
        return $this->shortname;
    }

    /**
     * Get the Auth Provider for this white label.
     *
     * @return string
     */
    public function authProvider(): string
    {
        return $this->auth_provider ?? 'libryo';
    }

    /**
     * Get the URLs that are allowed for this white label.
     *
     * @return array<int, string>
     */
    public function urls(): array
    {
        return array_merge(
            $this->urls ?? [],
            [AppManager::appWhiteLabelURL($this->shortname)],
        );
    }

    /**
     * The cache key.
     *
     * @return string
     */
    protected static function cacheKey(): string
    {
        return 'app.white_labels.cache';
    }

    /**
     * Get the cached white labels.
     *
     * @return Collection<self>
     */
    public static function cached(): Collection
    {
        if (Cache::has(self::cacheKey())) {
            return Cache::get(self::cacheKey());
        }

        return static::cache();
    }

    /**
     * Cache the current white labels.
     *
     * @return Collection<self>
     */
    public static function cache(): Collection
    {
        $key = self::cacheKey();

        Cache::forget($key);

        /** @var Collection<self> */
        return Cache::rememberForever($key, fn () => static::where('enabled', true)->get());
    }
}
