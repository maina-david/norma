<?php

namespace App\Enums\Corpus;

use App\Enums\Enum;

/**
 * @property string $value
 *
 * @method static WorkType act()
 * @method static WorkType agreement()
 * @method static WorkType announcement()
 * @method static WorkType bill()
 * @method static WorkType bylaw()
 * @method static WorkType change_notification()
 * @method static WorkType circular()
 * @method static WorkType code()
 * @method static WorkType code_of_ordinances()
 * @method static WorkType code_of_practice()
 * @method static WorkType constitution()
 * @method static WorkType convention()
 * @method static WorkType decision()
 * @method static WorkType declaration()
 * @method static WorkType decree()
 * @method static WorkType direction()
 * @method static WorkType directive()
 * @method static WorkType enrolled_bill()
 * @method static WorkType executive_order()
 * @method static WorkType final_rule()
 * @method static WorkType gazette()
 * @method static WorkType guidance()
 * @method static WorkType guidance_notes()
 * @method static WorkType guidelines()
 * @method static WorkType interim_final_rule()
 * @method static WorkType law()
 * @method static WorkType legislation()
 * @method static WorkType legal_update()
 * @method static WorkType legislative_instrument()
 * @method static WorkType measures()
 * @method static WorkType ministerial_order()
 * @method static WorkType miscellaneous()
 * @method static WorkType notice()
 * @method static WorkType notification()
 * @method static WorkType order()
 * @method static WorkType ordinance()
 * @method static WorkType permit()
 * @method static WorkType policy()
 * @method static WorkType procedures()
 * @method static WorkType proclamation()
 * @method static WorkType proposal()
 * @method static WorkType public_law()
 * @method static WorkType regulation()
 * @method static WorkType resolution()
 * @method static WorkType rule()
 * @method static WorkType standard()
 * @method static WorkType statutory_instrument()
 * @method static WorkType treaty()
 */
class WorkType extends Enum
{
    /** @var string */
    protected static string $langPrefix = 'corpus.work.types.';

    /**
     * Get the allowed enums.
     *
     * @return array<array-key,string>
     */
    protected static function enums(): array
    {
        return [
            'act',
            'agreement',
            'announcement',
            'bill',
            'bylaw',
            'change_notification',
            'circular',
            'code',
            'code_of_ordinances',
            'code_of_practice',
            'constitution',
            'convention',
            'decision',
            'declaration',
            'decree',
            'direction',
            'directive',
            'enrolled_bill',
            'executive_order',
            'final_rule',
            'gazette',
            'guidance',
            'guidance_notes',
            'guidelines',
            'interim_final_rule',
            'law',
            'legal_update',
            'legislation',
            'legislative_instrument',
            'measures',
            'ministerial_order',
            'miscellaneous',
            'notice',
            'notification',
            'order',
            'ordinance',
            'permit',
            'policy',
            'procedures',
            'proclamation',
            'proposal',
            'public_law',
            'regulation',
            'resolution',
            'rule',
            'standard',
            'statutory_instrument',
            'treaty',
        ];
    }
}
