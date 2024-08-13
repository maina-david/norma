<div class="grid grid-cols-5 gap-2">
  <div>
    <x-ui.input :value="old('prefix', $resource->prefix ?? '')" name="prefix"
                label="{{ __('compilation.context_question.prefix') }}" />
  </div>

  <div>
    <x-ui.input :value="old('predicate', $resource->predicate ?? '')" name="predicate"
                label="{{ __('compilation.context_question.predicate') }}" />
  </div>

  <div>
    <x-ui.input :value="old('pre_object', $resource->pre_object ?? '')" name="pre_object"
                label="{{ __('compilation.context_question.pre_object') }}" />
  </div>

  <div>
    <x-ui.input :value="old('object', $resource->object ?? '')" name="object"
                label="{{ __('compilation.context_question.object') }}" />
  </div>



  <div>
    <x-ui.input :value="old('post_object', $resource->post_object ?? '')" name="post_object"
                label="{{ __('compilation.context_question.post_object') }}" />
  </div>

</div>
<div class="w-full md:w-1/2">
  <x-ui.input type="select" :value="old('category_id', $resource->category_id ?? '')" name="category_id"
              label="{{ __('compilation.context_question.category.category') }}"
              :options="\App\Enums\Compilation\ContextQuestionCategory::lang()" />
</div>


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.assessment-items.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
