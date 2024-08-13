<x-ui.show-field :label="__('arachno.update_email.date')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>

<x-ui.show-field :label="__('arachno.update_email.to')" :value="$resource->to" />
<x-ui.show-field :label="__('arachno.update_email.from')" :value="$resource->from" />
<x-ui.show-field :label="__('arachno.update_email.subject')" :value="$resource->subject" />


<x-ui.show-field :label="__('arachno.update_email.text')">{!! $resource->text !!}</x-ui.show-field>

<x-ui.show-field :label="__('arachno.update_email.html')">{!! $resource->html !!}</x-ui.show-field>
