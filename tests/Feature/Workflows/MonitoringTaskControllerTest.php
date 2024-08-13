<?php

namespace Tests\Feature\Workflows;

use App\Models\Geonames\Location;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\MonitoringTaskConfig;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Feature\Abstracts\CrudTestCase;

class MonitoringTaskControllerTest extends CrudTestCase
{
    protected string $sortBy = 'title';
    protected bool $collaborate = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return MonitoringTaskConfig::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.monitoring-task-configs';
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleFields(): array
    {
        return ['title'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function visibleLabels(): array
    {
        return ['Title'];
    }

    /**
     * {@inheritDoc}
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        if ($route === 'store') {
            return $factory->state([
                'locations' => [Location::factory()->create()->id],
                'legal_domains' => [LegalDomain::factory()->create()->id],
            ]);
        }
        if ($route === 'update') {
            return $factory->state([
                'locations' => [Location::factory()->create()->id],
                'legal_domains' => [LegalDomain::factory()->create()->id],
            ]);
        }

        return $factory;
    }
}
