@php
  use App\Enums\Notify\NotificationStatus;use App\Models\Workflows\Board;
  use App\Enums\Notify\LegalUpdatePublishedStatus;
@endphp
<div id="doc-notification-task-{{ $row->id }}" class="relative w-52">

  @if($row->updateCandidate)

    @if (!$row->work_id && !$row->latestLegalUpdate)

      @can('collaborate.corpus.doc.update-candidate.set-notification-task')

        @if(NotificationStatus::NOT_REQUIRED->value != $row->updateCandidate->notification_status)

          <x-ui.form
              method="post"
              :action="route('collaborate.corpus.docs.for-update.create-update', ['doc' => $row->id])"
          >
            <div class="flex justify-center">
              <x-ui.button styling="outline" theme="primary" @click="open = true" type="submit">
                {{ __('corpus.doc.generate_update') }}
              </x-ui.button>
            </div>
          </x-ui.form>

        @endif
      @endcan

    @elseif(!$row->work_id && $row->latestLegalUpdate)

      <div class="">
        @if($row->updateCandidate->task_id)

          <div>
            <x-ui.button type="link" styling="outline" theme="primary" @click="open = true"
                         href="{{ route('collaborate.tasks.show', ['task' => $row->updateCandidate->task_id]) }}">
              {{ __('tasks.view_task') }}
            </x-ui.button>
          </div>

        @elseif($board = Board::forUpdate()->first()?->id)
          @can('collaborate.workflows.create-from-doc')
            <x-ui.form
                method="post"
                :action="route('collaborate.tasks.wizard.store', ['board' => $board, 'stage' => 1])"
            >
              <input type="hidden" name="legal_update_id" value="{{ $row->latestLegalUpdate->id }}">
              <x-ui.button styling="outline" theme="primary" @click="open = true" type="submit">
                {{ __('corpus.doc.generate_workflow') }}
              </x-ui.button>
            </x-ui.form>

            @error('legal_update_id')
            <div class="mt-4 text-negative font-semibold">
              {!! $message !!}
            </div>
            @enderror
          @endcan
        @endif

        <div class="mt-2">
          <x-ui.button class="!p-0" type="link" styling="outline" theme="primary" @click="open = true"
                       href="{{ route('collaborate.legal-updates.show', ['legal_update' => $row->latestLegalUpdate->id]) }}">
            <div class="relative group px-4 py-2">
              <div>{{ __('corpus.doc.view_update') }}</div>

              <div
                  class="font-normal font-sans hidden group-hover:block absolute top-10 left-0 bg-white shadow-lg rounded-lg p-4 w-96 max-w-screen overflow-hidden z-10 text-xs text-libryo-gray-600 border border-libryo-gray-100">
                <table class="w-full">
                  <tr>
                    <th class="text-left">{{ __('corpus.doc.id') }}</th>
                    <td class="text-left">{{ $row->latestLegalUpdate->id }}</td>
                  </tr>
                  <tr>
                    <th class="text-left">{{ __('interface.title') }}</th>
                    <td class="text-left">
                      {{ $row->latestLegalUpdate->title }}
                    </td>
                  </tr>
                  <tr>
                    <th class="text-left">{{ __('workflows.task.status') }}</th>
                    <td class="text-left">{{ LegalUpdatePublishedStatus::tryFrom($row->latestLegalUpdate->status)->label() }}</td>
                  </tr>
                  <tr>
                    <th class="text-left w-28">{{ __('notify.legal_update.release_date') }}</th>
                    <td class="text-left">{{ $row->latestLegalUpdate->release_at?->format('d M Y') ?? '-' }}</td>
                  </tr>
                  <tr>
                    <th class="text-left">{{ __('arachno.source.created_at') }}</th>
                    <td class="text-left">{{ $row->latestLegalUpdate->created_at?->format('d M Y') ?? '-' }}</td>
                  </tr>
                </table>
              </div>
            </div>

          </x-ui.button>

        </div>


      </div>

    @endif

  @endif
</div>

