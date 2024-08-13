<?php

namespace App\Logging\MicrosoftTeams;

use Illuminate\Support\Facades\Http;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LogLevel;

/**
 * @codeCoverageIgnore
 */
class MicrosoftTeamsLogHandler extends AbstractProcessingHandler
{
    /**
     * @param string                                     $url
     * @param int|string|\Monolog\Level                  $level
     * @param string|null                                $title
     * @param \Monolog\Formatter\FormatterInterface|null $formatter
     * @param bool                                       $bubble
     *
     * @phpstan-param value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::* $level
     */
    public function __construct(
        protected string $url,
        int|string|Level $level = Level::Debug,
        protected ?string $title = null,
        protected ?FormatterInterface $formatter = null,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(LogRecord $record): void
    {
        $message = new MicrosoftTeamsRecord($this->title, $this->formatter);
        Http::asJson()->post($this->url, $message->getData($record));
    }
}
