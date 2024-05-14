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
    @paper([
      'padding' => 4, 
      'classList' => [
        'u-margin__bottom--4', 
        $step->state->isFuture ? 'u-disabled' : '',
      ],
      'id' => 'formstep' . $step->step,
    ])
      
    
      <div class="u-display--flex u-align-content--center">
        <div class="u-flex-grow--1">
        @if($step->state->isPassed)
          <div class="u-display--inline-flex u-color__text--lightest u-color__bg--success u-padding__y--05 u-padding__left--1 u-padding__right--2 u-margin__bottom--1 u-rounded--full">
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

        @if ($step->description && $step->state->isCurrent)
          @typography([
            'element' => 'p',
            'variant' => 'body',
            'classList' => ['u-margin--0']
          ])
            {!! $step->description !!}
          @endtypography
        @endif

        @if($step->state->isCurrent)
          <div class="u-margin__top--4">
            {!! $form($step) !!}
          </div>
        @endif
      </div>

        @if($step->state->isPassed)
          <div class="u-margin--auto">
            @button([
              'href' => $step->nav->current ?? '#',
              'text' => $lang->edit,
              'color' => 'default',
              'style' => 'basic',
              'icon' => 'edit',
              'reversePositions' => true,
              'classList' => [],
              'size' => 'sm'
            ])
            @endbutton
          </div>
        @endif

        
      </div>
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