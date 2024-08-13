<x-ui.show-field :label="__('lookups.canned_response.response')" :value="$resource->response" />

<x-ui.show-field :label="__('lookups.canned_response.for_field')"
                 :value="\App\Enums\Lookups\CannedResponseField::lang()[$resource->for_field] ?? '-'" />

<x-ui.show-field :label="__('lookups.canned_response.created_at')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>
