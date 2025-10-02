<x-ui.dropdown position="left">
    <x-slot:trigger>
        <x-ui.button theme="primary">
            {{ __('actions.create') }}
        </x-ui.button>
    </x-slot:trigger>

    <div
         class="flex flex-col font-normal text-sm w-96 max-w-screen-90 max-h-[70vh] divide-y divide-norma-gray-100 overflow-y-auto custom-scroll">
        @foreach ($boards as $board)
            <a class="px-4 py-2 hover:text-primary"
               href="{{ route('collaborate.tasks.wizard.create', ['board' => $board->id, 'project' => $projectId]) }}">{{ $board->title }}</a>
        @endforeach
    </div>

</x-ui.dropdown>
