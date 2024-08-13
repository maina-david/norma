<div>
  <div class="filter-container py-4" x-data="{ onCheck: function (el, val) { el.closest('.filter-container').querySelectorAll('.filter-children input').forEach(function (ch) { ch.checked = el.checked; }); } }">
    <div class="mb-3">
      <x-ui.input
        type="checkbox"
        label=""
        name=""
        @change="onCheck($event.target)"
        id="all-domains"
      >
        <x-slot:label>
          <span class="font-bold">{{ __('assess.categories') }}</span>
        </x-slot:label>
      </x-ui.input>
    </div>

    <div class="space-y-2 filter-children">
      @foreach($domains as $domain)
        <div class="mb-3">
          <x-ui.input
            type="checkbox"
            :label="$domain->title"
            id="domain{{ $domain->id }}"
            name="domains[]"
            :checkbox-value="$domain->id"
            :value="in_array($domain->id, $applied)"
          />
        </div>
      @endforeach
    </div>
  </div>

</div>
