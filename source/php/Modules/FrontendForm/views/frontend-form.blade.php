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
      <div class="u-display--flex u-justify-content--space-between">
        
        @if ($step->title)
          @typography([
              'element' => 'h5',
              'variant' => 'h3',
              'classList' => []
          ])
            {{ $step->title }}
          @endtypography
        @endif

        @if($step->isPassed)
          @icon([
              'icon' => 'check',
              'size' => 'md',
              'classList' => ['c-icon--emblem', 'u-color__bg--success', 'u-color__text--lightest']
          ])
          @endicon
        @endif
      </div>

      @if ($step->description)
        @typography([
            'element' => 'p',
            'variant' => 'body',
            'classList' => ['u-margin--0']
        ])
          {{ $step->description }}
        @endtypography
      @endif

      @if($step->isCurrent)
        <div class="u-margin__top--4">
          {!! $form() !!}
        </div>
      @endif

    @endpaper
  @endforeach
@endif