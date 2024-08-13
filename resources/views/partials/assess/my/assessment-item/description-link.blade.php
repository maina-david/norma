<div class="flex items-center group">

  <div class="shrink-0 flex items-center pt-0.5 mr-2">
    @include('bookmarks.bookmark-icon', ['bookmarks' => $assessmentItem->bookmarks, 'turboKey' => "assess-{$assessmentItem->id}", 'routePrefix' => 'my.assess.bookmarks', 'routePayload' => ['item' => $assessmentItem->id]])
  </div>

  <a href="{{ route('my.assess.assessment-item.show', ['assessmentItem' => $assessmentItem]) }}"
     class="cursor-pointer hover:text-primary flex-grow">
    @include(
        'partials.assess.assessment-item.risk-rating-description',
        ['assessmentItem' => $assessmentItem]
    )
  </a>
  <div>
    @include(
        'partials.assess.assessment-item.domain-breadcrumbs',
        ['assessmentItem' => $assessmentItem]
    )
  </div>
</div>
