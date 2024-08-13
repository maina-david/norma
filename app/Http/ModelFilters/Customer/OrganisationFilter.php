<?php

namespace App\Http\ModelFilters\Customer;

use EloquentFilter\ModelFilter;

/**
 * @method static OrganisationFilter titleLike(string $title)
 * @method static OrganisationFilter whitelabel(string $whitelabelId)
 * @method static OrganisationFilter plan(string $plan)
 * @method static OrganisationFilter type(string $type)
 * @method static OrganisationFilter customerCategory(string $customerCategory)
 */
class OrganisationFilter extends ModelFilter
{
    /**
     * @param string $search
     *
     * @return OrganisationFilter
     */
    public function search(string $search): OrganisationFilter
    {
        /** @var OrganisationFilter $filter */
        $filter = $this->titleLike($search);

        return $filter;
    }

    /**
     * @param string $whitelabelId
     *
     * @return OrganisationFilter
     */
    public function whitelabel(string $whitelabelId): OrganisationFilter
    {
        /** @var OrganisationFilter $filter */
        $filter = $this->where('whitelabel_id', $whitelabelId);

        return $filter;
    }

    /**
     * @param string $plan
     *
     * @return OrganisationFilter
     */
    public function plan(string $plan): OrganisationFilter
    {
        /** @var OrganisationFilter $filter */
        $filter = $this->where('plan', $plan);

        return $filter;
    }

    /**
     * @param string $type
     *
     * @return OrganisationFilter
     */
    public function type(string $type): OrganisationFilter
    {
        /** @var OrganisationFilter $filter */
        $filter = $this->where('type', $type);

        return $filter;
    }

    /**
     * @param string $customerCategory
     *
     * @return OrganisationFilter
     */
    public function customerCategory(string $customerCategory): OrganisationFilter
    {
        /** @var OrganisationFilter $filter */
        $filter = $this->where('customer_category', $customerCategory);

        return $filter;
    }

    /**
     * @param string $value
     *
     * @return OrganisationFilter
     */
    public function parent(string $value): OrganisationFilter
    {
        /** @var OrganisationFilter $filter */
        $filter = $this->where('is_parent', $value);

        return $filter;
    }
}
