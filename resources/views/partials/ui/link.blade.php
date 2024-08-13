<x-ui.link data-turbo="{{ isset($turbo) && $turbo === false ? 'false' : 'true' }}" :href="$href"
           target="{{ $target ?? '' }}" data-turbo-frame="_top">{{ $linkText }}</x-ui.link>
