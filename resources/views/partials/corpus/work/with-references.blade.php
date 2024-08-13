<div x-data="{ showing: false }">
  <div @click="showing = !showing" class="cursor-pointer text-primary">
    {{ $work->title }}
  </div>
  <div x-show="showing" class="mt-5">
    <ul role="list" class="divide-y divide-libryo-gray-200">
      @foreach ($work->references as $reference)
        <li class="py-2 flex">
          <div class="ml-3">
            <p class="text-sm font-medium text-libryo-gray-900">{{ $reference->citation?->number }}
              {{ $reference->citation?->heading }}</p>
          </div>
        </li>
      @endforeach
    </ul>
  </div>
</div>
