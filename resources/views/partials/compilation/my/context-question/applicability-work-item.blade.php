<tr>
  <td class="pl-4">
    <x-ui.input
      type="checkbox"
      label=""
      name="work_{{ $work->id }}"
      :checkbox-value="true"
      @change="checkWorkChildren('.work_{{ $work->id }}_child', $event.target.checked);validateActionVisibility()"
    />
  </td>
  <td colspan="3" class="px-1 py-4 whitespace-normal text-sm font-semibold text-libryo-gray-900">
    {{ $work->title }}
  </td>
</tr>

@foreach($references->get($work->id) as $reference)
  @include('partials.compilation.my.context-question.applicability-reference-item')
@endforeach

