<?php

namespace App\Enums\Corpus;

use App\Enums\Traits\HasLabels;
use Illuminate\Support\Facades\Auth;

enum ReferenceLinkType: int
{
    use HasLabels;

    case READ_WITH = 1;
    //    case OPTIONAL_READ_WITH = 2;
    case CONSEQUENCE = 3;
    //    case CONSTITUTIVE = 4;
    //    case BEARER = 5;
    case AMENDMENT = 6;

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function languagePrefix(): string
    {
        return 'corpus.reference.link_type.';
    }

    /**
     * Get the link types when adding a parent.
     *
     * @return array<int, string>
     */
    public static function linkTypes(bool $hasConsequences): array
    {
        $user = Auth::user();

        $options = collect();

        collect(self::cases())
            ->filter(fn ($case) => $user && $user->can("collaborate.corpus.reference.link.link_type_{$case->value}"))
            ->each(function ($case) use ($options) {
                $parent = __(sprintf('corpus.reference.link_type_relations.%d_parent', $case->value));
                $child = __(sprintf('corpus.reference.link_type_relations.%d_child', $case->value));

                // if ($case === self::CONSEQUENCE) {
                //     $prefix = $hasConsequences ? '' : '-';

                //     $options->put("{$prefix}{$case->value}", $hasConsequences ? $parent : $child);

                //     return;
                // }

                $options->put($case->value, $parent);

                if ($parent !== $child) {
                    // @codeCoverageIgnoreStart
                    $options->put("-{$case->value}", $child);
                    // @codeCoverageIgnoreEnd
                }
            });

        /** @var array<int, string> */
        return $options->toArray();
    }
}
