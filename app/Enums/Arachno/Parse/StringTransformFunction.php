<?php

namespace App\Enums\Arachno\Parse;

use App\Enums\Traits\EnumToValues;

enum StringTransformFunction: string
{
    use EnumToValues;

    case after = 'after';
    case afterLast = 'afterLast';
    case append = 'append';
    case ascii = 'ascii';
    case basename = 'basename';
    case classBasename = 'classBasename';
    case before = 'before';
    case beforeLast = 'beforeLast';
    case between = 'between';
    case camel = 'camel';
    case dirname = 'dirname';
    case finish = 'finish';
    case kebab = 'kebab';
    case length = 'length';
    case limit = 'limit';
    case lower = 'lower';
    case markdown = 'markdown';
    case MATCH = 'match';
    case test = 'test';
    case padBoth = 'padBoth';
    case padLeft = 'padLeft';
    case padRight = 'padRight';
    case pipe = 'pipe';
    case plural = 'plural';
    case pluralStudly = 'pluralStudly';
    case prepend = 'prepend';
    case remove = 'remove';
    case repeat = 'repeat';
    case replace = 'replace';
    case replaceArray = 'replaceArray';
    case replaceFirst = 'replaceFirst';
    case replaceLast = 'replaceLast';
    case replaceMatches = 'replaceMatches';
    case start = 'start';
    case upper = 'upper';
    case title = 'title';
    case singular = 'singular';
    case slug = 'slug';
    case snake = 'snake';
    case studly = 'studly';
    case substr = 'substr';
    case substrCount = 'substrCount';
    case trim = 'trim';
    case ltrim = 'ltrim';
    case rtrim = 'rtrim';
    case ucfirst = 'ucfirst';
}
