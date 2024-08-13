<?php

namespace App\Services\Search\Elastic;

class DocumentSearchIndices
{
    public const REFERENCES_INDEX = 'references';
    public const WORK_TYPE = 'work';
    public const INDEX_DELIMITER = '_';
    public const DEFAULT_KEY = 'default';

    /** @var array<string, mixed> referencesIndexSettings */
    protected $referencesIndexSettings = [
        'settings' => [
            'analysis' => [
                'analyzer' => [
                    'rebuilt_default_analyzer' => [
                        'type' => 'custom',
                        'char_filter' => [
                            'html_strip',
                        ],
                        'tokenizer' => 'ngram',
                        'filter' => [
                            'lowercase',
                        ],
                    ],
                ],
                'filter' => [
                    // 'custom_language_stemmer' => [
                    //    'type' => 'stemmer',
                    //    'name' => 'english',
                    // ],
                ],
            ],
        ],
        'mappings' => [
            'properties' => [
                'title_default' => [
                    'type' => 'text',
                    'analyzer' => 'rebuilt_default_analyzer',
                ],
                'content_default' => [
                    'type' => 'text',
                    'analyzer' => 'rebuilt_default_analyzer',
                ],
                'tags' => [
                    'type' => 'text',
                    'analyzer' => 'rebuilt_eng_analyzer',
                ],
                'status' => [
                    'type' => 'integer',
                    'index' => false,
                ],
                'work_id' => [
                    'type' => 'integer',
                    'index' => false,
                ],
                'language_code' => [
                    'type' => 'keyword',
                    'index' => false,
                ],
            ],
        ],
    ];

    /** @var array<string, string> */
    protected $languageMap = [
        'ara' => 'arabic',
        'hye' => 'armenian',
        'eus' => 'basque',
        'ben' => 'bengali',
        'bul' => 'bulgarian',
        'cat' => 'catalan',
        'ces' => 'czech',
        'dan' => 'danish',
        'nld' => 'dutch',
        'eng' => 'english',
        'est' => 'estonian',
        'fin' => 'finnish',
        'fra' => 'french',
        'glg' => 'galician',
        'deu' => 'german',
        'ell' => 'greek',
        'hin' => 'hindi',
        'hun' => 'hungarian',
        'ind' => 'indonesian',
        'gle' => 'irish',
        'ita' => 'italian',
        'lav' => 'latvian',
        'lit' => 'lithuanian',
        'nor' => 'norwegian',
        // 'fas' => 'persian',
        'por' => 'portuguese',
        'ron' => 'romanian',
        'rus' => 'russian',
        'spa' => 'spanish',
        'swe' => 'swedish',
        'tur' => 'turkish',
        // 'tha' => 'thai',
    ];

    /**
     * @param Client $client
     **/
    public function __construct(protected Client $client)
    {
    }

    /**
     * Create references index.
     *
     * @return array<mixed>
     **/
    public function createReferencesIndex(): array
    {
        $result = $this->client->indices()->create([
            'index' => static::REFERENCES_INDEX,
            'body' => $this->getReferenceIndexSettings(),
        ]);

        return $result;
    }

    /**
     * @return array<mixed>
     **/
    public function getReferenceIndexSettings(): array
    {
        $arr = $this->referencesIndexSettings;
        // $arr['settings']['analysis']['filter']['synonym']['synonyms_path'] = config('elasticsearch.synonyms_path');

        foreach ($this->languageMap as $langKey => $language) {
            $stemmerName = $language;
            if (in_array($language, ['french', 'german', 'italian', 'portuguese', 'spanish'])) {
                $stemmerName = 'light_' . $language;
            }

            if ($stemmerName) {
                $langStemmerKey = 'custom_' . $langKey . '_stemmer';
                $arr['settings']['analysis']['filter'][$langStemmerKey] = [
                    'language' => $stemmerName,
                    'type' => 'stemmer',
                ];
                $langStopKey = 'custom_' . $langKey . '_stop';
                $arr['settings']['analysis']['filter'][$langStopKey] = [
                    'language' => '_' . $language . '_',
                    'type' => 'stop',
                ];

                $langAnalyzerKey = 'rebuilt_' . $langKey . '_analyzer';
                $arr['settings']['analysis']['analyzer'][$langAnalyzerKey] = [
                    'type' => 'custom',
                    'char_filter' => [
                        'html_strip',
                    ],
                    'tokenizer' => 'standard',
                    'filter' => [
                        'lowercase',
                        $langStopKey,
                        $langStemmerKey,
                    ],
                ];

                $arr['mappings']['properties']['title_' . $langKey] = [
                    'type' => 'text',
                    'analyzer' => $langAnalyzerKey,
                ];
                $arr['mappings']['properties']['content_' . $langKey] = [
                    'type' => 'text',
                    'analyzer' => $langAnalyzerKey,
                ];
                $arr['mappings']['properties']['work_title_' . $langKey] = [
                    'type' => 'text',
                    'analyzer' => $langAnalyzerKey,
                ];
            }
        }

        return $arr;
    }

    /**
     * @param string $isoLanguageCode
     *
     * @return string|null
     */
    public function chooseLanguageFromIsoCode(string $isoLanguageCode): ?string
    {
        return $this->languageMap[$isoLanguageCode] ?? null;
    }

    /**
     * @return string
     */
    public function getReferencesCurrentIndexName(): string
    {
        return static::REFERENCES_INDEX;
    }

    /**
     * @return string
     **/
    public function getCurrentIndexNameOrCreate(): string
    {
        return $this->getReferencesCurrentIndexName();
    }

    /**
     * @return array<mixed>
     */
    public function getIndicesMapping(): array
    {
        return $this->client->indices()->getMapping();
    }

    /**
     * @return array<string>
     */
    public function getIndices(): array
    {
        return array_keys($this->getIndicesMapping());
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param string $index
     *
     * @return array<mixed>
     */
    public function deleteIndex(string $index): array
    {
        return $this->client->indices()->delete([
            'index' => $index,
        ]);
    }

    /**
     * @return array<mixed>
     */
    public function deleteReferencesIndex(): array
    {
        return $this->client->indices()->delete([
            'index' => static::REFERENCES_INDEX,
        ]);
    }

    /**
     * @param string $languageCode
     *
     * @return string
     */
    public function getTitleField(string $languageCode): string
    {
        $lang = isset($this->languageMap[$languageCode]) ? $languageCode : static::DEFAULT_KEY;

        return 'title_' . $lang;
    }

    /**
     * @param string $languageCode
     *
     * @return string
     */
    public function getWorkTitleField(string $languageCode): string
    {
        $lang = isset($this->languageMap[$languageCode]) ? $languageCode : static::DEFAULT_KEY;

        return 'work_title_' . $lang;
    }

    /**
     * @param string $languageCode
     *
     * @return string
     */
    public function getContentField(string $languageCode): string
    {
        $lang = isset($this->languageMap[$languageCode]) ? $languageCode : static::DEFAULT_KEY;

        return 'content_' . $lang;
    }

    /**
     * @return array<string>
     */
    public function getAllContentFields(): array
    {
        $arr = ['content_' . static::DEFAULT_KEY];
        foreach ($this->languageMap as $langKey => $language) {
            $arr[] = 'content_' . $langKey;
        }

        return $arr;
    }

    /**
     * @return array<string>
     */
    public function getAllTitleFields(): array
    {
        $arr = ['title_' . static::DEFAULT_KEY];
        foreach ($this->languageMap as $langKey => $language) {
            $arr[] = 'title_' . $langKey;
        }

        return $arr;
    }

    /**
     * @return array<string>
     */
    public function getAllWorkTitleFields(): array
    {
        $arr = ['work_title_' . static::DEFAULT_KEY];
        foreach ($this->languageMap as $langKey => $language) {
            $arr[] = 'work_title_' . $langKey;
        }

        return $arr;
    }
}
