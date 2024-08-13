@if ($row->libryos_count > 0)
  @can('my.settings.customer.organisation.manage')
    <a data-turbo="false" data-turbo-frame="_top" target="_blank"
       href="https://my.{{ app(App\Managers\AppManager::class)->requestDomain() }}{{ route('my.settings.libryos.index', ['citations' => [$row->id]], false) }}"
       class="text-primary">
    @endcan()
    {{ trans_choice('customer.libryo.in_count_streams', $row->libryos_count, ['value' => $row->libryos_count]) }}

    @can('my.settings.customer.organisation.manage')
    </a>
  @endcan()
@endif
