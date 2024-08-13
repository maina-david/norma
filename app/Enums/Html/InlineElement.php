<?php

namespace App\Enums\Html;

use App\Enums\Traits\EnumToValues;

enum InlineElement: string
{
    use EnumToValues;

    case I_A = 'a';
    case I_ABBR = 'abbr';
    case I_ACRONYM = 'acronym';
    case I_B = 'b';
    case I_BDO = 'bdo';
    case I_BIG = 'big';
    case I_BR = 'br';
    case I_BUTTON = 'button';
    case I_CITE = 'cite';
    case I_CODE = 'code';
    case I_DFN = 'dfn';
    case I_EM = 'em';
    case I_I = 'i';
    case I_IMG = 'img';
    case I_INPUT = 'input';
    case I_KBD = 'kbd';
    case I_LABEL = 'label';
    case I_MAP = 'map';
    case I_OBJECT = 'object';
    case I_OUTPUT = 'output';
    case I_Q = 'q';
    case I_SAMP = 'samp';
    case I_SCRIPT = 'script';
    case I_SELECT = 'select';
    case I_SMALL = 'small';
    case I_SPAN = 'span';
    case I_STRONG = 'strong';
    case I_SUB = 'sub';
    case I_SUP = 'sup';
    case I_TEXTAREA = 'textarea';
    case I_TIME = 'time';
    case I_TT = 'tt';
    case I_VAR = 'var';
}
