@if (!$hideTitle && !empty($postTitle))
  @typography([
      'element' => 'h4',
      'variant' => 'h2',
      'classList' => ['module-title']
  ])
    {{ $postTitle }}
  @endtypography
@endif

@if($error != false)
  {!! $error !!}
@endif

@if($error === false)
  @foreach ($steps as $groupId => $step)
    @paper(['padding' => 4, 'classList' => ['u-margin__bottom--4']])
      <div class="u-display--flex flex-wrap items-center justify-between">
        
        @if ($step->title)
          @typography([
              'element' => 'h5',
              'variant' => 'h3',
              'classList' => ['u-margin__bottom--4']
          ])
            {{ $step->title }}
          @endtypography
        @endif

        @if($step->isPassed)
          @icon([
              'icon' => 'check',
              'size' => 'sm',
              'classList' => ['text-green-500']
          ])
          @endicon
        @endif
      </div>

      @if($step->isCurrent)
        {!! $form() !!}
      @endif

    @endpaper
  @endforeach
@endif