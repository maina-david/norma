@php use App\Enums\Assess\RiskRating; @endphp
<div class="flex flex-col justify-center mr-3 whitespace-nowrap">
  <div class="{{ $row->archived_at ? 'text-negative' : '' }}">{{ $row->description }}</div>
  <div class="sub-row">
    @if ($row->legal_domain_id)
      <div>
        <a
          href="{{ route('collaborate.legal-domains.show', $row->legal_domain_id) }}"
          class="{{ $row->archived_at ? 'text-negative' : 'text-primary' }}"
        >
          {{ $domains[$row->legal_domain_id] ?? '' }}
        </a>
      </div>
    @else
      <div>-</div>
    @endif
    <div class="text-libryo-gray-700">{{ RiskRating::lang()[$row->risk_rating] }}</div>
  </div>
</div>

