<div x-data="{ contentShowing: $persist(false).as('work-panel-content-showing-{{ $work->id }}').using(sessionStorage)}"
     class="mb-3">
  <div class="py-4 px-3 shadow bg-white rounded">
    <div @click="contentShowing = !contentShowing" class="cursor-pointer grid grid-cols-12 gap-4">
      @if ($showFlag)
        <x-corpus.work.my.work-flags :work="$work" :show-multiple-flags="$showMultipleFlags" />
      @endif
      <div class="col-span-10">
        <div class="text-sm">
          @if ($showType)
            <div class="inline-block mr-2 rounded bg-norma-gray-200 text-norma-gray-700 px-1.5 py-0.5 text-xs">
              <div class="flex flex-row items-center">
                <x-ui.icon name="file-alt" size="3" class="mr-1" />
                {{ __('corpus.work.types.' . $work->work_type) }}
              </div>
            </div>
          @endif

          {{ $work->title }}
        </div>
      </div>
      <div class="flex flex-row justify-end items-center">
        <x-ui.icon x-cloak x-show="!contentShowing" name="chevron-down" size="4" />
        <x-ui.icon x-cloak x-show="contentShowing" name="chevron-up" size="4" />
      </div>
    </div>

    <div x-cloak x-show="contentShowing" class="mt-5 p-2 pt-3 bg-norma-gray-50 shadow-inner ">
      {{ $slot }}
    </div>
  </div>
</div>
