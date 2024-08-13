<?php

namespace App\Logging\MicrosoftTeams;

use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class MicrosoftTeamsRecord
{
    /**
     * @param string|null                                $title
     * @param \Monolog\Formatter\FormatterInterface|null $formatter
     */
    public function __construct(
        protected ?string $title = null,
        protected ?FormatterInterface $formatter = null
    ) {
    }

    /**
     * Generate the body for MS Teams webhook.
     *
     * @return array<string, mixed>
     */
    public function getData(LogRecord $record): array
    {
        $dataArray = [
            '@type' => 'MessageCard',
            '@context' => 'http://schema.org/extensions',
            'themeColor' => $this->getThemeColor($record->level->value),
            'text' => mb_convert_encoding($record->message, 'UTF-8'),
            'sections' => [],
        ];

        if ($this->title !== null) {
            $dataArray['title'] = mb_convert_encoding($this->title, 'UTF-8');
        }

        foreach (['extra', 'context'] as $key) {
            if (!empty($record->{$key})) {
                $dataArray['sections'][] = $this->generateSection($record->{$key}, $record->level->getName());
            }
        }

        return $dataArray;
    }

    /**
     * @param array<string, mixed> $elements
     * @param string               $levelName
     *
     * @return array<string, mixed>
     */
    protected function generateSection(array $elements, string $levelName): array
    {
        $facts = [
            $this->toFact('Level', $levelName),
        ];

        foreach ($elements as $key => $value) {
            if ($value instanceof Throwable) {
                array_push($facts,
                    //                    $this->toFact('Code', $value->getCode()),
                    $this->toFact('File', $value->getFile()),
                    $this->toFact('Line', $value->getLine()),
                    $this->toFact('Trace', sprintf('<pre>%s</pre>', $value->getTraceAsString()))
                );
            } else {
                $facts[] = $this->toFact($key, $value);
            }
        }

        return [
            'facts' => $facts,
        ];
    }

    /**
     * Create a name and value pair.
     *
     * @param string          $name
     * @param string|int|bool $value
     *
     * @return array<string, mixed>
     */
    protected function toFact(string $name, string|int|bool $value): array
    {
        $value = is_string($value) ? mb_convert_encoding($value, 'UTF-8') : $value;

        return ['name' => $name, 'value' => $value];
    }

    /**
     * Returns Microsoft Teams Card message theme color based on log level.
     *
     * @param int $level
     *
     * @return string
     */
    public function getThemeColor(int $level): string
    {
        return match (true) {
            $level >= Level::Error->value => '#A93226',
            $level >= Level::Warning->value => '#D68910',
            $level >= Level::Info->value => '#2471A3',
            default => '#A6ACAF',
        };
    }
}
