<x-layouts.settings :header="$header" :actions="$actions ?? ''">
  <div class="mt-8 mx-auto grid grid-cols-1 gap-3 lg:grid-flow-col-dense lg:grid-cols-3">
    <div class="space-y-6 lg:col-start-1 lg:col-span-1">
      {{ $leftCol }}
    </div>
    <div class="lg:col-start-2 lg:col-span-2">
      {{ $rightCol }}
    </div>
  </div>
</x-layouts.settings>
