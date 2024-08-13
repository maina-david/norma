<?php

namespace App\Http\Requests\Arachno;

use App\Models\Arachno\Crawler;
use Closure;
use Cron\CronExpression;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $description
 */
class CrawlerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'needs_browser' => $this->has('needs_browser'),
            'enabled' => $this->has('enabled'),
            'can_fetch' => $this->has('can_fetch'),
            'description' => strip_tags($this->description),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed> $rules
     */
    public function rules()
    {
        $crawlers = Crawler::class;

        $cronRule = function (string $attribute, mixed $value, Closure $fail) {
            // @codeCoverageIgnoreStart
            if (!CronExpression::isValidExpression($value)) {
                $fail(__('arachno.crawler.invalid_cron'));
            }
            // @codeCoverageIgnoreEnd
        };

        $rules = [
            'title' => ['string', "unique:{$crawlers}", 'required', 'max:255'],
            'slug' => ['string', "unique:{$crawlers}", 'required', 'max:255', 'lowercase'],
            'needs_browser' => ['bool', 'required'],
            'enabled' => ['bool', 'required'],
            'can_fetch' => ['bool', 'required'],
            'schedule_new_works' => ['string', 'max:255', 'nullable', $cronRule],
            'schedule_changes' => ['string', 'max:255', 'nullable', $cronRule],
            'schedule_updates' => ['string', 'max:255', 'nullable', $cronRule],
            'schedule_full_catalogue' => ['string', 'max:255', 'nullable', $cronRule],
            'class_name' => ['string', 'max:255', 'nullable'],
            'description' => ['string', 'nullable'],
            'source_id' => ['nullable'],
        ];

        if ($this->method() === 'POST') {
            return $rules;
        }

        /** @var string $crawlerStr */
        $crawlerStr = request('crawler');
        $rules['title'] = "required|unique:{$crawlers},title," . $crawlerStr;
        $rules['slug'] = "required|unique:{$crawlers},slug," . $crawlerStr;

        return $rules;
    }
}
