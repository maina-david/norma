<div>
  <div class="filter-container py-4">
    <div class="space-y-2 filter-children">
        <x-ui.input
          type="checkbox"
          :label="$included->label()"
          id="inclusion_{{ $included->value }}"
          name="included"
          :checkbox-value="$included->value"
          @change="$event.target.closest('.filter-children').querySelector('#inclusion_{{ $excluded->value }}').checked = false"
          :value="$applied['included'] ?? false"
        />

        <x-ui.input
          type="checkbox"
          :label="$excluded->label()"
          id="inclusion_{{ $excluded->value }}"
          name="excluded"
          :checkbox-value="$excluded->value"
          @change="$event.target.closest('.filter-children').querySelector('#inclusion_{{ $included->value }}').checked = false"
          :value="$applied['excluded'] ?? false"
        />

    </div>
  </div>
</div>
