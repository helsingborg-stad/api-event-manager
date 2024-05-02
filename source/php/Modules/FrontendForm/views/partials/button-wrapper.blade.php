@if($step->nav)
  <div class="u-display--flex u-justify-content--space-between u-margin__top--4">
  
    @if($step->nav->previous)
      @include('partials.previous', ['href' => $step->nav->previous])
    @endif

    @if($step->state->isFirst && $step->nav->next)
      @include('partials.next', [
        'href' => $step->nav->next, 
        'classList' => ['u-margin__left--auto']
      ])
    @else
      @if($step->nav->next && !$step->state->isLast)
        @include('partials.next', ['href' => $step->nav->next])
      @endif
    @endif

    @if($step->state->isLast)
      @include('partials.submit', ['href' => $step->nav->next])
    @endif

  </div>
@endif