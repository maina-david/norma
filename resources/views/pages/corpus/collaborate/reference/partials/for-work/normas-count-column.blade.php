@if ($row->normas_count > 0)
  @can('my.settings.customer.organisation.manage')
    <a data-turbo="false" data-turbo-frame="_top" target="_blank"
       href="https://my.{{ app(App\Managers\AppManager::class)->requestDomain() }}{{ route('my.settings.normas.index', ['citations' => [$row->id]], false) }}"
       class="text-primary">
    @endcan()
    {{ trans_choice('customer.norma.in_count_streams', $row->normas_count, ['value' => $row->normas_count]) }}

    @can('my.settings.customer.organisation.manage')
    </a>
  @endcan()
@endif
