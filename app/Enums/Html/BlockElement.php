<?php

namespace App\Enums\Html;

use App\Enums\Traits\EnumToValues;

enum BlockElement: string
{
    use EnumToValues;

    case ADDRESS = 'address';
    case ARTICLE = 'article';
    case ASIDE = 'aside';
    case BLOCKQUOTE = 'blockquote';
    case DETAILS = 'details';
    case DIALOG = 'dialog';
    case DD = 'dd';
    case DIV = 'div';
    case DL = 'dl';
    case DT = 'dt';
    case FIELDSET = 'fieldset';
    case FIGCAPTION = 'figcaption';
    case FIGURE = 'figure';
    case FOOTER = 'footer';
    case FORM = 'form';
    case H1 = 'h1';
    case H2 = 'h2';
    case H3 = 'h3';
    case H4 = 'h4';
    case H5 = 'h5';
    case H6 = 'h6';
    case HEADER = 'header';
    case HGROUP = 'hgroup';
    case HR = 'hr';
    case LI = 'li';
    case MAIN = 'main';
    case NAV = 'nav';
    case OL = 'ol';
    case P = 'p';
    case PRE = 'pre';
    case SECTION = 'section';
    case TABLE = 'table';
    case UL = 'ul';
}
