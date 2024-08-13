<?php

namespace Database\Seeders\Lookups;

use App\Enums\Lookups\AnnotationSourceType;
use App\Models\Lookups\AnnotationSource;
use Illuminate\Database\Seeder;

class AnnotationSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AnnotationSource::insert([
            ['id' => AnnotationSourceType::CONTEXT_QUESTION, 'title' => 'Context Questions'],
            ['id' => AnnotationSourceType::ASSESSMENT_ITEM, 'title' => 'Assessment Items'],
            ['id' => AnnotationSourceType::TAG, 'title' => 'Tags'],
            ['id' => AnnotationSourceType::CDM, 'title' => 'CDM'],
            ['id' => AnnotationSourceType::LIBRYO_AI, 'title' => 'Libryo AI'],
            ['id' => AnnotationSourceType::WORDCLOUD, 'title' => 'Wordcloud'],
            ['id' => AnnotationSourceType::QC_OTHER, 'title' => 'Quality Control / Other'],
            ['id' => AnnotationSourceType::MAGI, 'title' => 'Magi'],
            ['id' => AnnotationSourceType::ACTION_AREA, 'title' => 'Action Areas'],
        ]);
    }
}
