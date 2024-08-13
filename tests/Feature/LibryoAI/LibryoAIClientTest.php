<?php

namespace Tests\Feature\LibryoAI;

use App\Enums\Lookups\ContentMetaSuggestions;
use App\Http\Services\LibryoAI\Client;
use App\Models\Corpus\CatalogueDoc;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LibryoAIClientTest extends TestCase
{
    /**
     * @return void
     */
    public function testGenerationOfAssess(): void
    {
        Config::set('services.libryo_ai.enabled', false);
        Config::set('services.libryo_ai.host', 'http://libryo-ai.libryo.rocks:7000');

        Http::fake([
            'libryo-ai.libryo.rocks:7000/*' => Http::response([
                'query' => "2.Prohibition of certain advertisements visible from public roads\n(1) Subject to the provisions of sub-sections (4) and (5) of this section and of section six, no person shall display an advertisement which is visible from a public road, unless it is displayed in accordance with the written permission of a controlling authority concerned=> Provided that any person may without the permission of a controlling authority (but subject to the provisions of sub-section (2) of this section and of sub-section (1) of section four) -\n(a) display, on a building such an advertisement which discloses merely the name or nature of any business or undertaking carried on therein or the name of the proprietor or manager of that business or undertaking or any information which relates solely to that business or undertaking or to any article or service supplied in connection with that business or undertaking or in connection with any other business or undertaking of that proprietor; or",
                'results' => [
                    [
                        'document' => [
                            'id' => 121,
                            'title' => 'obtain permit / authorisation / licence for advertisement / controlled signage',
                            'description' => 'obtain permit / authorisation / licence for advertisement / controlled signage',
                            'keywords' => null,
                            'category_id' => [
                                100013,
                                113055,
                            ],
                            'risk_rating' => 3,
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 379.5875,
                    ],
                    [
                        'document' => [
                            'id' => 1040,
                            'title' => 'maintain a layout or map of areas where pesticides or herbicides are applied',
                            'description' => 'maintain a layout or map of areas where pesticides or herbicides are applied',
                            'keywords' => null,
                            'category_id' => [
                                100032,
                                102039,
                                108006,
                                108045,
                            ],
                            'risk_rating' => 2,
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 224.28,
                    ],
                    [
                        'document' => [
                            'id' => 1127,
                            'title' => 'obtain a permit for the import or export of ozone depleting substances',
                            'description' => 'obtain a permit for the import or export of ozone depleting substances',
                            'keywords' => null,
                            'category_id' => [
                                100013,
                                101006,
                                111270,
                            ],
                            'risk_rating' => 3,
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 202.35,
                    ],
                    [
                        'document' => [
                            'id' => 1178,
                            'title' => 'ensure licence / registration of a private clinic or medical facility',
                            'description' => 'ensure licence / registration of a private clinic or medical facility',
                            'keywords' => null,
                            'category_id' => [
                                100013,
                                109022,
                            ],
                            'risk_rating' => 3,
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 196.06,
                    ],
                    [
                        'document' => [
                            'id' => 41,
                            'title' => 'obtain a permit / authorisation / licence for cutting or planting of trees in forests',
                            'description' => 'obtain a permit / authorisation / licence for cutting or planting of trees in forests',
                            'keywords' => null,
                            'category_id' => [
                                100013,
                                102024,
                                102026,
                                102047,
                                111026,
                            ],
                            'risk_rating' => 3,
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 191.11,
                    ],
                ],
            ], 200, ['content-type' => 'application/json']),
        ]);

        Config::set('services.libryo_ai.enabled', false);

        $response = (new Client())->suggest(ContentMetaSuggestions::ASSESS, 'hauling of dangerous waste');

        $this->assertEmpty($response->all());

        Config::set('services.libryo_ai.enabled', true);

        $response = (new Client())->suggest(ContentMetaSuggestions::ASSESS, 'hauling of dangerous waste');

        $this->assertSame([121, 1040, 1127, 1178, 41], $response->all());
    }

    /**
     * @return void
     */
    public function testGenerationOfContext(): void
    {
        Config::set('services.libryo_ai.enabled', true);
        Config::set('services.libryo_ai.host', 'http://libryo-ai.libryo.rocks:7000');

        Http::fake([
            'libryo-ai.libryo.rocks:7000/*' => Http::response([
                'query' => "2.Prohibition of certain advertisements visible from public roads\n(1) Subject to the provisions of sub-sections (4) and (5) of this section and of section six, no person shall display an advertisement which is visible from a public road, unless it is displayed in accordance with the written permission of a controlling authority concerned => Provided that any person may without the permission of a controlling authority (but subject to the provisions of sub-section (2) of this section and of sub-section (1) of section four) -\n(a) display, on a building such an advertisement which discloses merely the name or nature of any business or undertaking carried on therein or the name of the proprietor or manager of that business or undertaking or any information which relates solely to that business or undertaking or to any article or service supplied in connection with that business or undertaking or in connection with any other business or undertaking of that proprietor; or",
                'results' => [
                    [
                        'document' => [
                            'id' => 20,
                            'title' => 'Do your operations have advertising signs',
                            'description' => 'Do your operations have advertising signs',
                            'keywords' => null,
                            'category_id' => [
                                113056,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 504.26750000000004,
                    ],
                    [
                        'document' => [
                            'id' => 319,
                            'title' => 'Do your operations involve storing water from a water resource',
                            'description' => 'Do your operations involve storing water from a water resource',
                            'keywords' => null,
                            'category_id' => [
                                118002,
                                118008,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 99.97,
                    ],
                    [
                        'document' => [
                            'id' => 233,
                            'title' => 'Are your operations a telecommunications service provider',
                            'description' => 'Are your operations a telecommunications service provider',
                            'keywords' => null,
                            'category_id' => [
                                111064,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 94.62,
                    ],
                    [
                        'document' => [
                            'id' => 120,
                            'title' => 'Do your operations manage a fire brigade service',
                            'description' => 'Do your operations manage a fire brigade service',
                            'keywords' => null,
                            'category_id' => [
                                111255,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 91.2,
                    ],
                    [
                        'document' => [
                            'id' => 97,
                            'title' => 'Do your operations involve private streets',
                            'description' => 'Do your operations involve private streets',
                            'keywords' => null,
                            'category_id' => [
                                116026,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 46.91,
                    ],
                    [
                        'document' => [
                            'id' => 655,
                            'title' => 'Do your operations involve the distribution of advertising material',
                            'description' => 'Do your operations involve the distribution of advertising material',
                            'keywords' => null,
                            'category_id' => [
                                111232,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 46.91,
                    ],
                ],
            ], 200, ['content-type' => 'application/json']),
        ]);

        $response = (new Client())->suggest(ContentMetaSuggestions::CONTEXT, 'hauling of dangerous waste');

        $this->assertSame([20, 319, 233, 120, 97, 655], $response->all());
    }

    /**
     * @return void
     */
    public function testGenerationOfAction(): void
    {
        Config::set('services.libryo_ai.enabled', true);
        Config::set('services.libryo_ai.host', 'http://libryo-ai.libryo.rocks:7000');

        Http::fake([
            'libryo-ai.libryo.rocks:7000/*' => Http::response([
                'query' => "2.Prohibition of certain advertisements visible from public roads\n(1) Subject to the provisions of sub-sections (4) and (5) of this section and of section six, no person shall display an advertisement which is visible from a public road, unless it is displayed in accordance with the written permission of a controlling authority concerned => Provided that any person may without the permission of a controlling authority (but subject to the provisions of sub-section (2) of this section and of sub-section (1) of section four) -\n(a) display, on a building such an advertisement which discloses merely the name or nature of any business or undertaking carried on therein or the name of the proprietor or manager of that business or undertaking or any information which relates solely to that business or undertaking or to any article or service supplied in connection with that business or undertaking or in connection with any other business or undertaking of that proprietor; or",
                'results' => [
                    [
                        'document' => [
                            'id' => 20,
                            'title' => 'Do your operations have advertising signs',
                            'description' => 'Do your operations have advertising signs',
                            'keywords' => null,
                            'category_id' => [
                                113056,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 504.26750000000004,
                    ],
                    [
                        'document' => [
                            'id' => 319,
                            'title' => 'Do your operations involve storing water from a water resource',
                            'description' => 'Do your operations involve storing water from a water resource',
                            'keywords' => null,
                            'category_id' => [
                                118002,
                                118008,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 99.97,
                    ],
                    [
                        'document' => [
                            'id' => 233,
                            'title' => 'Are your operations a telecommunications service provider',
                            'description' => 'Are your operations a telecommunications service provider',
                            'keywords' => null,
                            'category_id' => [
                                111064,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 94.62,
                    ],
                    [
                        'document' => [
                            'id' => 120,
                            'title' => 'Do your operations manage a fire brigade service',
                            'description' => 'Do your operations manage a fire brigade service',
                            'keywords' => null,
                            'category_id' => [
                                111255,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 91.2,
                    ],
                    [
                        'document' => [
                            'id' => 97,
                            'title' => 'Do your operations involve private streets',
                            'description' => 'Do your operations involve private streets',
                            'keywords' => null,
                            'category_id' => [
                                116026,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 46.91,
                    ],
                    [
                        'document' => [
                            'id' => 655,
                            'title' => 'Do your operations involve the distribution of advertising material',
                            'description' => 'Do your operations involve the distribution of advertising material',
                            'keywords' => null,
                            'category_id' => [
                                111232,
                            ],
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'relevance' => 46.91,
                    ],
                ],
            ], 200, ['content-type' => 'application/json']),
        ]);

        $response = (new Client())->suggest(ContentMetaSuggestions::ACTION, 'hauling of dangerous waste');

        $this->assertSame([20, 319, 233, 120, 97, 655], $response->all());
    }

    public function testMagiSearchEngine(): void
    {
        Config::set('services.libryo_ai.enabled', false);
        Config::set('services.libryo_ai.host', 'http://libryo-ai.libryo.rocks:7000');

        /** @var CatalogueDoc $doc */
        $doc = CatalogueDoc::factory()->make();
        $doc->saveQuietly();

        $searchResults = [
            'query' => $doc->title,
            'results' => [
                [
                    'document' => [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'source_id' => $doc->source_id,
                    ],
                ],
            ],
        ];

        Http::fake([
            'libryo-ai.libryo.rocks:7000/*' => Http::sequence()
                ->push(null, 200, ['content-type' => 'application/json'])
                ->push($searchResults, 200, ['content-type' => 'application/json'])
                ->push($searchResults, 200, ['content-type' => 'application/json'])
                ->push($searchResults, 200, ['content-type' => 'application/json'])
                ->push(null, 200, ['content-type' => 'application/json']),
        ]);

        // Magi is disabled so no results should be found.
        $results = CatalogueDoc::search($doc->title)->get();
        $this->assertTrue($results->isEmpty());
        $this->assertCount(0, CatalogueDoc::search($doc->title)->cursor());

        Config::set('scout.queue', false);
        Config::set('services.libryo_ai.enabled', true);

        // Test updating the magi
        $doc->update(['summary' => 'Test Summary']);

        // Test Searching
        $results = CatalogueDoc::search($doc->title)->get();
        $this->assertContains($doc->id, $results->pluck('id')->all());

        $results = CatalogueDoc::search($doc->title)->paginate(20);
        $this->assertContains($doc->id, collect($results->items())->pluck('id')->all());

        $this->assertCount(1, CatalogueDoc::search($doc->title)->cursor());

        // Test Deleting
        $doc->delete();
    }
}
