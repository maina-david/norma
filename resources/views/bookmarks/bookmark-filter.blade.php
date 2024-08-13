<x-ui.input
  label="{{ __('bookmarks.my_bookmarks') }}"
  name="bookmarked"
  type="checkbox"
  value="{{ $checked }}"
  checkbox-value="yes"
  @change="$dispatch('changed', {{ $checked ? 'null' : '$el.value' }});"
/>
