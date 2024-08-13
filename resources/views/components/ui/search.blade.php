<div>
  <form method="GET">
    <x-ui.params-to-form :exclude="['search']" />

    <div class="flex items-center">
      <div class="min-w-64 flex items-center relative">
        <div class="flex-grow">
          <x-ui.input
            name="search"
            label=""
            :placeholder="__('interface.search')"
            class="pr-12"
            value="{{ request('search') }}"
          />
        </div>
      </div>

      <button type="submit" class="bg-dark py-2 px-3 mt-1 -ml-11 text-white rounded-r-md relative">
        <x-ui.icon name="search" />
      </button>
    </div>
  </form>

</div>
