<td class="py-4 px-4 rounded-tl-md" {!! $attributes !!}>
  <div class="flex">
    <div class="flex-shrink-0 pr-4">
      <x-ui.country-flag class="h-8 w-8 rounded-full" :countryCode="$row->flag" />
    </div>
    <div class="flex-grow">
      <div class="text-primary font-semibold">{{ $row->title }}</div>
      <div class="text-xs text-norma-gray-500 mt-1">{{ $row->work_title ?? '-' }}</div>
    </div>
  </div>
</td>
