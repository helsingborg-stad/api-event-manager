@if($step->nav)
  <div class="u-display--flex u-justify-content--space-between u-margin__top--4">
    @if($step->nav->next && !$step->state->isLast)
      @include('partials.next', ['href' => $step->nav->next])
    @endif

    @if($step->state->isLast)
      @include('partials.submit', ['href' => $step->nav->next])
    @endif
  </div>
@endif