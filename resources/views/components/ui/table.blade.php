@props(['rows', 'noSearch', 'title', 'whiteHeader' => false, 'noMargins' => false, 'notRounded' => false])

<div class="flex flex-col">
  <div class="overflow-x-auto {{ $noMargins ? '' : 'sm:-mx-6 lg:-mx-8 -my-2' }}">
    <div class="py-2 align-middle inline-block min-w-full {{ $noMargins ? '' : 'sm:px-6 lg:px-8' }}">
      <div class="shadow overflow-hidden border-b border-norma-gray-200 {{ $notRounded ? '' : 'rounded-lg' }}">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-norma-gray-200 bg-white']) }}>
          @if (isset($head) || isset($title))
            <thead class="{{ $whiteHeader ? '' : 'bg-norma-gray-50' }}">
              @if(isset($title) || !($noSearch ?? false))
                <tr>
                  <td colspan="20">
                    <div class="flex items-center justify-start md:justify-between m-4">

                      <div class="font-semibold text-lg px-2">
                        {{ $title ?? '' }}
                      </div>

                      @if(!($noSearch ?? false))
                        <x-ui.form x-ref="form" method="get" class="flex items-center"
                                   x-data="{ search: '{{ request('search', '') }}'}">
                          <input type="hidden" name="page" value="1">

                          <div class="flex items-center relative">
                            <x-ui.input x-model="search" x-ref="search" placeholder="{{ __('interface.search') }}..."
                                        name="search" class="mt-0 rounded-r-none" :value="request('search')" />

                            <button x-show="search.length > 0" style="display:none" type="button"
                                    @click="$refs.search.value = ''; $refs.form.submit();"
                                    class="mt-1 hover:text-primary text-norma-gray-300 transition-colors ease-in-out w-8 flex justify-center items-center absolute right-0 top-0 h-full bg-transparent">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                   stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>

                          <x-ui.button type="submit" class="mt-1 rounded-l-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                          </x-ui.button>
                        </x-ui.form>
                      @endif

                    </div>
                  </td>
                </tr>
              @endif

              <tr>
                {{ $head }}
              </tr>
            </thead>
          @endisset
          <tbody>
            {{ $body }}
          </tbody>

          @if(isset($tfoot))
            {{ $tfoot }}
          @else
            @if (method_exists($rows, 'hasPages') && $rows->hasPages())
              <tfoot>
              <tr>
                <td colspan="20" class="p-4">
                  {{ $rows->withQueryString()->links() }}
                </td>
              </tr>
              </tfoot>
            @endif
          @endif
      </table>
    </div>
  </div>
</div>
</div>
