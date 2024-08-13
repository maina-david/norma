<?php

namespace App\Services\Corpus;

/**
 * Helper class to manage document types.
 */
class DocumentTypes
{
    // LEGAL DOCUMENTS
    public const ACT = 1;
    public const LEGISLATION = 1; // backwards compatibility
    public const REGULATION = 2; // backwards compatibility
    public const NOTICE = 3; // backwards compatibility
    public const LEGAL_UPDATE = 4;
    public const LAW = 6;
    public const RULE = 7;
    public const ORDER = 8;
    public const ORDINANCE = 9;
    public const DECREE = 10;
    public const NOTIFICATION = 11;
    public const BYLAW = 12;
    public const BILL = 13;
    public const TREATY = 14;
    public const DIRECTIVE = 15;
    // const AMENDMENT = 16;
    public const DIRECTION = 17;
    public const STATUTORY_INSTRUMENT = 18;
    public const LEGISLATIVE_INSTRUMENT = 19;
    public const PROCLAMATION = 20;
    public const CONSTITUTION = 21;
    public const CODE = 22;
    public const CODE_OF_ORDINANCES = 23;
    public const MEASURES = 24;
    public const DECLARATION = 25;
    public const MINISTERIAL_ORDER = 26;
    public const PROCEDURES = 27;
    public const GUIDANCE = 28;
    public const GUIDANCE_NOTES = 29;
    public const CIRCULAR = 30;
    public const ENROLLED_BILL = 31;
    public const EXECUTIVE_ORDER = 32;
    public const FINAL_RULE = 33;
    public const INTERIM_FINAL_RULE = 34;
    public const PUBLIC_LAW = 35;

    // ...SPACE FOR MORE LEGAL DOCUMENTS

    // OTHER DOCUMENTS
    public const GAZETTE = 200;
    public const STANDARD = 201;
    public const CODE_OF_PRACTICE = 202;
    public const POLICY = 203;
    public const PERMIT = 204;
    public const DECISION = 205;
    public const PROPOSAL = 206;
    public const RESOLUTION = 207;
    public const GUIDELINES = 208;
    public const ANNOUNCEMENT = 209;
    public const AGREEMENT = 210;
    public const CONVENTION = 211;

    public const MAP = [
        'legislation' => self::LEGISLATION,
        'regulation' => self::REGULATION,
        'legal_update' => self::LEGAL_UPDATE,
        'act' => self::ACT,
        'law' => self::LAW,
        'rule' => self::RULE,
        'order' => self::ORDER,
        'ordinance' => self::ORDINANCE,
        'decree' => self::DECREE,
        'notice' => self::NOTICE,
        'notification' => self::NOTIFICATION,
        'bylaw' => self::BYLAW,
        'bill' => self::BILL,
        'treaty' => self::TREATY,
        'directive' => self::DIRECTIVE,
        // 'amendment' => self::AMENDMENT,
        'direction' => self::DIRECTION,
        'statutory_instrument' => self::STATUTORY_INSTRUMENT,
        'legislative_instrument' => self::LEGISLATIVE_INSTRUMENT,
        'proclamation' => self::PROCLAMATION,
        'constitution' => self::CONSTITUTION,
        'code' => self::CODE,
        'code_of_ordinances' => self::CODE_OF_ORDINANCES,
        'measures' => self::MEASURES,
        'declaration' => self::DECLARATION,
        'ministerial_order' => self::MINISTERIAL_ORDER,
        'procedures' => self::PROCEDURES,
        'guidance' => self::GUIDANCE,
        'guidance_notes' => self::GUIDANCE_NOTES,
        'circular' => self::CIRCULAR,
        'gazette' => self::GAZETTE,
        'standard' => self::STANDARD,
        'code_of_practice' => self::CODE_OF_PRACTICE,
        'policy' => self::POLICY,
        'permit' => self::PERMIT,
        'decision' => self::DECISION,
        'proposal' => self::PROPOSAL,
        'resolution' => self::RESOLUTION,
        'guidelines' => self::GUIDELINES,
        'announcement' => self::ANNOUNCEMENT,
        'agreement' => self::AGREEMENT,
        'convention' => self::CONVENTION,
    ];

    /**
     * @return string
     **/
    // public static function getString(int $id): string
    // {
    //     return array_flip(static::MAP)[$id] ?? '';
    // }

    /**
     * @return string
     **/
    public static function getPrettyString(int $id): string
    {
        $str = array_flip(static::MAP)[$id] ?? '';

        return ucwords(str_replace('_', ' ', $str));
    }

    /**
     * @return int
     **/
    public static function getInt(string $str): int
    {
        return static::MAP[strtolower($str)] ?? 0;
    }
}
