<?php

namespace Database\Factories\Arachno;

use App\Models\Arachno\ContentCache;
use App\Models\Arachno\Crawl;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentCacheFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContentCache::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'crawl_id' => Crawl::factory(),
        ];
    }
}
