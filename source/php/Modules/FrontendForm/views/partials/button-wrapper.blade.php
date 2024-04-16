@if($navItems)
  <div class="u-display--flex u-justify-content--space-between u-margin__top--4">
    @foreach($navItems as $navItem)
      {!! $navItem !!}
    @endforeach
  </div>
@else
  <!-- Buttons are missing -->
@endif