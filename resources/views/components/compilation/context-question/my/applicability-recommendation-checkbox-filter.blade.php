<div>
  <div class="filter-container py-4">
    <div class="space-y-2 filter-children">
      <x-ui.input
        type="checkbox"
        :label="$recommended->label()"
        name="recommended"
        :checkbox-value="$recommended->value"
        :value="$applied['recommended'] ?? false"
      />
    </div>
  </div>
</div>
