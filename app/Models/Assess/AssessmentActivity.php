<?php

namespace App\Models\Assess;

use App\Enums\Assess\AssessActivityType;
use App\Enums\Assess\ResponseStatus;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Corpus\ReferenceText;
use App\Models\Customer\Libryo;
use App\Models\Storage\My\File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAssessmentActivity
 */
class AssessmentActivity extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function assessmentItemResponse(): BelongsTo
    {
        return $this->belongsTo(AssessmentItemResponse::class, 'assessment_item_response_id');
    }

    /**
     * @return BelongsTo
     */
    public function assessmentItem(): BelongsTo
    {
        return $this->belongsTo(AssessmentItem::class, 'assessment_item_id');
    }

    /**
     * @return BelongsTo
     */
    public function libryo(): BelongsTo
    {
        return $this->belongsTo(Libryo::class, 'place_id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeAnswerChange(Builder $builder): Builder
    {
        return $builder->where('activity_type', AssessActivityType::answerChange()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeMaxActivitySubquery(Builder $builder, string $tableStr): Builder
    {
        /** @var Builder */
        return $builder->whereRaw('place_id = ' . $tableStr)
            ->selectRaw('max(`created_at`)');
    }

    /*
    |--------------------------------------------------------------------------
    | Model Methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return string
     **/
    public function toText(): string
    {
        /** @var string */
        $unknown = __('auth.user.unknown_user');
        $name = $this->user?->full_name ?? $unknown;
        switch ($this->activity_type) {
            case AssessActivityType::requirementUnlinked()->value:
                /** @var ReferenceText $text */
                $text = ReferenceText::where('reference_id', $this->reference_id)->first();
                /** @var string */
                $str = __('assess.assessment_activity.reference_unlinked', ['requirement' => $text->plain_text ?? '']);

                break;
            case AssessActivityType::requirementLinked()->value:
                /** @var ReferenceText $text */
                $text = ReferenceText::where('reference_id', $this->reference_id)->first();
                /** @var string */
                $str = __('assess.assessment_activity.reference_linked', ['requirement' => $text->plain_text ?? '']);

                break;
            case AssessActivityType::responseAdded()->value:
                /** @var string */
                $str = __('assess.assessment_activity.response_added');

                break;
            case AssessActivityType::answerChange()->value:
                /** @var string */
                $str = __('assess.assessment_activity.changed_response_status', [
                    'name' => $name,
                    'from' => is_null($this->from_status) ? __('assess.assessment_item_response.status.' . ResponseStatus::notAssessed()->value) : __('assess.assessment_item_response.status.' . $this->from_status),
                    'to' => is_null($this->to_status) ? __('assess.assessment_item_response.status.' . ResponseStatus::notAssessed()->value) : __('assess.assessment_item_response.status.' . $this->to_status),
                ]);

                break;
            case AssessActivityType::comment()->value:
                /** @var string */
                $str = __('comments.made_comment', [
                    'name' => $name,
                ]);

                break;
            case AssessActivityType::fileUpload()->value:
                /** @var int|null */
                $fileId = $this->file_id ?? null;
                /** @var File|null */
                $document = File::find($fileId);
                /** @var string */
                $str = __('storage.uploaded_document', [
                    'name' => $name,
                    'file' => !is_null($document) ? $document->title : __('storage.file.deleted_file'),
                ]);

                break;
            default:
                /** @var string */
                $str = '';

                break;
        }

        return $str;
    }

    /**
     * @return bool
     */
    public function isAnswerChanged(): bool
    {
        return AssessActivityType::answerChange()->is($this->activity_type);
    }
}
