<div x-data="{parent:{{ json_encode(old('is_parent', isset($resource) ? $resource->is_parent : false)) }} }">
  <x-ui.input :value="old('title', $resource->title ?? '')" name="title" label="{{ __('interface.title') }}" required />


  <x-customer.organisation.plan-selector :value="$resource->plan ?? null" />
  <x-customer.organisation.type-selector :value="$resource->type ?? null" />
  <x-customer.organisation.category-selector :value="$resource->customer_category ?? null" />

  <x-partners.white-label-selector :value="$resource->whitelabel_id ?? null" />

  <div x-show="!parent">
    <x-customer.organisation.organisation-selector
        :required="false"
        name="parent_id"
        :id="$resource->parent->id ?? null"
        :title="$resource->parent->title ?? null"
        :payload="['parent' => 1]"
    />
  </div>


  <div class="my-8">
    <x-ui.input type="checkbox"
                :value="old('translation_enabled', isset($resource) ? $resource->translation_enabled : true)"
                name="translation_enabled"
                label="{{ __('customer.organisation.translation_services') }}" />

    <div class="mt-3">
      <x-ui.input
        x-model="parent"
        type="checkbox"
        :value="old('is_parent', isset($resource) ? $resource->is_parent : false)"
        name="is_parent"
        label="{{ __('customer.organisation.parent_organisation') }}"
      />
    </div>

  </div>
</div>


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button
                      :fallback="isset($resource) && $resource->id ? route('my.settings.organisations.show', ['organisation' => $resource->id]) : route('my.settings.organisations.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
