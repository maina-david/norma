<x-ui.input
            :value="old('title', $resource->title ?? '')"
            name="title"
            label="{{ __('system.system_notification.title') }}"
            required />

<x-ui.input
            type="select"
            name="type"
            label="{{ __('system.system_notification.type') }}"
            :options="App\Enums\System\SystemNotificationAlertType::lang()"
            :value="old('type', $resource->type ?? App\Enums\System\SystemNotificationAlertType::alert())" />

<x-ui.input
            type="textarea"
            required
            name="content"
            label="{{ __('system.system_notification.content') }}"
            :value="old('content', $resource->content ?? '')" />

<x-ui.input
            type="date"
            name="expiry_date"
            required
            label="{{ __('system.system_notification.expiry_date') }}"
            :value="old('expiry_date', $resource->expiry_date ?? '')"
            class="mb-5" />


<x-ui.input
            type="checkbox"
            name="is_permanent"
            label="{{ __('system.system_notification.is_permanent') }}"
            :value="old('is_permanent', $resource->is_permanent ?? '')" />

<x-ui.input
            type="checkbox"
            name="active"
            label="{{ __('system.system_notification.active') }}"
            :value="old('active', $resource->active ?? '')" />

<div
     x-data="{ hasUserAction: {{ old('has_user_action', isset($resource) && $resource->has_user_action ? 'true' : 'false') }} }">
  <x-ui.input
              x-model="hasUserAction"
              type="checkbox"
              name="has_user_action"
              label="{{ __('system.system_notification.has_user_action') }}"
              :value="old('has_user_action', $resource->has_user_action ?? '')" />

  <div x-show="hasUserAction">
    <x-ui.input
                type="text"
                name="user_action_text"
                label="{{ __('system.system_notification.user_action_text') }}"
                :value="old('user_action_text', $resource->user_action_text ?? '')" />

    <x-ui.input
                type="text"
                name="user_action_link"
                label="{{ __('system.system_notification.user_action_link') }}"
                :value="old('user_action_link', $resource->user_action_link ?? '')" />
  </div>
</div>

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('admin.system-notifications.index')" />
    <x-ui.button
                 type="submit"
                 theme="primary">
      {{ __('actions.save') }}
    </x-ui.button>
  </div>
</x-slot>
