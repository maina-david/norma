<?php

namespace App\Managers;

use App\Enums\Application\ApplicationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use InvalidArgumentException;

class AppManager
{
    /**
     * These are the colours that can be used in the application in items that accept a colour type
     * e.g. the button.
     */
    public const COLOURS = ['primary', 'secondary', 'tertiary', 'accent', 'positive', 'negative', 'info', 'warning', 'dark', 'gray'];

    /** The key used to store the current app name in the session. */
    protected const SESSION_KEY = 'norma.app';

    public function __construct(protected Request $request)
    {
    }

    /**
     * Get the default app url.
     *
     * @return string
     */
    public static function appDomain(): string
    {
        return self::extractDomain(config('app.url'));
    }

    /**
     * Get the current domain for the given request.
     *
     * @return string
     */
    public function requestDomain(): string
    {
        return self::extractDomain($this->request->getUri());
    }

    /**
     * Extract the domain from the given url.
     *
     * @param string $url
     *
     * @return string
     */
    protected static function extractDomain(string $url): string
    {
        /** @var string */
        $parsedUrl = parse_url($url, PHP_URL_HOST);
        $domain = explode('.', $parsedUrl);

        if (count($domain) < 3) {
            return implode('.', $domain);
        }

        return "{$domain[1]}.{$domain[2]}";
    }

    /**
     * Get the current subdomain for the given request.
     *
     * @return string
     */
    public function requestSubdomain(): string
    {
        /** @var string|false|null $parsedUrl */
        $parsedUrl = parse_url($this->request->getUri(), PHP_URL_HOST);
        if ($parsedUrl === false || is_null($parsedUrl)) {
            // @codeCoverageIgnoreStart
            return '';
            // @codeCoverageIgnoreEnd
        }
        $domain = explode('.', $parsedUrl);

        // @codeCoverageIgnoreStart
        if (count($domain) < 3) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        if (config('app.env') === 'staging') {
            // @codeCoverageIgnoreStart
            return preg_replace('/\d+/', '', $domain[0]) ?? '';
            // @codeCoverageIgnoreEnd
        }

        return $domain[0];
    }

    /**
     * Generate a valid white label url using the default app domain.
     *
     * @param string $whiteLabel
     *
     * @return string
     */
    public static function appWhiteLabelURL(string $whiteLabel): string
    {
        return "{$whiteLabel}." . self::appDomain();
    }

    /**
     * Set the current app in the session.
     *
     * @param string $name
     *
     * @return void
     */
    public function setApp(string $name): void
    {
        if (!in_array($name, ApplicationType::options())) {
            throw new InvalidArgumentException('The provided application is invalid.');
        }

        Session::put(self::SESSION_KEY, $name);

        if (ApplicationType::collaborate()->is($name)) {
            Config::set('app.name', 'Norma Collaborate');
        }

        if (ApplicationType::my()->is($name)) {
            Inertia::setRootView('inertia.my.layout');
        }
    }

    /**
     * Get the alias of the currently running app either
     * collaborate, admin or my.
     *
     * @return string
     */
    public static function getApp(): string
    {
        return Session::get(self::SESSION_KEY, 'my');
    }

    /**
     * Validate the theme and return it if valid, else throw exception.
     *
     * @param string $theme
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public static function validateThemeAttribute(string $theme): string
    {
        if (!in_array($theme, self::COLOURS)) {
            throw new InvalidArgumentException("The {$theme} theme is not valid.");
        }

        return $theme;
    }
}
