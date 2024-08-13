@props(['domain'])
<div x-data="{ showingChildren: false }" {{ $attributes->merge(['class' => 'pl-5 mb-2']) }}>
  <div class="flex flex-row items-top">
    @if ($domain->children->isEmpty())
      <div class="mr-6">

      </div>
    @else
      <div>
        <div x-show="!showingChildren" @click="showingChildren = !showingChildren">
          <x-ui.icon name="plus-square" size="4" class="mr-2" />
        </div>
        <div x-show="showingChildren" @click="showingChildren = !showingChildren">
          <x-ui.icon name="minus-square" size="4" class="mr-2" />
        </div>
      </div>
    @endif

    <div>
      <span class="hover:text-primary cursor-pointer"
            @click="$dispatch('selected', {{ $domain->id }})">{{ $domain->title }}</span>
    </div>
  </div>
  <div x-show="showingChildren">
    @foreach ($domain->children as $child)
      <x-ontology.legal-domain.my.assess-category-selector-item :domain="$child" />
    @endforeach
  </div>
</div>
