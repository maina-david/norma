<?php

namespace App\Logging\MicrosoftTeams;

use Monolog\Logger;

/**
 * @codeCoverageIgnore
 */
class MicrosoftTeamsLogger
{
    /**
     * @param array<string, mixed> $config
     *
     * @return \Monolog\Logger
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('ms-teams');
        $logger->pushHandler(new MicrosoftTeamsLogHandler(
            $config['url'],
            $config['level'],
            $config['title'] ?? null,
            $config['formatter'] ?? null,
            $config['bubble'] ?? true
        ));

        return $logger;
    }
}
