<x-ui.input
            label="{{ __('compilation.context_question.without_questions') }}"
            name="no_questions"
            type="checkbox"
            value="{{ $checked }}"
            checkbox-value="yes"
            @change="$dispatch('changed', {{ $checked ? 'null' : '$el.value' }});" />
