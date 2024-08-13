<?php

namespace App\Enums\Collaborators;

use App\Enums\Traits\HasLabels;

enum DocumentType: int
{
    use HasLabels;

    case CONTRACT = 1;
    case IDENTIFICATION = 2;
    case SCHEDULE_A = 3;
    case TERM_DATES = 4;
    case TRANSCRIPT = 5;
    case VISA = 6;
    case VITAE = 7;

    /**
     * Get the fields for each document type.
     *
     * @return string
     */
    public function field(): string
    {
        return match ($this->value) {
            self::CONTRACT->value => 'contract',
            self::IDENTIFICATION->value => 'identification',
            self::SCHEDULE_A->value => 'schedule-a',
            self::TERM_DATES->value => 'term-dates',
            self::TRANSCRIPT->value => 'transcript',
            self::VISA->value => 'visa',
            default => 'vitae',
        };
    }

    /**
     * Get the enum from the field.
     *
     * @param string $field
     *
     * @return DocumentType|null
     */
    public static function fromField(string $field): ?DocumentType
    {
        return match ($field) {
            self::CONTRACT->field() => self::CONTRACT,
            self::IDENTIFICATION->field() => self::IDENTIFICATION,
            self::SCHEDULE_A->field() => self::SCHEDULE_A,
            self::TERM_DATES->field() => self::TERM_DATES,
            self::TRANSCRIPT->field() => self::TRANSCRIPT,
            self::VISA->field() => self::VISA,
            self::VITAE->field() => self::VITAE,
            default => null,
        };
    }

    /**
     * Get the field regex to be used when validating routes.
     *
     * @return string
     */
    public static function fieldsRegex(): string
    {
        return collect(self::cases())->map(fn ($case) => $case->field())->join('|');
    }

    /**
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'collaborators.collaborator.document_type.';
    }
}
