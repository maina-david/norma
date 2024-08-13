<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

/**
 * @method static int|null getPageFromQueryParams()
 * @method static int      getPerPageFromQueryParams()
 * @method static array    getFieldsFromQueryParams()
 * @method static array    getFiltersFromQueryParams()
 * @method static array    getOrderFromQueryParams()
 * @method static array    getCountsFromQueryParams()
 * @method static array    getHasFromQueryParams()
 * @method static array    getIncludesFromQueryParams()
 * @method static array    getIdsFromQueryParams()
 */
class ApiMultiGetRequest extends Request
{
}
