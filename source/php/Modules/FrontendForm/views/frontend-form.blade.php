@if (!$hideTitle && !empty($postTitle))
  @typography([
      'element' => 'h4',
      'variant' => 'h2',
      'classList' => ['module-title']
  ])
    {{ $postTitle }}
  @endtypography
@endif

@if($empty !== false)
  {!! $empty !!}
@elseif($error !== false)
  {!! $error !!}
@endif

@if($error === false && $empty === false)

  <!-- Progress bar -->
  @progressBar([
    'value' => $state->percentageCompleted,
    'classList' => [
      'u-margin__bottom--4'
    ],
  ])
  @endprogressBar

  @foreach ($steps as $stepKey => $step)
    @paper(['padding' => 4, 'classList' => ['u-margin__bottom--4']])
      @if($step->state->isPassed)
        <div class="u-display--inline-flex u-color__bg--default u-rounded u-padding__y--05 u-padding__left--1 u-padding__right--2 u-margin__bottom--1">
          @icon([
              'icon' => 'check',
              'size' => 'md'
          ])
          @endicon
          @typography([
              'element' => 'span',
              'variant' => 'meta',
              'classList' => ['u-margin__left--1']
          ])
            {{ $lang->completed }}
          @endtypography
        </div>
      @endif
    
      @typography([
          'element' => 'div',
          'variant' => 'meta',
          'classList' => [
            'c-typography__iteration'
          ]
      ])
        {{ $lang->step }} {{ $step->step }} {{ $lang->of }} {{ $state->totalSteps }}
      @endtypography

      @if ($step->title)
        @typography([
          'element' => 'h5',
          'variant' => 'h3',
          'classList' => ['u-margin--0']
        ])
          {{ $step->title }}
        @endtypography
      @endif

      @if ($step->description)
        @typography([
          'element' => 'p',
          'variant' => 'body',
          'classList' => ['u-margin--0']
        ])
          {!! $step->description !!}
        @endtypography
      @endif

      @if($step->state->isPassed)
        @button([
          'href' => $step->nav->current ?? '#',
          'text' => $lang->edit,
          'color' => 'default',
          'style' => 'filled',
          'icon' => 'edit',
          'reversePositions' => true,
          'classList' => ['u-margin__top--2'],
          'size' => 'sm'
        ])
        @endbutton
      @endif

      @if($step->state->isCurrent)
        <div class="u-margin__top--4">
          {!! $form($step) !!}
        </div>
      @endif

    @endpaper
  @endforeach

  @typography([
      'element' => 'p',
      'variant' => 'meta',
      'classList' => ['u-margin__top--4']
  ])
    {!! $lang->disclaimer !!}
  @endtypography
  

@endif