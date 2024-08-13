@props(['expressionId', 'tooltip', 'filter', 'icon', 'states' => null, 'stateColours' => [1 => 'border-primary text-primary']])
@php
  $query = request()->query() ?? [];
  $query['expression'] = $expressionId;

  $filter = "has-{$filter}";
  $filterValue = $query[$filter] ?? null;
  $classes = $stateColours[$filterValue] ?? 'border-transparent';


  $filters = function($key) use ($query, $states) {
    $toggle = [
        $key =>  $states ? $states[0] : 1,
    ];

    if (!isset($query[$key])) {
        return [...$query, ...$toggle];
    }

    if (!$states) {
        unset($query[$key]);
        $toggle = [];

        return [...$query, ...$toggle];
    }

    $index = array_search($query[$key], $states);

    if ($index !== false && isset($states[$index + 1])) {
        $toggle[$key] = $states[$index + 1];

        return [...$query, ...$toggle];
    }

      unset($query[$key]);

      return [...$query];
  }
@endphp

<a
  data-tippy-content="{{ $tooltip }}"
  class="{{ $classes }} tippy h-10 w-8 hover:text-primary flex items-center justify-center border-t-2 pt-2"
  href="{{ route('collaborate.work-expressions.references.filters', $filters($filter)) }}"
>
  <x-ui.icon :name="$icon" />
</a>
